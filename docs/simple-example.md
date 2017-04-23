---
layout: default
permalink: simple-example
title: Simple example
---

# Simple Example

To demonstrate how to use this package, let's validate some JSON against the JSON that defines the JSON schema itself.

First we create a dereferencer, and dereference the schema.  This resolves any JSON like `{"$ref" "#"}` into reference objects, which are required for the validator to resolve `$ref` keywords properly.  Make sure you [installed league/json-reference](json-reference/installation) if you are using references.

Next we create the validator.  The first argument is the data we are validating.  The second argument is the dereferenced schema.

Once the validator is created you can call `$validator->passes()` or `$validator->fails()` to validate the schema.  If the validator fails, you can view the errors by calling `$validator->errors()`.

```php
<?php

$dereferencer  = League\JsonReference\Dereferencer::draft4();
$schema        = $dereferencer->dereference('http://json-schema.org/draft-04/schema#');
$data          = json_decode('{ "id": "json-guard.dev/schema#" }');

$validator     = new League\JsonGuard\Validator($data, $schema);

if ($validator->fails()) {
    $errors = $validator->errors();
}
```
