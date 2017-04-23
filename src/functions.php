<?php

namespace League\JsonGuard;

/**
 * A helper function to quickly build an error from a validator instance.
 *
 * @param string                      $message
 * @param \League\JsonGuard\Validator $validator
 *
 * @return \League\JsonGuard\ValidationError
 */
function error($message, Validator $validator)
{
    return new ValidationError(
        $message,
        $validator->getCurrentKeyword(),
        $validator->getCurrentParameter(),
        $validator->getData(),
        $validator->getDataPath(),
        $validator->getSchema(),
        $validator->getSchemaPath()
    );
}

/**
 * @param string $string
 * @param string $charset
 *
 * @return int
 */
function strlen($string, $charset = 'UTF-8')
{
    if (function_exists('iconv_strlen')) {
        return iconv_strlen($string, $charset);
    }

    if (function_exists('mb_strlen')) {
        return mb_strlen($string, $charset);
    }

    if (function_exists('utf8_decode') && $charset === 'UTF-8') {
        $string = utf8_decode($string);
    }

    return \strlen($string);
}

/**
 * Returns the string representation of a value.
 *
 * @param mixed $value
 * @return string
 */
function as_string($value)
{
    switch (true) {
        case is_scalar($value);
            $result = (string) $value;
            break;
        case is_resource($value):
            $result = '<RESOURCE>';
            break;
        default:
            $result = (string) json_encode($value, JSON_UNESCAPED_SLASHES);
    }

    if (\strlen($result) > 100) {
        $result = substr($result, 0, 97) . '...';
    }

    return $result;
}

/**
 * Get the properties matching $pattern from the $data.
 *
 * @param string       $pattern
 * @param array|object $data
 * @return array
 */
function properties_matching_pattern($pattern, $data)
{
    // If an object is supplied, extract an array of the property names.
    if (is_object($data)) {
        $data = array_keys(get_object_vars($data));
    }

    return preg_grep(delimit_pattern($pattern), $data);
}

/**
 * Delimit a regular expression pattern.
 *
 * The regular expression syntax used for JSON schema is ECMA 262, from Javascript,
 * and does not use delimiters.  Since the PCRE functions do, this function will
 * delimit a pattern and escape the delimiter if found in the pattern.
 *
 * @see http://json-schema.org/latest/json-schema-validation.html#anchor6
 * @see http://php.net/manual/en/regexp.reference.delimiters.php
 *
 * @param string $pattern
 *
 * @return string
 */
function delimit_pattern($pattern)
{
    return '/' . str_replace('/', '\\/', $pattern) . '/';
}

/**
 * Determines if the value is an integer or an integer that was cast to a string
 * because it is larger than PHP_INT_MAX.
 *
 * @param  mixed $value
 * @return boolean
 */
function is_json_integer($value)
{
    if (is_string($value) && \strlen($value) && $value[0] === '-') {
        $value = substr($value, 1);
    }

    return is_int($value) || (is_string($value) && ctype_digit($value) && bccomp($value, PHP_INT_MAX) === 1);
}

/**
 * Determines if the value is a number.  A number is a float, integer, or a number that was cast
 * to a string because it is larger than PHP_INT_MAX.
 *
 * @param mixed $value
 *
 * @return boolean
 */
function is_json_number($value)
{
    return is_float($value) || is_json_integer($value);
}
