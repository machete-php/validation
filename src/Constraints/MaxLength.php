<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard;
use League\JsonGuard\Assert;
use League\JsonGuard\ValidationError;
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
        Assert::type($parameter, 'number', self::KEYWORD, $validator->getPointer());
        Assert::nonNegative($parameter, self::KEYWORD, $validator->getPointer());

        if (!is_string($value) || JsonGuard\strlen($value, $this->charset) <= $parameter) {
            return null;
        }

        return new ValidationError(
            'String is not at most {max_length} characters long',
            self::KEYWORD,
            $value,
            $validator->getPointer(),
            ['max_length' => $parameter]
        );
    }
}
