<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard;
use League\JsonGuard\Assert;
use League\JsonGuard\ValidationError;

class MinProperties implements PropertyConstraint
{
    const KEYWORD = 'minProperties';

    /**
     * {@inheritdoc}
     */
    public static function validate($value, $min, $pointer = null)
    {
        Assert::type($min, 'integer', self::KEYWORD, $pointer);
        Assert::nonNegative($min, self::KEYWORD, $pointer);

        if (!is_object($value) || count(get_object_vars($value)) >= $min) {
            return null;
        }

        return new ValidationError(
            'Object does not contain at least {min_properties} properties',
            self::KEYWORD,
            $value,
            $pointer,
            ['min_properties' => $min]
        );
    }
}
