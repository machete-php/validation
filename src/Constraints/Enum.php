<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard\ErrorCode;
use League\JsonGuard\ValidationError;
use League\JsonGuard;

class Enum implements PropertyConstraint
{
    /**
     * {@inheritdoc}
     */
    public static function validate($value, $parameter, $pointer = null)
    {
        if (!is_array($parameter)) {
            return null;
        }

        if (in_array($value, $parameter, true)) {
            return null;
        }

        return new ValidationError(
            'Value {value} is not one of: {choices}',
            ErrorCode::INVALID_ENUM,
            $value,
            $pointer,
            ['choices' => $parameter, 'value' => $value]
        );
    }
}
