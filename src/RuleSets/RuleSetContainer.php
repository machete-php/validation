<?php

namespace League\JsonGuard\RuleSets;

use League\JsonGuard\Exceptions\ConstraintNotFoundException;
use Psr\Container\ContainerInterface;

/**
 * A lightweight container for building rule sets.
 */
class RuleSetContainer implements ContainerInterface
{
    /**
     * @var \Closure[]
     */
    private $rules = [];

    /**
     * @param array $rules
     */
    public function __construct(array $rules = [])
    {
        foreach ($rules as $keyword => $rule) {
            $this->set($keyword, $rule);
        }
    }

    public function get($keyword)
    {
        if (!$this->has($keyword)) {
            throw ConstraintNotFoundException::forRule($keyword);
        }

        return $this->rules[$keyword]($this);
    }

    public function has($keyword)
    {
        return isset($this->rules[$keyword]);
    }

    /**
     * Adds a rule to the container.
     *
     * @param string          $keyword Identifier of the entry.
     * @param \Closure|string $factory The closure to invoke when this entry is resolved or the FQCN.
     *                                 The closure will be given this container as the only
     *                                 argument when invoked.
     */
    public function set($keyword, $factory)
    {
        if (!(is_string($factory) || $factory instanceof \Closure)) {
            throw new \InvalidArgumentException(
                sprintf('Expected a string or Closure, got %s', gettype($keyword))
            );
        }

        $this->rules[$keyword] = function ($container) use ($factory) {
            static $object;

            if (is_null($object)) {
                $object = is_string($factory) ? new $factory() : $factory($container);
            }

            return $object;
        };
    }
}
