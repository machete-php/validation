<?php

namespace League\JsonGuard\Constraint\DraftFour;

use League\JsonGuard\Assert;
use League\JsonGuard\Constraint\DraftFour\Format\FormatExtensionInterface;
use League\JsonGuard\ConstraintInterface;
use League\JsonGuard\Validator;
use function League\JsonGuard\error;
use League\JsonGuard\Exception\InvalidSchemaException;

final class Format implements ConstraintInterface
{
    const KEYWORD = 'format';

    /**
     * @see https://tools.ietf.org/html/rfc3339#section-5.6
     */
    const DATE_TIME_PATTERN =
        '/^(?<fullyear>\d{4})-(?<month>0[1-9]|1[0-2])-(?<mday>0[1-9]|[12][0-9]|3[01])' . 'T' .
        '(?<hour>[01][0-9]|2[0-3]):(?<minute>[0-5][0-9]):(?<second>[0-5][0-9]|60)(?<secfrac>\.[0-9]+)?' .
        '(Z|(\+|-)(?<offset_hour>[01][0-9]|2[0-3]):(?<offset_minute>[0-5][0-9]))$/i';

    /**
     * @internal
     */
    const HOST_NAME_PATTERN = '/^[_a-z]+\.([_a-z]+\.?)+$/i';

    /**
     * @internal
     */
    const CVV_PATTERN = '/^[0-9]{3,4}$/i';

    /**
     * @internal
     *
     * @var string[]
     */
    const KNOWN_FORMATS = ['date-time', 'uri', 'email', 'ipv4', 'ipv6','hostname', 'cvv'];

    /**
     * @var \League\JsonGuard\Constraint\DraftFour\Format\FormatExtensionInterface[]
     */
    private $extensions = [];

    /**
     * @var bool
     */
    private $ignoreUnknownFormats = true;

    /**
     * Any custom format extensions to use, indexed by the format name.
     *
     * @param array \League\JsonGuard\Constraint\DraftFour\Format\FormatExtensionInterface[] $extensions
     * @param bool                                                                           $ignoreUnknownFormats
     */
    public function __construct(array $extensions = [], $ignoreUnknownFormats = true)
    {
        foreach ($extensions as $format => $extension) {
            $this->addExtension($format, $extension);
        }

        $this->ignoreUnknownFormats = $ignoreUnknownFormats;
    }

    /**
     * Add a custom format extension.
     *
     * @param string                                                                 $format
     * @param \League\JsonGuard\Constraint\DraftFour\Format\FormatExtensionInterface $extension
     */
    public function addExtension($format, FormatExtensionInterface $extension)
    {
        $this->extensions[$format] = $extension;
    }

    /**
     * Define if unknown formats shall be ignored
     *
     * @param boolean
     */
    public function setIgnoreUnknownFormats($ignoreUnknownFormats)
    {
        $this->ignoreUnknownFormats = $ignoreUnknownFormats;
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
            case 'cvv':
                return self::validateRegex(
                    $value,
                    self::CVV_PATTERN,
                    $validator
                );
            default:
                if (!$this->ignoreUnknownFormats) {
                    throw InvalidSchemaException::invalidParameter(
                        $parameter,
                        array_merge(self::KNOWN_FORMATS, array_keys($this->extensions)),
                        self::KEYWORD,
                        $validator->getSchemaPath()
                    );
                }
        }
    }

    /**
     * @param mixed                       $value
     * @param string                      $pattern
     * @param \League\JsonGuard\Validator $validator
     *
     * @return \League\JsonGuard\ValidationError|null
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
