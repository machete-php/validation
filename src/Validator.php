<?php

namespace League\JsonGuard;

use League\JsonGuard\Constraints\Constraint;
use League\JsonGuard\Constraints\ContainerInstanceConstraint;
use League\JsonGuard\Constraints\ParentSchemaAwareContainerInstanceConstraint;
use League\JsonGuard\Constraints\ParentSchemaAwarePropertyConstraint;
use League\JsonGuard\Constraints\PropertyConstraint;
use League\JsonGuard\Exceptions\MaximumDepthExceededException;
use League\JsonGuard\RuleSets\DraftFour;

class Validator implements SubSchemaValidatorFactory
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
    private $pointer = '';

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
     * @var \League\JsonGuard\FormatExtension[]
     */
    private $formatExtensions = [];

    /**
     * @var \League\JsonGuard\RuleSet
     */
    private $ruleSet;

    /**
     * @var bool
     */
    private $hasValidated;

    /**
     * @param mixed  $data
     * @param object $schema
     * @param RuleSet|null   $ruleSet
     */
    public function __construct($data, $schema, $ruleSet = null)
    {
        if (!is_object($schema)) {
            throw new \InvalidArgumentException(
                sprintf('The schema should be an object from a json_decode call, got "%s"', gettype($schema))
            );
        }

        if ($schema instanceof Reference) {
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
     * @return $this
     */
    public function setMaxDepth($maxDepth)
    {
        $this->maxDepth = $maxDepth;

        return $this;
    }

    /**
     * Register a custom format validation extension.
     *
     * @param string          $format
     * @param FormatExtension $extension
     */
    public function registerFormatExtension($format, FormatExtension $extension)
    {
        $this->formatExtensions[$format] = $extension;
    }

    /**
     * @internal
     * @param FormatExtension[] $formatExtensions
     * @return $this
     */
    public function setFormatExtensions(array $formatExtensions)
    {
        $this->formatExtensions = $formatExtensions;

        return $this;
    }

    /**
     * @internal
     * @param int $depth
     * @return $this
     */
    public function setDepth($depth)
    {
        $this->depth = $depth;

        return $this;
    }

    /**
     * @internal
     * @return string
     */
    public function getPointer()
    {
        return $this->pointer;
    }

    /**
     * @internal
     * @param string $pointer
     * @return $this
     */
    public function setPointer($pointer)
    {
        $this->pointer = $pointer;

        return $this;
    }

    /**
     * Create a new sub-validator.
     *
     * @param mixed  $data
     * @param object $schema
     * @param string $pointer
     * @return Validator
     */
    public function makeSubSchemaValidator($data, $schema, $pointer)
    {
        return (new Validator($data, $schema))
            ->setPointer($pointer)
            ->setMaxDepth($this->maxDepth)
            ->setFormatExtensions($this->formatExtensions)
            ->setDepth($this->depth + 1);
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
            $errors = $this->validateRule($rule, $parameter);
            $this->mergeErrors($errors);
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
     * @param string $rule
     * @param mixed $parameter
     * @return null|ValidationError|ValidationError[]
     */
    private function validateRule($rule, $parameter)
    {
        if (!$this->ruleSet->has($rule)) {
            return null;
        }

        if ($this->isCustomFormatExtension($rule, $parameter)) {
            return $this->validateCustomFormat($parameter);
        }

        $constraint = $this->ruleSet->getConstraint($rule);

        return $this->invokeConstraint($constraint, $parameter);
    }

    /**
     * Invoke the given constraint and return the validation errors.
     *
     * @param \League\JsonGuard\Constraints\Constraint $constraint
     * @param mixed                                    $parameter
     *
     * @return \League\JsonGuard\ValidationError|\League\JsonGuard\ValidationError[]|null
     */
    private function invokeConstraint(Constraint $constraint, $parameter)
    {
        if ($constraint instanceof PropertyConstraint) {
            return $constraint::validate($this->data, $parameter, $this->getPointer());
        } elseif ($constraint instanceof ParentSchemaAwarePropertyConstraint) {
            return $constraint::validate($this->data, $this->schema, $parameter, $this->getPointer());
        } elseif ($constraint instanceof ContainerInstanceConstraint) {
            return $constraint::validate($this->data, $parameter, $this, $this->getPointer());
        } elseif ($constraint instanceof ParentSchemaAwareContainerInstanceConstraint) {
            return $constraint::validate($this->data, $this->schema, $parameter, $this, $this->getPointer());
        }

        throw new \InvalidArgumentException('Invalid constraint.');
    }

    /**
     * Determine if a rule has a custom format extension registered.
     *
     * @param string $rule
     * @param mixed $parameter
     *
     * @return bool
     */
    private function isCustomFormatExtension($rule, $parameter)
    {
        return $rule === 'format' && isset($this->formatExtensions[$parameter]);
    }

    /**
     * Call a custom format extension to validate the data.
     *
     * @param string $format
     *
     * @return ValidationError|null
     */
    private function validateCustomFormat($format)
    {
        /** @var FormatExtension $extension */
        $extension = $this->formatExtensions[$format];

        return $extension->validate($this->data, $this->getPointer());
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

        if (is_array($errors)) {
            $this->errors = array_merge($this->errors, $errors);
            return;
        }

        $this->errors[] = $errors;
    }
}
