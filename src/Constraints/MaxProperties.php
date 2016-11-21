<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard;
use League\JsonGuard\ValidationError;

class MaxProperties implements PropertyConstraint
{
    const KEYWORD = 'maxProperties';

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
            self::KEYWORD,
            $value,
            $pointer,
            ['max_properties' => $parameter]
        );
    }
}
