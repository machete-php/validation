<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard\Assert;
use function League\JsonGuard\error;
use League\JsonGuard\Validator;

class Enum implements Constraint
{
    const KEYWORD = 'enum';

    /**
     * {@inheritdoc}
     */
    public function validate($value, $parameter, Validator $validator)
    {
        Assert::type($parameter, 'array', self::KEYWORD, $validator->getSchemaPath());

        if (is_object($value)) {
            foreach ($parameter as $i) {
                if (is_object($i) && $value == $i) {
                    return null;
                }
            }
        } else {
            if (in_array($value, $parameter, true)) {
                return null;
            }
        }

        return error('Value {cause} is not one of: {parameter}', $validator);
    }
}
