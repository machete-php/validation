# Change Log

## Unreleased

## Fixed

* Fixed many instances of the date-time validation rejecting valid values and allowing invalid values.

## 1.0.1 - 2017-05-03

## Fixed

* Fixed the type validation to stop rejecting numeric strings larger than PHP_INT_MAX.  This was originally implemented to prevent integers decoded with JSON_BIGINT_AS_STRING passing string validation but caused false negatives.  If you need to prevent numeric strings you should add a pattern constraint.  Contributed by @akeeman.

## 1.0.0 - 2017-04-29

1.0.0 is a complete rewrite.  Please review the library and update your code accordingly.

### Fixed

* Fixed the date-time validation to not allow invalid date-times in some cases.

### Changed

#### General

* Classes not meant to be extended have been marked final.

#### Dependencies

* Support was dropped for PHP 5.5.
* HHVM is not actively supported anymore.
* bcmatch is now a required extension.

#### Separate Packages

Starting with the 1.0 release json-guard is maintained as two separate packages - a JSON Schema validator implementation and a JSON Reference implementation.

You will need to require both `league/json-guard` and `league/json-reference` if you are using JSON references.

#### Dereferencing

* The Dereferencer does not use JSON Schema draft 4 scope resolution rules (`id`) by default anymore.  See [the scope resolution documentation](json-reference/scope-resolution) for more info.
* Loaders are now registered with a loader manager.  See [the loader documentation](json-reference/loaders) for more info.

#### Constraints

* All constraints now implement a single interface.  See `League\JsonGuard\Constraint` for more info.  If you are using custom constraints you should update them to match the new signature.
* All draft 4 constraints were moved to the `League\JsonGuard\Constraint\DraftFour` namespace.
* All constraints use dependency injection for configuration.  This includes the precision used for minimum, maximum, and their exclusive variants and the charset used for minimumLength and maximumLength.
* Custom format extensions are now registered with the format constraint directly.

#### Rule Sets

* The rule set interface was dropped in favor of the PSR-11 container interface.  Custom rule sets can extend the `League\JsonGuard\RuleSet\RuleSetContainer` to make implementation easier.
* The default rule set now uses the same instance each time instead of creating a new instance.

#### Errors

* Error messages no longer implement array access.
* The error message 'value' has been renamed to 'data' and 'pointer' has been renamed to 'data_path'.
* The data path for data at the root path will now return '/', not ''.
* All error messages now return the same context.  See `League\JsonGuard\ValidationError` for more info.
* The error message constructor now requires (message, keyword, parameter, data, data path, schema, schema path).  You can optionally set a cause.  The `League\JsonGuard\error` function can be used to make building errors easier.
* The data pointer will correctly return '/' instead of '' for errors in the root document.
* The error messages have been rewritten to use consistent wording and do not include the value in the message.
* The error context will truncate any strings over 100 characters.

### Removed

* Removed the SubSchemaValidatorFactory interface.
* Removed the the RuleSet interface.
* Removed the Comparator.
* Removed Pointer Parser. 

## 0.5.1 - 2016-11-28

### Fixed

* Fixed a bug where the context was being encoded as a string twice, resulting in extra quotes around parameters in the error message.
* Updated incorrect docblock types for ValidationError keyword.

## 0.5.0 - 2016-11-28

### Changed

* ValidationError "constraints" were replaced with "context".
    * Any calls to `ValidationError@getConstraint` need to be changed to `ValidationError@getContext`.
    * If you are using the `ArrayAccess` interface for `ValidationError` you need to replace any usage of the `constraints` key with `context`.
    * Unlike the old constraints array, every entry in the context array is a string. This makes implementing your own error messages a lot easier.
* ValidationError "code" was replaced with "keyword".
    * Each validation error will now return a string keyword instead of a numeric code.
    * The League\JsonGuard\ErrorCode class was removed.
    * Any calls to `ValidationError@getCode` need to be changed to `ValidationError@getKeyword`.
    * If you are using the `ArrayAccess` interface for `ValidationError` you need to replace any usage of the `code` key with `keyword`.
    * Instead of there being a different code for every format failure, they just return the keyword 'format'.
* Invalid schemas now throw an InvalidSchemaException.
* Dereferencer@getLoader is now public.

### Fixed

