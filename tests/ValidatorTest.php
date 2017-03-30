<?php

namespace League\JsonGuard\Test;

use League\JsonGuard;
use League\JsonGuard\Constraints\Constraint;
use League\JsonReference\Dereferencer;
use League\JsonGuard\Exceptions\InvalidSchemaException;
use League\JsonGuard\ValidationError;
use League\JsonGuard\Exceptions\MaximumDepthExceededException;
use League\JsonGuard\FormatExtension;
use League\JsonReference\Loaders\ArrayLoader;
use League\JsonReference\Loaders\ChainableLoader;
use League\JsonReference\Loaders\CurlWebLoader;
use League\JsonGuard\Validator;
use League\JsonGuard\RuleSets\DraftFour;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function allDraft4Tests()
    {
        $required = glob(static::schemaTestSuitePath() . '/draft4/*.json');
        $optional = glob(static::schemaTestSuitePath() . '/draft4/optional/*.json');
        $files    = array_merge($required, $optional);

        return array_map(function ($file) {
            return [$file];
        }, $files);
    }

    public function invalidSchemas()
    {
        $schemas = json_decode(file_get_contents(__DIR__ . '/fixtures/invalid-schemas.json'));

        return array_map(function ($schema) {
            return [$schema];
        }, $schemas);
    }

    public static function schemaTestSuitePath()
    {
        return realpath(__DIR__ . '/../vendor/json-schema/JSON-Schema-Test-Suite/tests');
    }

    /**
     * @dataProvider allDraft4Tests
     *
     * @param string $testFile
     */
    function test_it_passes_the_draft4_test_suite($testFile)
    {
        // We need to use the option that treats big numbers as a
        // string value so that the 'bignum.json' test will pass.
        $test = json_decode(file_get_contents($testFile), false, 512, JSON_BIGINT_AS_STRING);

        $this->runTestCase($test);
    }

    /**
     * @dataProvider invalidSchemas
     */
    function test_invalid_schemas($schema)
    {
        $this->setExpectedException(InvalidSchemaException::class);
        $validator = new Validator([], $schema);
        $validator->errors();
    }

    /**
     * Run a test case from the standard test suite.
     *
     * @param object $test
     */
    public function runTestCase($test)
    {
        foreach ($test as $testCase) {
            $schema      = $testCase->schema;
            $description = $testCase->description;
            $refResolver = self::createDereferencer();
            $schema      = $refResolver->dereference($schema);

            foreach ($testCase->tests as $test) {
                $validator = new Validator($test->data, $schema);
                $msg       = $description . ' : ' . $test->description;

                if ($test->valid) {
                    $this->assertTrue($validator->passes(), $msg);
                } else {
                    $this->assertTrue($validator->fails(), $msg);
                }
            }
        }
    }

    /**
     * This method creates a dereferencer that will load the json-schema.org/draft-04/schema
     * schema from memory, but defer any other http(s) calls to the Curl loader.  This allows
     * us to run tests without requiring the json-schema.org website to be available.
     *
     * @return \League\JsonReference\Dereferencer
     */
    private static function createDereferencer()
    {
        $arrayLoader = new ArrayLoader(
            ['json-schema.org/draft-04/schema' => file_get_contents(__DIR__ . '/fixtures/draft4-schema.json')]
        );
        $httpsLoader = new ChainableLoader(
            $arrayLoader,
            new CurlWebLoader('https://')
        );
        $httpLoader = new ChainableLoader(
            $arrayLoader,
            new CurlWebLoader('http://')
        );
        $refResolver  = Dereferencer::draft4();
        $refResolver->getLoaderManager()->registerLoader('http', $httpLoader);
        $refResolver->getLoaderManager()->registerLoader('https', $httpsLoader);

        return $refResolver;
    }

    function test_error_messages()
    {
        $data   = json_decode(file_get_contents(__DIR__ . '/fixtures/invalid.json'));
        $schema = json_decode(file_get_contents(__DIR__ . '/fixtures/schema.json'));

        $deref  = new Dereferencer();
        $schema = $deref->dereference($schema);

        $v = new Validator($data, $schema);

        $errors = $v->errors();
        $this->assertCount(2, $errors);
        $this->assertTrue(isset($errors[0]['keyword']));
        $this->assertSame(JsonGuard\Constraints\Type::KEYWORD, $errors[0]['keyword']);
        $this->assertSame('/name', $errors[0]['pointer']);

        $this->assertSame(JsonGuard\Constraints\Type::KEYWORD, $errors[1]['keyword']);
        $this->assertSame('/sub-product/sub-product/tags/1', $errors[1]['pointer']);
        $this->assertSame(json_encode($errors[0]->toArray()), json_encode($errors[0]));
    }

    function test_error_message_pointer_is_escaped()
    {
        $data   = json_decode(file_get_contents(__DIR__ . '/fixtures/needs-escaping-data.json'));
        $schema = json_decode(file_get_contents(__DIR__ . '/fixtures/needs-escaping-schema.json'));

        $deref  = new Dereferencer();
        $schema = $deref->dereference($schema);

        $v = new Validator($data, $schema);

        $errors = $v->errors();
        $this->assertSame('/~1path/~0prop', $errors[0]['pointer']);
    }

    function test_deeply_nested_data_within_reason_validates()
    {
        $schema = json_decode('{"properties": {"foo": {"$ref": "#"}}, "additionalProperties": false}');
        $deref  = new Dereferencer();
        $schema = $deref->dereference($schema);

        $data = json_decode('{"foo": {"foo": {"foo": {"foo": {"foo": {"foo": {"foo": {"foo": {"foo": {"bar": {}}}}}}}}}}}');
        $v = new Validator($data, $schema);
        $this->assertTrue($v->fails());
        $error = $v->errors()[0];
        $this->assertSame('/foo/foo/foo/foo/foo/foo/foo/foo/foo', $error['pointer']);
        $this->assertSame(JsonGuard\Constraints\AdditionalProperties::KEYWORD, $error['keyword']);
    }

    function test_stack_attack_throws_max_depth_exception()
    {
        $this->setExpectedException(MaximumDepthExceededException::class);
        $schema = json_decode('{"properties": {"foo": {"$ref": "#"}}, "additionalProperties": false}');
        $deref  = new Dereferencer();
        $schema = $deref->dereference($schema);

        $data = json_decode(file_get_contents(__DIR__ . '/fixtures/stack-attack.json'));

        $v = new Validator($data, $schema);
        $v->setMaxDepth(10);
        $v->passes();
    }

    function test_it_throws_when_max_depth_is_exceeded()
    {
        $schema = json_decode('{"properties": {"foo": {"$ref": "#"}}, "additionalProperties": false}');
        $deref  = new Dereferencer();
        $schema = $deref->dereference($schema);

        $data = json_decode('{"foo": {"foo": {}}}');

        $v = new Validator($data, $schema);
        $v->setMaxDepth(2);
        $v->passes(); // should not throw an exception.

        $this->setExpectedException(MaximumDepthExceededException::class);
        $v = new Validator($data, $schema);
        $v->setMaxDepth(1);
        $v->passes();
    }

    function test_max_depth_is_reset()
    {
        $schema = json_decode('{"properties": {"foo": {"$ref": "#"}}, "additionalProperties": false}');
        $deref  = new Dereferencer();
        $schema = $deref->dereference($schema);

        $data = json_decode('{"foo": {"foo": {}}}');

        $v = new Validator($data, $schema);
        $v->setMaxDepth(3);
        $v->passes();
        $v->passes();
        $v->passes();
    }

    function test_it_can_use_a_custom_format()
    {
        $schema = json_decode('{"format": "hello"}');

        $data = 'hello world';
        $v = new Validator($data, $schema);
        $v->registerFormatExtension('hello', new HelloFormatStub());

        $this->assertTrue($v->passes());

        $data = 'good morning world';
        $v = new Validator($data, $schema);
        $v->registerFormatExtension('hello', new HelloFormatStub());

        $this->assertTrue($v->fails());
        $this->assertSame(JsonGuard\Constraints\Format::KEYWORD, $v->errors()[0]['keyword']);
    }

    function test_custom_format_works_when_nested()
    {
        $schema = json_decode('{"properties": { "foo": {"type": "string", "format": "hello"} } }');

        $data = json_decode('{ "foo": "hello world" }');
        $v = new Validator($data, $schema);
        $v->registerFormatExtension('hello', new HelloFormatStub());

        $this->assertTrue($v->passes());

        $data = json_decode('{ "foo": "good morning" }');
        $v = new Validator($data, $schema);
        $v->registerFormatExtension('hello', new HelloFormatStub());

        $this->assertTrue($v->fails());
        $this->assertSame('format', $v->errors()[0]['keyword']);
    }

    function test_it_can_use_a_custom_ruleset()
    {
        $schema = json_decode('{"properties": { "foo": {"type": "string", "emoji": true} } }');
        $data = json_decode('{ "foo": ":)" }');

        $ruleSet = new DraftFour();
        $ruleSet->set('emoji', EmojiConstraint::class);
        $v = new Validator($data, $schema, $ruleSet);
        $this->assertTrue($v->passes());

        $data = json_decode('{ "foo": "yo" }');
        $v = new Validator($data, $schema, $ruleSet);
        $this->assertTrue($v->fails());
    }

    function test_throws_when_instantiated_with_a_non_object_schema()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        new Validator([], []);
    }

    function test_nested_reference() {
        $deref = new Dereferencer();
        $path   = 'file://' . __DIR__ . '/fixtures/client.json';
        $schema = $deref->dereference($path);

        $validator = new Validator((object) [
            'name' => 'Test user',
            'phone' => 'some-phone',
            'company' => (object) [
                'name' => 'some-company'
            ]
        ], $schema);

        $this->assertTrue($validator->fails());
    }
}

class EmojiConstraint implements Constraint
{
    protected static $emojis = [
        ':)',
        ':(',
        ':D',
        ';)'
    ];

    public function validate($value, $parameter, Validator $validator)
    {
        if (array_search($value, static::$emojis) !== false) {
            return null;
        }

        return new ValidationError('Not an emoji', 999, (string) $value);
    }
}

class HelloFormatStub implements FormatExtension
{
    public function validate($value, $pointer = null)
    {
        if (stripos($value, 'hello') !== 0) {
            return new JsonGuard\ValidationError('Must start with hello', 'format', $value, $pointer);
        }
    }
}
