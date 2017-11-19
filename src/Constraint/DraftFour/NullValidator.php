<?php

namespace League\JsonGuard\Constraint\DraftFour;

use League\JsonGuard\Assert;
use League\JsonGuard\ConstraintInterface;
use League\JsonGuard\Validator;
use function League\JsonGuard\error;

final class NullValidator implements ConstraintInterface
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, $parameter, Validator $validator)
    {
        return null;
    }
}
