<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard\Assert;
use function League\JsonGuard\error;
use League\JsonGuard\Validator;
use function League\JsonReference\pointer_push;

class AnyOf implements Constraint
{
    const KEYWORD = 'anyOf';

    /**
     * {@inheritdoc}
     */
    public function validate($value, $parameter, Validator $validator)
    {
        Assert::type($parameter, 'array', self::KEYWORD, $validator->getSchemaPath());
        Assert::notEmpty($parameter, self::KEYWORD, $validator->getSchemaPath());

        foreach ($parameter as $key => $schema) {
            $validator = $validator->makeSubSchemaValidator(
                $value,
                $schema,
                $validator->getDataPath(),
                pointer_push($validator->getSchemaPath(), $key)
            );
            if ($validator->passes()) {
                return null;
            }
        }
        return error('Failed matching any of the provided schemas.', $validator);
    }
}
