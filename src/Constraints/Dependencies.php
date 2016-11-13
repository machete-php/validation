<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard;
use League\JsonGuard\ErrorCode;
use League\JsonGuard\SubSchemaValidatorFactory;
use League\JsonGuard\ValidationError;

class Dependencies implements ContainerInstanceConstraint
{
    /**
     * {@inheritdoc}
     */
    public static function validate($data, $parameter, SubSchemaValidatorFactory $validatorFactory, $pointer = null)
    {
        $errors = [];
        foreach ($parameter as $property => $dependencies) {
            if (!is_object($data) || !property_exists($data, $property)) {
                continue;
            }

            if (is_array($dependencies)) {
                $errors = array_merge($errors, self::validatePropertyDependency($data, $dependencies, $pointer));
            } elseif (is_object($dependencies)) {
                $errors = array_merge(
                    $errors,
                    self::validateSchemaDependency($data, $dependencies, $validatorFactory, $pointer)
                );
            }
        }

        return $errors;
    }

    /**
     * @param object $data
     * @param array  $dependencies
     * @param string $pointer
     *
     * @return array
     */
    protected static function validatePropertyDependency($data, $dependencies, $pointer)
    {
        $errors = [];
        foreach ($dependencies as $dependency) {
            if (!in_array($dependency, array_keys(get_object_vars($data)), true)) {
                $errors[] = new ValidationError(
                    'Unmet dependency {dependency}',
                    ErrorCode::UNMET_DEPENDENCY,
                    $data,
                    $pointer,
                    ['dependency' => $dependency]
                );
            }
        }

        return $errors;
    }

    /**
     * @param object                                      $data
     * @param object                                      $dependencies
     * @param \League\JsonGuard\SubSchemaValidatorFactory $validatorFactory
     * @param string                                      $pointer
     *
     * @return array
     */
    protected static function validateSchemaDependency(
        $data,
        $dependencies,
        SubSchemaValidatorFactory $validatorFactory,
        $pointer
    ) {
        $validator = $validatorFactory->makeSubSchemaValidator(
            $data,
            $dependencies,
            $pointer
        );

        return $validator->errors();
    }
}
