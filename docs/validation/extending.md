---
layout: default
permalink: validation/extending
title: Extending
---

# Introduction

You may need to validate JSON with constraints beyond what is defined in Draft4 of the JSON Schema specification.  To add validaton rules you can either define a custom rule set or write a format extension.

## Rule Sets

Internally JSON Guard uses rule sets, which are composed of [constraints](https://github.com/league/json-guard/tree/master/src/Constraints).  The rule set is just a [PSR-11 container](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-11-container.md) of constraints identified by the validation keyword.  By default the Draft4 rule set is used, which corresponds to Draft 4 of the JSON Schema specification.  You can easily provide your own rule set by passing it as a constructor parameter.

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

use League\JsonGuard\Constraints\DraftFour\Format\FormatExtension;
use League\JsonGuard\Validator;

class TwitterHandleFormatExtension implements FormatExtension
{
    public function validate($value, Validator $validator)
    {
        if (stripos($value, '@') !== 0) {
            return \League\JsonGuard\error('A twitter handle must start with "@"', $validator);
        }
    }
}
```

Once the extension is written, you can register it with the format constraint.

```php
<?php

$schema = json_decode('{"format": "twitter-handle"}');
$data = '@PHP_CEO';

$validator = new Validator($data, $schema);
$validator->getRuleset()->get('format')->addExtension('twitter-handle', new TwitterHandleFormatExtension());
```
