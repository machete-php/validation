<?php

namespace League\JsonGuard\Test\Constraints;

use League\JsonGuard\Constraints\Required;

class RequiredTest extends \PHPUnit_Framework_TestCase
{
    public function testExceptionMessageContainsPropertyName()
    {
        $required = new Required();
        $error = $required->validate(new \StdClass(), ['shouldBeHere']);
        $this->assertRegExp('/shouldBeHere/', $error->getMessage());
    }
}

