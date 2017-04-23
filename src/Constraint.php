<?php

namespace League\JsonGuard;

interface Constraint
{
    /**
     * @param mixed $value
     * @param mixed $parameter
     * @param Validator $validator
     *
     * @return \League\JsonGuard\ValidationError|null
     */
    public function validate($value, $parameter, Validator $validator);
}
