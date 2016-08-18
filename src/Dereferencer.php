<?php

namespace League\JsonGuard;

use League\JsonGuard\Loaders\CurlWebLoader;
use League\JsonGuard\Loaders\FileGetContentsWebLoader;
use League\JsonGuard\Loaders\FileLoader;

/**
 * The Dereferencer resolves all external $refs and replaces
 * internal references with Reference objects.
 */
class Dereferencer
{
    /**
     * @var array
     */
    private $loaders;

    /**
     * Create a new Dereferencer.
     */
    public function __construct()
    {
        $this->registerFileLoader();
        $this->registerDefaultWebLoaders();
    }

    /**
     * Return the schema with all references resolved.
     *
     * @param string|object $schema Either a valid path like "http://json-schema.org/draft-03/schema#"
     *                              or the object resulting from a json_decode call.
     *
     * @return object
     */
    public function dereference($schema)
    {
        // If a string is provided, assume they passed a path.
        if (is_string($schema)) {
            $schema = $this->loadExternalRef($schema);
        }

        return $this->crawl($schema);
    }

    /**
     * Register a Loader for the given prefix.
     *
     * @param Loader $loader
     * @param string $prefix
     */
    public function registerLoader(Loader $loader, $prefix)
    {
        $this->loaders[$prefix] = $loader;
    }

    /**
     * Get the loader for the given prefix.
     *
     * @param $prefix
     *
     * @return Loader
     * @throws \InvalidArgumentException
     */
    private function getLoader($prefix)
    {
        if (!array_key_exists($prefix, $this->loaders)) {
            throw new \InvalidArgumentException(sprintf('A loader is not registered for the prefix "%s"', $prefix));
        }

        return $this->loaders[$prefix];
    }

    /**
     * Register the default file loader.
     */
    private function registerFileLoader()
    {
        $this->loaders['file'] = new FileLoader();
    }

    /**
     * Register the default web loaders.  If the curl extension is loaded,
     * the CurlWebLoader will be used.  Otherwise the FileGetContentsWebLoader
     * will be used.  You can override this by registering your own loader
     * for the 'http' and 'https' protocols.
     */
    private function registerDefaultWebLoaders()
    {
        if (function_exists('curl_init')) {
            $this->loaders['https'] = new CurlWebLoader('https://');
            $this->loaders['http']  = new CurlWebLoader('http://');
        } else {
            $this->loaders['https'] = new FileGetContentsWebLoader('https://');
            $this->loaders['http']  = new FileGetContentsWebLoader('http://');
        }
    }

    /**
     * Crawl the schema and resolve any references.
     *
     * @param object $schema
     *
     * @return object
     */
    private function crawl($schema)
    {
        $references = $this->getReferences($schema);

        foreach ($references as $path => $ref) {
            // resolve
            if ($this->isExternalRef($ref)) {
                $ref      = $this->makeReferenceAbsolute($schema, $path, $ref);
                $resolved = $this->loadExternalRef($ref);
                $resolved = $this->crawl($resolved);
            } else {
                $resolved = new Reference($schema, $ref);
            }

            // handle any fragments
            $fragment = parse_url($ref, PHP_URL_FRAGMENT);
            if ($this->isExternalRef($ref) && is_string($fragment)) {
                $pointer  = new Pointer($resolved);
                $resolved = $pointer->get($fragment);
            }

            // Immediately resolve any root references.
            if ($path === '') {
                while ($resolved instanceof Reference) {
                    $resolved = $resolved->resolve();
                }
            }

            // merge
            if ($path === '') {
                $this->mergeRootRef($schema, $resolved);
            } else {
                $pointer = new Pointer($schema);
                if ($pointer->has($path)) {
                    $pointer->set($path, $resolved);
                }
            }
        }

        return $schema;
    }

