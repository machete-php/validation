<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard;
use League\JsonGuard\ErrorCode;
use League\JsonGuard\ValidationError;

class MaxProperties implements PropertyConstraint
{
    /**
     * {@inheritdoc}
     */
    public static function validate($value, $parameter, $pointer = null)
    {
        if (!is_object($value) || count(get_object_vars($value)) <= $parameter) {
            return null;
        }

        return new ValidationError(
            'Object does not contain less than {max_properties} properties',
            ErrorCode::MAX_PROPERTIES_EXCEEDED,
            $value,
            $pointer,
            ['max_properties' => $parameter]
        );
    }
}
