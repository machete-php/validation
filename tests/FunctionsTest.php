<?php

namespace League\JsonGuard\Test;

class FunctionsTest extends \PHPUnit_Framework_TestCase
{
    public function validJsonIntegerDataProvider()
    {
        return [
            ["98249283749234923498293171823948729348710298301928331", true],
            ["-98249283749234923498293171823948729348710298301928331", true],
            [99, true],
            ["hello-world", false],
            ["99", false],
            ["-99", false],
            ["", false]
        ];
    }

    /**
     * @dataProvider validJsonIntegerDataProvider
     *
     * @param string|int $jsonInteger
     * @param bool $isValid
     */
    public function testIsJsonInteger($jsonInteger, $isValid)
    {
        if ($isValid) {
            $this->assertTrue(\League\JsonGuard\is_json_integer($jsonInteger));
        } else {
            $this->assertFalse(\League\JsonGuard\is_json_integer($jsonInteger));
        }
    }

    public function testUris()
    {
        return [
            // Technically the spec adds the superfluous # at the end, but we don't need to enforce that.
            ['http://x.y.z/rootschema.json#', '', 'http://x.y.z/rootschema.json#'],
            ['#foo', 'http://x.y.z/rootschema.json#', 'http://x.y.z/rootschema.json#foo'],
            ['otherschema.json', 'http://x.y.z/rootschema.json#', 'http://x.y.z/otherschema.json'],
            ['#bar', 'http://x.y.z/otherschema.json#', 'http://x.y.z/otherschema.json#bar'],
            ['t/inner.json#a', 'http://x.y.z/otherschema.json#', 'http://x.y.z/t/inner.json#a'],
            ['some://where.else/completely#', 'http://x.y.z/rootschema.json#', 'some://where.else/completely'],
            ['folderInteger.json', 'http://localhost:1234/folder/', 'http://localhost:1234/folder/folderInteger.json'],
            ['some-id.json', '', 'some-id.json'],
            ['item.json', 'http://some/where/other-item.json', 'http://some/where/item.json'],
            ['item.json', 'file:///schemas/other-item.json', 'file:///schemas/item.json'],
        ];
    }

    /**
     * @dataProvider testUris
     */
    public function testResolveUri($id, $parentScope, $expectedResult)
    {
        $result = \League\JsonGuard\resolve_uri($id, $parentScope);
        $this->assertSame($expectedResult, $result);
    }
}
