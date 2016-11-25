<?php

namespace League\JsonGuard\Test\Loaders;

use League\JsonGuard\Exceptions\SchemaLoadingException;
use League\JsonGuard\Loaders\FileLoader;

class FileLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadWhenNotFound()
    {
        $this->setExpectedException(SchemaLoadingException::class);
        $loader = new FileLoader();
        $response = $loader->load(__DIR__ . '/not-found.json');
    }
}
