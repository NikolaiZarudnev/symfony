<?php

namespace App\Service;

use Symfony\Component\Translation\LocaleSwitcher;

class LocaleService
{
    public function __construct(
        private LocaleSwitcher $localeSwitcher,
    ) {
    }

    public function getLocale(): string
    {
        return $this->localeSwitcher->getLocale();

    }

    public function setLocale(string $locale): void
    {
        $this->localeSwitcher->setLocale($locale);

    }
    public function getLocaleFromUrl(string $link): string
    {
        $linkArr = explode('/', $link);

        if (in_array('ru', $linkArr)) {
            return 'ru';
        } else {
            return 'en';
        }
    }
}