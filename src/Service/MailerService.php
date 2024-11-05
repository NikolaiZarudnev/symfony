<?php

namespace App\Service;

use App\Entity\AuthMail;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MailerService
{
    public function __construct(
        private readonly MailerInterface     $mailer,
        private readonly TranslatorInterface $translator,
    )
    {
    }

    public function sentVerifyUser(AuthMail $authMail, string $locale): void
    {
        $this->sendEmail(
            to: $authMail->getUser()->getEmail(),
            subject: $this->translator->trans('subject.verify', domain: 'emails', locale: $locale),
            template: 'security/emails/auth_mail.html.twig',
            context: [
                'expiration_date' => $authMail->getExpirationDate(),
                'username' => $authMail->getUser()->getUserIdentifier(),
                'link' => $authMail->getLink(),
                'user_locale' => $locale,
            ]
        );
    }

    public function sentRecoverPassword(AuthMail $authMail, string $locale): void
    {
        $this->sendEmail(
            to: $authMail->getUser()->getEmail(),
            subject: $this->translator->trans('subject.recovery', domain: 'emails', locale: $locale),
            template: 'security/emails/recovery_password.html.twig',
            context: [
                'expiration_date' => $authMail->getExpirationDate(),
                'username' => $authMail->getUser()->getUserIdentifier(),
                'link' => $authMail->getLink(),
                'user_locale' => $locale,
            ]
        );
    }

    public function sentAboutUs(AuthMail $authMail, string $locale): void
    {
        $this->sendEmail(
            to: $authMail->getUser()->getEmail(),
            subject: $this->translator->trans('subject.about.us', domain: 'emails', locale: $locale),
            template: 'security/emails/about_us.html.twig',
            context: [
                'username' => $authMail->getUser()->getUserIdentifier(),
                'link' => $authMail->getLink(),
                'user_locale' => $locale,
            ]
        );
    }

    private function sendEmail($to, $subject, $template, $context = [], $from = 'nikolaizarudnrv@gmail.com'): void
    {
        $email = (new TemplatedEmail())
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->htmlTemplate($template)
            ->context($context);

        try {
            $this->mailer->send($email);
        } catch (\Throwable $e) {
            dd($e);
        }
    }
}