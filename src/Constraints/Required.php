<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard\ErrorCode;
use League\JsonGuard\ValidationError;

class Required implements PropertyConstraint
{
    /**
     * {@inheritdoc}
     */
    public static function validate($data, $parameter, $pointer = null)
    {
        if (!is_object($data)) {
            return null;
        }

        $actualProperties = array_keys(get_object_vars($data));
        $missing          = array_diff($parameter, $actualProperties);
        if (count($missing)) {
            return new ValidationError(
                'Required properties missing: ' . implode(', ', array_values($missing)),
                ErrorCode::MISSING_REQUIRED,
                $data,
                $pointer,
                ['required' => $parameter]
            );
        }

        return null;
    }
}
