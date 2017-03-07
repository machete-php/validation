<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard\Assert;
use League\JsonGuard\ValidationError;
use League\JsonGuard\Validator;
use League\JsonReference;

class AdditionalItems implements Constraint
{
    const KEYWORD = 'additionalItems';

    /**
     * {@inheritdoc}
     */
    public function validate($value, $parameter, Validator $validator)
    {
        Assert::type($parameter, ['boolean', 'object'], self::KEYWORD, $validator->getPointer());

        if (!is_array($value) || $parameter === true) {
            return null;
        }

        if (!is_array($items = self::getItems($validator->getSchema()))) {
            return null;
        }

        if ($parameter === false) {
            return self::validateAdditionalItemsWhenNotAllowed($value, $items, $validator->getPointer());
        } elseif (is_object($parameter)) {
            $additionalItems = array_slice($value, count($items));

            return self::validateAdditionalItemsAgainstSchema($additionalItems, $parameter, $validator);
        }
    }

    /**
     * @param object $schema
     *
     * @return mixed
     */
    private static function getItems($schema)
    {
        return property_exists($schema, 'items') ? $schema->items : null;
    }

    /**
     * @param array     $items
     * @param object    $schema
     * @param Validator $validator
     *
     * @return array
     */
    private static function validateAdditionalItemsAgainstSchema($items, $schema, Validator $validator)
    {
        $errors = [];
        foreach ($items as $key => $item) {
            // Escaping isn't necessary since the key is always numeric.
            $currentPointer = JsonReference\pointer_push($validator->getPointer(), $key);
            $validator      = $validator->makeSubSchemaValidator($item, $schema, $currentPointer);
            $errors         = array_merge($errors, $validator->errors());
        }

        return $errors;
    }

    /**
     * @param array $value
     * @param array $items
     * @param $pointer
     *
     * @return \League\JsonGuard\ValidationError
     */
    private static function validateAdditionalItemsWhenNotAllowed($value, $items, $pointer)
    {
        if (count($value) > count($items)) {
            return new ValidationError(
                'Additional items are not allowed.',
                self::KEYWORD,
                $value,
                $pointer
            );
        }
    }
}
