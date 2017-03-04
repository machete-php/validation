---
layout: default
permalink: /json-reference/scope-resolution
title: Scope Resolution
---

# Scope Resolution

When the dereferencer encounters a reference, the reference is resolved like a URI.  For example:

| Base                           | Reference                       | Resolved                        |
| -------------------------------|---------------------------------|---------------------------------|
| http://ref.dev/api/schema.json | user.json                       | http://ref.dev/api/user.json    |
| http://ref.dev/api/schema.json | ../user.json                    | http://ref.dev/user.json        |
| http://ref.dev/api/schema.json | http://ref.dev/api/v2/user.json | http://ref.dev/api/v2/user.json |

If you would like to customize how the Base URI scope is resolved, you can implement the `ScopeResolverInterface`.  For every URI encountered, the scope resolver is invoked with the current schema, a pointer to the current location in the schema, and the current uri scope (if any).

## JSON Schema Scope Resolution

In JSON Schema [the id property](https://spacetelescope.github.io/understanding-json-schema/structuring.html#the-id-property) is used to alter the resolution scope of references.

If the top of your schema had `{ "id": "http://ref.dev/api/schema.json" }` and you encountered the reference `{"$ref": "user.json"}`, JSON Schema dictates that the schema should be loaded from `http://ref.dev/api/user.json`, even if the schema was loaded from `http://ref.dev/api/v2/schema.json` or even the local filesystem.

To enable JSON Schema scope resolution, you can pass the `JsonSchemaScopeResolver` into the dereferencer.

```php
<?php

use League\JsonReference\CoreDereferencer;
use League\JsonReference\ScopeResolvers\JsonSchemaScopeResolver;

$dereferencer = new CoreDereferencer(null, new JsonSchemaScopeResolver());
```
