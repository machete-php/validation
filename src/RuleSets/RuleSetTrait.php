<?php

namespace League\JsonGuard\RuleSets;

use League\JsonGuard\Exceptions\ConstraintException;
use League\JsonGuard\Exceptions\ConstraintNotFoundException;

trait RuleSetTrait
{
    /**
     * @return array
     */
    abstract protected function rules();

    /**
     * @param string          $id
     * @param string|\Closure $constraint
     *
     * @return void
     */
    abstract protected function setRule($id, $constraint);

    /**
     * @param string          $id
     * @param string|\Closure $constraint
     */
    public function set($id, $constraint)
    {
        if (!is_string($constraint) && !$constraint instanceof \Closure) {
            throw new \InvalidArgumentException(sprintf('Expected a string or Closure, got %s', gettype($constraint)));
        }
        $this->setRule($id, $constraint);
    }

    /**
     * {@inheritdoc}
     */
    public function has($id)
    {
        return array_key_exists($id, $this->rules());
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        if (!$this->has($id)) {
            throw ConstraintNotFoundException::forRule($id);
        }

        $concrete = $this->rules()[$id];

        try {
            return is_string($concrete) ? new $concrete() : $concrete();
        } catch (\Exception $e) {
            throw ConstraintException::forRule($id, $e);
        }
    }
}
