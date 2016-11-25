<?php

namespace League\JsonGuard\Test\Loaders;

use League\JsonGuard\Exceptions\SchemaLoadingException;
use League\JsonGuard\Loaders\CurlWebLoader;

class CurlWebLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $loader = new CurlWebLoader('http://');
        $response = $loader->load('localhost:1234/integer.json');
        $this->assertSame('{"type":"integer"}', json_encode($response));
    }

    public function testLoadWithCustomOptions()
    {
        $this->setExpectedException(SchemaLoadingException::class);
        $loader = new CurlWebLoader('http://', [CURLOPT_NOBODY => true]);
        $loader->load('localhost:1234/integer.json');
    }

    public function testNotFound()
    {
        $this->setExpectedException(SchemaLoadingException::class);
        $loader = new CurlWebLoader('http://');
        $loader->load('localhost:1234/unknown');
    }
}
