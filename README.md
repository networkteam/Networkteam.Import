Generic import framework
========================

Imports are based on data providers that generate or transform data. A data provider is basically an iterator returning
array values for each row in the data set. By composing data providers and transformers, more complex scenarios can be
implemented. This package provides a `TransformingProviderDecorator` that has a configurable mapping and allows to use
the Symfony expression language for custom processing (e.g. renaming or combining fields).

The abstract `EntityImporter` is a base class for imports using Doctrine ORM.

[![Build Status](https://travis-ci.org/networkteam/Networkteam.Import.png?branch=master)](https://travis-ci.org/networkteam/Networkteam.Import)

Installation
------------

```bash
composer require networkteam/import
```

Usage
-----

Extend `AbstractImporter` and implement `processImportData` with your custom import logic. For Doctrine ORM entities the
`EntityImporter` can be extended.

For further examples and how to handle different tasks look into the documentation

- [Basic example](doc/basic_import.md)
- [Transforming Data](doc/transforming_data.md)
- [Validating Rows](doc/validating_rows.md)

License
-------

This package is released under the [MIT license](http://opensource.org/licenses/MIT).
