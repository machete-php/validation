<?php

namespace League\JsonGuard\Test\Constraints;

use League\JsonGuard\Constraints\MinLength;
use League\JsonGuard\ValidationError;
use League\JsonGuard\Validator;

class MinLengthTest extends \PHPUnit_Framework_TestCase
{
    function test_it_validates_multibyte_string_length_correctly()
    {
        $value      = '按时间先后进行排序';
        $constraint = new MinLength();
        $validator  = new Validator((object) [], (object) []);
        $this->assertNull($constraint->validate($value, 9, $validator));
        $this->assertInstanceOf(ValidationError::class, $constraint->validate($value, 10, $validator));
    }
}
