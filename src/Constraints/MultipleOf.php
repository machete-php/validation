<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard;
use League\JsonGuard\Assert;
use League\JsonGuard\ValidationError;

class MultipleOf implements PropertyConstraint
{
    const KEYWORD = 'multipleOf';

    /**
     * {@inheritdoc}
     */
    public static function validate($value, $multiple, $pointer = null)
    {
        Assert::type($multiple, 'number', self::KEYWORD, $pointer);
        Assert::nonNegative($multiple, self::KEYWORD, $pointer);

        if (!is_numeric($value)) {
            return null;
        }

        // for some reason fmod does not return 0 for cases like fmod(0.0075,0.0001) so I'm doing this manually.
        $quotient = $value / $multiple;
        $mod      = $quotient - floor($quotient);
        if ($mod == 0) {
            return null;
        }

        return new ValidationError(
            'Number {value} is not a multiple of {multiple_of}',
            self::KEYWORD,
            $value,
            $pointer,
            ['value' => $value, 'multiple_of' => $multiple]
        );
    }
}
