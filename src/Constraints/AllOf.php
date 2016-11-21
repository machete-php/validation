<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard\SubSchemaValidatorFactory;

class AllOf implements ContainerInstanceConstraint
{
    const KEYWORD = 'allOf';

    /**
     * {@inheritdoc}
     */
    public static function validate($data, $parameter, SubSchemaValidatorFactory $validatorFactory, $pointer = null)
    {
        if (!is_array($parameter)) {
            return null;
        }

        $errors = [];

        foreach ($parameter as $schema) {
            $validator = $validatorFactory->makeSubSchemaValidator($data, $schema, $pointer);
            $errors = array_merge($errors, $validator->errors());
        }

        return $errors;
    }
}
