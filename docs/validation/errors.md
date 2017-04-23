---
layout: default
permalink: validation/errors
title: Errors
---

# Introduction

The validator returns detailed errors for each validation failure.  Calling the `errors` method on the validator will return an array of [ValidationError objects](https://github.com/thephpleague/json-guard/blob/master/src/ValidationError.php).  Since the ValidationError objects can be serialized as JSON, if you serialized them they would look something like this:

```json
[
    {
        "message": "Value 1234 is not a(n) \"string\"",
        "keyword": "type",
        "parameter": "string",
        "data": 1234,
        "data_path": "/name",
        "schema": {
            "description": "Name of the product",
            "type": "string"
        },
        "schema_path": "/properties/name/type",
        "cause": 1234
    },
    {
        "message": "Value 2 is not a(n) \"string\"",
        "keyword": "type",
        "parameter": "string",
        "data": 2,
        "data_path": "/sub-product/sub-product/tags/1",
        "schema": {
            "type": "string"
        },
        "schema_path": "/properties/sub-product/properties/sub-product/properties/tags/items/1/type",
        "cause": 2
    }
]
```

## Error Format

### Message

The message is a developer friendly explanation of what caused the error.

The `message` is intended for developers and is not localized.  Error messages can be easily localized for your application using the keyword, the context, and the [symfony/translation](http://symfony.com/doc/current/components/translation/usage.html) component or a similar library.

### Keyword

The keyword is a unique identifier for this error type.  The keyword is the property name used in the schema.  You can view the complete list of keywords [here](http://json-schema.org/latest/json-schema-validation.html#rfc.section.5).

### Parameter

The parameter passed to the constraint.  For the schema `{"type": "string"}`, the parameter would be "string".

### Data

The data that caused the error.

### Data Path

The data path is a [JSON Pointer](https://tools.ietf.org/html/rfc6901) to the data that caused the error.

### Schema

The schema the data failed to validate against.  If the error was thrown by a nested schema this will only contain the nested schema.

### Schema Path

The schema path is a [JSON Pointer](https://tools.ietf.org/html/rfc6901) to the schema the data failed to validate against.

### Cause

The cause of the failed validation.  In most cases this is the data.  This may be a subset of the data that caused validation to fail (i.e. the additional properties that caused additionalProperties to fail) or missing data that was expected (i.e. the missing dependency that caused dependencies to fail).

### Context

The context array holds any data meant to be interpolated into the error message.  For example, the schema `{"minimum": 2}` and the data `1` would return this context:

```
{
    "keyword": "minimum",
    "parameter": "2",
    "data": "1",
    "data_path": "/",
    "schema": "{\"minimum\":2}",
    "schema_path": "/minimum",
    "cause": "1"
}
```

Every value in the array is cast to a string so that it can be safely interpolated and truncated if over 100 characters long.
