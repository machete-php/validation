<?php

namespace League\JsonGuard\Exceptions;

use Psr\Container\ContainerExceptionInterface;

class ConstraintException extends \Exception implements ContainerExceptionInterface
{
    /**
     * @param string     $rule
     * @param \Exception $previous
     *
     * @return ConstraintException
     */
    public static function forRule($rule, \Exception $previous)
    {
        return new static(sprintf('An exception occurred while building %s.', $rule), 0, $previous);
    }
}
