<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard;
use League\JsonGuard\ValidationError;

class MinItems implements PropertyConstraint
{
    const KEYWORD = 'minItems';

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
            self::KEYWORD,
            $value,
            $pointer,
            ['min_items' => $parameter]
        );
    }
}
