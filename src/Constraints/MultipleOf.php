<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard\Assert;
use function League\JsonGuard\error;
use League\JsonGuard\Validator;

class MultipleOf implements Constraint
{
    const KEYWORD = 'multipleOf';

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
        Assert::nonNegative($parameter, self::KEYWORD, $validator->getSchemaPath());

        if (!is_numeric($value)) {
            return null;
        }

        $quotient = bcdiv($value, $parameter, $this->precision);
        $mod      = bcsub($quotient, floor($quotient), $this->precision);
        if (bccomp($mod, 0, $this->precision) === 0) {
            return null;
        }

        return error('The number must be a multiple of {parameter}.', $validator);
    }
}
