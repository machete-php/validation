<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard\Assert;
use League\JsonGuard\ValidationError;
use League\JsonGuard\Validator;

class MaxItems implements Constraint
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

        return new ValidationError(
            'Array does not contain less than {max_items} items',
            self::KEYWORD,
            $value,
            $validator->getDataPath(),
            ['max_items' => $parameter]
        );
    }
}
