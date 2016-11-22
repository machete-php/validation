<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard\Assert;
use League\JsonGuard\SubSchemaValidatorFactory;

class AllOf implements ContainerInstanceConstraint
{
    const KEYWORD = 'allOf';

    /**
     * {@inheritdoc}
     */
    public static function validate($data, $parameter, SubSchemaValidatorFactory $validatorFactory, $pointer = null)
    {
        Assert::type($parameter, 'array', self::KEYWORD, $pointer);
        Assert::notEmpty($parameter, self::KEYWORD, $pointer);

        $errors = [];

        foreach ($parameter as $schema) {
            $validator = $validatorFactory->makeSubSchemaValidator($data, $schema, $pointer);
            $errors = array_merge($errors, $validator->errors());
        }

        return $errors;
    }
}
