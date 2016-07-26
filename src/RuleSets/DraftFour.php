<?php

namespace League\JsonGuard\RuleSets;

use League\JsonGuard\Constraints\AdditionalItems;
use League\JsonGuard\Constraints\AdditionalProperties;
use League\JsonGuard\Constraints\AllOf;
use League\JsonGuard\Constraints\AnyOf;
use League\JsonGuard\Constraints\Dependencies;
use League\JsonGuard\Constraints\Enum;
use League\JsonGuard\Constraints\Format;
use League\JsonGuard\Constraints\Items;
use League\JsonGuard\Constraints\Max;
use League\JsonGuard\Constraints\MaxItems;
use League\JsonGuard\Constraints\MaxLength;
use League\JsonGuard\Constraints\MaxProperties;
use League\JsonGuard\Constraints\Min;
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
use League\JsonGuard\Exceptions\ConstraintNotFoundException;
use League\JsonGuard\RuleSet;

/**
 * The default rule set for JSON Schema Draft 4.
 * @see http://tools.ietf.org/html/draft-zyp-json-schema-04
 */
class DraftFour implements RuleSet
{
    protected $rules = [
        'additionalItems'      => AdditionalItems::class,
        'additionalProperties' => AdditionalProperties::class,
        'allOf'                => AllOf::class,
        'anyOf'                => AnyOf::class,
        'dependencies'         => Dependencies::class,
        'enum'                 => Enum::class,
        'format'               => Format::class,
        'items'                => Items::class,
        'maximum'              => Max::class,
        'maxItems'             => MaxItems::class,
        'maxLength'            => MaxLength::class,
        'maxProperties'        => MaxProperties::class,
        'minimum'              => Min::class,
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

    /**
     * {@inheritdoc}
     */
    public function has($rule)
    {
        return array_key_exists($rule, $this->rules);
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraint($rule)
    {
        if (!$this->has($rule)) {
            throw ConstraintNotFoundException::forRule($rule);
        }

        return new $this->rules[$rule];
    }
}
