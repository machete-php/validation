<?php

namespace League\JsonGuard;

/**
 * @param $string
 * @return int
 */
function strlen($string)
{
    if (extension_loaded('intl')) {
        return grapheme_strlen($string);
    }

    if (extension_loaded('mbstring')) {
        return mb_strlen($string, mb_detect_encoding($string));
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
    if (is_resource($value)) {
        return '<RESOURCE>';
    }

    return (string) json_encode($value);
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
    if (is_string($value) && strlen($value) && $value[0] === '-') {
        $value = substr($value, 1);
    }

    return is_int($value) || (is_string($value) && ctype_digit($value) && compare($value, PHP_INT_MAX) === 1);
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

/**
 * @param string|double|int $leftOperand
 * @param string|double|int $rightOperand
 *
 * @return int Returns 0 if the two operands are equal, 1 if the left_operand is larger than the right_operand,
 * -1 otherwise.
 */
function compare($leftOperand, $rightOperand)
{
    return Comparator::compare($leftOperand, $rightOperand);
}
