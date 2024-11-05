<?php

namespace App\Serializer;

use App\Entity\Account;
use App\Entity\User;
use App\Repository\AccountRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class AccountNormalizer implements NormalizerInterface
{
    public function __construct(
        private ObjectNormalizer $normalizer,
        private readonly Security $security,
        private readonly AccountRepository      $accountRepository,
    )
    {
    }

    public function normalize($account, string $format = null, array $context = []): array
    {
        $data = $this->normalizer->normalize($account, $format, $context);

        $data = $this->moveUp($data, ['fullName' => $data['firstName'] . ' ' . $data['lastName']], 1);

        if ($data['address']) {
            $data['address'] = $this->moveUp($data['address'], ['street' => $data['address']['street1'] . ', ' . $data['address']['street2']], 5);
        }

        unset($data['firstName']);
        unset($data['lastName']);
        unset($data['address']['street1']);
        unset($data['address']['street2']);

        return $data;
    }

    public function getNormalizedArrayBySearch($search): array
    {
        $user = null;
        if (!$this->security->isGranted(User::ROLE_MANAGER)) {
            $user = $this->security->getUser();
        }

        $accounts = $this->accountRepository->findBySearch($search, $user);

        $normalizedContent = [];

        foreach ($accounts as $account) {
            $normalizedContent[] = $this->normalize($account, null, ['groups' => ['account'], AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true]);
        }
        return $normalizedContent;
    }

    /**
     * @param array $data target array
     * @param array $item new item
     * @param int $index index of new item in array
     * @return array
     */
    private function moveUp(array $data, array $item, int $index): array
    {
        $res = [];
        if ((count($data) > $index) && ($index > 0)) {
            $res = array_slice($data, 0, $index, true) +
                $item +
                array_slice($data, $index, count($data) - 1, true);;
        }

        return $res;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Account;
    }
}