<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard;
use League\JsonGuard\ErrorCode;
use League\JsonGuard\ValidationError;

class Max implements ParentSchemaAwarePropertyConstraint
{
    /**
     * {@inheritdoc}
     */
    public static function validate($value, $schema, $parameter, $pointer = null)
    {
        if (isset($schema->exclusiveMaximum) && $schema->exclusiveMaximum === true) {
            return self::validateExclusiveMax($value, $parameter, $pointer);
        }

        return self::validateMax($value, $parameter, $pointer);
    }

    /**
     * @param mixed       $value
     * @param mixed       $parameter
     * @param string|null $pointer
     *
     * @return \League\JsonGuard\ValidationError|null
     */
    public static function validateMax($value, $parameter, $pointer = null)
    {
        if (!is_numeric($value) ||
            JsonGuard\compare($value, $parameter) === -1 || JsonGuard\compare($value, $parameter) === 0) {
            return null;
        }

        return new ValidationError(
            'Value {value} is not at most {max}',
            ErrorCode::INVALID_MAX,
            $value,
            $pointer,
            ['value' => $value, 'max' => $parameter]
        );
    }

    /**
     * @param mixed       $value
     * @param mixed       $parameter
     * @param string|null $pointer
     *
     * @return \League\JsonGuard\ValidationError|null
     */
    public static function validateExclusiveMax($value, $parameter, $pointer = null)
    {
        if (!is_numeric($value) || JsonGuard\compare($value, $parameter) === -1) {
            return null;
        }

        return new ValidationError(
            'Value {value} is not less than {exclusive_max}',
            ErrorCode::INVALID_EXCLUSIVE_MAX,
            $value,
            $pointer,
            ['value' => $value, 'exclusive_max' => $parameter]
        );
    }
}
