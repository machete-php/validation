<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard;
use League\JsonGuard\Assert;
use League\JsonGuard\ValidationError;
use League\JsonGuard\Validator;

class Type implements Constraint
{
    const KEYWORD = 'type';

    /**
     * {@inheritdoc}
     */
    public function validate($value, $type, Validator $validator)
    {
        Assert::type($type, ['array', 'string'], self::KEYWORD, $validator->getPointer());

        if (is_array($type)) {
            return $this->anyType($value, $type, $validator);
        }

        switch ($type) {
            case 'object':
                return $this->validateType($value, $type, 'is_object', $validator->getPointer());
            case 'array':
                return $this->validateType($value, $type, 'is_array', $validator->getPointer());
            case 'boolean':
                return $this->validateType($value, $type, 'is_bool', $validator->getPointer());
            case 'null':
                return $this->validateType($value, $type, 'is_null', $validator->getPointer());
            case 'number':
                return $this->validateType(
                    $value,
                    $type,
                    'League\JsonGuard\is_json_number',
                    $validator->getPointer()
                );
            case 'integer':
                return $this->validateType(
                    $value,
                    $type,
                    'League\JsonGuard\is_json_integer',
                    $validator->getPointer()
                );
            case 'string':
                return $this->validateType(
                    $value,
                    $type,
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
                    $validator->getPointer()
                );
        }
    }

    /**
     * @param mixed    $value
     * @param string   $type
     * @param callable $callable
     * @param string   $pointer
     *
     * @return \League\JsonGuard\ValidationError|null
     */
    private function validateType($value, $type, callable $callable, $pointer)
    {
        if (call_user_func($callable, $value) === true) {
            return null;
        }

        return new ValidationError(
            'Value {value} is not a(n) {type}',
            self::KEYWORD,
            $value,
            $pointer,
            ['value' => $value, 'type' => $type]
        );
    }

    /**
     * @param mixed     $value
     * @param array     $choices
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

        return new ValidationError(
            'Value {value} is not one of: {choices}',
            self::KEYWORD,
            $value,
            $validator->getPointer(),
            [
                'value'   => $value,
                'type'    => $choices
            ]
        );
    }
}
