<?php

namespace League\JsonGuard\Test\Exceptions;

use League\JsonGuard\Exceptions\InvalidSchemaException;

class InvalidSchemaExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testCanGetKeywordAndPointer()
    {
        $e = new InvalidSchemaException('Invalid format', $keyword = 'format', $pointer = '/properties/email/format');
        $this->assertSame($keyword, $e->getKeyword());
        $this->assertSame($pointer, $e->getPointer());
    }

    public function testInvalidParameterTypeConstructor()
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

    public function testNegativeValueConstructor()
    {
        $e = InvalidSchemaException::negativeValue(-5, $keyword = 'minimum', $pointer = '/properties/likes');

        $this->assertSame('Integer value "-5" must be greater than, or equal to, 0', $e->getMessage());
        $this->assertSame($keyword, $e->getKeyword());
        $this->assertSame($pointer, $e->getPointer());
    }

    public function testEmptyArrayConstructor()
    {
        $e = InvalidSchemaException::emptyArray($keyword = 'items', $pointer = '/properties/emails');

        $this->assertSame('Array must have at least one element', $e->getMessage());
        $this->assertSame($keyword, $e->getKeyword());
        $this->assertSame($pointer, $e->getPointer());
    }
}
