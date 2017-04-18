<?php

namespace League\JsonGuard\Exceptions;

// @todo: these should use pointers to the schema, not the data.

class InvalidSchemaException extends \RuntimeException
{
    /**
     * @var string
     */
    private $keyword;

    /**
     * @var string|null
     */
    private $pointer;

    /**
     * @param string      $message
     * @param string      $keyword
     * @param string|null $pointer
     */
    public function __construct($message, $keyword, $pointer)
    {
        parent::__construct($message);

        $this->keyword = $keyword;
        $this->pointer = $pointer;
    }

    /**
     * @param string      $actualType
     * @param array       $allowedTypes
     * @param string      $keyword
     * @param string|null $pointer
     *
     * @return \League\JsonGuard\Exceptions\InvalidSchemaException
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
     * @param integer     $value
     * @param string      $keyword
     * @param string|null $pointer
     *
     * @return \League\JsonGuard\Exceptions\InvalidSchemaException
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
     * @param string      $keyword
     * @param string|null $pointer
     *
     * @return \League\JsonGuard\Exceptions\InvalidSchemaException
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
     * @return string
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * @return string|null
     */
    public function getPointer()
    {
        return $this->pointer;
    }
}
