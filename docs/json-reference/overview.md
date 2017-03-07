---
layout: default
permalink: /json-reference/overview
title: Overview
---

# Overview

[![Author][ico-author]][link-author]
[![Source Code][ico-source]][link-source]
[![Software License][ico-license]][link-license]
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]

A package for working with [JSON references](https://tools.ietf.org/id/draft-pbryan-zyp-json-ref-03.html).

- Resolves all references, replacing them with proxy objects.
- Supports references to external files, urls, or custom sources.
- Safely resolves circular references.
- Supports caching dereferenced schemas.
- Dereferenced schemas can be cached.
- Dereferenced schemas can be safely json_encoded.
- Works with Swagger, JSON Schema, and any other spec compliant JSON documents.

[link-source]: https://github.com/thephpleague/json-reference
[link-author]: https://twitter.com/__yuloh
[link-license]: https://github.com/thephpleague/json-reference/blob/master/LICENSE.md
[link-travis]: https://travis-ci.org/thephpleague/json-reference
[link-scrutinizer]: https://scrutinizer-ci.com/g/thephpleague/json-reference/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/thephpleague/json-reference
[link-docs]: https://github.com/thephpleague/json-reference/tree/gh-pages

[ico-source]: http://img.shields.io/badge/source-league/json--reference-blue.svg?style=flat-square
[ico-author]: http://img.shields.io/badge/author-@__yuloh-blue.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/thephpleague/json-reference/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/thephpleague/json-reference.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/thephpleague/json-reference.svg?style=flat-square
