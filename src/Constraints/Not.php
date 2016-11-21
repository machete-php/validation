<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard\ValidationError;
use League\JsonGuard\SubSchemaValidatorFactory;

class Not implements ContainerInstanceConstraint
{
    const KEYWORD = 'not';

    /**
     * {@inheritdoc}
     */
    public static function validate($data, $parameter, SubSchemaValidatorFactory $validatorFactory, $pointer = null)
    {
        $validator = $validatorFactory->makeSubSchemaValidator($data, $parameter, $pointer);
        if ($validator->passes()) {
            return new ValidationError(
                'Data should not match the schema.',
                self::KEYWORD,
                $data,
                $pointer,
                ['not_schema' => $parameter]
            );
        }
        return null;
    }
}
