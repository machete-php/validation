<?php

namespace League\JsonGuard\Test;

use League\JsonGuard\Assert;
use League\JsonGuard\Exceptions\InvalidSchemaException;

class AssertTest extends \PHPUnit_Framework_TestCase
{
    function test_not_empty_when_invalid()
    {
        $this->throws('someConstraint', '/someConstraint');
        Assert::notEmpty([], 'someConstraint', '/someConstraint');
    }

    function test_not_empty_when_valid()
    {
        Assert::notEmpty([1,2,3], 'someConstraint', '/someConstraint');
    }

    function test_non_negative_when_invalid()
    {
        $this->throws('someConstraint', '/someConstraint');
        Assert::nonNegative(-1, 'someConstraint', '/someConstraint');
    }

    function test_non_negative_when_valid()
    {
        Assert::nonNegative(1, 'someConstraint', '/someConstraint');
    }

    function test_type_when_invalid()
    {
        $this->throws('someConstraint', '/someConstraint');
        Assert::type([], ['string', 'integer'], 'someConstraint', '/someConstraint');
    }

    public function types()
    {
        return [
            ['name', ['string', 'array']],
            // accepts a single type instead of an array of choices
            [new \stdClass(), 'object'],
            [1, 'number'],
            // larger than PHP_INT_MAX is still a number
            [98249283749234923498293171823948729348710298301928331, 'number'],
            // floats are numbers
            [1.00, 'number']
        ];
    }

    /**
     * @dataProvider types
     * @param $value
     * @param $choices
     */
    function test_type_when_valid($value, $choices)
    {
        Assert::type($value, $choices, 'someConstraint', '/someConstraint');
    }

    private function throws($keyword, $pointer)
    {
        $this->setExpectedException(InvalidSchemaException::class);
    }
}
