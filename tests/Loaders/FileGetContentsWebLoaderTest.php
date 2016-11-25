<?php

namespace League\JsonGuard\Test\Loaders;

use League\JsonGuard\Exceptions\SchemaLoadingException;
use League\JsonGuard\Loaders\FileGetContentsWebLoader;

class FileGetContentsWebLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $loader = new FileGetContentsWebLoader('http://');
        $response = $loader->load('localhost:1234/integer.json');
        $this->assertSame('{"type":"integer"}', json_encode($response));
    }

    public function testNotFound()
    {
        $this->setExpectedException(SchemaLoadingException::class);
        $loader = new FileGetContentsWebLoader('http://');
        $loader->load('localhost:1234/unknown');
    }
}
