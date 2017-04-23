<?php

namespace League\JsonGuard\Constraints\DraftFour;

use League\JsonGuard\Assert;
use League\JsonGuard\Constraint;
use League\JsonGuard\Validator;
use function League\JsonReference\pointer_push;

final class Items implements Constraint
{
    const KEYWORD = 'items';

    /**
     * {@inheritdoc}
     */
    public function validate($value, $parameter, Validator $validator)
    {
        Assert::type($parameter, ['array', 'object'], self::KEYWORD, $validator->getSchemaPath());

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

            $subValidator = $validator->makeSubSchemaValidator(
                $itemValue,
                $schema,
                pointer_push($validator->getDataPath(), $key),
                pointer_push($validator->getSchemaPath(), $key)
            );
            $errors = array_merge($errors, $subValidator->errors());
        }

        return $errors ?: null;
    }

    /**
     * @param $parameter
     * @param $key
     *
     * @return mixed
     */
    private static function getSchema($parameter, $key)
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
