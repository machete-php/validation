<?php

namespace League\JsonGuard\Test\Constraints;

use League\JsonGuard\Constraints\Type;
use League\JsonGuard\ValidationError;

class TypeTest extends \PHPUnit_Framework_TestCase
{
    public function testNumericStringIsNotANumber()
    {
        $type = new Type();

        $error = $type->validate('1', 'number');

        $this->assertInstanceOf(ValidationError::class, $error);
    }
}
