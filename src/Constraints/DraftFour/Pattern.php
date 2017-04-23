<?php

namespace League\JsonGuard\Constraints\DraftFour;

use League\JsonGuard;
use League\JsonGuard\Assert;
use League\JsonGuard\Constraint;
use League\JsonGuard\Validator;
use function League\JsonGuard\error;

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
