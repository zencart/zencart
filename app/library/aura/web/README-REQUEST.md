# Aura.Web Request

The _Request_ object describes the current web execution context for PHP. Note
that it is **not** an HTTP request object proper, since it includes things
like `$_ENV` and various non-HTTP `$_SERVER` keys.

To create a _Request_ object, instantiate a _WebFactory_ and get new _Request_
object from it:

```php
<?php
use Aura\Web\WebFactory;

$web_factory = new WebFactory($GLOBALS);
$request = $web_factory->newRequest();
?>
```

The _Request_ object contains several property objects. Some represent a copy
of the PHP superglobals ...

- `$request->cookies` for `$_COOKIES`
- `$request->env` for `$_ENV`
- `$request->files` for `$_FILES`
- `$request->post` for `$_POST`
- `$request->query` for `$_GET`
- `$request->server` for `$_SERVER`

... and others represent more specific kinds of information about the request:

- `$request->client` for the client making the request
- `$request->content` for the raw body of the request
- `$request->headers` for the request headers
- `$request->method` for the request method
- `$request->params` for path-info parameters
- `$request->url` for the request URL

The _Request_ object has only one method, `isXhr()`, to indicate if the
request is an _XmlHttpRequest_ or not.

## Superglobals

Each of the superglobal representation objects has a single method, `get()`,
that returns the value of a key in the superglobal, or an alternative value
if the key is not present.  The values here are read-only.

```php
<?php
// returns the value of $_POST['field_name'], or 'not set' if 'field_name' is
// not present in $_POST
$field_name = $request->post->get('field_name', 'not set');

// if no key is given, returns an array of all values in the superglobal
$all_server_values = $request->server->get();

// the $_FILES array has been rearranged to look like $_POST
$file = $request->files->get('file_field', array());
?>
```

## Client

The `$request->client` object has these methods:

- `getForwardedFor()` returns the values of the `X-Forwarded-For` headers as
  an array.

- `getReferer()` returns the value of the `Referer` header.

- `getIp()` returns the value of `$_SEVER['REMOTE_ADDR']`, or the appropriate
  value of `X-Forwarded-For`.

- `getUserAgent()` return the value of the `User-Agent` header.

- `isCrawler()` returns true if the `User-Agent` header matches one of a list
  of bot/crawler/robot user agents (otherwise false).

- `isMobile()` returns true if the `User-Agent` header matches one of a list
  of mobile user agents (otherwise false).

To add to the list of recognized user agents, set up the _WebFactory_ with
them first, then create the _Request_ object afterwards.

```php
<?php
$web_factory->setMobileAgents(array(
    'NewMobileAgent',
    'AnotherNewMobile',
));

$web_factory->setCrawlerAgents(array(
    'NewCrawlerAgent',
    'AnotherNewCrawler',
));

$request = $web_factory->newRequest();
?>
```

## Content

The `$request->content` object has these methods:

- `getType()` returns the content-type of the request body

- `getRaw()` return the raw request body

- `get()` returns the request body after decoding it based on the content type

The _Content_ object has two decoders built in.
If the request specified a content type of `application/json`,
the `get()` method will automatically decode the body with `json_decode()`.
Likewise, if the content type is `application/x-www-form-urlencoded`, the
`get()` method will automatically decode the body with `parse_str()`.

If you want to add or change content decoders, set up the _WebFactory_ with
them first, then create the _Request_ object afterwards.

```php
<?php
// content-type => callable
$web_factory->setDecoders(array(
    'application/x-special-content-type' => function ($body) {
        // decoding logic
    },
));

$request = $web_factory->newRequest();
?>
```

## Headers

The `$request->headers` object has a single method, `get()`, that returns the
value of a particular header, or an alternative value if the key is not
present. The values here are read-only.

```php
<?php
// returns the value of 'X-Header' if present, or 'not set' if not
$header_value = $request->headers->get('X-Header', 'not set');
?>
```

## Method

The `$request->method` object has these methods:

- `get()`: returns the request method value
- `isDelete()`: Did the request use a DELETE method?
- `isGet()`: Did the request use a GET method?
- `isHead()`: Did the request use a HEAD method?
- `isOptions()`: Did the request use an OPTIONS method?
- `isPatch()`: Did the request use a PATCH method?
- `isPut()`: Did the request use a PUT method?
- `isPost()`: Did the request use a POST method?

```php
<?php
if ($request->method->isPost()) {
    // perform POST actions
}
?>
```

You can also call `is*()` on the _Method_ object; the part after `is` is
treated as custom HTTP method name, and checks if the request was made using
that HTTP method.

```php
<?php
if ($request->method->isCustom()) {
    // perform CUSTOM actions
}
?>
```

Sometimes forms use a special field to indicate a custom HTTP method on a
POST. By default, the _Method_ object honors the `_method` form field.

```php
<?php
// a POST with the field '_method' will use the _method value instead of POST
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['_method'] = 'PUT';
$request = $web_factory->newRequest();
echo $request->method->get(); // PUT
?>
```

To set the form field used to indicate a custom HTTP method on a POST, set up
the _WebFactory_ with it first, then create the _Request_ object.

```php
<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['_http_method_override'] = 'DELETE';
$web_factory->setMethodField('_http_method_override');
$request = $web_factory->newRequest();
echo $request->method->get(); // DELETE
?>
```

## Params

Unlike most _Request_ property objects, the _Params_ object is read-write (not
read-only). The _Params_ object allows you to set application-specific
parameter values. These are typically discovered by parsing a URL path through
a router of some sort (e.g. [Aura.Router][]).

  [Aura.Router]: https://github.com/auraphp/Aura.Router

The `$request->params` object has two methods:

- `set()` to set the array of parameters
- `get()` to get back a specific parameter, or the array of all parameters

For example:

```php
<?php
// parameter values discovered by a routing mechanism
$values = array(
    'controller' => 'blog',
    'action' => 'read',
    'id' => '88',
);

// set the parameters on the request
$request->params->set($values);

// get the 'id' param, or false if it is not present
$id = $request->params->get('id', false);

// get all the params as an array
$all_params = $request->params->get();
?>
```

## Url

The `$request->url` object has two methods:

- `get()` returns the full URL string; or, if a component constant is passed,
  returns only that part of the URL

- `isSecure()` indicates if the request is secure, whether via SSL, TLS, or
  forwarded from a secure protocol

```php
<?php
// get the full URL string
$string = $request->url->get();

// get a particular part of the URL; for the component constants, see
// http://php.net/parse-url
$scheme   = $request->url->get(PHP_URL_SCHEME);
$host     = $request->url->get(PHP_URL_HOST);
$port     = $request->url->get(PHP_URL_PORT);
$user     = $request->url->get(PHP_URL_USER);
$pass     = $request->url->get(PHP_URL_PASS);
$path     = $request->url->get(PHP_URL_PATH);
$query    = $request->url->get(PHP_URL_QUERY);
$fragment = $request->url->get(PHP_URL_FRAGMENT);
?>
```

