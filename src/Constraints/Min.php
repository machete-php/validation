<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard;
use League\JsonGuard\ErrorCode;
use League\JsonGuard\ValidationError;

class Min implements ParentSchemaAwarePropertyConstraint
{
    /**
     * {@inheritdoc}
     */
    public static function validate($value, $schema, $parameter, $pointer = null)
    {
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
        if (!is_numeric($value) ||  JsonGuard\compare($value, '>=', $parameter)) {
            return null;
        }

        $message = sprintf(
            'Number "%s" is not at least "%d"',
            JsonGuard\as_string($value),
            JsonGuard\as_string($parameter)
        );

        return new ValidationError($message, ErrorCode::INVALID_MIN, $value, $pointer, ['min' => $parameter]);
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
        if (!is_numeric($value) || JsonGuard\compare($value, '>', $parameter)) {
            return null;
        }

        $message = sprintf(
            'Number "%s" is not at least greater than "%d"',
            JsonGuard\as_string($value),
            JsonGuard\as_string($parameter)
        );

        return new ValidationError(
            $message,
            ErrorCode::INVALID_EXCLUSIVE_MIN,
            $value,
            $pointer,
            ['exclusive_min' => $parameter]
        );
    }
}
