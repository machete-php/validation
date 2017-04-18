<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard\Assert;
use League\JsonGuard\ValidationError;
use League\JsonGuard\Validator;

class Required implements Constraint
{
    const KEYWORD = 'required';

    /**
     * {@inheritdoc}
     */
    public function validate($value, $parameter, Validator $validator)
    {
        Assert::type($parameter, 'array', self::KEYWORD, $validator->getSchemaPath());
        Assert::notEmpty($parameter, self::KEYWORD, $validator->getSchemaPath());

        if (!is_object($value)) {
            return null;
        }

        $actualProperties = array_keys(get_object_vars($value));
        $missing          = array_diff($parameter, $actualProperties);
        if (count($missing)) {
            return new ValidationError(
                'Required properties missing: {missing}',
                self::KEYWORD,
                $value,
                $validator->getDataPath(),
                ['missing' => array_values($missing)]
            );
        }

        return null;
    }
}
