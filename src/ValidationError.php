<?php

namespace League\JsonGuard;

final class ValidationError implements \JsonSerializable
{
    /**
     * @var string
     */
    private $message;

    /**
     * @var string|null
     */
    private $interpolatedMessage;

    /**
     * @var mixed
     */
    private $cause;

    /**
     * @var string[]|null
     */
    private $context;

    /**
     * @var string
     */
    private $keyword;

    /**
     * @var mixed
     */
    private $parameter;

    /**
     * @var mixed
     */
    private $data;

    /**
     * @var string
     */
    private $dataPath;

    /**
     * @var object
     */
    private $schema;

    /**
     * @var string
     */
    private $schemaPath;

    public function __construct(
        $message,
        $keyword,
        $parameter,
        $data,
        $dataPath,
        $schema,
        $schemaPath
    ) {
        $this->message    = $message;
        $this->keyword    = $keyword;
        $this->parameter  = $parameter;
        $this->data       = $data;
        $this->dataPath   = $dataPath;
        $this->schema     = $schema;
        $this->schemaPath = $schemaPath;
    }

    /**
     * Get the human readable error message for this error.
     *
     * @return string
     */
    public function getMessage()
    {
        if ($this->interpolatedMessage === null) {
            $this->interpolatedMessage = $this->interpolate($this->message, $this->getContext());
        }

        return $this->interpolatedMessage;
    }

    /**
     * @return string
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * @return mixed
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getDataPath()
    {
        return $this->dataPath;
    }

    /**
     * @return object
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * @return string
     */
    public function getSchemaPath()
    {
        return $this->schemaPath;
    }

    /**
     * Get the cause of the error.  The cause is either the the value itself
     * or the subset of the value that failed validation.  For example, the
     * cause of a failed minimum constraint would be the number itself, while
     * the cause of a failed additionalProperties constraint would be the
     * additional properties in the value that are not allowed.
     *
     * @return mixed
     */
    public function getCause()
    {
        return $this->cause !== null ? $this->cause : $this->data;
    }

    /**
     * @param $cause
     *
     * @return \League\JsonGuard\ValidationError
     */
    public function withCause($cause)
    {
        $error        = clone $this;
        $error->cause = $cause;

        return $error;
    }

    /**
     * Get the context that applied to the failed assertion.
     *
     * @return string[]
     */
    public function getContext()
    {
        if ($this->context === null) {
            $this->context = array_map(
                'League\JsonGuard\as_string',
                [
                    'keyword'     => $this->keyword,
                    'parameter'   => $this->parameter,
                    'source'      => $this->data,
                    'source_path' => $this->dataPath,
                    'schema'      => $this->schema,
                    'schema_path' => $this->schemaPath,
                    'cause'       => $this->getCause(),
                ]
            );
        }

        return $this->context;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'message'     => $this->getMessage(),
            'keyword'     => $this->keyword,
            'parameter'   => $this->parameter,
            'source'      => $this->data,
            'source_path' => $this->dataPath,
            'schema'      => $this->schema,
            'schema_path' => $this->schemaPath,
            'cause'       => $this->getCause(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return $this->toArray();
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
            $replace['{' . $key . '}'] = $val;
        }

        return strtr($message, $replace);
    }
}
