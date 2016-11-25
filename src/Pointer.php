<?php

namespace League\JsonGuard;

use League\JsonGuard\Pointer\InvalidPointerException;
use League\JsonGuard\Pointer\Parser;

/**
 * A simple JSON Pointer implementation that can traverse
 * an object resulting from a json_decode() call.
 *
 * @see https://tools.ietf.org/html/rfc6901
 */
class Pointer
{
    /**
     * @var object
     */
    private $json;

    /**
     * Pointer constructor.
     * @param object $json
     */
    public function __construct($json)
    {
        $this->json = $json;
    }

    /**
     * @param string $pointer
     * @return mixed
     * @throws InvalidPointerException
     */
    public function get($pointer)
    {
        $pointer = (new Parser($pointer))->get();

        return $this->traverse($this->json, $pointer);
    }

    /**
     * @param string $pointer
     * @return bool
     */
    public function has($pointer)
    {
        try {
            $this->get($pointer);

            return true;
        } catch (InvalidPointerException $e) {
            return false;
        }
    }

    /**
     * @param string $pointer
     * @param mixed  $data
     * @return null
     * @throws InvalidPointerException
     * @throws \InvalidArgumentException
     *
     */
    public function set($pointer, $data)
    {
        if ($pointer === '') {
            throw new \InvalidArgumentException('Cannot replace the object with set.');
        }

        $pointer = (new Parser($pointer))->get();

        // Simple way to check if the path exists.
        // It will throw an exception if it isn't valid.
        $this->traverse($this->json, $pointer);

        $replace = array_pop($pointer);
        $target  = $this->json;
        foreach ($pointer as $segment) {
            if (is_array($target)) {
                $target =& $target[$segment];
            } else {
                $target =& $target->$segment;
            }
        }

        if (is_array($target)) {
            if ($replace === '-') {
                $target[] = $data;
            } else {
                $target[$replace] = $data;
            }
        } elseif (is_object($target)) {
            $target->$replace = $data;
        } else {
            throw new \InvalidArgumentException('Cannot set data because pointer target is not an object or array.');
        }

        return null;
    }

    /**
     * @param mixed $json    The result of a json_decode call or a portion of it.
     * @param array $pointer The parsed pointer
     * @return mixed
     */
    private function traverse($json, $pointer)
    {
        // If we are out of pointers to process we are done.
        if (empty($pointer)) {
            return $json;
        }

        $reference = array_shift($pointer);

        // who does this?
        if ($reference === '' && is_object($json) && property_exists($json, '_empty_')) {
            return $this->traverse($json->_empty_, $pointer);
        }

        if (is_object($json)) {
            if (!property_exists($json, $reference)) {
                throw InvalidPointerException::nonexistentValue($reference);
            }

            return $this->traverse($json->$reference, $pointer);
        } elseif (is_array($json)) {
            if ($reference === '-') {
                return $json;
            }
            if (!array_key_exists($reference, $json)) {
                throw InvalidPointerException::nonexistentValue($reference);
            }

            return $this->traverse($json[$reference], $pointer);
        } else {
            throw InvalidPointerException::nonexistentValue($reference);
        }
    }
}
