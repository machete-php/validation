<?php

namespace League\JsonGuard\Test\Constraint;

use League\JsonGuard\Constraint\DraftFour\Format;
use League\JsonGuard\ValidationError;
use League\JsonGuard\Validator;

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
    function test_format_passes_for_non_string_values($value, $parameter)
    {
        $format = new Format();
        $result = $format->validate($value, $parameter, new Validator([], new \stdClass()));
        $this->assertNull($result);
    }

    public function invalidDateTimeValues()
    {
        return [
            ['9999-99-9999999999'],
            ['9999-99-99'],
            ['2222-11-11abcderf'],
            ['1963-06-19'],
            ['-1990-12-31T15:59:60-08:00'], // leading -
            ['1990-12-31T23:59:61Z'], // seconds > 60
        ];
    }

    /**
     * @dataProvider invalidDateTimeValues
     */
    function test_date_time_does_not_pass_for_invalid_values($value)
    {
        $result = (new Format())->validate($value, 'date-time', new Validator([], new \stdClass()));
        $this->assertInstanceOf(ValidationError::class, $result);
    }

    public function validDateTimeValues()
    {
        return [
            ['1963-06-19T08:30:06Z'], // without fractional seconds
            ['1985-04-12T23:20:50.52Z'], // with fractional seconds
            ['1990-12-31T23:59:50+01:01'], // positive offset
            ['1990-12-31T23:59:50-01:01'], // negative offset
            ['1990-12-31T23:59:50+23:00'], // the offset can be any hour
            ['1990-12-31T23:59:60Z'], // leap second
            ['0000-06-19T08:30:06Z'], // literally any 4 digits can be a year
            ['1963-06-19t08:30:06.283185z'], // lowercase t and z
        ];
    }

    /**
     * @dataProvider validDateTimeValues
     */
    function test_date_time_passes_for_valid_iso8601_date_time($value)
    {
        $result = (new Format())->validate($value, 'date-time', new Validator([], new \stdClass()));
        $this->assertNull($result);
    }
}
