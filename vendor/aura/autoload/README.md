# Aura.Autoload

Provides a PSR-4 (and limited PSR-0) autoloading facility. Although it is
installable via Composer, its best use is probably outside a Composer-oriented
project.

## Foreword

### Requirements

This library requires PHP 5.3 or later, and has no userland dependencies.

### Installation

This library is installable and autoloadable via Composer with the following
`require` element in your `composer.json` file:

    "require": {
        "aura/autoload": "dev-develop-2"
    }
    
Alternatively, download or clone this repository, then require or include its
_autoload.php_ file.

### Tests

[![Build Status](https://travis-ci.org/auraphp/Aura.Autoload.png?branch=develop-2)](https://travis-ci.org/auraphp/Aura.Autoload)

This library has 100% code coverage with [PHPUnit][]. To run the tests at the
command line, go to the _tests_ directory and issue `phpunit`.

[PHPUnit]: http://phpunit.de/manual/

### PSR Compliance

This library attempts to comply with [PSR-1][], [PSR-2][], and [PSR-4][]. If
you notice compliance oversights, please send a patch via pull request.

[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md


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
