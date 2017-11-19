<?php

namespace League\JsonGuard\Test;

use League\JsonGuard;
use League\JsonGuard\Constraint\DraftFour\Format\FormatExtensionInterface;
use League\JsonGuard\ConstraintInterface;
use League\JsonGuard\Exception\InvalidSchemaException;
use League\JsonGuard\Exception\MaximumDepthExceededException;
use League\JsonGuard\RuleSet\DraftFour;
use League\JsonGuard\Validator;
use League\JsonReference\Dereferencer;
use League\JsonReference\Loader\ArrayLoader;
use League\JsonReference\Loader\ChainedLoader;
use League\JsonReference\Loader\CurlWebLoader;
use PHPUnit\Framework\TestCase;
use function League\JsonGuard\error;
use League\JsonGuard\Constraint\DraftFour\NullValidator;

class ValidatorTest extends TestCase
{
    const TEST_SUITE_PATH = __DIR__ . '/../vendor/json-schema/JSON-Schema-Test-Suite/tests';

    public function allDraft4Tests()
    {
        $required = glob(self::TEST_SUITE_PATH . '/draft4/*.json');
        $optional = glob(self::TEST_SUITE_PATH . '/draft4/optional/*.json');
        $files    = array_merge($required, $optional);

        return array_map(function ($file) {
            return [$file];
        }, $files);
    }

    public function loadTest($uri)
    {
        // We need to use the option that treats big numbers as a
        // string value so that the 'bignum.json' test will pass.
        return json_decode(file_get_contents($uri), false, 512, JSON_BIGINT_AS_STRING);
    }

    /**
     * @dataProvider allDraft4Tests
     *
     * @param string $testFile
     */
    function test_it_passes_the_draft4_test_suite($testFile)
    {
        $this->runTestCase($this->loadTest($testFile));
    }

    public function invalidSchemas()
    {
        $schemas = json_decode(file_get_contents(__DIR__ . '/fixtures/invalid-schemas.json'));

        return array_map(function ($schema) {
            return [$schema];
        }, $schemas);
    }

    /**
     * @dataProvider invalidSchemas
     *
     * @param object $schema
     */
    function test_invalid_schemas_cause_exceptions($schema)
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

                if ($test->description === 'a bignum is not a string') {
                    // PHP cannot differentiate between large integers and strings.
                    continue;
                }

