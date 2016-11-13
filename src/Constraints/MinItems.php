<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard;
use League\JsonGuard\ErrorCode;
use League\JsonGuard\ValidationError;

class MinItems implements PropertyConstraint
{
    /**
     * {@inheritdoc}
     */
    public static function validate($value, $parameter, $pointer = null)
    {
        if (!is_array($value) || count($value) >= $parameter) {
            return null;
        }

        return new ValidationError(
            'Array does not contain more than {min_items} items',
            ErrorCode::INVALID_MIN_COUNT,
            $value,
            $pointer,
            ['min_items' => $parameter]
        );
    }
}
