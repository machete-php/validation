<?php

namespace League\JsonGuard\Constraints\DraftFour;

use League\JsonGuard\Assert;
use League\JsonGuard\Constraint;
use League\JsonGuard\Constraints\DraftFour\Format\FormatExtension;
use League\JsonGuard\Validator;
use function League\JsonGuard\error;

final class Format implements Constraint
{
    const KEYWORD = 'format';

    // @codingStandardsIgnoreStart
    // @see https://www.w3.org/TR/2012/REC-xmlschema11-2-20120405/datatypes.html#dateTime-lexical-mapping
    const DATE_TIME_PATTERN = '/^-?([1-9][0-9]{3,}|0[0-9]{3})-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])T(([01][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9](\.[0-9]+)?|(24:00:00(\.0+)?))(Z|(\+|-)((0[0-9]|1[0-3]):[0-5][0-9]|14:00))?$/';
    // @codingStandardsIgnoreEnd

    const HOST_NAME_PATTERN = '/^[_a-z]+\.([_a-z]+\.?)+$/i';

    /**
     * @var \League\JsonGuard\Constraints\DraftFour\Format\FormatExtension[]
     */
    private $extensions = [];

    /**
     * Any custom format extensions to use, indexed by the format name.
     *
     * @param array \League\JsonGuard\Constraints\DraftFour\Format\FormatExtension[]
     */
    public function __construct(array $extensions = [])
    {
        foreach ($extensions as $format => $extension) {
            $this->addExtension($format, $extension);
        }
    }

    /**
     * Add a custom format extension.
     *
     * @param string                                                         $format
     * @param \League\JsonGuard\Constraints\DraftFour\Format\FormatExtension $extension
     */
    public function addExtension($format, FormatExtension $extension)
    {
        $this->extensions[$format] = $extension;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, $parameter, Validator $validator)
    {
        Assert::type($parameter, 'string', self::KEYWORD, $validator->getSchemaPath());

        if (isset($this->extensions[$parameter])) {
            return $this->extensions[$parameter]->validate($value, $validator);
        }

        switch ($parameter) {
            case 'date-time':
                return self::validateRegex(
                    $value,
                    self::DATE_TIME_PATTERN,
                    $validator
                );
            case 'uri':
                return self::validateFilter(
                    $value,
                    FILTER_VALIDATE_URL,
                    null,
                    $validator
                );
            case 'email':
                return self::validateFilter(
                    $value,
                    FILTER_VALIDATE_EMAIL,
                    null,
                    $validator
                );
            case 'ipv4':
                return self::validateFilter(
                    $value,
                    FILTER_VALIDATE_IP,
                    FILTER_FLAG_IPV4,
                    $validator
                );
            case 'ipv6':
                return self::validateFilter(
                    $value,
                    FILTER_VALIDATE_IP,
                    FILTER_FLAG_IPV6,
                    $validator
                );
            case 'hostname':
                return self::validateRegex(
                    $value,
                    self::HOST_NAME_PATTERN,
                    $validator
                );
        }
    }

    /**
     * @param mixed                       $value
     * @param string                      $pattern
     * @param \League\JsonGuard\Validator $validator
     *
     * @return \League\JsonGuard\ValidationError|null
     *
     */
    private static function validateRegex($value, $pattern, Validator $validator)
    {
        if (!is_string($value) || preg_match($pattern, $value) === 1) {
            return null;
        }

        return error('The value {data} must match the format {parameter}.', $validator);
    }

    /**
     * @param mixed                       $value
     * @param int                         $filter
     * @param mixed                       $options
     * @param \League\JsonGuard\Validator $validator
     *
     * @return \League\JsonGuard\ValidationError|null
     *
     */
    private static function validateFilter($value, $filter, $options, Validator $validator)
    {
        if (!is_string($value) || filter_var($value, $filter, $options) !== false) {
            return null;
        }

        // This workaround allows otherwise valid protocol relative urls to pass.
        // @see https://bugs.php.net/bug.php?id=72301
        if ($filter === FILTER_VALIDATE_URL && is_string($value) && strpos($value, '//') === 0) {
            if (filter_var('http:' . $value, $filter, $options) !== false) {
                return null;
            }
        }

        return error('The value must match the format {parameter}.', $validator);
    }
}
