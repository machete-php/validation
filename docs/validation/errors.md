---
layout: default
permalink: validation/errors/
title: Errors
---

# Introduction

The validator returns detailed errors for each validation failure.  Calling the `errors` method on the validator will return an array of [ValidationError objects](https://github.com/thephpleague/json-guard/blob/master/src/ValidationError.php).  Since the ValidationError objects have a `toArray` method, if you cast them to arrays they would look like this:

```php
[
 [
   "code"        => 50,
   "message"     => "Value 'json-guard.dev/schema#' does not match the format 'uri'",
   "pointer"     => "/id",
   'value'       => 'json-guard.dev/schema#',
   'context'     => ['value' => 'json-guard.dev/schema#', 'format' => 'uri'],
 ],
 [
   "code"        => 25,
   "message"     => "Value '2192191' is not a string.",
   "pointer"     => "/name",
   'value'       => 2192191,
   'context'     => ['value' => '"2192191"'],
 ]
]
```

## Error Format

### Code

The code is a unique identifier for this error type.  You can view the complete list of error codes [here](https://github.com/thephpleague/json-guard/blob/master/src/ErrorCode.php).

### Message

The message is a developer friendly explanation of what caused the error.

The `message` is intended for developers and is not localized.  Error messages can be easily localized for your application using the error code, the context, and the [symfony/translation](http://symfony.com/doc/current/components/translation/usage.html) component or a similar library.

### Pointer

The pointer is a [JSON Pointer](https://tools.ietf.org/html/rfc6901) to the attribute that caused the error.

### Value

The value that caused the error.

### Context

The context array holds any data meant to be interpolated into the error message.  For example, the schema `{"minimum": 2}` and the data `1` would return the context `['value' => '1, min' => 2]`.

Every value in the array is cast to a string so that it can be safely interpolated.

