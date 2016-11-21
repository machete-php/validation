<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard\ValidationError;
use League\JsonGuard;

class Enum implements PropertyConstraint
{
    const KEYWORD = 'enum';

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
            self::KEYWORD,
            $value,
            $pointer,
            ['choices' => $parameter, 'value' => $value]
        );
    }
}
