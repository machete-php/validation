<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard;
use League\JsonGuard\Assert;
use function League\JsonGuard\error;
use League\JsonGuard\Validator;

class Pattern implements Constraint
{
    const KEYWORD = 'pattern';

    /**
     * {@inheritdoc}
     */
    public function validate($value, $parameter, Validator $validator)
    {
        Assert::type($parameter, 'string', self::KEYWORD, $validator->getSchemaPath());

        if (!is_string($value)) {
            return null;
        }

        if (preg_match(JsonGuard\delimit_pattern($parameter), $value) === 1) {
            return null;
        }

        return error('The string must match the pattern {parameter}.', $validator);
    }
}
