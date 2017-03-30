<?php

namespace League\JsonGuard\Test;

use League\JsonGuard\ValidationError;

class ValidationErrorTest extends \PHPUnit_Framework_TestCase
{
    function test_interpolates_context_into_message()
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

    function test_context_is_always_astring()
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

    function test_validation_error_is_array_accessible()
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

    function test_cannot_set_or_unset_using_array_access()
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
