# Aura.Autoload

Provides a full PSR-4 and limited PSR-0 autoloader. Although it is
installable via Composer, its best use is probably outside a Composer-oriented
project.

For a full PSR-0 only autoloader, please see [Aura.Autoload v1](https://github.com/auraphp/Aura.Autoload/tree/develop).

## Foreword

### Installation

This library requires PHP 5.3 or later; we recommend using the latest available version of PHP as a matter of principle. It has no userland dependencies.

It is installable and autoloadable via Composer as [aura/autoload](https://packagist.org/packages/aura/autoload).

Alternatively, [download a release](https://github.com/auraphp/Aura.Autoload/releases) or clone this repository, then require or include its _autoload.php_ file.

### Quality

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/auraphp/Aura.Autoload/badges/quality-score.png?b=develop-2)](https://scrutinizer-ci.com/g/auraphp/Aura.Autoload/)
[![Code Coverage](https://scrutinizer-ci.com/g/auraphp/Aura.Autoload/badges/coverage.png?b=develop-2)](https://scrutinizer-ci.com/g/auraphp/Aura.Autoload/)
[![Build Status](https://travis-ci.org/auraphp/Aura.Autoload.png?branch=develop-2)](https://travis-ci.org/auraphp/Aura.Autoload)

To run the unit tests at the command line, issue `phpunit` at the package root. (This requires [PHPUnit][] to be available as `phpunit`.)

[PHPUnit]: http://phpunit.de/manual/

This library attempts to comply with [PSR-1][], [PSR-2][], and [PSR-4][]. If
you notice compliance oversights, please send a patch via pull request.

[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md


### Community

To ask questions, provide feedback, or otherwise communicate with the Aura community, please join our [Google Group](http://groups.google.com/group/auraphp), follow [@auraphp on Twitter](http://twitter.com/auraphp), or chat with us on #auraphp on Freenode.


## Getting Started

To use the autoloader, first instantiate it, then register it with SPL
autoloader stack:

```php
<?php
// instantiate
$loader = new \Aura\Autoload\Loader;

// append to the SPL autoloader stack; use register(true) to prepend instead
$loader->register();
?>
```

### PSR-4 Namespace Prefixes

To add a namespace conforming to [PSR-4][] specifications, point to the base
directory for that namespace. Multiple base directories are allowed, and will
be searched in the order they are added.

```php
<?php
$loader->addPrefix('Foo\Bar', '/path/to/foo-bar/src');
$loader->addPrefix('Foo\Bar', '/path/to/foo-bar/tests');
?>
```

To set several namespaces prefixes at once, overriding all previous prefix
settings, use `setPrefixes()`.

```php
<?php
$loader->setPrefixes(array(
    'Foo\Bar' => array(
        '/path/to/foo-bar/src',
        '/path/to/foo-bar/tests',
    ),

    'Baz\Dib' => array(
        '/path/to/baz.dib/src',
        '/path/to/baz.dib/tests',
    ),
));
?>
```

### PSR-0 Namespaces

To add a namespace conforming to [PSR-0][] specifications, one that uses only
namespace separators in the class names (no underscores allowed!), point to
the directory containing classes for that namespace. Multiple directories are
allowed, and will be searched in the order they are added.

[PSR-0]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md

```php
<?php
$loader->addPrefix('Baz\Dib', '/path/to/baz-dib/src/Baz/Dib');
$loader->addPrefix('Baz\Dib', '/path/to/baz-dib/tests/Baz/Dib');
?>
```

To set several namespaces prefixes at once, as with PSR-4, use `setPrefixes()`.

### Explicit Class-to-File Mappings

To map a class explictly to a file, use the `setClassFile()` method.

```php
<?php
$loader->setClassFile('Foo\Bar\Baz', '/path/to/Foo/Bar/Baz.php');
?>
```

To set several class-to-file mappings at once, overriding all previous
mappings, use `setClassFiles()`. (Alternatively, use `addClassFiles()` to
append to the existing mappings.)

```php
<?php
$loader->setClassFiles(array(
    'Foo\Bar\Baz'  => '/path/to/Foo/Bar/Baz.php',
    'Foo\Bar\Qux'  => '/path/to/Foo/Bar/Qux.php',
    'Foo\Bar\Quux' => '/path/to/Foo/Bar/Quux.php',
));
?>
```

### Inspection and Debugging

These methods are available to inspect the `Loader`:

- `getPrefixes()` returns all the added namespace prefixes and their base
  directories

- `getClassFiles()` returns all the explicit class-to-file mappings

- `getLoadedClasses()` returns all the class names loaded by the `Loader` and
  the file names for the loaded classes

If a class file cannot be loaded for some reason, review the debug information
using `getDebug()`. This will show a log of information for the most-recent
autoload attempt involving the `Loader`.

```php
<?php
// set the wrong path for Foo\Bar classes
$loader->addPrefix('Foo\Bar', '/wrong/path/to/foo-bar/src');

// this will fail
$baz = new \Foo\Bar\Baz;

// examine the debug information
var_dump($loader->getDebug());
// array(
//     'Loading Foo\\Bar\\Baz',
//     'No explicit class file',
//     'Foo\\Bar\\: /path/to/foo-bar/Baz.php not found',
//     'Foo\\: no base dirs',
//     'Foo\\Bar\\Baz not loaded',
// )
?>
```
