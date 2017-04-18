<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard\Assert;
use League\JsonGuard\ValidationError;
use League\JsonGuard\Validator;

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
            return self::validateExclusiveMax($value, $parameter, $validator->getDataPath());
        }

        return self::validateMax($value, $parameter, $validator->getDataPath());
    }

    /**
     * @param mixed       $value
     * @param mixed       $parameter
     * @param string|null $pointer
     *
     * @return \League\JsonGuard\ValidationError|null
     */
    private function validateMax($value, $parameter, $pointer)
    {
        if (!is_numeric($value) ||
            bccomp($value, $parameter, $this->precision) === -1 || bccomp($value, $parameter, $this->precision) === 0) {
            return null;
        }

        return new ValidationError(
            'Value {value} is not at most {max}',
            self::KEYWORD,
            $value,
            $pointer,
            ['value' => $value, 'max' => $parameter]
        );
    }

    /**
     * @param mixed       $value
     * @param mixed       $parameter
     * @param string|null $pointer
     *
     * @return \League\JsonGuard\ValidationError|null
     */
    private function validateExclusiveMax($value, $parameter, $pointer)
    {
        if (!is_numeric($value) || bccomp($value, $parameter, $this->precision) === -1) {
            return null;
        }

        return new ValidationError(
            'Value {value} is not less than {exclusive_max}',
            self::EXCLUSIVE_KEYWORD,
            $value,
            $pointer,
            ['value' => $value, 'exclusive_max' => $parameter]
        );
    }
}
