<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard\Assert;
use League\JsonGuard\ValidationError;
use League\JsonGuard\Validator;
use function League\JsonReference\pointer_push;

class OneOf implements Constraint
{
    const KEYWORD = 'oneOf';

    /**
     * {@inheritdoc}
     */
    public function validate($value, $parameter, Validator $validator)
    {
        Assert::type($parameter, 'array', self::KEYWORD, $validator->getSchemaPath());
        Assert::notEmpty($parameter, self::KEYWORD, $validator->getSchemaPath());

        $passed = 0;
        foreach ($parameter as $key => $schema) {
            $validator = $validator->makeSubSchemaValidator(
                $value,
                $schema,
                $validator->getDataPath(),
                pointer_push($validator->getSchemaPath(), $key)
            );
            if ($validator->passes()) {
                $passed++;
            }
        }
        if ($passed !== 1) {
            return new ValidationError(
                'Failed matching exactly one of the provided schemas.',
                self::KEYWORD,
                $value,
                $validator->getDataPath(),
                ['one_of' => $parameter]
            );
        }

        return null;
    }
}
