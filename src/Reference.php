<?php

namespace League\JsonGuard;

use Closure;

/**
 * A Reference object represents an internal $ref in a JSON object.
 * Because JSON references can be circular, in-lining the reference is
 * impossible.  This object can be substituted for the $ref instead,
 * allowing lazy resolution of the $ref when needed.
 */
class Reference implements \JsonSerializable, \IteratorAggregate
{
    /**
     * A JSON object resulting from a json_decode call or a Closure.
     * If a closure is used it should resolve to a JSON object when called.
     *
     * @var object|Closure
     */
    private $schema;

    private $cached;

    /**
     * A valid JSON reference.  The reference should point to a location in $schema.
     * @see https://tools.ietf.org/html/draft-pbryan-zyp-json-ref-03
     *
     * @var string
     */
    private $ref;

    /**
     * @param object|Closure $schema
     * @param string         $ref
     */
    public function __construct($schema, $ref)
    {
        $this->schema = $schema;
        $this->ref    = $ref;
    }

    /**
     * Specify data which should be serialized to JSON.
     * Because a reference can be circular, references are always
     * re-serialized as the reference property instead of attempting
     * to inline the data.
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        return ['$ref' => $this->ref];
    }

    /**
     * Resolve the reference and return the data.
     *
     * @return mixed
     */
    public function resolve()
    {
        if (empty($this->cached)) {
            if ($this->schema instanceof Closure) {
                return $this->resolveExternalReference();
            }

            return $this->resolveInternalReference();
        }

        return $this->cached;
    }

    /**
     * Resolve an external reference and return the resolved schema.
     *
     * @return mixed The resolved schema
     */
    private function resolveExternalReference()
    {
        return call_user_func($this->schema);
    }

    /**
     * Resolve an internal reference and return the resolved schema.
     *
     * @return mixed The resolved schema
     */
    private function resolveInternalReference()
    {
        $path    = trim($this->ref, '#');
        $pointer = new Pointer($this->schema);
        return $pointer->get($path);
    }

    /**
     * Proxies property access to the underlying schema.
     *
     * @param  string $property
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function __get($property)
    {
        $schema = $this->resolve();
        if (isset($schema->$property)) {
            return $schema->$property;
        }

        throw new \InvalidArgumentException(sprintf('Unknown property "%s"', $property));
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->resolve());
    }
}
