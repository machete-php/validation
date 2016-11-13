<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard;
use League\JsonGuard\ErrorCode;
use League\JsonGuard\ValidationError;

class MinLength implements PropertyConstraint
{
    /**
     * {@inheritdoc}
     */
    public static function validate($value, $parameter, $pointer = null)
    {
        if (!is_string($value) || JsonGuard\strlen($value) >= $parameter) {
            return null;
        }

        return new ValidationError(
            'String is not at least {min_length} characters long',
            ErrorCode::INVALID_MIN_LENGTH,
            $value,
            $pointer,
            ['min_length' => $parameter]
        );
    }
}
