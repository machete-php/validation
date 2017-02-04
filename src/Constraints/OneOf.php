<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard\Assert;
use League\JsonGuard\ValidationError;
use League\JsonGuard\Validator;

class OneOf implements Constraint
{
    const KEYWORD = 'oneOf';

    /**
     * {@inheritdoc}
     */
    public function validate($value, $parameter, Validator $validator)
    {
        Assert::type($parameter, 'array', self::KEYWORD, $validator->getPointer());
        Assert::notEmpty($parameter, self::KEYWORD, $validator->getPointer());

        $passed = 0;
        foreach ($parameter as $schema) {
            $validator = $validator->makeSubSchemaValidator($value, $schema, $validator->getPointer());
            if ($validator->passes()) {
                $passed++;
            }
        }
        if ($passed !== 1) {
            return new ValidationError(
                'Failed matching exactly one of the provided schemas.',
                self::KEYWORD,
                $value,
                $validator->getPointer(),
                ['one_of' => $parameter]
            );
        }

        return null;
    }
}
