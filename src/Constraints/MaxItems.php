<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard;
use League\JsonGuard\Assert;
use League\JsonGuard\ValidationError;

class MaxItems implements PropertyConstraint
{
    const KEYWORD = 'maxItems';

    /**
     * {@inheritdoc}
     */
    public static function validate($value, $parameter, $pointer = null)
    {
        Assert::type($parameter, 'integer', self::KEYWORD, $pointer);
        Assert::nonNegative($parameter, self::KEYWORD, $pointer);

        if (!is_array($value) || count($value) <= $parameter) {
            return null;
        }

        return new ValidationError(
            'Array does not contain less than {max_items} items',
            self::KEYWORD,
            $value,
            $pointer,
            ['max_items' => $parameter]
        );
    }
}
