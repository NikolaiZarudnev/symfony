<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\User;
use App\Form\Objects\SearchObject;
use App\Form\Type\AccountType;
use App\Form\Type\SearchObjectType;
use App\Model\AccountModel;
use App\Repository\AccountRepository;
use App\Repository\PhoneRepository;
use App\Security\AccountVoter;
use App\Serializer\AccountNormalizer;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class AccountController extends AbstractController
{
    public function __construct(
        private readonly AccountModel           $accountModel,
        private readonly AccountRepository      $accountRepository,
        private readonly FileUploader           $fileUploader,
        private readonly PaginatorInterface     $paginator,
        private readonly EntityManagerInterface $entityManager,
        private readonly SerializerInterface    $serializer,
        private readonly AccountNormalizer      $accountNormalizer,
    ) {}

    public function create(Request $request, $id = null): Response
    {
        $this->entityManager->getFilters()->disable('soft_deleteable');

        $routeName = $request->attributes->get('_route');

        if ($routeName === 'app_account_create') {
            $options['mode'] = 'create';
            $account = new Account();
        } else {
            $options['mode'] = 'edit';
            if (!$id) {
                throw new NotFoundHttpException();
            }
            if (is_null($account = $this->accountRepository->findOneBySlug($id))) {
                $account = $this->accountRepository->find($id) ?? throw new NotFoundHttpException();
            }

            $this->denyAccessUnlessGranted(AccountVoter::EDIT, $account);
        }

        $form = $this->createForm(AccountType::class, $account, $options);

        if ($options['mode'] === 'edit') {
            $form->get('address')->get('street')->setData($account->getAddress());
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $account = $form->getData();

            $file = $form->get('image')->getData();
            $fileName = $this->fileUploader->uploadImage($file, $account->getImage());

            if ($options['mode'] === 'create') {
                $this->accountModel->create($account, ['image' => $fileName]);
            } else {
                $streetExploded = $form->get('address')->get('street')->getData();
                $this->accountModel->update($account, ['streetExploded' => $streetExploded, 'image' => $fileName]);
            }

            return $this->redirectToRoute('app_account_edit', [
                'id' => $account->getId(),
                'image' => $account->getImage(),
            ]);
        }

        return $this->render('account/create.html.twig', [
            'form' => $form,
            'image' => $account->getImage(),
        ]);
    }

    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $search = new SearchObject();

        $searchForm = $this->createForm(SearchObjectType::class, $search,
            [
                'method' => 'GET'
            ]);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $search = $searchForm->getData();
        }

        $user = null;
        if (!$this->isGranted(User::ROLE_MANAGER)) {
            $user = $this->getUser();
        }

        $accounts = $this->accountRepository->findBySearch($search, $user);

        $searchJson = $this->serializer->serialize($search, 'json');

        return $this->render('account/index.html.twig', [
            'accounts' => $accounts,
            'searchForm' => $searchForm,
            'searchJson' => $searchJson,
        ]);
    }

    public function show(PhoneRepository $phoneRepository, Request $request, $id): Response
    {
        if (is_null($account = $this->accountRepository->findOneBySlug($id))) {
            $account = $this->accountRepository->find($id) ?? throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted(AccountVoter::SHOW, $account);

        $query = $phoneRepository->findByAccountIdQuery($account->getId());
        $pagination = $this->paginator->paginate(
            $query,
            $request->query->getInt('page', 1), /*page number*/
            1 /*limit per page*/
        );

        return $this->render('account/show.html.twig', [
            'account' => $account,
            'pagination' => $pagination,
        ]);
    }

    public function delete($id): Response
    {
        $this->entityManager->getFilters()->enable('soft_deleteable');
        if (is_null($account = $this->accountRepository->findOneBySlug($id))) {
            $account = $this->accountRepository->find($id) ?? throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted(AccountVoter::DELETE, $account);

        $this->fileUploader->remove($account->getImage());
        $this->accountModel->delete($account);
        return $this->redirectToRoute('app_account');
    }

    public function apiDelete($id): Response
    {
        if (is_null($account = $this->accountRepository->findOneBySlug($id))) {
            $account = $this->accountRepository->find($id) ?? throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted(AccountVoter::DELETE, $account);

        $this->fileUploader->remove($account->getImage());
        $this->accountModel->delete($account);
        return new JsonResponse();
    }

    public function downloadAccountsCsv(string $searchJson): Response
    {
        $search = $this->serializer->deserialize($searchJson, SearchObject::class, 'json');

        $accountsNormalized = $this->accountNormalizer->getNormalizedArrayBySearch($search);

        $csvContent = $this->serializer->serialize($accountsNormalized, 'csv', ['groups' => ['account'], AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true]);

        $fileName = 'accounts.csv';
        $pathFile = $this->fileUploader->uploadFile($fileName, $csvContent);

        $response = new BinaryFileResponse($pathFile);

        return $response;
    }

    public function downloadAccountsPdf(Request $request, string $searchJson)
    {
        $search = $this->serializer->deserialize($searchJson, SearchObject::class, 'json');

        $accountsNormalized = $this->accountNormalizer->getNormalizedArrayBySearch($search);

        $dompdf = new Dompdf([
            'defaultFont' => 'DejaVu Serif',

        ]);

        $contents = $this->renderView('pdf/account_list.html.twig', [
            'accounts' => $accountsNormalized,
        ]);
        $contents .= '<style>' . file_get_contents("/home/nikolay/PhpstormProjects/symfony/public/css/bootstrap.css") . '</style>';

        $contents .= "<style>body { font-family: DejaVu Sans }</style>";

        $dompdf->loadHtml($contents);

        $dompdf->setPaper('A4', 'landscape');

        $dompdf->render();

        $dompdf->stream('accounts.pdf');

    }

}
