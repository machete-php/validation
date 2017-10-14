<?php

namespace League\JsonGuard\Test\Constraint;

use League\JsonGuard\Constraint\DraftFour\Format;
use League\JsonGuard\ValidationError;
use League\JsonGuard\Validator;
use League\JsonGuard\Constraint\DraftFour\Format\FormatExtensionInterface;
use function League\JsonGuard\error;
use League\JsonGuard\Exception\InvalidSchemaException;

class FormatUuid implements FormatExtensionInterface
{
    public function validate($value, Validator $validator)
    {
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
        
        if (!is_string($value) || preg_match($pattern, $value) === 1) {
            return null;
        }

        return error('The value {data} must match the format {parameter}.', $validator);
    }
}

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
    
    public function validUuidValues()
    {
        return [
            ['6ac84682-ac2f-11e7-8f1a-0800200c9a66'],
            ['e1eae6a3-2584-3e90-80bf-ca0ab5dd527a'],
            ['274ec8c0-cda9-44f5-8962-8e21052a24a9'],
            ['acd9d304-3432-59d2-a14f-701c96fe0c10']
        ];
    }
    
    /**
     * @dataProvider validUuidValues
     */
    function test_if_ignore_unknown_formats_is_true_by_default($value)
    {
        $result = (new Format())->validate($value, 'uuid', new Validator([], new \stdClass()));
        $this->assertNull($result);
    }
    
    /**
     * @dataProvider validUuidValues
     */
    function test_unknown_format_passes_if_ignore_unknown_formats_is_true($value)
    {
        $format = new Format();
        $format->setIgnoreUnknownFormats(true);
        $result = $format->validate($value, 'uuid', new Validator([], new \stdClass()));
        $this->assertNull($result);
    }
    
    /**
     * @dataProvider validUuidValues
     */
    function test_unknown_format_does_not_pass_if_ignore_unknown_formats_is_false_by_constructor($value)
    {
        $this->setExpectedException(InvalidSchemaException::class);
        $format = new Format([], false);
        $result = $format->validate($value, 'uuid', new Validator([], new \stdClass()));
        $this->assertInstanceOf(ValidationError::class, $result);
    }
    
    /**
     * @dataProvider validUuidValues
     */
    function test_unknown_format_does_not_pass_if_ignore_unknown_formats_is_false_by_setter($value)
    {
        $this->setExpectedException(InvalidSchemaException::class);
        $format = new Format();
        $format->setIgnoreUnknownFormats(false);
        $result = $format->validate($value, 'uuid', new Validator([], new \stdClass()));
        $this->assertInstanceOf(ValidationError::class, $result);
    }
    
    /**
     * @dataProvider validUuidValues
     */
    function test_user_defined_format_passes_if_implemented($value)
    {
        $format = new Format(['uuid' => new FormatUuid()], false);
        $result = $format->validate($value, 'uuid', new Validator([], new \stdClass()));
        $this->assertNull($result);
    }
}
