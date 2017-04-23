<?php

namespace League\JsonGuard\Constraints\DraftFour;

use League\JsonGuard\Assert;
use League\JsonGuard\Constraint;
use League\JsonGuard\ValidationError;
use League\JsonGuard\Validator;
use function League\JsonGuard\error;

class Type implements Constraint
{
    const KEYWORD = 'type';

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
                            if (!(ctype_digit($value) && bccomp($value, PHP_INT_MAX) === 1)) {
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
