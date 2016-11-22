<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard;
use League\JsonGuard\Assert;
use League\JsonGuard\ValidationError;

class UniqueItems implements PropertyConstraint
{
    const KEYWORD = 'uniqueItems';

    /**
     * {@inheritdoc}
     */
    public static function validate($value, $parameter, $pointer = null)
    {
        Assert::type($parameter, 'boolean', self::KEYWORD, $pointer);

        if (!is_array($value) || $parameter === false) {
            return null;
        }

        if (count($value) === count(array_unique(array_map('serialize', $value)))) {
            return null;
        }

        return new ValidationError(
            'Array {value} is not unique.',
            self::KEYWORD,
            $value,
            $pointer,
            ['value' => $value]
        );
    }
}
