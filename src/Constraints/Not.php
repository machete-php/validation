<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard\Assert;
use function League\JsonGuard\error;
use League\JsonGuard\Validator;

class Not implements Constraint
{
    const KEYWORD = 'not';

    /**
     * {@inheritdoc}
     */
    public function validate($value, $parameter, Validator $validator)
    {
        Assert::type($parameter, 'object', self::KEYWORD, $validator->getSchemaPath());

        $subValidator = $validator->makeSubSchemaValidator($value, $parameter);
        if ($subValidator->passes()) {
            return error('The data must not match the schema.', $validator);
        }
        return null;
    }
}
