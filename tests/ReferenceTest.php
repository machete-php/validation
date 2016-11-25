<?php

namespace League\JsonGuard\Test;

use League\JsonGuard\Reference;

class ReferenceTest extends \PHPUnit_Framework_TestCase
{
    private $json = <<<HERE
{
  "properties": {
    "id": { "type": "integer", "minLength": 1 },
    "name": { "type": "string" }
  }
}
HERE;

    public function testResolvesInternalReference()
    {
        $reference = new Reference(json_decode($this->json), '#/properties/id/type');

        $this->assertSame('integer', $reference->resolve());
    }

    public function testProxiesAccessToUnderlyingSchema()
    {
        $reference = new Reference(json_decode($this->json), '#/properties');

        $this->assertSame('string', $reference->name->type);
    }

    public function testThrowsWhenTryingToAccessANonExistentProperty()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        $reference = new Reference(json_decode($this->json), '#/properties');
        $val = $reference->address;
    }

    public function testCanResolveAnExternalReferenceLazily()
    {
        $called  = false;
        $closure = function () use (&$called) {
            $called = true;
            return json_decode($this->json)->properties->id->type;
        };
        $reference = new Reference($closure, '#/properties/id/type');
        $this->assertFalse($called, 'The closure should not be resolved when the reference is instantiated.');
        $this->assertSame('integer', $reference->resolve());
    }

    public function testSerializesAsTheReference()
    {
        $reference = new Reference(json_decode($this->json), '#/properties/id/type');
        $expected = '{"$ref":"#/properties/id/type"}';
        $this->assertSame($expected, json_encode($reference, JSON_UNESCAPED_SLASHES));
    }
}
