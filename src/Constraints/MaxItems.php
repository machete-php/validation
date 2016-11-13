<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard;
use League\JsonGuard\ErrorCode;
use League\JsonGuard\ValidationError;

class MaxItems implements PropertyConstraint
{
    /**
     * {@inheritdoc}
     */
    public static function validate($value, $parameter, $pointer = null)
    {
        if (!is_array($value) || count($value) <= $parameter) {
            return null;
        }

        return new ValidationError(
            'Array does not contain less than {max_items} items',
            ErrorCode::MAX_ITEMS_EXCEEDED,
            $value,
            $pointer,
            ['max_items' => $parameter]
        );
    }
}
