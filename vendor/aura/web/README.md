# Aura.Web

Provides web _Request_ and _Response_ objects for use by web controllers and
actions. These are representations of the PHP web environment, not HTTP request
and response objects proper.

## Foreword

### Installation

This library requires PHP 5.3 or later; we recommend using the latest available version of PHP as a matter of principle. It has no userland dependencies.

It is installable and autoloadable via Composer as [aura/web](https://packagist.org/packages/aura/web).

Alternatively, [download a release](https://github.com/auraphp/Aura.Web/releases) or clone this repository, then require or include its _autoload.php_ file.

### Quality

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/auraphp/Aura.Web/badges/quality-score.png?b=develop-2)](https://scrutinizer-ci.com/g/auraphp/Aura.Web/)
[![Code Coverage](https://scrutinizer-ci.com/g/auraphp/Aura.Web/badges/coverage.png?b=develop-2)](https://scrutinizer-ci.com/g/auraphp/Aura.Web/)
[![Build Status](https://travis-ci.org/auraphp/Aura.Web.png?branch=develop-2)](https://travis-ci.org/auraphp/Aura.Web)

To run the unit tests at the command line, issue `composer install` and then `phpunit` at the package root. This requires [Composer](http://getcomposer.org/) to be available as `composer`, and [PHPUnit](http://phpunit.de/manual/) to be available as `phpunit`.

This library attempts to comply with [PSR-1][], [PSR-2][], and [PSR-4][]. If
you notice compliance oversights, please send a patch via pull request.

[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md

### Community

To ask questions, provide feedback, or otherwise communicate with the Aura community, please join our [Google Group](http://groups.google.com/group/auraphp), follow [@auraphp on Twitter](http://twitter.com/auraphp), or chat with us on #auraphp on Freenode.


## Getting Started

### Instantiation

First, instantiate a _WebFactory_ object, then use it to create _Request_ and
_Response_ objects.

```php
<?php
use Aura\Web\WebFactory;

$web_factory = new WebFactory($GLOBALS);
$request = $web_factory->newRequest();
$response = $web_factory->newResponse();
?>
```

Note that if `jit-globals` is turned on, merely passing `$GLOBALS` will not
work properly. In this case, use `compact()` to pass the needed values. For
example:

```php
<?php
use Aura\Web\WebFactory;

$web_factory = new WebFactory(array(
    '_ENV' => $_ENV,
    '_GET' => $_GET,
    '_POST' => $_POST,
    '_COOKIE' => $_COOKIE,
    '_SERVER' => $_SERVER
));
$request = $web_factory->newRequest();
$response = $web_factory->newResponse();
?>
```

### Request and Response Objects

Because each object contains so much functionality, we have split up the
documentation into a [Request](README-REQUEST.md) page and a
[Response](README-RESPONSE.md) page.

By way of overview, the _Request_ object has these sub-objects ...

- [$request->cookies](README-REQUEST.md#superglobals) for $_COOKIES
- [$request->env](README-REQUEST.md#superglobals) for $_ENV
- [$request->files](README-REQUEST.md#superglobals) for $_FILES
- [$request->post](README-REQUEST.md#superglobals) for $_POST
- [$request->query](README-REQUEST.md#superglobals) for $_GET
- [$request->server](README-REQUEST.md#superglobals) for $_SERVER
- [$request->client](README-REQUEST.md#client) for the client making the
  request
- [$request->content](README-REQUEST.md#content) for the raw body of the
  request
- [$request->headers](README-REQUEST.md#headers) for the request headers
- [$request->method](README-REQUEST.md#method) for the request method
- [$request->params](README-REQUEST.md#params) for path-info parameters
- [$request->url](README-REQUEST.md#url) for the request URL

... and the _Response_ object has these sub-objects:

- [$response->status](README-RESPONSE.md#status) for the status code, status
  phrase, and HTTP version
- [$response->headers](README-RESPONSE.md#headers) for non-cookie headers
- [$response->cookies](README-RESPONSE.md#cookies) for cookie headers
- [$response->content](README-RESPONSE.md#content) for describing the response
  content, and for convenience methods related to content type, charset,
  disposition, and filename
- [$response->cache](README-RESPONSE.md#cache) for convenience methods related
  to cache headers
- [$response->redirect](README-RESPONSE.md#redirect) for convenience methods
  related to Location and Status

Once you have built a _Response_ you can send it with any HTTP mechanism you
prefer, [including plain PHP](README-RESPONSE.md#sending-the-response).

Be sure to read the [Request](README-REQUEST.md) and
[Response](README-RESPONSE.md) pages for more detailed information.
