<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard\Assert;
use League\JsonGuard\ValidationError;
use League\JsonGuard\Validator;

class MinItems implements Constraint
{
    const KEYWORD = 'minItems';

    /**
     * {@inheritdoc}
     */
    public function validate($value, $parameter, Validator $validator)
    {
        Assert::type($parameter, 'integer', self::KEYWORD, $validator->getSchemaPath());
        Assert::nonNegative($parameter, self::KEYWORD, $validator->getSchemaPath());

        if (!is_array($value) || count($value) >= $parameter) {
            return null;
        }

        return new ValidationError(
            'Array does not contain more than {min_items} items',
            self::KEYWORD,
            $value,
            $validator->getDataPath(),
            ['min_items' => $parameter]
        );
    }
}