                if ($test->valid) {
                    $this->assertTrue($validator->passes(), $msg);
                } else {
                    $this->assertTrue($validator->fails(), $msg);
                }
            }
        }
    }

    public function custom_keyword_test()
    {
        $data        = (object) ["id" => 1, "name" => "test", "price" => 5];
        $schema      = $this->loadTest(__DIR__ . '/fixtures/custom_constraint.json');
        $refResolver = self::createDereferencer();
        $schema      = $refResolver->dereference($schema);

        return [[$data, $schema]];
    }

    /**
     * @dataProvider custom_keyword_test
     */
    public function test_it_passes_with_unknown_constraints_per_default($data, $schema)
    {
        $validator = new Validator($data, $schema);   
        $this->assertTrue($validator->passes());
    }

    /**
     * @dataProvider custom_keyword_test
     * @expectedException League\JsonGuard\Exception\ConstraintNotFoundException
     */
    public function test_it_fails_if_unknown_constraints_are_not_ignored_setter($data, $schema)
    {
        $validator = new Validator($data, $schema);
        $validator->setIgnoreUnknownConstraints(false);        
        $this->assertTrue($validator->passes());
    }

    /**
     * @dataProvider custom_keyword_test
     * @expectedException League\JsonGuard\Exception\ConstraintNotFoundException
     */
    public function test_it_fails_if_unknown_constraints_are_not_ignored_constructor($data, $schema)
    {
        $validator = new Validator($data, $schema, null, false);
        $this->assertTrue($validator->passes());
    }

    /**
     * @dataProvider custom_keyword_test
     */
    public function test_it_passes_with_unknown_constraints_if_not_ignored_but_registered($data, $schema)
    {
        $ruleSet = new DraftFour();
        $ruleSet->set('x-custom', NullValidator::class);       
        $validator = new Validator($data, $schema, $ruleSet, false);
        $this->assertTrue($validator->passes());
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
        $httpsLoader = new ChainedLoader(
            $arrayLoader,
            new CurlWebLoader('https://')
        );
        $httpLoader = new ChainedLoader(
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

        $this->assertSame(
            JsonGuard\Constraint\DraftFour\Type::KEYWORD,
            $errors[0]->getKeyword(),
            'The error should include the keyword'
        );
        $this->assertSame(
            'string',
            $errors[0]->getParameter(),
            'The error should include the parameter'
        );
        $this->assertSame(
            '/name',
            $errors[0]->getDataPath(),
            'The error should have a pointer to the data'
        );
        $this->assertSame(
            1234,
            $errors[0]->getData(),
            'The error should include the data'
        );
        $this->assertSame(
            '/properties/name/type',
            $errors[0]->getSchemaPath(),
            'The error should have a pointer to the schema'
        );
        $this->assertEquals(
            (object) ['type' => 'string', 'description' => 'Name of the product'],
            $errors[0]->getSchema(),
            'The error should include the schema'
        );
        $this->assertSame(
            json_encode($errors[0]->toArray()),
            json_encode($errors[0]),
            'The error message should encode as JSON.'
        );
        $this->assertSame(
            1234,
            $errors[0]->getCause(),
            'The error should include the cause'
        );

        $this->assertSame(
            '/sub-product/sub-product/tags/1',
            $errors[1]->getDataPath(),
            'Deeply nested data paths should work'
        );
        $this->assertSame(
            '/properties/sub-product/properties/sub-product/properties/tags/items/1/type',
            $errors[1]->getSchemaPath(),
            'Deeply nested schema paths should work'
        );
    }

    function test_error_message_pointer_is_escaped()
    {
        $data   = json_decode(file_get_contents(__DIR__ . '/fixtures/needs-escaping-data.json'));
        $schema = json_decode(file_get_contents(__DIR__ . '/fixtures/needs-escaping-schema.json'));

        $deref  = new Dereferencer();
        $schema = $deref->dereference($schema);

        $v = new Validator($data, $schema);

        $errors = $v->errors();
        $this->assertSame('/~1path/~0prop', $errors[0]->getDataPath());
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
        $this->assertSame('/foo/foo/foo/foo/foo/foo/foo/foo/foo', $error->getDataPath());
        $this->assertSame(JsonGuard\Constraint\DraftFour\AdditionalProperties::KEYWORD, $error->getKeyword());
    }

    function test_it__throws_max_depth_exception_when_max_depth_is_exceeded()
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

    function test_it_resets_max_depth_each_time_it_validates()
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
        $v->getRuleSet()->get('format')->addExtension('hello', new HelloFormatStub());

        $this->assertTrue($v->passes());

        $data = 'good morning world';
        $v = new Validator($data, $schema);
        $v->getRuleSet()->get('format')->addExtension('hello', new HelloFormatStub());

        $this->assertTrue($v->fails());
        $this->assertSame(JsonGuard\Constraint\DraftFour\Format::KEYWORD, $v->errors()[0]->getKeyword());
    }

    function test_custom_format_works_when_nested()
    {
        $schema = json_decode('{"properties": { "foo": {"type": "string", "format": "hello"} } }');

        $data = json_decode('{ "foo": "hello world" }');
        $v = new Validator($data, $schema);
        $v->getRuleSet()->get('format')->addExtension('hello', new HelloFormatStub());

        $this->assertTrue($v->passes());

        $data = json_decode('{ "foo": "good morning" }');
        $v = new Validator($data, $schema);
        $v->getRuleSet()->get('format')->addExtension('hello', new HelloFormatStub());

        $this->assertTrue($v->fails());
        $this->assertSame('format', $v->errors()[0]->getKeyword());
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

class EmojiConstraint implements ConstraintInterface
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

        return error('Not an emoji', $validator);
    }
}

class HelloFormatStub implements FormatExtensionInterface
{
    public function validate($value, Validator $validator)
    {
        if (stripos($value, 'hello') !== 0) {
            return error('must start with hello', $validator);
        }
    }
}
