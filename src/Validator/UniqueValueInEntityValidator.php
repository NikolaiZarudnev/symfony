<?php

namespace App\Validator;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueValueInEntityValidator extends ConstraintValidator
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    )
    {
    }

    public function validate($value, Constraint $constraint): void
    {
        $entityRepository = $this->em->getRepository($constraint->getEntityClass());

        $searchResults = $entityRepository->findBy([
            $constraint->getField() => $value->getEmail(),
        ]);

        if (count($searchResults) > 0 && (is_null($value->getid()) || !$this->isCurrentObject($searchResults, $value->getId()))) {
            $this->context->buildViolation($constraint->message)
                ->atPath('email')
                ->addViolation();
        }

    }

    private function isCurrentObject($searchResults, $id): bool
    {
        foreach ($searchResults as $object) {
            if ($object->getId() == $id) {
                return true;
            }
        }
        return false;
    }
}