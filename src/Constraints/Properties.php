<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard\Assert;
use League\JsonGuard\Validator;
use function League\JsonReference\pointer_push;

class Properties implements Constraint
{
    const KEYWORD = 'properties';

    /**
     * {@inheritdoc}
     */
    public function validate($value, $parameter, Validator $validator)
    {
        Assert::type($parameter, ['array', 'object'], self::KEYWORD, $validator->getSchemaPath());

        if (!is_object($value)) {
            return null;
        }

        // Iterate through the properties and create a new validator for that property's schema and data.
        $errors = [];
        foreach ($parameter as $property => $schema) {
            if (is_object($value) && property_exists($value, $property)) {
                $subValidator = $validator->makeSubSchemaValidator(
                    $value->$property,
                    $schema,
                    pointer_push($validator->getDataPath(), $property),
                    pointer_push($validator->getSchemaPath(), $property)
                );
                if ($subValidator->fails()) {
                    $errors = array_merge($errors, $subValidator->errors());
                }
            }
        }

        return $errors;
    }
}
