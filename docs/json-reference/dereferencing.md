---
layout: default
permalink: /json-reference/dereferencing
title: Dereferencing
---

# Dereferencing

Let's say you have a JSON document like this:

```json
{
  "$schema": "http://json-schema.org/draft-04/schema#",
  "definitions": {
    "pet": {
      "type": "object",
      "properties": {
        "name":  { "type": "string" },
        "breed": { "type": "string" },
        "age":  { "type": "string" }
      },
      "required": ["name", "breed", "age"]
    }
  },
  "type": "object",
  "properties": {
    "cat": { "$ref": "#/definitions/pet" },
    "dog": { "$ref": "#/definitions/pet" }
  }
}
```

This document only has _internal_ references.  Internal references use a [JSON Pointer](https://tools.ietf.org/html/rfc6901) and start with an anchor (`#`) character.  We want to resolve the references `#/definitions/pet` and replace them with the JSON value at that location in the schema.

## Usage

To dereference your schema, create a new `Dereferencer` instance.

```php
<?php

$dereferencer  = new League\JsonReference\Dereferencer();
```

Now call the `dereference` method with your schema.  The schema should be the result from a json_decode call.

<div class="message-warning">
  The dereferencer only works with JSON decoded as an object, not an array.
</div>

```php
<?php

$schema = json_decode('{"properties": { "username": {"type": "string"}, "login": {"$ref": "#/properties/username"} } }');
$schema = $dereferencer->dereference($schema);
```

The resulting object is identical, but references have been replaced with Reference objects.

## Paths

Alternatively, you can provide the dereferencer with a path to load the schema from.

```php
<?php

$schema = $dereferencer->dereference('http://json-schema.org/draft-04/schema#');
```

By default `http://`, `https://`, and `file://` paths are supported.

Even if you are using a decoded object, you can still specify the path the schema was loaded from.  This allows the dereferencer to resolve relative external references (i.e. `{"$ref": "some-other-schema.json"}`).  To specify a path, just pass it as the second argument:

```php
<?php

$schema = $dereferencer->dereference($schema, 'http://json-schema.org/draft-04/schema#');
```
