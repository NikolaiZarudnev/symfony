<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AccountExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('email', [$this, 'formatEmail']),
        ];
    }

    public function formatEmail(string $email): string
    {
        $email = preg_replace('/(?<=@).*?(?=\.)/', '*', $email);
        return $email;
    }
}