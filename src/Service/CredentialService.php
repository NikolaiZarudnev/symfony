<?php

namespace App\Service;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\AuthorizationHeaderTokenExtractor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Contracts\Translation\TranslatorInterface;

class CredentialService
{
    public function __construct(
        private readonly JWTEncoderInterface $JWTEncoder,
        private readonly TranslatorInterface $translator,
    ) {}
    public function getCredentials(Request $request): false|null|string
    {
        $extractor = new AuthorizationHeaderTokenExtractor(
            'Bearer',
            'Authorization'
        );

        $token = $extractor->extract($request);

        if (!$token) {
            return null;
        }
        return $token;
    }

    public function hasCredentials(Request $request): bool
    {
        $extractor = new AuthorizationHeaderTokenExtractor(
            'Bearer',
            'Authorization'
        );

        if ($extractor->extract($request)) {
            return true;
        }
        return false;
    }


    public function getJWTData($token): ?array
    {
        if ($token) {
            try {
                return $this->JWTEncoder->decode($token);
            } catch (JWTDecodeFailureException $e) {
                throw new CustomUserMessageAuthenticationException($this->translator->trans('invalid.token'));
            }
        }

        return null;
    }
}