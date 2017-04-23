---
layout: default
permalink: /json-reference/circular-references
title: Circular References
---

# Circular References

This package fully supports recursive references.  Consider the following example:

```json
{
  "author": {
    "properties": {
        "name": {
          "type": "string"
        },
        "co-author": {
            "$ref": "#/author" // circular reference
          }
        }
    }
  }
}
```

## Resolving

If the dereferencer attempted to fully resolve this reference, the dereferencer would continue looping infintely.  Instead of resolving references immediately, the $ref is replaced with a [reference object](https://github.com/league/json-reference/blob/master/src/Reference.php).

The reference object is resolved lazily as it is accessed.  Because circular object references are possible, make sure code accessing the dereferenced object does not get stuck in an infinite loop.

## Serializing

Because a $ref may be circular, attempting to inline the $ref would be impossible.

When serialized as JSON, all references are transformed into the original `{ "$ref": "#/some/reference" }` format instead of attempting to inline them.

You can override this behavior by setting the reference serializer used by the dereferencer.

The `InlineReferenceSerializer` will attempt to inline references and throw an exception if a direct circular reference is found.  An indirect circular reference may still exist, in which case `json_encode` will fail and `json_last_error` will return `JSON_ERROR_RECURSION`.
