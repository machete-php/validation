<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard;
use League\JsonGuard\ErrorCode;
use League\JsonGuard\SubSchemaValidatorFactory;
use League\JsonGuard\ValidationError;

class AdditionalProperties implements ParentSchemaAwareContainerInstanceConstraint
{
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
        if (!is_object($data)) {
            return null;
        }

        $diff = self::getDiff($data, $schema);

        if (count($diff) === 0) {
            return null;
        }

        if ($parameter === false) {
            return new ValidationError(
                'Additional properties are not allowed: \'' . implode('\', \'', $diff) . '\'.',
                ErrorCode::NOT_ALLOWED_PROPERTY,
                $data,
                $pointer
            );
        } elseif (is_object($parameter)) {
            // If additionalProperties is an object it's a schema,
            // so validate all additional properties against it.
            $additionalSchema = array_fill_keys($diff, $parameter);

            return Properties::validate($data, $additionalSchema, $validatorFactory, $pointer);
        }

        return null;
    }

    /**
     * Get the properties in $data which are not in $schema 'properties' or matching 'patternProperties'.
     *
     * @param object $data
     * @param object $schema
     *
     * @return array
     */
    protected static function getDiff($data, $schema)
    {
        if (property_exists($schema, 'properties')) {
            $definedProperties = array_keys(get_object_vars($schema->properties));
        } else {
            $definedProperties = [];
        }

        $actualProperties = array_keys(get_object_vars($data));
        $diff             = array_diff($actualProperties, $definedProperties);

        // The diff doesn't account for patternProperties, so lets filter those out too.
        if (property_exists($schema, 'patternProperties')) {
            foreach ($schema->patternProperties as $property => $schema) {
                $matches = JsonGuard\properties_matching_pattern($property, $diff);
                $diff    = array_diff($diff, $matches);
            }

            return $diff;
        }

        return $diff;
    }
}
