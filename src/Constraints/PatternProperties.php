<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard;
use League\JsonGuard\Assert;
use League\JsonGuard\Validator;

class PatternProperties implements Constraint
{
    const KEYWORD = 'patternProperties';

    /**
     * {@inheritdoc}
     */
    public function validate($value, $parameter, Validator $validator)
    {
        Assert::type($parameter, 'object', self::KEYWORD, $validator->getSchemaPath());

        if (!is_object($value)) {
            return null;
        }

        $errors = [];
        foreach ($parameter as $property => $schema) {
            $matches       = JsonGuard\properties_matching_pattern($property, $value);
            $matchedSchema = array_fill_keys($matches, $schema);
            $propertyErrors = (new Properties())->validate($value, $matchedSchema, $validator);
            if (is_array($propertyErrors)) {
                $errors = array_merge($errors, $propertyErrors);
            }
        }
        return $errors;
    }
}
