<?php

namespace App\Validator;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[\Attribute]
class UniqueValueInEntity extends Constraint
{
    public $message = 'There is already an object with this field';
    protected string $entityClass;
    protected string $field;

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }

    public function getRequiredOptions()
    {
        return ['entityClass', 'field'];
    }

    public function validatedBy(): string
    {
        return static::class.'Validator';
    }

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }
}
