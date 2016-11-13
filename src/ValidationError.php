<?php

namespace League\JsonGuard;

class ValidationError implements \ArrayAccess, \JsonSerializable
{
    /**
     * @var string
     */
    private $message;

    /**
     * @var int
     */
    private $code;

    /**
     * @var string|null
     */
    private $pointer;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var array
     */
    private $context;

    /**
     * @param string      $message
     * @param int         $code
     * @param mixed       $value
     * @param string|null $pointer
     * @param array       $context
     */
    public function __construct($message, $code, $value, $pointer = null, array $context = [])
    {
        $this->message     = $message;
        $this->code        = $code;
        $this->pointer     = $pointer;
        $this->value       = $value;
        $this->context     = array_map('League\JsonGuard\as_string', $context);
    }

    /**
     * Get the human readable error message for this error.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->interpolate($this->message, $this->context);
    }

    /**
     * Interpolate the context values into the message placeholders.
     *
     * @param  string $message
     * @param  array  $context
     *
     * @return string
     */
    private function interpolate($message, array $context = [])
    {
        $replace = [];
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = as_string($val);
        }

        return strtr($message, $replace);
    }

    /**
     * Get the error code for this error.
     *
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Get the path to the property that failed validation.
     *
     * @return string|null
     */
    public function getPointer()
    {
        return $this->pointer;
    }

    /**
     * Get the value that caused the assertion to fail.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get the context that applied to the failed assertion.
     *
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'code'    => $this->getCode(),
            'message' => $this->getMessage(),
            'pointer' => $this->getPointer(),
            'value'   => $this->getValue(),
            'context' => $this->getContext(),
        ];
    }

    /**
     * Specify data which should be serialized to JSON
     * @link  http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Whether a offset exists
     * @link  http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->toArray());
    }

    /**
     * Offset to retrieve
     * @link  http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     *
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        $errorArray = $this->toArray();
        return array_key_exists($offset, $errorArray) ? $errorArray[$offset] : null;
    }

    /**
     * Offset to set
     * @link  http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        // A ValidationError is immutable.
        return null;
    }

    /**
     * Offset to unset
     * @link  http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        // A ValidationError is immutable.
        return null;
    }
}
