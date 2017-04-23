<?php

namespace League\JsonGuard\Exception;

use Psr\Container\ContainerExceptionInterface;

final class ConstraintException extends \Exception implements ContainerExceptionInterface
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
