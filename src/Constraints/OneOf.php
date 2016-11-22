<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard\Assert;
use League\JsonGuard\SubSchemaValidatorFactory;
use League\JsonGuard\ValidationError;

class OneOf implements ContainerInstanceConstraint
{
    const KEYWORD = 'oneOf';

    /**
     * {@inheritdoc}
     */
    public static function validate($data, $parameter, SubSchemaValidatorFactory $validatorFactory, $pointer = null)
    {
        Assert::type($parameter, 'array', self::KEYWORD, $pointer);
        Assert::notEmpty($parameter, self::KEYWORD, $pointer);

        $passed = 0;
        foreach ($parameter as $schema) {
            $validator = $validatorFactory->makeSubSchemaValidator($data, $schema, $pointer);
            if ($validator->passes()) {
                $passed++;
            }
        }
        if ($passed !== 1) {
            return new ValidationError(
                'Failed matching exactly one of the provided schemas.',
                self::KEYWORD,
                $data,
                $pointer,
                ['one_of' => $parameter]
            );
        }

        return null;
    }
}
