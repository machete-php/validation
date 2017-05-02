<?php

namespace League\JsonGuard\Test\Constraint;

use League\JsonGuard\Constraint\DraftFour\Type;
use League\JsonGuard\ValidationError;
use League\JsonGuard\Validator;

class TypeTest extends \PHPUnit_Framework_TestCase
{
    function test_numeric_string_is_not_a_number()
    {
        $type = new Type();

        $error = $type->validate('1', 'number', new Validator([], new \stdClass()));

        $this->assertInstanceOf(ValidationError::class, $error);
    }

    function test_bigint_mode_valid()
    {
        $type = new Type(Type::BIGINT_MODE_STRING_VALID);

        $error = $type->validate('98249283749234923498293171823948729348710298301928331', 'string', new Validator([], new \stdClass()));

        $this->assertNull($error);
    }

    function test_bigint_mode_invalid()
    {
        $type = new Type(Type::BIGINT_MODE_STRING_INVALID);

        $error = $type->validate('98249283749234923498293171823948729348710298301928331', 'string', new Validator([], new \stdClass()));

        $this->assertInstanceOf(ValidationError::class, $error);
    }
}
