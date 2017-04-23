<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard\Assert;
use function League\JsonGuard\error;
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
            return self::validateExclusiveMin($value, $parameter, $validator);
        }

        return self::validateMin($value, $parameter, $validator);
    }

    /**
     * @param mixed                       $value
     * @param mixed                       $parameter
     *
     * @param \League\JsonGuard\Validator $validator
     *
     * @return \League\JsonGuard\ValidationError|null
     */
    private function validateMin($value, $parameter, Validator $validator)
    {
        if (!is_numeric($value) ||
            bccomp($value, $parameter, $this->precision) === 1 || bccomp($value, $parameter, $this->precision) === 0) {
            return null;
        }

        return error('Number {cause} is not at least {parameter}', $validator);
    }

    /**
     * @param mixed                       $value
     * @param mixed                       $parameter
     * @param \League\JsonGuard\Validator $validator
     *
     * @return \League\JsonGuard\ValidationError|null
     *
     */
    private function validateExclusiveMin($value, $parameter, Validator $validator)
    {
        if (!is_numeric($value) || bccomp($value, $parameter, $this->precision) === 1) {
            return null;
        }

        return error('Number {cause} is not at least greater than {parameter}', $validator);
    }
}
