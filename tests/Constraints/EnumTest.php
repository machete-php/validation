<?php

namespace League\JsonGuard\Test\Constraints;

use League\JsonGuard\Constraints\Enum;
use League\JsonGuard\Exceptions\InvalidSchemaException;

class EnumTest extends \PHPUnit_Framework_TestCase
{
    public function testNonArrayParameter()
    {
        $this->setExpectedException(InvalidSchemaException::class);
        Enum::validate([1,2,3], 'not-array');
    }
}
