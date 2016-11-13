<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard;
use League\JsonGuard\ErrorCode;
use League\JsonGuard\ValidationError;

class UniqueItems implements PropertyConstraint
{
    /**
     * {@inheritdoc}
     */
    public static function validate($value, $parameter, $pointer = null)
    {
        if (!is_array($value)) {
            return null;
        }

        if (count($value) === count(array_unique(array_map('serialize', $value)))) {
            return null;
        }

        return new ValidationError(
            'Array {value} is not unique.',
            ErrorCode::NOT_UNIQUE_ITEM,
            $value,
            $pointer,
            ['value' => $value]
        );
    }
}
