<?php

namespace League\JsonGuard\RuleSets;

use League\JsonGuard\Constraints\AdditionalItems;
use League\JsonGuard\Constraints\AdditionalProperties;
use League\JsonGuard\Constraints\AllOf;
use League\JsonGuard\Constraints\AnyOf;
use League\JsonGuard\Constraints\Dependencies;
use League\JsonGuard\Constraints\Enum;
use League\JsonGuard\Constraints\ExclusiveMaximum;
use League\JsonGuard\Constraints\ExclusiveMinimum;
use League\JsonGuard\Constraints\Format;
use League\JsonGuard\Constraints\Items;
use League\JsonGuard\Constraints\Maximum;
use League\JsonGuard\Constraints\MaxItems;
use League\JsonGuard\Constraints\MaxLength;
use League\JsonGuard\Constraints\MaxProperties;
use League\JsonGuard\Constraints\Minimum;
use League\JsonGuard\Constraints\MinItems;
use League\JsonGuard\Constraints\MinLength;
use League\JsonGuard\Constraints\MinProperties;
use League\JsonGuard\Constraints\MultipleOf;
use League\JsonGuard\Constraints\Not;
use League\JsonGuard\Constraints\OneOf;
use League\JsonGuard\Constraints\Pattern;
use League\JsonGuard\Constraints\PatternProperties;
use League\JsonGuard\Constraints\Properties;
use League\JsonGuard\Constraints\Required;
use League\JsonGuard\Constraints\Type;
use League\JsonGuard\Constraints\UniqueItems;

/**
 * The default rule set for JSON Schema Draft 4.
 * @see http://tools.ietf.org/html/draft-zyp-json-schema-04
 * @see  https://tools.ietf.org/html/draft-fge-json-schema-validation-00
 */
class DraftFour extends RuleSetContainer
{
    const DEFAULT_RULES = [
        'additionalItems'      => AdditionalItems::class,
        'additionalProperties' => AdditionalProperties::class,
        'allOf'                => AllOf::class,
        'anyOf'                => AnyOf::class,
        'dependencies'         => Dependencies::class,
        'enum'                 => Enum::class,
        'exclusiveMaximum'     => ExclusiveMaximum::class,
        'exclusiveMinimum'     => ExclusiveMinimum::class,
        'format'               => Format::class,
        'items'                => Items::class,
        'maximum'              => Maximum::class,
        'maxItems'             => MaxItems::class,
        'maxLength'            => MaxLength::class,
        'maxProperties'        => MaxProperties::class,
        'minimum'              => Minimum::class,
        'minItems'             => MinItems::class,
        'minLength'            => MinLength::class,
        'minProperties'        => MinProperties::class,
        'multipleOf'           => MultipleOf::class,
        'not'                  => Not::class,
        'oneOf'                => OneOf::class,
        'pattern'              => Pattern::class,
        'patternProperties'    => PatternProperties::class,
        'properties'           => Properties::class,
        'required'             => Required::class,
        'type'                 => Type::class,
        'uniqueItems'          => UniqueItems::class,
    ];

    public function __construct(array $rules = [])
    {
        parent::__construct(array_merge(self::DEFAULT_RULES, $rules));
    }
}
