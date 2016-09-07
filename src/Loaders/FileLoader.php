<?php

namespace League\JsonGuard\Loaders;

use League\JsonGuard;
use League\JsonGuard\Exceptions\SchemaLoadingException;
use League\JsonGuard\Loader;

class FileLoader implements Loader
{
    /**
     * {@inheritdoc}
     */
    public function load($path)
    {
        $path = rtrim(JsonGuard\strip_fragment($path), '#');
        if (!file_exists($path)) {
            throw SchemaLoadingException::notFound($path);
        }

        return JsonGuard\json_decode(file_get_contents($path), false, 512, JSON_BIGINT_AS_STRING);
    }
}
