<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard\Assert;
use League\JsonGuard\ValidationError;
use League\JsonGuard\Validator;
use function League\JsonReference\pointer_push;

class Dependencies implements Constraint
{
    const KEYWORD = 'dependencies';

    /**
     * {@inheritdoc}
     */
    public function validate($value, $parameter, Validator $validator)
    {
        Assert::type($parameter, ['object', 'array'], self::KEYWORD, $validator->getSchemaPath());

        $errors = [];
        foreach ($parameter as $property => $dependencies) {
            if (!is_object($value) || !property_exists($value, $property)) {
                continue;
            }

            if (is_array($dependencies)) {
                $errors = array_merge(
                    $errors,
                    array_filter(array_map(function ($dependency) use ($value, $validator) {
                        if (!in_array($dependency, array_keys(get_object_vars($value)), true)) {
                            return new ValidationError(
                                'Unmet dependency {dependency}',
                                self::KEYWORD,
                                $value,
                                $validator->getDataPath(),
                                ['dependency' => $dependency]
                            );
                        }
                    }, $dependencies))
                );
            } elseif (is_object($dependencies)) {
                $errors = array_merge(
                    $errors,
                    $validator->makeSubSchemaValidator(
                        $value,
                        $dependencies,
                        $validator->getDataPath(),
                        pointer_push($validator->getSchemaPath(), $property)
                    )->errors()
                );
            }
        }

        return $errors;
    }
}
