<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard;
use League\JsonGuard\ValidationError;

class MaxLength implements PropertyConstraint
{
    const KEYWORD = 'maxLength';

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
            self::KEYWORD,
            $value,
            $pointer,
            ['max_length' => $parameter]
        );
    }
}
