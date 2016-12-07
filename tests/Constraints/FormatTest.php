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
    public function testFormatPassesForNonStringValues($value, $parameter)
    {
        $format = new Format();
        $result = $format::validate($value, $parameter);
        $this->assertNull($result);
    }

    public function invalidDateTimeValues()
    {
        return [
            ['9999-99-9999999999'],
            ['9999-99-99'],
            ['2222-11-11abcderf'],
            ['1963-06-19t08:30:06.283185Z'],
            ['1963-06-19'],
        ];
    }

    /**
     * @dataProvider invalidDateTimeValues
     */
    public function testDateTimeDoesNotPassForInvalidValues($value)
    {
        $result = Format::validate($value, 'date-time');
        $this->assertInstanceOf(ValidationError::class, $result);
    }
}
