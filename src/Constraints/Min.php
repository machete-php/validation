<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard\Assert;
use League\JsonGuard\ValidationError;
use League\JsonGuard\Validator;

class Min implements Constraint
{
    const KEYWORD           = 'minimum';
    const EXCLUSIVE_KEYWORD = 'exclusiveMinimum';

    /**
     * @var int|null
     */
    private $precision;

    /**
     * @param int|null $precision
     */
    public function __construct($precision = 10)
    {
        $this->precision = $precision;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, $parameter, Validator $validator)
    {
        Assert::type($parameter, 'number', self::KEYWORD, $validator->getSchemaPath());

        if (isset($validator->getSchema()->exclusiveMinimum) && $validator->getSchema()->exclusiveMinimum === true) {
            return self::validateExclusiveMin($value, $parameter, $validator->getDataPath());
        }

        return self::validateMin($value, $parameter, $validator->getDataPath());
    }

    /**
     * @param mixed       $value
     * @param mixed       $parameter
     * @param string|null $pointer
     *
     * @return \League\JsonGuard\ValidationError|null
     */
    private function validateMin($value, $parameter, $pointer = null)
    {
        if (!is_numeric($value) ||
            bccomp($value, $parameter, $this->precision) === 1 || bccomp($value, $parameter, $this->precision) === 0) {
            return null;
        }

        return new ValidationError(
            'Number {value} is not at least {min}',
            self::KEYWORD,
            $value,
            $pointer,
            ['value' => $value, 'min' => $parameter]
        );
    }

    /**
     * @param mixed       $value
     * @param mixed       $parameter
     * @param string|null $pointer
     *
     * @return \League\JsonGuard\ValidationError|null
     */
    private function validateExclusiveMin($value, $parameter, $pointer = null)
    {
        if (!is_numeric($value) || bccomp($value, $parameter, $this->precision) === 1) {
            return null;
        }

        return new ValidationError(
            'Number {value} is not at least greater than {exclusive_min}',
            self::EXCLUSIVE_KEYWORD,
            $value,
            $pointer,
            ['value' => $value, 'exclusive_min' => $parameter]
        );
    }
}
