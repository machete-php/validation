<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard;
use League\JsonGuard\Assert;
use function League\JsonGuard\error;
use League\JsonGuard\Validator;

class MaxLength implements Constraint
{
    const KEYWORD = 'maxLength';

    /**
     * @var string
     */
    private $charset;

    /**
     * @param string $charset
     */
    public function __construct($charset = 'UTF-8')
    {
        $this->charset = $charset;
    }


    /**
     * {@inheritdoc}
     */
    public function validate($value, $parameter, Validator $validator)
    {
        Assert::type($parameter, 'number', self::KEYWORD, $validator->getSchemaPath());
        Assert::nonNegative($parameter, self::KEYWORD, $validator->getSchemaPath());

        if (!is_string($value) || JsonGuard\strlen($value, $this->charset) <= $parameter) {
            return null;
        }

        return error('The string must be less than {parameter} characters long.', $validator);
    }
}
