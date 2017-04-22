<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard;
use League\JsonGuard\Assert;
use League\JsonGuard\Validator;
use function League\JsonGuard\error;
use function League\JsonReference\pointer_push;

class AdditionalProperties implements Constraint
{
    const KEYWORD = 'additionalProperties';

    /**
     * {@inheritdoc}
     */
    public function validate($value, $parameter, Validator $validator)
    {
        Assert::type($parameter, ['object', 'boolean'], self::KEYWORD, $validator->getSchemaPath());

        if (!is_object($value)) {
            return null;
        }

        $diff = self::getDiff($value, $validator->getSchema());

        if (count($diff) === 0) {
            return null;
        }

        if ($parameter === false) {
            $message = 'Additional properties found which are not allowed: {cause}';
            return error($message, $validator)->withCause($diff);
        } elseif (is_object($parameter)) {
            // If additionalProperties is an object it's a schema,
            // so validate all additional properties against it.
            $errors = [];
            foreach ($diff as $property) {
                $subValidator = $validator->makeSubSchemaValidator(
                    $value->$property,
                    $parameter,
                    pointer_push($validator->getDataPath(), $property)
                );
                $errors = array_merge($errors, $subValidator->errors());
            }

            return $errors;
        }
    }

    /**
     * Get the properties in $value which are not in $schema 'properties' or matching 'patternProperties'.
     *
     * @param object $value
     * @param object $schema
     *
     * @return array
     */
    protected static function getDiff($value, $schema)
    {
        if (property_exists($schema, Properties::KEYWORD)) {
            $definedProperties = array_keys(get_object_vars($schema->properties));
        } else {
            $definedProperties = [];
        }

        $actualProperties = array_keys(get_object_vars($value));
        $diff             = array_diff($actualProperties, $definedProperties);

        // The diff doesn't account for patternProperties, so lets filter those out too.
        if (property_exists($schema, PatternProperties::KEYWORD)) {
            foreach ($schema->patternProperties as $property => $schema) {
                $matches = JsonGuard\properties_matching_pattern($property, $diff);
                $diff    = array_diff($diff, $matches);
            }

            return $diff;
        }

        return $diff;
    }
}
