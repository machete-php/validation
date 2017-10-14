<?php

namespace League\JsonGuard\Exception;

final class InvalidSchemaException extends \RuntimeException
{
    /**
     * @var string
     */
    private $keyword;

    /**
     * @var string
     */
    private $pointer;

    /**
     * @param string $message
     * @param string $keyword
     * @param string $pointer
     */
    public function __construct($message, $keyword, $pointer)
    {
        parent::__construct($message);

        $this->keyword = $keyword;
        $this->pointer = $pointer;
    }

    /**
     * @param string $actualType
     * @param array  $allowedTypes
     * @param string $keyword
     * @param string $pointer
     *
     * @return \League\JsonGuard\Exception\InvalidSchemaException
     */
    public static function invalidParameterType($actualType, array $allowedTypes, $keyword, $pointer)
    {
        $message = sprintf(
            'Value has type "%s" but must be one of: "%s"',
            $actualType,
            implode(', ', $allowedTypes)
        );

        return new self($message, $keyword, $pointer);
    }

    /**
     * @param string $actualParameter
     * @param array  $allowedParameter
     * @param string $keyword
     * @param string $pointer
     *
     * @return \League\JsonGuard\Exception\InvalidSchemaException
     */
    public static function invalidParameter($actualParameter, array $allowedParameter, $keyword, $pointer)
    {
        $message = sprintf(
            'Value is "%s" but must be one of: "%s"',
            $actualParameter,
            implode(', ', $allowedParameter)
        );

        return new self($message, $keyword, $pointer);
    }

    /**
     * @param integer $value
     * @param string  $keyword
     * @param string  $pointer
     *
     * @return \League\JsonGuard\Exception\InvalidSchemaException
     */
    public static function negativeValue($value, $keyword, $pointer)
    {
        $message = sprintf(
            'Integer value "%d" must be greater than, or equal to, 0',
            $value
        );

        return new self($message, $keyword, $pointer);
    }

    /**
     * @param string $keyword
     * @param string $pointer
     *
     * @return \League\JsonGuard\Exception\InvalidSchemaException
     */
    public static function emptyArray($keyword, $pointer)
    {
        return new self(
            'Array must have at least one element',
            $keyword,
            $pointer
        );
    }

    /**
     * @param string $property
     * @param string $keyword
     * @param string $pointer
     *
     * @return \League\JsonGuard\Exception\InvalidSchemaException
     */
    public static function missingProperty($property, $keyword, $pointer)
    {
        $message = sprintf(
            'The schema must contain the property %s',
            $property
        );

        return new self($message, $keyword, $pointer);
    }

    /**
     * @return string
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * @return string
     */
    public function getPointer()
    {
        return $this->pointer;
    }
}
