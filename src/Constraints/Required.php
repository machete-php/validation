<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard\ValidationError;

class Required implements PropertyConstraint
{
    const KEYWORD = 'required';

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
                'Required properties missing: {missing}',
                self::KEYWORD,
                $data,
                $pointer,
                ['missing' => array_values($missing)]
            );
        }

        return null;
    }
}
