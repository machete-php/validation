<?php

namespace League\JsonGuard\Constraints\DraftFour;

use League\JsonGuard\Assert;
use League\JsonGuard\ConstraintInterface;
use League\JsonGuard\Validator;
use function League\JsonGuard\error;
use function League\JsonReference\pointer_push;

final class AdditionalItems implements ConstraintInterface
{
    const KEYWORD = 'additionalItems';

    /**
     * {@inheritdoc}
     */
    public function validate($value, $parameter, Validator $validator)
    {
        Assert::type($parameter, ['boolean', 'object'], self::KEYWORD, $validator->getSchemaPath());

        if (!is_array($value) || $parameter === true) {
            return null;
        }

        if (!is_array($items = self::getItems($validator->getSchema()))) {
            return null;
        }

        if ($parameter === false) {
            return self::validateAdditionalItemsWhenNotAllowed($value, $items, $validator);
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
        return property_exists($schema, Items::KEYWORD) ? $schema->items : null;
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
            $subValidator = $validator->makeSubSchemaValidator(
                $item,
                $schema,
                pointer_push($validator->getDataPath(), $key)
            );
            $errors = array_merge($errors, $subValidator->errors());
        }

        return $errors;
    }

    /**
     * @param array                       $value
     * @param array                       $items
     * @param \League\JsonGuard\Validator $validator
     *
     * @return \League\JsonGuard\ValidationError
     */
    private static function validateAdditionalItemsWhenNotAllowed($value, $items, Validator $validator)
    {
        if (count($value) > count($items)) {
            return error('The array must not contain additional items.', $validator);
        }
    }
}
