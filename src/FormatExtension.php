<?php

namespace League\JsonGuard;

interface FormatExtension
{
    /**
     * @param string                      $value The value to validate
     * @param \League\JsonGuard\Validator $validator
     *
     * @return \League\JsonGuard\ValidationError|null A ValidationError if validation fails, otherwise null.
     */
    public function validate($value, Validator $validator);
}
