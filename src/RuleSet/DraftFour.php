<?php

namespace League\JsonGuard\RuleSet;

use League\JsonGuard\Constraint\DraftFour\AdditionalItems;
use League\JsonGuard\Constraint\DraftFour\AdditionalProperties;
use League\JsonGuard\Constraint\DraftFour\AllOf;
use League\JsonGuard\Constraint\DraftFour\AnyOf;
use League\JsonGuard\Constraint\DraftFour\Dependencies;
use League\JsonGuard\Constraint\DraftFour\Enum;
use League\JsonGuard\Constraint\DraftFour\ExclusiveMaximum;
use League\JsonGuard\Constraint\DraftFour\ExclusiveMinimum;
use League\JsonGuard\Constraint\DraftFour\Format;
use League\JsonGuard\Constraint\DraftFour\Items;
use League\JsonGuard\Constraint\DraftFour\Maximum;
use League\JsonGuard\Constraint\DraftFour\MaxItems;
use League\JsonGuard\Constraint\DraftFour\MaxLength;
use League\JsonGuard\Constraint\DraftFour\MaxProperties;
use League\JsonGuard\Constraint\DraftFour\Minimum;
use League\JsonGuard\Constraint\DraftFour\MinItems;
use League\JsonGuard\Constraint\DraftFour\MinLength;
use League\JsonGuard\Constraint\DraftFour\MinProperties;
use League\JsonGuard\Constraint\DraftFour\MultipleOf;
use League\JsonGuard\Constraint\DraftFour\Not;
use League\JsonGuard\Constraint\DraftFour\OneOf;
use League\JsonGuard\Constraint\DraftFour\Pattern;
use League\JsonGuard\Constraint\DraftFour\PatternProperties;
use League\JsonGuard\Constraint\DraftFour\Properties;
use League\JsonGuard\Constraint\DraftFour\Required;
use League\JsonGuard\Constraint\DraftFour\Type;
use League\JsonGuard\Constraint\DraftFour\UniqueItems;

/**
 * The default rule set for JSON Schema Draft 4.
 * @see http://tools.ietf.org/html/draft-zyp-json-schema-04
 * @see  https://tools.ietf.org/html/draft-fge-json-schema-validation-00
 */
final class DraftFour extends RuleSetContainer
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
