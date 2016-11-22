<?php

namespace League\JsonGuard\Test\Constraints;

use League\JsonGuard\Constraints\AllOf;
use League\JsonGuard\Exceptions\InvalidSchemaException;
use League\JsonGuard\Validator;

class AllOfTest extends \PHPUnit_Framework_TestCase
{
    public function testNonArrayParameter()
    {
        $this->setExpectedException(InvalidSchemaException::class);
        $v = new Validator(json_decode('{}'), json_decode('{}'));
        AllOf::validate([1,2,3], 'not-array', $v);
    }
}
