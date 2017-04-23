<?php

namespace League\JsonGuard\Exceptions;

use Psr\Container\NotFoundExceptionInterface;

class ConstraintNotFoundException extends \Exception implements NotFoundExceptionInterface
{
    public static function forRule($keyword)
    {
        return new static(sprintf('The constraint for "%s" was not found.', $keyword));
    }
}
