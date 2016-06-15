<?php

namespace League\JsonGuard\Test {
    use League\JsonGuard\Constraints\Type;

    class TypeTest extends \PHPUnit_Framework_TestCase
    {
        /**
         * @runInSeparateProcess
         */
        public function testTypeStringPassesWhenValidAndBcMathIsNotInstalled()
        {
            require_once __DIR__ . '/../stubs/constraint_function_exists.php';
            $this->assertNull(Type::validate('hello world', 'string'));
        }
    }
}
