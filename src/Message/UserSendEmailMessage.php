<?php

namespace App\Message;

use App\Entity\AuthMail;

class UserSendEmailMessage
{
    const VERIFY = 'verify';
    const ABOUT_US = 'about_us';
    const RECOVERY = 'recovery';

    public function __construct(
        protected AuthMail|null $authMail,
        protected string        $type,
        protected string $locale,
    )
    {
    }

    /**
     * @return AuthMail|null
     */
    public function getAuthMail(): ?AuthMail
    {
        return $this->authMail;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

}