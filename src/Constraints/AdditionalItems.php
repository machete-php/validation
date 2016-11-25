<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard\Assert;
use League\JsonGuard\SubSchemaValidatorFactory;
use League\JsonGuard\ValidationError;

class AdditionalItems implements ParentSchemaAwareContainerInstanceConstraint
{
    const KEYWORD = 'additionalItems';

    /**
     * {@inheritdoc}
     */
    public static function validate(
        $data,
        $schema,
        $parameter,
        SubSchemaValidatorFactory $validatorFactory,
        $pointer = null
    ) {
        Assert::type($parameter, ['boolean', 'object'], self::KEYWORD, $pointer);

        if (!is_array($data) || $parameter === true) {
            return null;
        }


        if (!is_array($items = self::getItems($schema))) {
            return null;
        }

        if ($parameter === false) {
            return self::validateAdditionalItemsWhenNotAllowed($data, $items, $pointer);
        } elseif (is_object($parameter)) {
            $additionalItems = array_slice($data, count($items));

            return self::validateAdditionalItemsAgainstSchema(
                $additionalItems,
                $parameter,
                $validatorFactory,
                $pointer
            );
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
     * @param array                                       $items
     * @param object                                      $schema
     * @param \League\JsonGuard\SubSchemaValidatorFactory $validatorFactory
     * @param string                                      $pointer
     *
     * @return array
     */
    private static function validateAdditionalItemsAgainstSchema(
        $items,
        $schema,
        SubSchemaValidatorFactory $validatorFactory,
        $pointer
    ) {
        $errors = [];
        foreach ($items as $key => $item) {
            // Escaping isn't necessary since the key is always numeric.
            $currentPointer = $pointer . '/' . $key;
            $validator      = $validatorFactory->makeSubSchemaValidator($item, $schema, $currentPointer);
            $errors         = array_merge($errors, $validator->errors());
        }

        return $errors;
    }

    /**
     * @param array $data
     * @param array $items
     * @param $pointer
     *
     * @return \League\JsonGuard\ValidationError
     */
    private static function validateAdditionalItemsWhenNotAllowed($data, $items, $pointer)
    {
        if (count($data) > count($items)) {
            return new ValidationError(
                'Additional items are not allowed.',
                self::KEYWORD,
                $data,
                $pointer
            );
        }
    }
}
