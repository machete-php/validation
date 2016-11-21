<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard;
use League\JsonGuard\ValidationError;

class Pattern implements PropertyConstraint
{
    const KEYWORD = 'pattern';

    /**
     * {@inheritdoc}
     */
    public static function validate($value, $pattern, $pointer = null)
    {
        if (!is_string($value)) {
            return null;
        }

        if (preg_match(JsonGuard\delimit_pattern($pattern), $value) === 1) {
            return null;
        }

        return new ValidationError(
            'Value {value} does not match the pattern {pattern}.',
            self::KEYWORD,
            $value,
            $pointer,
            ['value' => $value, 'pattern' => $pattern]
        );
    }
}
