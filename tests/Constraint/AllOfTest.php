<?php

namespace League\JsonGuard\Test\Constraint;

use League\JsonGuard\Constraint\DraftFour\AllOf;
use League\JsonGuard\Exception\InvalidSchemaException;
use League\JsonGuard\Validator;

class AllOfTest extends \PHPUnit_Framework_TestCase
{
    function test_it_throws_when_parameter_is_not_an_array()
    {
        $this->setExpectedException(InvalidSchemaException::class);
        $v = new Validator(json_decode('{}'), json_decode('{}'));
        (new AllOf)->validate([1,2,3], 'not-array', $v);
    }
}
