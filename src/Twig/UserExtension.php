<?php

namespace App\Twig;

use App\Entity\User;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class UserExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('role', [$this, 'formatRole']),
        ];
    }

    public function formatRole(string $role): string
    {
        $role = str_replace(['ROLE_', '_'], ['', ' '], $role);
        return $role;
    }
}