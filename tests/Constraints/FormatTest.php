<?php

namespace League\JsonGuard\Test\Constraints;

use League\JsonGuard\Constraints\Format;
use League\JsonGuard\ValidationError;

class FormatTest extends \PHPUnit_Framework_TestCase
{
    public function invalidFormatValues()
    {
        return [
            [[], 'date-time'],
            [new \stdClass(), 'uri'],
            [1234, 'email'],
        ];
    }

    /**
     * @dataProvider invalidFormatValues
     */
    public function testFormatFailsForNonStringValues($value, $parameter)
    {
        $format = new Format();
        $result = $format::validate($value, $parameter);
        $this->assertInstanceOf(ValidationError::class, $result);
    }

} 
