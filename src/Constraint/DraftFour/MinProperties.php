<?php

namespace League\JsonGuard\Constraint\DraftFour;

use League\JsonGuard\Assert;
use League\JsonGuard\ConstraintInterface;
use League\JsonGuard\Validator;
use function League\JsonGuard\error;

final class MinProperties implements ConstraintInterface
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

        return error('The object must contain at least {parameter} properties.', $validator);
    }
}
