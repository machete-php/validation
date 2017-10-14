<?php

namespace League\JsonGuard\Test\Constraint;

use League\JsonGuard\Constraint\DraftFour\Required;
use League\JsonGuard\Validator;
use PHPUnit\Framework\TestCase;

class RequiredTest extends TestCase
{
    function test_it_returns_property_name_in_error_message()
    {
        $required = new Required();
        $error = $required->validate(new \stdClass(), ['shouldBeHere'], new Validator([], new \stdClass()));
        $this->assertRegExp('/shouldBeHere/', $error->getMessage());
    }
}

