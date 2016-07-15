<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard;
use League\JsonGuard\ErrorCode;
use League\JsonGuard\ValidationError;

class Type implements PropertyConstraint
{
    /**
     * {@inheritdoc}
     */
    public static function validate($value, $type, $pointer = null)
    {
        if (is_array($type)) {
            return self::anyType($value, $type, $pointer);
        }

        switch ($type) {
            case 'object':
                return self::validateType($value, $type, 'is_object', ErrorCode::INVALID_OBJECT, $pointer);
            case 'array':
                return self::validateType($value, $type, 'is_array', ErrorCode::INVALID_ARRAY, $pointer);
            case 'boolean':
                return self::validateType($value, $type, 'is_bool', ErrorCode::INVALID_BOOLEAN, $pointer);
            case 'null':
                return self::validateType($value, $type, 'is_null', ErrorCode::INVALID_NULL, $pointer);
            case 'number':
                return self::validateType($value, $type, 'is_numeric', ErrorCode::INVALID_NUMERIC, $pointer);
            case 'integer':
                return self::validateType(
                    $value,
                    $type,
                    'League\JsonGuard\is_json_integer',
                    ErrorCode::INVALID_INTEGER,
                    $pointer
                );
            case 'string':
                return self::validateType(
                    $value,
                    $type,
                    function ($value) {
                        if (is_string($value)) {
                            if (!function_exists('bccomp')) {
                                return true;
                            }

                            // Make sure the string isn't actually a number that was too large
                            // to be cast to an int on this platform.  This is only possible
                            // if the bcmath extension is loaded, and will only happen if
                            // you decode JSON with the JSON_BIGINT_AS_STRING option.
                            if (!(ctype_digit($value) && bccomp($value, PHP_INT_MAX, 0) === 1)) {
                                return true;
                            }
                        }

                        return false;
                    },
                    ErrorCode::INVALID_STRING,
                    $pointer
                );
        }
    }

    /**
     * @param mixed    $value
     * @param string   $type
     * @param callable $callable
     * @param int      $errorCode
     * @param string   $pointer
     *
     * @return \League\JsonGuard\ValidationError|null
     */
    private static function validateType($value, $type, callable $callable, $errorCode, $pointer)
    {
        if (call_user_func($callable, $value) === true) {
            return null;
        }

        $message = sprintf('Value "%s" is not a(n) %s.', JsonGuard\as_string($value), $type);

        return new ValidationError($message, $errorCode, $value, $pointer);
    }

    /**
     * @param mixed  $value
     * @param array  $choices
     * @param string $pointer
     *
     * @return \League\JsonGuard\ValidationError|null
     */
    private static function anyType($value, array $choices, $pointer)
    {
        foreach ($choices as $type) {
            $error = static::validate($value, $type, $pointer);
            if (is_null($error)) {
                return null;
            }
        }

        $message = sprintf(
            'Value "%s" is not one of: %s',
            JsonGuard\as_string($value),
            implode(', ', array_map('League\JsonGuard\as_string', $choices))
        );

        return new ValidationError($message, ErrorCode::INVALID_TYPE, $value, $pointer, ['types' => $choices]);
    }
}