    /**
     * Recursively get all of the references for the given schema.
     * Returns an associative array like [path => reference].
     * Example:
     *
     * ['/properties' => '#/definitions/b']
     *
     * The path does NOT include the $ref.
     *
     * @param object $schema The schema to resolve references for.
     * @param string $path   The current schema path.
     *
     * @return array
     */
    private function getReferences($schema, $path = '')
    {
        $refs = [];

        if (!is_array($schema) && !is_object($schema)) {
            if ($this->isRef($path, $schema)) {
                $refs[$path] = $schema;
            }

            return $refs;
        }

        foreach ($schema as $attribute => $parameter) {
            if ($this->isRef($attribute, $parameter)) {
                $refs[$path] = $parameter;
            }
            if (is_object($parameter)) {
                $refs = array_merge($refs, $this->getReferences($parameter, $this->pathPush($path, $attribute)));
            }
            if (is_array($parameter)) {
                foreach ($parameter as $k => $v) {
                    $refs = array_merge(
                        $refs,
                        $this->getReferences($v, $this->pathPush($this->pathPush($path, $attribute), $k))
                    );
                }
            }
        }

        return $refs;
    }

    /**
     * Push a segment onto the given path.
     *
     * @param string $path
     * @param string $segment
     *
     * @return string
     */
    private function pathPush($path, $segment)
    {
        return $path . '/' . escape_pointer($segment);
    }

    /**
     * @param string $attribute
     * @param mixed $attributeValue
     *
     * @return bool
     */
    private function isRef($attribute, $attributeValue)
    {
        return $attribute === '$ref' && is_string($attributeValue);
    }

    /**
     * @param string $parameter
     *
     * @return bool
     */
    private function isInternalRef($parameter)
    {
        return is_string($parameter) && substr($parameter, 0, 1) === '#';
    }

    /**
     * @param string $parameter
     *
     * @return bool
     */
    private function isExternalRef($parameter)
    {
        return !$this->isInternalRef($parameter);
    }

    /**
     * Load an external ref and return the JSON object.
     *
     * @param string $reference
     *
     * @return object
     */
    private function loadExternalRef($reference)
    {
        $this->validateAbsolutePath($reference);
        list($prefix, $path) = explode('://', $reference, 2);

        $loader = $this->getLoader($prefix);

        $schema = $loader->load($path);

        return $schema;
    }

    /**
     * Merge a resolved reference into the root of the given schema.
     *
     * @param object $rootSchema
     * @param object $resolvedRef
     */
    private function mergeRootRef($rootSchema, $resolvedRef)
    {
        $ref = '$ref';
        unset($rootSchema->$ref);
        foreach (get_object_vars($resolvedRef) as $prop => $value) {
            $rootSchema->$prop = $value;
        }
    }

    /**
     * Validate an absolute path is valid.
     *
     * @param string $path
     */
    private function validateAbsolutePath($path)
    {
        if (!preg_match('#^.+\:\/\/.*#', $path)) {
            throw new \InvalidArgumentException(
                'Your path is missing a valid prefix.  The schema path should start with a prefix i.e. "file://".'
            );
        }
    }

    /**
     * Determine if a reference is relative.
     * A reference is relative if it does not being with a prefix.
     *
     * @param string $ref
     *
     * @return bool
     */
    private function isRelativeRef($ref)
    {
        return !preg_match('#^.+\:\/\/.*#', $ref);
    }

    /**
     * Take a relative reference, and prepend the id of the schema and any
     * sub schemas to get the absolute url.
     *
     * @param object $schema
     * @param string $path
     * @param string $ref
     *
     * @return string
     */
    private function makeReferenceAbsolute($schema, $path, $ref)
    {
        if (!$this->isRelativeRef($ref)) {
            return $ref;
        }

        $pointer     = new Pointer($schema);
        $baseUrl     = $pointer->get('/id');
        $currentPath = '';
        foreach (array_slice(explode('/', $path), 1) as $segment) {
            $currentPath .= '/' . $segment;
            if ($pointer->has($currentPath . '/id')) {
                $baseUrl .= $pointer->get($currentPath . '/id');
            }
        }
        $ref = $baseUrl . $ref;

        return $ref;
    }
}
