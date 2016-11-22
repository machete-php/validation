<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard;
use League\JsonGuard\Assert;
use League\JsonGuard\ValidationError;

class Min implements ParentSchemaAwarePropertyConstraint
{
    const KEYWORD           = 'minimum';
    const EXCLUSIVE_KEYWORD = 'exclusiveMinimum';

    /**
     * {@inheritdoc}
     */
    public static function validate($value, $schema, $parameter, $pointer = null)
    {
        Assert::type($parameter, 'number', self::KEYWORD, $pointer);

        if (isset($schema->exclusiveMinimum) && $schema->exclusiveMinimum === true) {
            return self::validateExclusiveMin($value, $parameter, $pointer);
        }

        return self::validateMin($value, $parameter, $pointer);
    }

    /**
     * @param mixed       $value
     * @param mixed       $parameter
     * @param string|null $pointer
     *
     * @return \League\JsonGuard\ValidationError|null
     */
    public static function validateMin($value, $parameter, $pointer = null)
    {
        if (!is_numeric($value) ||
            JsonGuard\compare($value, $parameter) === 1 || JsonGuard\compare($value, $parameter) === 0) {
            return null;
        }

        return new ValidationError(
            'Number {value} is not at least {min}',
            self::KEYWORD,
            $value,
            $pointer,
            ['value' => $value, 'min' => $parameter]
        );
    }

    /**
     * @param mixed       $value
     * @param mixed       $parameter
     * @param string|null $pointer
     *
     * @return \League\JsonGuard\ValidationError|null
     */
    public static function validateExclusiveMin($value, $parameter, $pointer = null)
    {
        if (!is_numeric($value) || JsonGuard\compare($value, $parameter) === 1) {
            return null;
        }

        return new ValidationError(
            'Number {value} is not at least greater than {exclusive_min}',
            self::EXCLUSIVE_KEYWORD,
            $value,
            $pointer,
            ['value' => $value, 'exclusive_min' => $parameter]
        );
    }
}
