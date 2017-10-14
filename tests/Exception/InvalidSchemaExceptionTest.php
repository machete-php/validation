<?php

namespace League\JsonGuard\Test\Exception;

use League\JsonGuard\Exception\InvalidSchemaException;
use PHPUnit\Framework\TestCase;

class InvalidSchemaExceptionTest extends TestCase
{
    function test_can_get_keyword_and_pointer()
    {
        $e = new InvalidSchemaException('Invalid format', $keyword = 'format', $pointer = '/properties/email/format');
        $this->assertSame($keyword, $e->getKeyword());
        $this->assertSame($pointer, $e->getPointer());
    }

    function test_invalid_parameter_type_constructor()
    {
        $e = InvalidSchemaException::invalidParameterType(
            'string',
            ['integer'],
            $keyword = 'minimum',
            $pointer = '/properties/likes'
        );

        $this->assertSame('Value has type "string" but must be one of: "integer"', $e->getMessage());
        $this->assertSame($keyword, $e->getKeyword());
        $this->assertSame($pointer, $e->getPointer());
    }

    function test_negative_value_constructor()
    {
        $e = InvalidSchemaException::negativeValue(-5, $keyword = 'minimum', $pointer = '/properties/likes');

        $this->assertSame('Integer value "-5" must be greater than, or equal to, 0', $e->getMessage());
        $this->assertSame($keyword, $e->getKeyword());
        $this->assertSame($pointer, $e->getPointer());
    }

    function test_empty_array_constructor()
    {
        $e = InvalidSchemaException::emptyArray($keyword = 'items', $pointer = '/properties/emails');

        $this->assertSame('Array must have at least one element', $e->getMessage());
        $this->assertSame($keyword, $e->getKeyword());
        $this->assertSame($pointer, $e->getPointer());
    }
}
