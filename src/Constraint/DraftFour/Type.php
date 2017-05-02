<?php

namespace League\JsonGuard\Constraint\DraftFour;

use League\JsonGuard\Assert;
use League\JsonGuard\ConstraintInterface;
use League\JsonGuard\ValidationError;
use League\JsonGuard\Validator;
use function League\JsonGuard\error;

final class Type implements ConstraintInterface
{
    const KEYWORD = 'type';

    /**
     * Whether examples like 98249283749234923498293171823948729348710298301928331
     * and "98249283749234923498293171823948729348710298301928331" are valid strings.
     */
    const BIGINT_MODE_STRING_VALID = 1;
    const BIGINT_MODE_STRING_INVALID = 2;

    /**
     * @var int
     */
    private $bigintMode = 0;

    /**
     * @param int $bigintMode
     */
    public function __construct($bigintMode = self::BIGINT_MODE_STRING_INVALID)
    {
        $this->setBigintMode($bigintMode);
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, $type, Validator $validator)
    {
        Assert::type($type, ['array', 'string'], self::KEYWORD, $validator->getSchemaPath());

        if (is_array($type)) {
            return $this->anyType($value, $type, $validator);
        }

        switch ($type) {
            case 'object':
                return $this->validateType($value, 'is_object', $validator);
            case 'array':
                return $this->validateType($value, 'is_array', $validator);
            case 'boolean':
                return $this->validateType($value, 'is_bool', $validator);
            case 'null':
                return $this->validateType($value, 'is_null', $validator);
            case 'number':
                return $this->validateType(
                    $value,
                    'League\JsonGuard\is_json_number',
                    $validator
                );
            case 'integer':
                return $this->validateType(
                    $value,
                    'League\JsonGuard\is_json_integer',
                    $validator
                );
            case 'string':
                return $this->validateType(
                    $value,
                    function ($value) {
                        if (is_string($value)) {
                            // Make sure the string isn't actually a number that was too large
                            // to be cast to an int on this platform.  This will only happen if
                            // you decode JSON with the JSON_BIGINT_AS_STRING option.
                            if (self::BIGINT_MODE_STRING_VALID === $this->bigintMode
                                || !(ctype_digit($value) && bccomp($value, PHP_INT_MAX) === 1)) {
                                return true;
                            }
                        }

                        return false;
                    },
                    $validator
                );
        }
    }

    /**
     * @param int|null $bigintMode
     *
     * @throws \InvalidArgumentException
     */
    public function setBigintMode($bigintMode = self::BIGINT_MODE_STRING_INVALID)
    {
        if (!in_array($bigintMode, [self::BIGINT_MODE_STRING_VALID, self::BIGINT_MODE_STRING_INVALID])) {
            throw new \InvalidArgumentException('Please use one of the bigint mode constants.');
        }

        $this->bigintMode = $bigintMode;
    }

    /**
     * @return int
     */
    public function getBigintMode()
    {
        return $this->bigintMode;
    }

    /**
     * @param mixed                       $value
     * @param callable                    $callable
     * @param \League\JsonGuard\Validator $validator
     *
     * @return \League\JsonGuard\ValidationError|null
     *
     */
    private function validateType($value, callable $callable, Validator $validator)
    {
        if (call_user_func($callable, $value) === true) {
            return null;
        }

        return error('The data must be a(n) {parameter}.', $validator);
    }

    /**
     * @param mixed $value
     * @param array $choices
     *
     * @param Validator $validator
     *
     * @return ValidationError|null
     */
    private function anyType($value, array $choices, Validator $validator)
    {
        foreach ($choices as $type) {
            $error = $this->validate($value, $type, $validator);
            if (is_null($error)) {
                return null;
            }
        }

        return error('The data must be one of {parameter}.', $validator);
    }
}
