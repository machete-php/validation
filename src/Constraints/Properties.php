<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard\Assert;
use League\JsonGuard\Validator;
use League\JsonReference;

class Properties implements Constraint
{
    const KEYWORD = 'properties';

    /**
     * {@inheritdoc}
     */
    public function validate($value, $parameter, Validator $validator)
    {
        Assert::type($parameter, ['array', 'object'], self::KEYWORD, $validator->getPointer());

        if (!is_object($value)) {
            return null;
        }

        // Iterate through the properties and create a new
        // validator for that property's schema and data.
        // merge the errors.
        $errors = [];
        foreach ($parameter as $property => $schema) {
            if (is_object($value) && property_exists($value, $property)) {
                $propertyData    = $value->$property;
                $propertyPointer = JsonReference\pointer_push($validator->getPointer(), $property);
                $subValidator       = $validator->makeSubSchemaValidator($propertyData, $schema, $propertyPointer);
                if ($subValidator->fails()) {
                    $errors = array_merge($errors, $subValidator->errors());
                }
            }
        }

        return $errors;
    }
}
