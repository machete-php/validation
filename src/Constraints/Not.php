<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard\Assert;
use League\JsonGuard\ValidationError;
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

        $validator = $validator->makeSubSchemaValidator($value, $parameter);
        if ($validator->passes()) {
            return new ValidationError(
                'Data should not match the schema.',
                self::KEYWORD,
                $value,
                $validator->getDataPath(),
                ['not_schema' => $parameter]
            );
        }
        return null;
    }
}
