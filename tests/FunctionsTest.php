<?php

namespace League\JsonGuard\Test;

use League\JsonGuard;

class FunctionsTest extends \PHPUnit_Framework_TestCase
{
    public function compareData()
    {
        return [
            [12, '>', 6, true],
            [12, '>', 19, false],
            [99, '>=', 99, true],
            [99, '>=', 98, true],
            [99, '>=', 100, false],
            [15, '=', 15, true],
            [2, '=', 3, false],
            [4, '<', 18, true],
            [4, '<', 3, false],
            [15, '<=', 19, true],
            [15, '<=', 12, false],
        ];
    }

    /**
     * @dataProvider compareData
     */
    public function testCompareWithBcCompAvailable($leftOperand, $operator, $rightOperand, $isValid)
    {
        if ($isValid) {
            $this->assertTrue(JsonGuard\compare($leftOperand, $operator, $rightOperand));
        } else {
            $this->assertFalse(JsonGuard\compare($leftOperand, $operator, $rightOperand));
        }
    }

    /**
     * @dataProvider compareData
     * @runInSeparateProcess
     */
    public function testCompareWithoutBcCompAvailable($leftOperand, $operator, $rightOperand, $isValid)
    {
        require_once __DIR__ . '/stubs/constraint_function_exists.php';

        if ($isValid) {
            $this->assertTrue(JsonGuard\compare($leftOperand, $operator, $rightOperand));
        } else {
            $this->assertFalse(JsonGuard\compare($leftOperand, $operator, $rightOperand));
        }
    }
}
