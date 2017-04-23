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
        AdditionalItems::KEYWORD      => AdditionalItems::class,
        AdditionalProperties::KEYWORD => AdditionalProperties::class,
        AllOf::KEYWORD                => AllOf::class,
        Anyof::KEYWORD                => AnyOf::class,
        Dependencies::KEYWORD         => Dependencies::class,
        Enum::KEYWORD                 => Enum::class,
        ExclusiveMaximum::KEYWORD     => ExclusiveMaximum::class,
        ExclusiveMinimum::KEYWORD     => ExclusiveMinimum::class,
        Format::KEYWORD               => Format::class,
        Items::KEYWORD                => Items::class,
        Maximum::KEYWORD              => Maximum::class,
        MaxItems::KEYWORD             => MaxItems::class,
        MaxLength::KEYWORD            => MaxLength::class,
        MaxProperties::KEYWORD        => MaxProperties::class,
        Minimum::KEYWORD              => Minimum::class,
        MinItems::KEYWORD             => MinItems::class,
        MinLength::KEYWORD            => MinLength::class,
        MinProperties::KEYWORD        => MinProperties::class,
        MultipleOf::KEYWORD           => MultipleOf::class,
        Not::KEYWORD                  => Not::class,
        OneOF::KEYWORD                => OneOf::class,
        Pattern::KEYWORD              => Pattern::class,
        PatternProperties::KEYWORD    => PatternProperties::class,
        Properties::KEYWORD           => Properties::class,
        Required::KEYWORD             => Required::class,
        Type::KEYWORD                 => Type::class,
        UniqueItems::KEYWORD          => UniqueItems::class,
    ];

    public function __construct(array $rules = [])
    {
        parent::__construct(array_merge(self::DEFAULT_RULES, $rules));
    }
}
