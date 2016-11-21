---
layout: default
permalink: validation/extending/
title: Extending
---

# Introduction

You may need to validate JSON with constraints beyond what is defined in Draft4 of the JSON Schema specification.  To add validaton rules you can either define a custom rule set or write a format extension.

## Rulesets

Internally JSON Guard uses [rule sets](https://github.com/league/json-guard/blob/master/src/RuleSet.php), which are composed of [constraints](https://github.com/league/json-guard/tree/master/src/Constraints).  By default the Draft4 rule set is used, which corresponds to Draft 4 of the JSON Schema specification.  You can easily provide your own rule set by passing it as a constructor parameter.

```php
<?php

$data    = json_decode('{ "id": "json-guard.dev/schema#" }');
$schema  = json_decode('{ "properties": { "id": { "type": "string", "format": "uri" } } }');
$ruleset = new CustomRuleset();

$validator = new Validator($data, $schema, $ruleset);
```

## Format Extensions

JSON Schema allows defining formats like `ipv4` that strings will be validated against.  You can extend the validator with your own formats.

### Usage

The following example shows a simple extension to validate twitter handles.  The extension must take a value and pointer, and return a `ValidationError` if the value is invalid.

```php
<?php

use League\JsonGuard\Constraints\Format;
use League\JsonGuard\FormatExtension;
use League\JsonGuard\ValidationError;

class TwitterHandleFormatExtension implements FormatExtension
{
    /**
     * @param string      $value   The value to validate
     * @param string|null $pointer A pointer to the value
     * @return ValidationError|null
     */
    public function validate($value, $pointer = null)
    {
        if (stripos($value, '@') !== 0) {
            return new ValidationError('A twitter handle must start with "@"', Format::KEYWORD, $value, $pointer);
        }
    }
}
```

Once the extension is written, you can register it with the validator.

```php
<?php

$schema = json_decode('{"format": "twitter-handle"}');
$data = '@PHP_CEO';

$validator = new Validator($data, $schema);
$validator->registerFormatExtension('twitter-handle', new TwitterHandleFormatExtension());
```
