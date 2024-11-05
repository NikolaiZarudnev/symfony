<?php

namespace App\Form\DataTransformer;

use App\Entity\Address;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Contracts\Translation\TranslatorInterface;
use function PHPUnit\Framework\isEmpty;

class StreetTransformer implements DataTransformerInterface
{
    public function __construct(
        private readonly TranslatorInterface $translator,

    )
    {
    }

    /**
     * Transforms an object (streets) to a string (street).
     *
     * @param Address|null $address
     */
    public function transform($address): string
    {
        return $address ? $address->getStreet1() . ', ' . $address->getStreet2() : '';
    }

    /**
     * Transforms a string (street) to an array separeted by ','.
     *
     * @param string $street
     * @throws TransformationFailedException if street1 is null.
     */
    public function reverseTransform($street): ?array
    {
        if (!$street) {
            return null;
        }

        $streetExploded = explode(',', trim($street));
        $streetExploded = array_map('trim', $streetExploded);
        if (empty($streetExploded[0])) {
            $privateErrorMessage = sprintf('street is null', $street);
            $publicErrorMessage = $this->translator->trans('The given "{{ value }}" value is not a valid street.', domain: 'exceptions');

            $failure = new TransformationFailedException($privateErrorMessage);
            $failure->setInvalidMessage($publicErrorMessage, [
                '{{ value }}' => $street,
            ]);

            throw $failure;
        }
        if (!isset($streetExploded[1])) {
            $streetExploded[1] = '';
        }


        return $streetExploded;
    }
}