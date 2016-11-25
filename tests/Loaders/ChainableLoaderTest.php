<?php

namespace League\JsonGuard\Test\Loaders;

use League\JsonGuard\Loaders\ArrayLoader;
use League\JsonGuard\Loaders\ChainableLoader;

class ChainableLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $first  = ['first' => json_decode('{"first": "loader"}')];
        $second = ['second' => json_decode('{"second": "loader"}')];
        $loader = new ChainableLoader(new ArrayLoader($first), new ArrayLoader($second));

        $this->assertEquals($first['first'], $loader->load('first'));
        $this->assertEquals($second['second'], $loader->load('second'));
    }
}
