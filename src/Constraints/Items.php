<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard\Assert;
use League\JsonGuard\Validator;
use League\JsonReference;

class Items implements Constraint
{
    const KEYWORD = 'items';

    /**
     * {@inheritdoc}
     */
    public function validate($value, $parameter, Validator $validator)
    {
        Assert::type($parameter, ['array', 'object'], self::KEYWORD, $validator->getPointer());

        if (!is_array($value)) {
            return null;
        }

        $errors = [];
        foreach ($value as $key => $itemValue) {
            $schema = self::getSchema($parameter, $key);

            // Additional items are allowed by default,
            // so there might not be a schema for this.
            if (is_null($schema)) {
                continue;
            }

            $pointer   = JsonReference\pointer_push($validator->getPointer(), $key);
            $subValidator = $validator->makeSubSchemaValidator($itemValue, $schema, $pointer);
            $errors    = array_merge($errors, $subValidator->errors());
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
