<?php

namespace League\JsonGuard;

use League\JsonGuard\Exceptions\InvalidSchemaException;

/**
 * Assertions to verify a schema is valid.
 */
class Assert
{
    /**
     * Validate an array has at least one element.
     *
     * @param array       $value
     * @param string      $keyword
     * @param string|null $pointer
     */
    public static function notEmpty(array $value, $keyword, $pointer = null)
    {
        if (!empty($value)) {
            return;
        }

        throw InvalidSchemaException::emptyArray($keyword, $pointer);
    }

    /**
     * Validate an integer is non-negative.
     *
     * @param integer     $value
     * @param string      $keyword
     * @param string|null $pointer
     */
    public static function nonNegative($value, $keyword, $pointer = null)
    {
        if ($value >= 0) {
            return;
        }

        throw InvalidSchemaException::negativeValue(
            $value,
            $keyword,
            $pointer
        );
    }

    /**
     * Validate a value is one of the allowed types.
     *
     * @param mixed        $value
     * @param array|string $choices
     * @param string       $keyword
     * @param string|null  $pointer
     *
     * @throws InvalidSchemaException
     */
    public static function type($value, $choices, $keyword, $pointer = null)
    {
        $actualType = gettype($value);
        $choices    = is_array($choices)  ? $choices : [$choices];

        if (in_array($actualType, $choices) ||
            (is_json_number($value) && in_array('number', $choices))) {
            return;
        }

        throw InvalidSchemaException::invalidParameterType(
            $actualType,
            $choices,
            $keyword,
            $pointer
        );
    }

    /**
     * @param object      $schema
     * @param string      $property
     * @param string      $keyword
     * @param string|null $pointer
     */
    public static function hasProperty($schema, $property, $keyword, $pointer = null)
    {
        if (isset($schema->$property)) {
            return;
        }

        throw InvalidSchemaException::missingProperty(
            $property,
            $keyword,
            $pointer
        );
    }
}
