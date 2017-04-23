<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard\Assert;
use function League\JsonGuard\error;
use League\JsonGuard\Validator;

class Minimum implements Constraint
{
    const KEYWORD = 'minimum';

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
            return null;
        }

        if (!is_numeric($value) ||
            bccomp($value, $parameter, $this->precision) === 1 || bccomp($value, $parameter, $this->precision) === 0) {
            return null;
        }

        return error('Number {cause} is not at least {parameter}', $validator);
    }
}
