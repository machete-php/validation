<?php

namespace League\JsonGuard\Test;

use League\JsonGuard;
use League\JsonGuard\Dereferencer;
use League\JsonGuard\ErrorCode;
use League\JsonGuard\ValidationError;
use League\JsonGuard\Exceptions\MaximumDepthExceededException;
use League\JsonGuard\FormatExtension;
use League\JsonGuard\Loaders\ArrayLoader;
use League\JsonGuard\Loaders\CurlWebLoader;
use League\JsonGuard\Validator;
use League\JsonGuard\RuleSets\DraftFour;
use League\JsonGuard\Constraints\PropertyConstraint;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function allDraft4Tests()
    {
        $required = glob(schema_test_suite_path() . '/draft4/*.json');
        $optional = glob(schema_test_suite_path() . '/draft4/optional/*.json');
        $files    = array_merge($required, $optional);

        return array_map(function ($file) {
            return [$file];
        }, $files);
    }

    public function draft4CoreTests()
    {
        return array_map(function ($file) {
            return [$file];
        }, glob(schema_test_suite_path() . '/draft4/*.json'));
    }

    public function unofficialTests()
    {
        return array_map(function ($file) {
            return [$file];
        }, glob(__DIR__ . '/unofficial/*.json'));
    }

    /**
     * @dataProvider allDraft4Tests
     *
     * @param string $testFile
     */
    public function testAllOfDraft4($testFile)
    {
        // We need to use the option that treats big numbers as a
        // string value so that the 'bignum.json' test will pass.
        $test = json_decode(file_get_contents($testFile), false, 512, JSON_BIGINT_AS_STRING);

        $this->runTestCase($test);
    }

    /**
     * @dataProvider draft4CoreTests
     * @runInSeparateProcess
     *
     * @param string $testFile
     */
    public function testDraft4CoreTestsPassWithoutBcMath($testFile)
    {
        require __DIR__ . '/disable_bccomp.php';
        $test = json_decode(file_get_contents($testFile));

        $this->runTestCase($test);
    }

    /**
     * @dataProvider unofficialTests
     */
    public function testUnofficialTests($testFile)
    {
        $test = json_decode(file_get_contents($testFile));

        $this->runTestCase($test);
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
     * @return \League\JsonGuard\Dereferencer
     */
    private static function createDereferencer()
    {
        $arrayLoader = new ArrayLoader(
            ['json-schema.org/draft-04/schema#' => file_get_contents(__DIR__ . '/fixtures/draft4-schema.json')]
        );
        $httpsLoader = new JsonGuard\Loaders\ChainableLoader(
            $arrayLoader,
            new CurlWebLoader('https://')
        );
        $httpLoader = new JsonGuard\Loaders\ChainableLoader(
            $arrayLoader,
            new CurlWebLoader('http://')
        );
        $refResolver  = new Dereferencer();
        $refResolver->registerLoader($httpLoader, 'http');
        $refResolver->registerLoader($httpsLoader, 'https');

        return $refResolver;
    }

    public function testErrorMessages()
    {
        $data   = json_decode(file_get_contents(__DIR__ . '/fixtures/invalid.json'));
        $schema = json_decode(file_get_contents(__DIR__ . '/fixtures/schema.json'));

        $deref  = new Dereferencer();
        $schema = $deref->dereference($schema);

        $v = new Validator($data, $schema);

        $errors = $v->errors();
        $this->assertCount(2, $errors);
        $this->assertTrue(isset($errors[0]['code']));
        $this->assertSame(ErrorCode::INVALID_STRING, $errors[0]['code']);
        $this->assertSame('/name', $errors[0]['pointer']);

        $this->assertSame(ErrorCode::INVALID_STRING, $errors[1]['code']);
        $this->assertSame('/sub-product/sub-product/tags/1', $errors[1]['pointer']);
        $this->assertSame(json_encode($errors[0]->toArray()), json_encode($errors[0]));
    }

    public function testErrorMessagePointerIsEscaped()
    {
        $data   = json_decode(file_get_contents(__DIR__ . '/fixtures/needs-escaping-data.json'));
        $schema = json_decode(file_get_contents(__DIR__ . '/fixtures/needs-escaping-schema.json'));

        $deref  = new Dereferencer();
        $schema = $deref->dereference($schema);

        $v = new Validator($data, $schema);

        $errors = $v->errors();
        $this->assertSame('/~1path/~0prop', $errors[0]['pointer']);
    }

    public function testDeeplyNestedDataWithinReason()
    {
        $schema = json_decode('{"properties": {"foo": {"$ref": "#"}}, "additionalProperties": false}');
        $deref  = new Dereferencer();
        $schema = $deref->dereference($schema);

        $data = json_decode('{"foo": {"foo": {"foo": {"foo": {"foo": {"foo": {"foo": {"foo": {"foo": {"bar": {}}}}}}}}}}}');
        $v = new Validator($data, $schema);
        $this->assertTrue($v->fails());
        $error = $v->errors()[0];
        $this->assertSame('/foo/foo/foo/foo/foo/foo/foo/foo/foo', $error['pointer']);
        $this->assertSame(ErrorCode::NOT_ALLOWED_PROPERTY, $error['code']);
    }

    public function testStackAttack()
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

    public function testMaxDepth()
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

    public function testMaxDepthIsReset()
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

    public function testCustomFormat()
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
        $this->assertSame(99, $v->errors()[0]['code']);
    }

    public function testCustomFormatWorksWhenNested()
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
        $this->assertSame(99, $v->errors()[0]['code']);
    }

    public function testCustomRuleset()
    {
        $schema = json_decode('{"properties": { "foo": {"type": "string", "emoji": true} } }');
        $data = json_decode('{ "foo": ":)" }');

        $ruleSet = new CustomRulesetStub();
        $v = new Validator($data, $schema, $ruleSet);
        $this->assertTrue($v->passes());

        $data = json_decode('{ "foo": "yo" }');
        $v = new Validator($data, $schema, $ruleSet);
        $this->assertTrue($v->fails());
    }
}

class CustomRulesetStub extends DraftFour
{
    /**
     * {@inheritdoc}
     */
    public function has($rule)
    {
        if ($rule === 'emoji') {
            return true;
        }
        return parent::has($rule);
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraint($rule)
    {
        if ($rule === 'emoji') {
            return new EmojiConstraint();
        }
        return parent::getConstraint($rule);
    }
}

class EmojiConstraint implements PropertyConstraint
{
    protected static $emojis = [
        ':)',
        ':(',
        ':D',
        ';)'
    ];

    /**
     * @param mixed       $value
     * @param mixed       $parameter
     * @param string|null $pointer
     *
     * @return \League\JsonGuard\ValidationError|null
     */
    public static function validate($value, $parameter, $pointer = null)
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
            return new JsonGuard\ValidationError('Must start with hello', 99, $value, $pointer);
        }
    }
}
