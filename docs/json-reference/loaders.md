---
layout: default
permalink: /json-reference/loaders
title: Loaders
---

# Loaders

Loaders are used to load external references.  You need to register a loader for every URI schema you would like to load external references for.

## Default Loaders

By default loaders are registered for the `file`, `http`, and `https` protocols.  The web loaders will use curl if available and fall back to a `file_get_contents` loader.

## Available Loaders

### File Loader

Loads schemas from the local filesystem.  Automatically registered for the `file` scheme.

### Curl Loader

Loads schemas using curl.  This loader is automatically registered for the `http` and `https` schemes if the curl extension is available.

### File Get Contents Web Loader

Loads remote schemas using `file_get_contents`.  This loader will be used for the `http` and `https` protocols if the curl extension is not available.

### Array Loader

The Array loader loads schemas from an array.  Useful for testing or limiting the possible schemas to a defined set.

```
<?php

$schemas = [
    'user' => json_decode('{ "properties": { "name" : { "type": "string" } } }')
];
$loader = new ArrayLoader($schemas);
```

### Chained Loader

The chained loader takes two other loaders as constructor parameters, and will attempt to load from the first loader before deferring to the second loader.

This is useful if you would like to register multiple loaders from the same prefix.  For instance, you may want to load a specific url from the local filesystem while loading all other schemas via http.

```php
<?php

use \League\JsonReference\Loader\ArrayLoader;
use \League\JsonReference\Loader\ChainedLoader;
use \League\JsonReference\Loader\CurlWebLoader;

$loader = new ChainedLoader(
    new ArrayLoader(['json-schema.org/draft-04/schema' => json_decode(__DIR__ . '/schema.json')]),
    new CurlWebLoader('http')
);
```

### Cached Loader

The cached loader takes a [PSR-16 Simple Cache](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-16-simple-cache.md) implementation and another loader as constructor dependencies.  When a schema is loaded it will be returned from cache if available.  Otherwise it will be loaded using the decorated loader and cached.  The cached loader is used automatically when using the cached dereferencer.

## Custom Loaders

You can make your own loaders by implementing the [Loader Interface](https://github.com/thephpleague/json-reference/blob/master/src/LoaderInterface.php).  Imagine you may want to load schemas from a CouchDb database, and your references look like this:

```json
{ "$ref":"couchdb://00a271787f89c0ef2e10e88a0c0001f4" }
```

Once you have written your custom loader, you can register it with the dereferencer's `LoaderManager`.  The first argument should be the loader instance, and the second argument should be the prefix you would like to load references for.

```php
<?php

use My\App\CouchDbLoader;

$couchLoader = new CouchDbLoader();
$deref  = new League\JsonReference\Dereferencer();

$deref->getLoaderManager()->registerLoader($couchLoader, 'couchdb');
```
