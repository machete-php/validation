<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard\Assert;
use function League\JsonGuard\error;
use League\JsonGuard\Validator;

class MinProperties implements Constraint
{
    const KEYWORD = 'minProperties';

    /**
     * {@inheritdoc}
     */
    public function validate($value, $parameter, Validator $validator)
    {
        Assert::type($parameter, 'integer', self::KEYWORD, $validator->getSchemaPath());
        Assert::nonNegative($parameter, self::KEYWORD, $validator->getSchemaPath());

        if (!is_object($value) || count(get_object_vars($value)) >= $parameter) {
            return null;
        }

        return error('Object does not contain at least {parameter} properties', $validator);
    }
}
