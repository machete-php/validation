<?php

namespace League\JsonGuard\Test\Constraint;

use League\JsonGuard\Constraint\DraftFour\Enum;
use League\JsonGuard\Exception\InvalidSchemaException;
use League\JsonGuard\Validator;

class EnumTest extends \PHPUnit_Framework_TestCase
{
    function test_it_throws_when_parameter_is_not_an_array()
    {
        $this->setExpectedException(InvalidSchemaException::class);
        (new Enum())->validate([1,2,3], 'not-array', new Validator([], new \stdClass()));
    }
}
