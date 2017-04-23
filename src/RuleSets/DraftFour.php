<?php

namespace League\JsonGuard\RuleSets;

use League\JsonGuard\Constraints\DraftFour\AdditionalItems;
use League\JsonGuard\Constraints\DraftFour\AdditionalProperties;
use League\JsonGuard\Constraints\DraftFour\AllOf;
use League\JsonGuard\Constraints\DraftFour\AnyOf;
use League\JsonGuard\Constraints\DraftFour\Dependencies;
use League\JsonGuard\Constraints\DraftFour\Enum;
use League\JsonGuard\Constraints\DraftFour\ExclusiveMaximum;
use League\JsonGuard\Constraints\DraftFour\ExclusiveMinimum;
use League\JsonGuard\Constraints\DraftFour\Format;
use League\JsonGuard\Constraints\DraftFour\Items;
use League\JsonGuard\Constraints\DraftFour\Maximum;
use League\JsonGuard\Constraints\DraftFour\MaxItems;
use League\JsonGuard\Constraints\DraftFour\MaxLength;
use League\JsonGuard\Constraints\DraftFour\MaxProperties;
use League\JsonGuard\Constraints\DraftFour\Minimum;
use League\JsonGuard\Constraints\DraftFour\MinItems;
use League\JsonGuard\Constraints\DraftFour\MinLength;
use League\JsonGuard\Constraints\DraftFour\MinProperties;
use League\JsonGuard\Constraints\DraftFour\MultipleOf;
use League\JsonGuard\Constraints\DraftFour\Not;
use League\JsonGuard\Constraints\DraftFour\OneOf;
use League\JsonGuard\Constraints\DraftFour\Pattern;
use League\JsonGuard\Constraints\DraftFour\PatternProperties;
use League\JsonGuard\Constraints\DraftFour\Properties;
use League\JsonGuard\Constraints\DraftFour\Required;
use League\JsonGuard\Constraints\DraftFour\Type;
use League\JsonGuard\Constraints\DraftFour\UniqueItems;

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
