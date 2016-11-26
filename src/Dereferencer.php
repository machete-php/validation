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
        if (is_string($schema)) {
            $uri    = $schema;
            $schema = $this->loadExternalRef($uri);
            $schema = $this->resolveFragment($uri, $schema);

            return $this->crawl($schema, strip_fragment($uri));
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
     * Get all registered loaders, keyed by the prefix they are registered to load schemas for.
     *
     * @return Loader[]
     */
    public function getLoaders()
    {
        return $this->loaders;
    }

    /**
     * Get the loader for the given prefix.
     *
     * @param string $prefix
     *
     * @return Loader
     * @throws \InvalidArgumentException
     */
    public function getLoader($prefix)
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
     * @param object      $schema
     * @param string|null $currentUri
     *
     * @return object
     */
    private function crawl($schema, $currentUri = null)
    {
        $references = $this->getReferences($schema);

        foreach ($references as $path => $ref) {
            // resolve
            if ($this->isExternalRef($ref)) {
                $resolved = new Reference(function () use ($schema, $path, $ref, $currentUri) {
                    return $this->resolveExternalReference($schema, $path, $ref, $currentUri);
                }, $ref);
            } else {
                $resolved = new Reference($schema, $ref);
            }

            // handle any fragments
            $resolved = $this->resolveFragment($ref, $resolved);

            // merge
            $this->mergeResolvedReference($schema, $resolved, $path);
        }

        return $schema;
    }

    /**
     * Resolve the external reference at the given path.
     *
     * @param  object      $schema     The JSON Schema
     * @param  string      $path       A JSON pointer to the $ref's location in the schema.
     * @param  string      $ref        The JSON reference
     * @param  string|null $currentUri The URI of the schema, or null if the schema was loaded from an object.
     *
     * @return object                  The schema with the reference resolved.
     */
    private function resolveExternalReference($schema, $path, $ref, $currentUri)
    {
        $ref      = $this->makeReferenceAbsolute($schema, $path, $ref, $currentUri);
        $resolved = $this->loadExternalRef($ref);

        return $this->crawl($resolved, strip_fragment($ref));
    }

    /**
     * Merge the resolved reference with the schema, at the given path.
     *
     * @param  object $schema   The schema to merge the resolved reference with
     * @param  object $resolved The resolved schema
     * @param  string $path     A JSON pointer to the path where the reference should be merged.
     *
     * @return void
     */
    private function mergeResolvedReference($schema, $resolved, $path)
    {
        if ($path === '') {
            // Immediately resolve any root references.
            while ($resolved instanceof Reference) {
                $resolved = $resolved->resolve();
            }
            $this->mergeRootRef($schema, $resolved);
        } else {
            $pointer = new Pointer($schema);
            if ($pointer->has($path)) {
                $pointer->set($path, $resolved);
            }
        }
    }

    /**
     * Check if the reference contains a fragment and resolve
     * the pointer.  Otherwise returns the original schema.
     *
     * @param  string $ref
     * @param  object $schema
     *
     * @return object
     */
    private function resolveFragment($ref, $schema)
    {
        $fragment = parse_url($ref, PHP_URL_FRAGMENT);
        if ($this->isExternalRef($ref) && is_string($fragment)) {
            if ($schema instanceof Reference) {
                $schema = $schema->resolve();
            }
            $pointer  = new Pointer($schema);
            return $pointer->get($fragment);
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
            return $refs;
        }

        foreach ($schema as $attribute => $parameter) {
            switch (true) {
                case $this->isRef($attribute, $parameter):
                    $refs[$path] = $parameter;
                    break;
                case is_object($parameter):
                    $refs = array_merge($refs, $this->getReferences($parameter, $this->pathPush($path, $attribute)));
                    break;
                case is_array($parameter):
                    foreach ($parameter as $k => $v) {
                        $refs = array_merge(
                            $refs,
                            $this->getReferences($v, $this->pathPush($this->pathPush($path, $attribute), $k))
                        );
                    }
                    break;
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
     * @param mixed  $attributeValue
     *
     * @return bool
     */
    private function isRef($attribute, $attributeValue)
    {
        return $attribute === '$ref' && is_string($attributeValue);
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    private function isInternalRef($value)
    {
        return is_string($value) && substr($value, 0, 1) === '#';
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    private function isExternalRef($value)
    {
        return !$this->isInternalRef($value);
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
                sprintf(
                    'Your path  "%s" is missing a valid prefix.  ' .
                    'The schema path should start with a prefix i.e. "file://".',
                    $path
                )
            );
        }
    }

    /**
     * Take a relative reference, and prepend the id of the schema and any
     * sub schemas to get the absolute url.
     *
     * @param object      $schema
     * @param string      $path
     * @param string      $ref
     * @param string|null $currentUri
     *
     * @return string
     */
    private function makeReferenceAbsolute($schema, $path, $ref, $currentUri = null)
    {
        // If the reference is absolute, we can just return it without walking the schema.
        if (!is_relative_ref($ref)) {
            return $ref;
        }

        $scope = $currentUri ?: '';
        $scope = $this->getResolvedResolutionScope($schema, $path, $scope);

        return resolve_uri($ref, $scope);
    }

    /**
     * Get the resolved resolution scope by walking the schema and resolving
     * every `id` against the most immediate parent scope.
     *
     * @see  http://json-schema.org/latest/json-schema-core.html#anchor27
     *
     * @param  object $schema
     * @param  string $path
     * @param  string $scope
     *
     * @return string
     */
    private function getResolvedResolutionScope($schema, $path, $scope)
    {
        $pointer     = new Pointer($schema);
        $currentPath = '';

        foreach (explode('/', $path) as $segment) {
            if (!empty($segment)) {
                $currentPath .= '/' . $segment;
            }
            if ($pointer->has($currentPath . '/id')) {
                $id = $pointer->get($currentPath . '/id');
                if (is_string($id)) {
                    $scope = resolve_uri($id, $scope);
                }
            }
        }

        return $scope;
    }
}
