<?php

namespace League\JsonGuard\Exceptions;

use Psr\Container\NotFoundExceptionInterface;

class ConstraintNotFoundException extends \Exception implements NotFoundExceptionInterface
{
    public static function forRule($rule)
    {
        return new static(sprintf('The constraint for "%s" was not found.', $rule));
    }
}
