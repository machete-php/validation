<?php

namespace League\JsonGuard\Constraints\DraftFour;

use League\JsonGuard\Assert;
use League\JsonGuard\ConstraintInterface;
use League\JsonGuard\Validator;
use function League\JsonGuard\error;

final class MaxItems implements ConstraintInterface
{
    const KEYWORD = 'maxItems';

    /**
     * {@inheritdoc}
     */
    public function validate($value, $parameter, Validator $validator)
    {
        Assert::type($parameter, 'integer', self::KEYWORD, $validator->getSchemaPath());
        Assert::nonNegative($parameter, self::KEYWORD, $validator->getSchemaPath());

        if (!is_array($value) || count($value) <= $parameter) {
            return null;
        }

        return error('The array must contain less than {parameter} items.', $validator);
    }
}
