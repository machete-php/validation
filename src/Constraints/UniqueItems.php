<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard\Assert;
use League\JsonGuard\ValidationError;
use League\JsonGuard\Validator;

class UniqueItems implements Constraint
{
    const KEYWORD = 'uniqueItems';

    /**
     * {@inheritdoc}
     */
    public function validate($value, $parameter, Validator $validator)
    {
        Assert::type($parameter, 'boolean', self::KEYWORD, $validator->getSchemaPath());

        if (!is_array($value) || $parameter === false) {
            return null;
        }

        if (count($value) === count(array_unique(array_map('serialize', $value)))) {
            return null;
        }

        return new ValidationError(
            'Array {value} is not unique.',
            self::KEYWORD,
            $value,
            $validator->getDataPath(),
            ['value' => $value]
        );
    }
}
