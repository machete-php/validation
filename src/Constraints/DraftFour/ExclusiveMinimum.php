<?php

namespace League\JsonGuard\Constraints\DraftFour;

use League\JsonGuard\Assert;
use League\JsonGuard\ConstraintInterface;
use League\JsonGuard\Validator;
use function League\JsonGuard\error;

final class ExclusiveMinimum implements ConstraintInterface
{
    const KEYWORD = 'exclusiveMinimum';

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
     * @param mixed     $value
     * @param mixed     $parameter
     * @param Validator $validator
     *
     * @return \League\JsonGuard\ValidationError|null
     */
    public function validate($value, $parameter, Validator $validator)
    {
        Assert::type($parameter, 'boolean', self::KEYWORD, $validator->getSchemaPath());
        Assert::hasProperty($validator->getSchema(), 'minimum', self::KEYWORD, $validator->getSchemaPath());

        if ($parameter !== true) {
            return null;
        }

        if (!is_numeric($value) || bccomp($value, $validator->getSchema()->minimum, $this->precision) === 1) {
            return null;
        }

        return error('The number must be greater than {parameter}.', $validator);
    }
}
