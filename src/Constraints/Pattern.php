<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard;
use League\JsonGuard\Assert;
use League\JsonGuard\ValidationError;
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

        return new ValidationError(
            'Value {value} does not match the pattern {pattern}.',
            self::KEYWORD,
            $value,
            $validator->getDataPath(),
            ['value' => $value, 'pattern' => $parameter]
        );
    }
}
