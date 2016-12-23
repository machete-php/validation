<?php

namespace League\JsonGuard\Test;

use League\JsonGuard\Dereferencer;
use League\JsonGuard\Loader;
use League\JsonGuard\Loaders\ArrayLoader;
use League\JsonGuard\Reference;

class DereferencerTest extends \PHPUnit_Framework_TestCase
{
    public function testInline()
    {
        $deref  = new Dereferencer();
        $path   = 'file://' . __DIR__ . '/fixtures/inline-ref.json';
        $result = $deref->dereference($path);

        $this->assertSame(json_encode($result->definitions->address), json_encode($result->properties->billing_address->resolve()));
        $this->assertSame(json_encode($result->definitions->address), json_encode($result->properties->shipping_address->resolve()));
    }

    public function testRemote()
    {
        $loader = new ArrayLoader(
            ['json-schema.org/draft-04/schema' => file_get_contents(__DIR__ . '/fixtures/draft4-schema.json')]
        );
        $deref  = new Dereferencer();
        $deref->registerLoader($loader, 'http');
        $deref->registerLoader($loader, 'https');
        $result = $deref->dereference('http://json-schema.org/draft-04/schema#');
        $this->assertSame($result->definitions->positiveIntegerDefault0, $result->properties->minItems->resolve());
    }

    public function testRemoteWithoutId()
    {
        $deref  = new Dereferencer();
        $result = $deref->dereference('http://localhost:1234/albums.json');

        $this->assertSame('string', $result->items->properties->title->type);
    }

    public function testRemoteWithoutIdThrowsIfDereferencingAnObject()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        $deref  = new Dereferencer();
        $result = $deref->dereference(json_decode('{"$ref": "album.json"}'));
    }

    public function testWebRemoteWithFragment()
    {
        $deref  = new Dereferencer();
        $result = $deref->dereference('http://localhost:1234/subSchemas.json#/relativeRefToInteger');
        $this->assertSame(['type' => 'integer'], (array) $result);
    }

    public function testFileRemoteWithFragment()
    {
        $deref  = new Dereferencer();
        $path = 'file://' . __DIR__ . '/fixtures/schema.json#/properties';
        $result = $deref->dereference($path);
        $this->assertArrayHasKey('name', (array) $result);
    }

    public function testRecursiveRootPointer()
    {
        $deref  = new Dereferencer();
        $path   = 'file://' . __DIR__ . '/fixtures/recursive-root-pointer.json';
        $result = $deref->dereference($path);
        $this->assertSame($result->properties->foo, $result->properties->foo->properties->foo->properties->foo);
    }

    public function testCircularReferenceToSelf()
    {
        $deref  = new Dereferencer();
        $path   = 'file://' . __DIR__ . '/fixtures/circular-ref-self.json';
        $result = $deref->dereference($path);
        $this->assertSame(
            '{"$ref":"#\/definitions\/thing"}',
            json_encode($result->definitions->thing)
        );
        $this->assertSame(
            $result->definitions->thing,
            $result->definitions->thing->resolve()->resolve()->resolve()->resolve(),
            'You should be able to resolve recursive definitions to any depth'
        );
    }

    public function testCircularReferenceToParent()
    {
        $deref  = new Dereferencer();
        $path   = 'file://' . __DIR__ . '/fixtures/circular-ref-parent.json';
        $result = $deref->dereference($path);
        $ref    = $result
            ->definitions
            ->person
            ->properties
            ->spouse
            ->type
            ->resolve();

        $this->assertSame(json_encode($result->definitions->person), json_encode($ref));
    }

    public function testReferenceInArray()
    {
        $deref  = new Dereferencer();
        $path   = 'file://' . __DIR__ . '/fixtures/array-ref.json';
        $result = $deref->dereference($path);
        $this->assertSame($result->items[0], $result->items[1]->resolve());
    }

    public function testReferenceInPropertyThatBeginsWithSlash()
    {
        $deref  = new Dereferencer();
        $path   = 'file://' . __DIR__ . '/fixtures/slash-property.json';
        $result = $deref->dereference($path);
        $slashProperty = '/slash-item';
        $this->assertSame($result->$slashProperty->key, $result->item->key);
    }

    public function testReferenceInPropertyThatContainsTilde()
    {
        $deref  = new Dereferencer();
        $path   = 'file://' . __DIR__ . '/fixtures/tilde-property.json';
        $result = $deref->dereference($path);
        $tildeProperty = 'tilde~item';
        $this->assertSame($result->$tildeProperty->key, $result->item->key);
    }

    public function testPropertyNamedRefIsNotAReference()
    {
        $deref  = new Dereferencer();
        $path   = 'file://' . __DIR__ . '/fixtures/property-named-ref.json';
        $result = $deref->dereference($path);

        $ref = '$ref';
        $this->assertTrue(is_object($result->properties->$ref));
        $this->assertSame($result->properties->$ref->description, 'The name of the property is $ref, but it\'s not a reference.');
    }

    public function testProperlyResolvesRelativeScopeAgainstAnAbsoluteId()
    {
        $deref = new Dereferencer();
        $result = $deref->dereference(json_decode('{"id": "http://localhost:1234/test.json", "properties": {"album": {"$ref": "album.json"}}}'));
        $this->assertSame('object', $result->properties->album->type);
    }

    public function testCircularExternalReference()
    {
        $deref  = new Dereferencer();
        $path   = 'file://' . __DIR__ . '/fixtures/circular-ext-ref.json';
        $result = $deref->dereference($path);
        $this->assertInstanceOf(Reference::class, $result->properties->rating);
        $this->assertFalse($result->properties->rating->additionalProperties);
        $this->assertFalse($result->properties->rating->properties->rating->additionalProperties);
    }

    public function testGetLoaders()
    {
        $deref = new Dereferencer();
        $loaders = $deref->getLoaders();
        $this->assertArrayHasKey('file', $loaders);
        $this->assertInstanceOf(Loader::class, $loaders['file']);
        $this->assertArrayHasKey('http', $loaders);
        $this->assertInstanceOf(Loader::class, $loaders['http']);
        $this->assertArrayHasKey('https', $loaders);
        $this->assertInstanceOf(Loader::class, $loaders['https']);
    }

    public function testGetLoaderThrowsIfTheLoaderDoesNotExist()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        $deref = new Dereferencer();
        $deref->dereference('couchdb://some-schema');
    }
}
