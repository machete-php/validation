<?php

namespace League\JsonGuard\Test;

class FunctionsTest extends \PHPUnit_Framework_TestCase
{
    public function validJsonIntegerDataProvider()
    {
        return [
            ["98249283749234923498293171823948729348710298301928331", true],
            ["-98249283749234923498293171823948729348710298301928331", true],
            [99, true],
            ["hello-world", false],
            ["99", false],
            ["-99", false]
        ];
    }

    /**
     * @dataProvider validJsonIntegerDataProvider
     *
     * @param string|int $jsonInteger
     * @param bool $isValid
     */
    public function testIsJsonInteger($jsonInteger, $isValid)
    {
        if ($isValid) {
            $this->assertTrue(\League\JsonGuard\is_json_integer($jsonInteger));
        } else {
            $this->assertFalse(\League\JsonGuard\is_json_integer($jsonInteger));
        }
    }
}
