<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard;
use League\JsonGuard\ErrorCode;
use League\JsonGuard\ValidationError;

class MaxLength implements PropertyConstraint
{
    /**
     * {@inheritdoc}
     */
    public static function validate($value, $parameter, $pointer = null)
    {
        if (!is_string($value) || JsonGuard\strlen($value) <= $parameter) {
            return null;
        }

        return new ValidationError(
            'String is not at most {max_length} characters long',
            ErrorCode::INVALID_MAX_LENGTH,
            $value,
            $pointer,
            ['max_length' => $parameter]
        );
    }
}
