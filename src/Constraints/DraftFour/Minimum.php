<?php

namespace League\JsonGuard\Constraints\DraftFour;

use League\JsonGuard\Assert;
use League\JsonGuard\Constraint;
use League\JsonGuard\Validator;
use function League\JsonGuard\error;

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

        return error('The number must be at least {parameter}.', $validator);
    }
}
