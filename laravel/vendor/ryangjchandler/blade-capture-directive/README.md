# Create inline partials in your Blade templates with ease.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ryangjchandler/blade-capture-directive.svg?style=flat-square)](https://packagist.org/packages/ryangjchandler/blade-capture-directive)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/ryangjchandler/blade-capture-directive/run-tests?label=tests)](https://github.com/ryangjchandler/blade-capture-directive/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/ryangjchandler/blade-capture-directive/Check%20&%20fix%20styling?label=code%20style)](https://github.com/ryangjchandler/blade-capture-directive/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/ryangjchandler/blade-capture-directive.svg?style=flat-square)](https://packagist.org/packages/ryangjchandler/blade-capture-directive)

This package introduces a new `@capture` directive that allows you to capture small parts of your Blade templates and re-use them later on without needing to extract them into partials.

## Installation

You can install the package via Composer:

```bash
composer require ryangjchandler/blade-capture-directive
```

## Usage

This package adds a new pair of directives: `@capture` and `@endcapture`.

The `@capture` directive will capture all of your Blade until it reaches an `@endcapture` directive. It takes the code and stores it inside of a variable for usage later on.

```blade
@capture($hello)
    Hello, world!
@endcapture
```

The directive requires at least 1 argument. This argument should be a PHP variable that you would like to assign your partial to. The variable itself will become a `Closure` that can be invoked inside of Blade echo tags (`{{  }}`) anywhere after it's definition.

```blade
@capture($hello)
    Hello, world!
@endcapture

{{ $hello() }}
```

The above code will invoke your captured Blade code and output `Hello, world!` when compiled by Laravel and rendered in the browser.

The `@capture` directive also supports arguments. This means you can capture generalised chunks of Blade and change the output dynamically. This is achieved by specifying a comma-separated list of PHP variables like so:

```blade
@capture($hello, $name)
    Hello, {{ $name }}!
@endcapture
```

The above code will require that a name is passed to `$hello()`, like below:

```blade
@capture($hello, $name)
    Hello, {{ $name }}!
@endcapture

{{ $hello('Ryan') }}
```

The Blade will compile this and your view will output `Hello, Ryan!`. Cool, right?

The list of arguments can be treated like any set of arguments defined on a function. This means you can assign default values and specify multiple arguments:

```blade
@capture($hello, $name, $greeting = 'Hello, ')
    {{ $greeting }} {{ $name }}!
@endcapture

{{ $hello('Ryan') }}
{{ $hello('Taylor', 'Yo, ') }}
```

The above code will now output `Hello, Ryan!` as well as `Yo, Taylor!`. This is really cool, I know!

### Inheriting scope

All captured blocks will inherit the parent scope, just like a regular partial would in Blade. This means you can use any data passed to the view without having to pass it through to the block manually.

```blade
@php($name = 'Ryan')

@capture($hello)
    Hello, {{ $name }}!
@endcapture

{{ $hello() }}
```

> If your captured block has a parameter with the same name as a predefined variable from the inherited scope, the block's parameter will always take precedence.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Ryan Chandler](https://github.com/ryangjchandler)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
