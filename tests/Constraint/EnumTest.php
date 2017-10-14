<?php

namespace League\JsonGuard\Test\Constraint;

use League\JsonGuard\Constraint\DraftFour\Enum;
use League\JsonGuard\Exception\InvalidSchemaException;
use League\JsonGuard\Validator;
use PHPUnit\Framework\TestCase;

class EnumTest extends TestCase
{
    function test_it_throws_when_parameter_is_not_an_array()
    {
        $this->setExpectedException(InvalidSchemaException::class);
        (new Enum())->validate([1,2,3], 'not-array', new Validator([], new \stdClass()));
    }
}
