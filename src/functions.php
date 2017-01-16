<?php

namespace League\JsonGuard;

use Sabre\Uri;

/**
 * @param string $json
 * @param bool   $assoc
 * @param int    $depth
 * @param int    $options
 * @return mixed
 * @throws \InvalidArgumentException
 */
function json_decode($json, $assoc = false, $depth = 512, $options = 0)
{
    $data = \json_decode($json, $assoc, $depth, $options);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new \InvalidArgumentException(sprintf('Invalid JSON: %s', json_last_error_msg()));
    }

    return $data;
}

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
 * Escape a JSON Pointer.
 *
 * @param  string $pointer
 * @return string
 */
function escape_pointer($pointer)
{
    $pointer = str_replace('~', '~0', $pointer);
    return str_replace('/', '~1', $pointer);
}


/**
 * Push a segment onto the given JSON Pointer.
 *
 * @param string $path
 * @param string $segment
 *
 * @return string
 */
function pointer_push($path, $segment)
{
    return $path . '/' . escape_pointer($segment);
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

/**
 * Removes the fragment from a reference.
 *
 * @param  string $ref
 * @return string
 */
function strip_fragment($ref)
{
    $fragment = parse_url($ref, PHP_URL_FRAGMENT);

    return $fragment ? str_replace($fragment, '', $ref) : $ref;
}

/**
 * Determine if a reference is relative.
 * A reference is relative if it does not being with a prefix.
 *
 * @param string $ref
 *
 * @return bool
 */
function is_relative_ref($ref)
{
    return !preg_match('#^.+\:\/\/.*#', $ref);
}

/**
 * Resolve the given id against the parent scope and return the resolved URI.
 *
 * @param string $id          The id to resolve.  This should be a valid relative or absolute URI.
 * @param string $parentScope The parent scope to resolve against.  Should be a valid URI or empty.
 *
 * @return string
 */
function resolve_uri($id, $parentScope)
{
    // If there is no parent scope, there is nothing to resolve against.
    if ($parentScope === '') {
        return $id;
    }

    return Uri\resolve($parentScope, $id);
}

/**
 * Recursively iterates over each value in the schema passing them to the callback function.
 * If the callback function returns true, the value is returned into the result array, keyed by a JSON Pointer.
 *
 * @param mixed    $schema
 * @param callable $callback
 * @param string   $pointer
 *
 * @return array
 */
function schema_extract($schema, callable $callback, $pointer = '')
{
    $matches = [];

    if (!is_array($schema) && !is_object($schema)) {
        return $matches;
    }

    foreach ($schema as $keyword => $value) {
        switch (true) {
            case is_object($value):
                $matches = array_merge($matches, schema_extract($value, $callback, pointer_push($pointer, $keyword)));
                break;
            case is_array($value):
                foreach ($value as $k => $v) {
                    $matches = array_merge(
                        $matches,
                        schema_extract($v, $callback, pointer_push(pointer_push($pointer, $keyword), $k))
                    );
                }
                break;
            case $callback($keyword, $value):
                $matches[$pointer] = $value;
                break;
        }
    }

    return $matches;
}
