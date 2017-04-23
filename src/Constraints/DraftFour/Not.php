<?php

namespace League\JsonGuard\Constraints\DraftFour;

use League\JsonGuard\Assert;
use League\JsonGuard\ConstraintInterface;
use League\JsonGuard\Validator;
use function League\JsonGuard\error;

final class Not implements ConstraintInterface
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
