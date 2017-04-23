<?php

namespace League\JsonGuard;

use League\JsonGuard\Exceptions\MaximumDepthExceededException;
use League\JsonGuard\RuleSets\DraftFour;
use League\JsonReference\Reference;
use Psr\Container\ContainerInterface;
use function League\JsonReference\pointer_push;

final class Validator
{
    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var mixed
     */
    private $data;

    /**
     * @var object
     */
    private $schema;

    /**
     * @var string
     */
    private $dataPath = '/';

    /**
     * @var string
     */
    private $baseSchemaPath = '/';

    /**
     * The maximum depth the validator should recurse into $data
     * before throwing an exception.
     *
     * @var int
     */
    private $maxDepth = 50;

    /**
     * The depth the validator has reached in the data.
     *
     * @var int
     */
    private $depth = 0;

    /**
     * @var \Psr\Container\ContainerInterface
     */
    private $ruleSet;

    /**
     * @var bool
     */
    private $hasValidated;

    /**
     * @var string
     */
    private $currentKeyword;

    /**
     * @var mixed
     */
    private $currentParameter;

    /**
     * @param mixed                   $data
     * @param object                  $schema
     * @param ContainerInterface|null $ruleSet
     */
    public function __construct($data, $schema, ContainerInterface $ruleSet = null)
    {
        if (!is_object($schema)) {
            throw new \InvalidArgumentException(
                sprintf('The schema should be an object from a json_decode call, got "%s"', gettype($schema))
            );
        }

        while ($schema instanceof Reference) {
            $schema = $schema->resolve();
        }

        $this->data    = $data;
        $this->schema  = $schema;
        $this->ruleSet = $ruleSet ?: new DraftFour();
    }

    /**
     * @return boolean
     */
    public function fails()
    {
        return !$this->passes();
    }

    /**
     * @return boolean
     */
    public function passes()
    {
        return empty($this->errors());
    }

    /**
     * Get a collection of errors.
     *
     * @return ValidationError[]
     */
    public function errors()
    {
        $this->validate();

        return $this->errors;
    }

    /**
     * Set the maximum allowed depth data will be validated until.
     * If the data exceeds the stack depth an exception is thrown.
     *
     * @param int $maxDepth
     *
     * @return $this
     */
    public function setMaxDepth($maxDepth)
    {
        $this->maxDepth = $maxDepth;

        return $this;
    }

    /**
     * @return \Psr\Container\ContainerInterface
     */
    public function getRuleSet()
    {
        return $this->ruleSet;
    }

    /**
     * @return string
     */
    public function getDataPath()
    {
        return $this->dataPath;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return object
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * @return string
     */
    public function getSchemaPath()
    {
        return pointer_push($this->baseSchemaPath, $this->currentKeyword);
    }

    /**
     * @return string
     */
    public function getCurrentKeyword()
    {
        return $this->currentKeyword;
    }

    /**
     * @return mixed
     */
    public function getCurrentParameter()
    {
        return $this->currentParameter;
    }

    /**
     * Create a new sub-validator.
     *
     * @param mixed       $data
     * @param object      $schema
     * @param string|null $dataPath
     * @param string|null $schemaPath
     *
     * @return Validator
     */
    public function makeSubSchemaValidator($data, $schema, $dataPath = null, $schemaPath = null)
    {
        $validator = new Validator($data, $schema, $this->ruleSet);

        $validator->dataPath         = $dataPath ?: $this->dataPath;
        $validator->baseSchemaPath   = $schemaPath ?: $this->getSchemaPath();
        $validator->maxDepth         = $this->maxDepth;
        $validator->depth            = $this->depth + 1;

        return $validator;
    }

    /**
     * Validate the data and collect the errors.
     */
    private function validate()
    {
        if ($this->hasValidated) {
            return;
        }

        $this->checkDepth();

        foreach ($this->schema as $rule => $parameter) {
            $this->currentKeyword   = $rule;
            $this->currentParameter = $parameter;
            $this->mergeErrors($this->validateRule($rule, $parameter));
            $this->currentKeyword = $this->currentParameter = null;
        }

        $this->hasValidated = true;
    }

    /**
     * Keep track of how many levels deep we have validated.
     * This is to prevent a really deeply nested JSON
     * structure from causing the validator to continue
     * validating for an incredibly long time.
     *
     * @throws \League\JsonGuard\Exceptions\MaximumDepthExceededException
     */
    private function checkDepth()
    {
        if ($this->depth > $this->maxDepth) {
            throw new MaximumDepthExceededException();
        }
    }

    /**
     * Validate the data using the given rule and parameter.
     *
     * @param string $keyword
     * @param mixed  $parameter
     *
     * @return null|ValidationError|ValidationError[]
     */
    private function validateRule($keyword, $parameter)
    {
        if (!$this->ruleSet->has($keyword)) {
            return null;
        }

        return $this->ruleSet->get($keyword)->validate($this->data, $parameter, $this);
    }

    /**
     * Merge the errors with our error collection.
     *
     * @param ValidationError[]|ValidationError|null $errors
     */
    private function mergeErrors($errors)
    {
        if (is_null($errors)) {
            return;
        }

        $errors       = is_array($errors) ? $errors : [$errors];
        $this->errors = array_merge($this->errors, $errors);
    }
}
