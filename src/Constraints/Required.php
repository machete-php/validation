<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard\Assert;
use function League\JsonGuard\error;
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
            return error('Required properties missing: {cause}', $validator)->withCause(array_values($missing));
        }

        return null;
    }
}
