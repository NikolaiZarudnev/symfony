<?php

namespace App\Controller;

use App\DTO\UserDTO;
use App\Entity\User;
use App\Model\UserModel;
use App\Repository\UserRepository;
use App\Service\LocaleService;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{
    public function __construct(
        private readonly UserRepository      $userRepository,
        private readonly UserModel           $userModel,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface  $validator,
        private readonly LocaleService       $localeService,
    )
    {
    }

    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $this->denyAccessUnlessGranted(User::ROLE_ADMIN);

        $query = $this->userRepository->findAllQuery();

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        $count = $this->userRepository->getCountUsers();

        return $this->render('user/index.html.twig', [
            'pagination' => $pagination,
            'count' => $count,
        ]);
    }

    public function create(?int $id)
    {
        $this->denyAccessUnlessGranted(User::ROLE_ADMIN);

        if ($id) {
            $user = $this->userRepository->find($id);
        } else {
            $user = new User();
        }

        return $this->render('user/create.html.twig', [
            'userId' => $id,
            'user' => $user,
        ]);
    }

    public function apiCreate(Request $request, ?int $id): JsonResponse
    {

        $locale = $request->get('userLocale');
        if ($locale) {
            $request->setLocale($locale);
            $this->localeService->setLocale($locale);
        }

        $this->denyAccessUnlessGranted(User::ROLE_ADMIN);

        if ($id) {
            $user = $this->userRepository->find($id);
        } else {
            $user = new User();
        }

        $userJson = $request->get('userJson');

        if ($userJson) {
            /** @var UserDTO $userDTO */
            $userDTO = $this->serializer->deserialize($userJson, UserDTO::class, 'json');
            if ($id) {
                $userDTO->setId($id);
            }

            $errors = (array)$this->validator->validate($userDTO);
            $errors = array_shift($errors);
            if (count($errors) > 0) {
                $errorsJson = $this->serializer->serialize($errors, 'json');

                return new JsonResponse($errorsJson, 500, json: true);
            }

            $this->userModel->createByDTO($user, $userDTO);

            $response = [
                'id' => $user->getId(),
                '_locale' => $request->getLocale(),
                'url' => $this->generateUrl('app_user_edit', [
                    '_locale' => $request->getLocale(),
                    'id' => $user->getId()
                ]),
                'message' => $id ? 'User updated' : 'User created',
            ];
            return new JsonResponse($response);
        } else {
            return new JsonResponse("userJson is null", 400);
        }
    }

    public function changeActive(Request $request, int $id): JsonResponse
    {
        $this->denyAccessUnlessGranted(User::ROLE_ADMIN);

        $user = $this->userRepository->find($id) ?? throw new NotFoundHttpException();

        $this->userModel->update($user, ['isActive' => !$user->getIsActive()]);

        $response = ['isActive' => $user->getIsActive()];

        return new JsonResponse($response);
    }

    public function apiSearch(Request $request): JsonResponse
    {
        $locale = $request->get('userLocale');
        if ($locale) {
            $request->setLocale($locale);
            $this->localeService->setLocale($locale);
        }

        $this->denyAccessUnlessGranted(User::ROLE_ADMIN);

        $searchJson = $request->get('searchJson');

        $search = json_decode($searchJson, true);
        $search = array_shift($search);


        $users = $search ? $this->userRepository->findBySearch($search) : null;

        $response = [
            'locale' => $request->getLocale(),
            'users' => $users,
        ];

        $resJson = $this->serializer->serialize($response, 'json', ['groups' => ['userDTO'], AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true]);

        return new JsonResponse($resJson, json: true);
    }
}
