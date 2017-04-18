<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard\Assert;
use League\JsonGuard\Validator;
use function League\JsonReference\pointer_push;

class AllOf implements Constraint
{
    const KEYWORD = 'allOf';

    /**
     * {@inheritdoc}
     */
    public function validate($value, $parameter, Validator $validator)
    {
        Assert::type($parameter, 'array', self::KEYWORD, $validator->getSchemaPath());
        Assert::notEmpty($parameter, self::KEYWORD, $validator->getSchemaPath());

        $errors = [];

        foreach ($parameter as $key => $schema) {
            $subValidator = $validator->makeSubSchemaValidator(
                $value,
                $schema,
                $validator->getDataPath(),
                pointer_push($validator->getSchemaPath(), $key)
            );
            $errors = array_merge($errors, $subValidator->errors());
        }

        return $errors;
    }
}
