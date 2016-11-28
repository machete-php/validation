<?php

namespace League\JsonGuard\Test;

use League\JsonGuard\ValidationError;

class ValidationErrorTest extends \PHPUnit_Framework_TestCase
{
    public function testInterpolatesContextIntoMessage()
    {
        $e = new ValidationError(
            'Value {value} must be greater than {min}',
            'minimum',
            12,
            '/likes',
            ['value' => -5, 'min' => 0]
        );

        $this->assertSame(
            'Value -5 must be greater than 0',
            $e->getMessage()
        );
    }

    public function testContextIsAlwaysAString()
    {
        $e = new ValidationError(
            'some message',
            'minimum',
            12,
            '/likes',
            ['object' => new \stdClass(), 'integer' => 0, 'array' => [1,2,3]]
        );

        foreach ($e->getContext() as $value) {
            $this->assertInternalType('string', $value);
        }
    }

    public function testValidationErrorIsArrayAccessible()
    {
        $e = new ValidationError(
            'Value {value} must be greater than {min}',
            'minimum',
            12,
            '/likes',
            ['value' => -5, 'min' => 0]
        );

        $this->assertTrue(
          isset($e['message']) &&
          isset($e['keyword']) &&
          isset($e['value']) &&
          isset($e['pointer']) &&
          isset($e['context'])
        );
        $this->assertSame($e->getMessage(), $e['message']);
        $this->assertSame($e->getKeyword(), $e['keyword']);
        $this->assertSame($e->getValue(), $e['value']);
        $this->assertSame($e->getPointer(), $e['pointer']);
        $this->assertSame($e->getContext(), $e['context']);
    }

    public function testCannotSetOrUnsetUsingArrayAccess()
    {
        $e = new ValidationError(
            'Value {value} must be greater than {min}',
            'minimum',
            12
        );

        unset($e['message']);
        $this->assertTrue(isset($e['message']));

        $e['something'] = 1234;
        $this->assertFalse(isset($e['something']));
    }
}