* Type number was passing for numeric strings when it should not have been.

### Added

* Added a `getLoaders` method to the Dereferencer which returns all loaders.

## 0.4.0 - 2016-11-03

### Changed

* The dereferencer now lazily resolves external references.
* You can now use pointers when using the file loader, I.E. 'file://my-schema.json#/some/property'.

### Fixed

* Fixed a bug where non string values passed to `format` would fail when they should pass.
* Massive improvements to URI resolution.  Now using sabre/uri (BSD-3) to resolve reference URIs.
* Fixed a bug where the dereferencer would try to resolve ids that were not strings.

## 0.3.3 - 2016-08-22

### Fixed

* Fixed a bug that caused a Segmentation fault on a system where mbstring and intl extensions were missing by @msarca
* Avoid PHP notice on empty integer fields by @gbirke
* Fixed a bug where properties with the name `$ref` were considered a reference by @ribeiropaulor
* The dereferencer was fixed to resolve relative references when the parent schema does not have an `id`.
* Fixed a bug where absolute references in nested IDs were appended to the current resolution scope instead of replacing it.

### Added

* It is now possible to pass a path with a reference fragment to the dereferencer.
* Added the dependency constraint to the dependencies error.

## 0.3.2 - 2016-07-26

### Fixed

* the type : integer constraint now passes for valid negative integers that are larger than PHP_INT_MAX, and does not pass for numeric strings that are not larger than PHP_INT_MAX.
* The date-time format constraint was fixed to only pass if the date is RFC3339 instead of all of ISO 8601.
* The uri format constraint now passes for valid protocol relative URIs.
* Fixed a bug where custom format extensions only worked for the first level of data and were not used for nested objects.
* Minimum and Maximum comparisons will now work for numbers larger than PHP_INT_MAX if ext-bcmath is installed.
* Fixed a bug where a custom ruleset was not being used past the first level of data in a nested object.

### Added

A Comparator class was added so that the rest of the code doesn't have to constantly check if bccomp is avaiable.  You can specify the precision to use for comparisons by calling Comparator::setScale().

### Changed

The validator now passes version 1.2.0 of the official test suite.

### Removed

* Setters used when creating sub-schema validators were removed, since they are not necessary.  These were marked @internal so this should not be a breaking change.

## 0.3.1 - 2016-06-28

### Fixed

* The required constraint was not checking the type of the data.  It now correctly ignores the data if it isn't an object.  A fix was also added to the official JSON Schema Test Suite.

## 0.3.0 - 2016-05-11

### Fixed

* The type constraint was failing for string checks if the bcmath extension wasn't installed.  It now passes if the value is a string and bcmath isn't installed.
* Pointers are now escaped for error messages.
* The JSON Pointer now properly handles setting a new element in arrays.
* Fixed a bug where the DraftFour rule set was not throwing an exception when trying to get a missing rule.

### Changed

* The `ErrorCode` constants class is now marked as final.
* All function names are now snake cased.

## 0.2.1 - 2016-05-08

### Changed

* The loaders now `json_decode` with the option `JSON_BIGINT_AS_STRING` by default.  This allows validating numbers larger than `PHP_INT_MAX` properly.

### Fixed

* The dereferencer wasn't resolving references nested under properties that contained a slash character.  The JSON Pointer used internally is now escaped so that properties containing a slash character will dereference properly.

## 0.2.0 - 2016-05-04

### Added

 * Added an ArrayLoader and ChainableLoader.  The ChainableLoader was added to allow using multiple loaders for the same prefix.  The ArrayLoader is mostly for testing, when you want to return paths from an in memory array.

### Changed

* Only validate once when `passes`, `fails`, or `errors` is called instead of re-validating for each call.  This was causing a huge performance hit when validating nested schemas.  The properties constraint made two calls which resulted in an exponential slow down for each level of nesting.
* The `Ruleset` interface now requires throwing a `ConstraintNotFoundException` instead of returning null for missing constraints.
* Moved to the League namespace.
* The curl extension is now explicitly required for dev installs, since you need it to test the `CurlWebLoader`.
* The default max depth was increased to 50.  The validator can validate to that depth really quickly and it's high enough that it won't be reached with normal usage.
* The tests were switched to load json-schema.org urls from memory since their site went down and the build started failing.
