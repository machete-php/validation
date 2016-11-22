<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard;
use League\JsonGuard\Assert;
use League\JsonGuard\SubSchemaValidatorFactory;

class PatternProperties implements ContainerInstanceConstraint
{
    const KEYWORD = 'patternProperties';

    /**
     * {@inheritdoc}
     */
    public static function validate($data, $parameter, SubSchemaValidatorFactory $validatorFactory, $pointer = null)
    {
        Assert::type($parameter, 'object', self::KEYWORD, $pointer);

        if (!is_object($data)) {
            return null;
        }

        $errors = [];
        foreach ($parameter as $property => $schema) {
            $matches       = JsonGuard\properties_matching_pattern($property, $data);
            $matchedSchema = array_fill_keys($matches, $schema);
            $propertyErrors = Properties::validate($data, $matchedSchema, $validatorFactory, $pointer);
            if (is_array($propertyErrors)) {
                $errors = array_merge($errors, $propertyErrors);
            }
        }
        return $errors;
    }
}
