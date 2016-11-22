<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard\Assert;
use League\JsonGuard\SubSchemaValidatorFactory;

class Items implements ContainerInstanceConstraint
{
    const KEYWORD = 'items';

    /**
     * {@inheritdoc}
     */
    public static function validate($data, $parameter, SubSchemaValidatorFactory $validatorFactory, $pointer = null)
    {
        Assert::type($parameter, ['array', 'object'], self::KEYWORD, $pointer);

        if (!is_array($data)) {
            return null;
        }

        $errors = [];
        foreach ($data as $key => $value) {
            $schema = self::getSchema($parameter, $key);

            // Additional items are allowed by default,
            // so there might not be a schema for this.
            if (is_null($schema)) {
                continue;
            }

            // Escaping isn't necessary since the key is always numeric.
            $validator = $validatorFactory->makeSubSchemaValidator($value, $schema, $pointer . '/' . $key);
            $errors = array_merge($errors, $validator->errors());
        }

        return $errors ?: null;
    }

    /**
     * @param $parameter
     * @param $key
     *
     * @return mixed
     */
    protected static function getSchema($parameter, $key)
    {
        if (is_object($parameter)) {
            // list validation
            return $parameter;
        } elseif (is_array($parameter) && array_key_exists($key, $parameter)) {
            // tuple validation
            return $parameter[$key];
        }

        return null;
    }
}
