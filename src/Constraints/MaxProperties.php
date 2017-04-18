<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard\Assert;
use League\JsonGuard\ValidationError;
use League\JsonGuard\Validator;

class MaxProperties implements Constraint
{
    const KEYWORD = 'maxProperties';

    /**
     * {@inheritdoc}
     */
    public function validate($value, $parameter, Validator $validator)
    {
        Assert::type($parameter, 'integer', self::KEYWORD, $validator->getSchemaPath());
        Assert::nonNegative($parameter, self::KEYWORD, $validator->getSchemaPath());

        if (!is_object($value) || count(get_object_vars($value)) <= $parameter) {
            return null;
        }

        return new ValidationError(
            'Object does not contain less than {max_properties} properties',
            self::KEYWORD,
            $value,
            $validator->getDataPath(),
            ['max_properties' => $parameter]
        );
    }
}
