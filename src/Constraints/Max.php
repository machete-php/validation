<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard\Assert;
use League\JsonGuard\Validator;
use function League\JsonGuard\error;

class Max implements Constraint
{
    const KEYWORD           = 'maximum';
    const EXCLUSIVE_KEYWORD = 'exclusiveMaximum';

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

        if (isset($validator->getSchema()->exclusiveMaximum) && $validator->getSchema()->exclusiveMaximum === true) {
            return self::validateExclusiveMax($value, $parameter, $validator);
        }

        return self::validateMax($value, $parameter, $validator);
    }

    /**
     * @param mixed                       $value
     * @param mixed                       $parameter
     * @param \League\JsonGuard\Validator $validator
     *
     * @return \League\JsonGuard\ValidationError|null
     *
     */
    private function validateMax($value, $parameter, Validator $validator)
    {
        if (!is_numeric($value) ||
            bccomp($value, $parameter, $this->precision) === -1 || bccomp($value, $parameter, $this->precision) === 0) {
            return null;
        }

        return error('Value {value} is not at most {parameter}', $validator);
    }

    /**
     * @param mixed                       $value
     * @param mixed                       $parameter
     * @param \League\JsonGuard\Validator $validator
     *
     * @return \League\JsonGuard\ValidationError|null
     *
     */
    private function validateExclusiveMax($value, $parameter, Validator $validator)
    {
        if (!is_numeric($value) || bccomp($value, $parameter, $this->precision) === -1) {
            return null;
        }

        return error('Value {value} is not less than {parameter}', $validator);
    }
}
