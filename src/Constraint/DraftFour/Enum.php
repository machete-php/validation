<?php

namespace League\JsonGuard\Constraint\DraftFour;

use League\JsonGuard\Assert;
use League\JsonGuard\ConstraintInterface;
use League\JsonGuard\Validator;
use function League\JsonGuard\error;

final class Enum implements ConstraintInterface
{
    const KEYWORD = 'enum';

    /**
     * {@inheritdoc}
     */
    public function validate($value, $parameter, Validator $validator)
    {
        Assert::type($parameter, 'array', self::KEYWORD, $validator->getSchemaPath());

        if (is_object($value)) {
            foreach ($parameter as $i) {
                if (is_object($i) && $value == $i) {
                    return null;
                }
            }
        } else {
            if (in_array($value, $parameter, true)) {
                return null;
            }
        }

        return error('The value must be one of: {parameter}', $validator);
    }
}
