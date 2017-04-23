<?php

namespace League\JsonGuard\Exception;

use Psr\Container\NotFoundExceptionInterface;

final class ConstraintNotFoundException extends \Exception implements NotFoundExceptionInterface
{
    public static function forRule($keyword)
    {
        return new static(sprintf('The constraint for "%s" was not found.', $keyword));
    }
}
