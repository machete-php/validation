---
layout: default
permalink: dereferencing/overview/
title: Overview
---

# Introduction

Json Schema allows [references](https://tools.ietf.org/html/draft-pbryan-zyp-json-ref-03) so you don't need to repeat yourself.  For example:

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

References contain a URI, and use [JSON Pointer](https://tools.ietf.org/html/rfc6901).  References need to be resolved before the validator can be used.

## Usage

To dereference your schema, create a new `Dereferencer` instance.

```php
<?php

$deref  = new League\JsonGuard\Dereferencer();
```

Now call the `dereference` method with your schema.  The schema should be the result from a json_decode call.

```php
<?php

$schema = json_decode('"properties": { "username": {"type": "string"}, "login": {"$ref": "#/properties/username"} }');
$schema = $deref->dereference($schema);
```

The resulting object is identical, but references have been replaced with Reference objects.

## Loaders

Alternatively, you can provide the dereferencer with a path to load the schema from.

```php
<?php

$schema = $deref->dereference('http://json-schema.org/draft-04/schema#');
```

By default `http://`, `https://`, and `file://` paths are supported.

### Custom Loaders

You can make your own loaders by implementing the [Loader Interface](https://github.com/thephpleague/json-guard/blob/master/src/Loader.php).  Imagine you may want to load schemas from a CouchDb database, and your references look like this:

```json
{ "$ref":"couchdb://00a271787f89c0ef2e10e88a0c0001f4" }
```

Once you have written your custom loader, you can register it with the dereferencer.  The first argument should be the loader instance, and the second argument should be the prefix you would like to load references for.

```php
<?php

use My\App\CouchDbLoader;

$couchLoader = new CouchDbLoader();
$deref  = new League\JsonGuard\Dereferencer();

$deref->registerLoader($couchLoader, 'couchdb');
```
