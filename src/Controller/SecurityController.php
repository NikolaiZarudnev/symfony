<?php

namespace App\Controller;

use App\Entity\User;
use App\Event\UserAuthMailEvent;
use App\Form\Type\RecoveryPasswordFormType;
use App\Form\Type\RegistrationFormType;
use App\Message\UserSendEmailMessage;
use App\Model\AuthModel;
use App\Model\UserModel;
use App\Repository\AuthMailRepository;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


class SecurityController extends AbstractController
{
    public function __construct(
        private readonly AuthMailRepository       $authMailRepository,
        private readonly UserRepository           $userRepository,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly MessageBusInterface      $messageBus,
        private readonly UserModel                $userModel,
        private readonly AuthModel                $authModel,
        private readonly JWTTokenManagerInterface $JWTManager,
    )
    {
    }

    public function register(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->userModel->update($user, [
                'password' => $form->get('plainPassword')->getData(),
                'isActive' => false,
            ]);

            $authMail = $this->authModel->create($user, 'app_security_user_activate');
            $this->messageBus->dispatch(new UserSendEmailMessage($authMail, UserSendEmailMessage::VERIFY, $request->getLocale()));

            $authMail = $this->authModel->create($user, 'app_homepage');
            $this->messageBus->dispatch(new UserSendEmailMessage($authMail, UserSendEmailMessage::ABOUT_US, $request->getLocale()));

            return $this->redirectToRoute('app_homepage');
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    public function apiLogin(Request $request, #[CurrentUser] ?User $user): Response
    {
        if (null === $user) {
            return $this->json([
                'message' => 'missing credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }
        $token = $this->JWTManager->create($user);
        return $this->json([
            'user' => $user->getUserIdentifier(),
            'token' => $token,
        ]);
    }

    public function logout(Security $security): Response
    {
        // logout the user in on the current firewall
        $response = $security->logout();

        // you can also disable the csrf logout
        //$response = $security->logout(false);

        return $this->redirectToRoute('app_homepage');
    }

    public function activateUser(string $code): Response
    {
        $authMail = $this->authMailRepository->findOneBy(['code' => $code]) ?? throw new NotFoundHttpException();

        $event = new UserAuthMailEvent(user: null, authMail: $authMail, action: 'activateUser', params: null);
        $this->dispatcher->dispatch($event, UserAuthMailEvent::NAME);

        return $this->redirectToRoute('app_homepage');
    }

    public function recoverPassword(Request $request, ?string $code): Response
    {
        $user = new User();
        if (is_null($code)) {
            $options['mode'] = 'email';
        } else {
            $options['mode'] = 'newPassword';
        }
        $form = $this->createForm(RecoveryPasswordFormType::class, $user, $options);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($code) {
                $authMail = $this->authMailRepository->findOneBy(['code' => $code]) ?? throw new NotFoundHttpException();

                $event = new UserAuthMailEvent(user: null, authMail: $authMail, action: 'recoverPassword', params: ['password' => $form->get('plainPassword')->getData()]);
                $this->dispatcher->dispatch($event, UserAuthMailEvent::NAME);
            } else {
                $user = $this->userRepository->findOneBy(['email' => $form->get('email')->getData()]) ?? throw new NotFoundHttpException();

                $authMail = $this->authModel->create($user, 'app_security_recovery_password');
                $this->messageBus->dispatch(new UserSendEmailMessage($authMail, UserSendEmailMessage::RECOVERY, $request->getLocale()));
            }

            return $this->redirectToRoute('app_homepage');
        }
        return $this->render('security/recovery_password.html.twig', [
            'form' => $form,
        ]);

    }
}