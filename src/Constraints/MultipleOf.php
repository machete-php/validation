<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard;
use League\JsonGuard\ErrorCode;
use League\JsonGuard\ValidationError;

class MultipleOf implements PropertyConstraint
{
    /**
     * {@inheritdoc}
     */
    public static function validate($value, $multiple, $pointer = null)
    {
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
            ErrorCode::INVALID_MULTIPLE,
            $value,
            $pointer,
            ['value' => $value, 'multiple_of' => $multiple]
        );
    }
}
