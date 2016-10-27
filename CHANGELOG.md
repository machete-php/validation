# Change Log

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## Unreleased

### Fixed

* You can now use pointers when using the file loader, I.E. 'file://my-schema.json#/some/property'.
* Fixed a bug where non string values passed to `format` caused an error if they weren't caught by type validation.

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
