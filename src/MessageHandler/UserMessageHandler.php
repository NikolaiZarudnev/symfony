<?php

namespace App\MessageHandler;

use App\Message\UserSendEmailMessage;
use App\Service\MailerService;
use Liip\ImagineBundle\Exception\Config\Filter\NotFoundException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UserMessageHandler
{
    public function __construct(
        private readonly MailerService $mailerService,
    )
    {
    }

    public function __invoke(UserSendEmailMessage $message): void
    {
        if (!in_array($message->getType(), [
            UserSendEmailMessage::VERIFY,
            UserSendEmailMessage::ABOUT_US,
            UserSendEmailMessage::RECOVERY
        ])) {
            throw new NotFoundException();
        }

        switch ($message->getType()) {
            case UserSendEmailMessage::VERIFY:
                $this->mailerService->sentVerifyUser($message->getAuthMail(), $message->getLocale());
                break;
            case UserSendEmailMessage::ABOUT_US:
                $this->mailerService->sentAboutUs($message->getAuthMail(), $message->getLocale());
                break;
            case UserSendEmailMessage::RECOVERY:
                $this->mailerService->sentRecoverPassword($message->getAuthMail(), $message->getLocale());
                break;
        }
    }
}