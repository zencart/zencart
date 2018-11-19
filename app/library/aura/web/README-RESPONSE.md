# Aura.Web Response

The _Response_ object describes the web response that should be sent to the
client. It is **not** an HTTP response object proper. Instead, it is a series
of hints to be used when building the HTTP response with the delivery
mechanism of your choice.

Setting values on the _Response_ object **does not** cause values to be sent
to the client. The _Response_ can be inspected during testing to see if the
correct values have been set without generating output.

To create a _Response_ object, instantiate a _WebFactory_ and get new
_Response_ object from it:

```php
<?php
use Aura\Web\WebFactory;

$web_factory = new WebFactory($GLOBALS);
$response = $web_factory->newResponse();
?>
```

The _Response_ object is composed of several property objects representing
different parts of the response:

- `$response->status` for the status code, status phrase, and HTTP version

- `$response->headers` for non-cookie headers

- `$response->cookies` for cookie headers

- `$response->content` for describing the response content, and for
  convenience methods related to content type, charset, disposition, and
  filename

- `$response->cache` for convenience methods related to cache headers
  
- `$response->redirect` for convenience methods related to Location and Status
  

## Status

Use the `$response->status` object as follows:

```php
<?php
// set the status code, phrase, and version at once
$response->status->set('404', 'Not Found', '1.1');

// set them individually
$response->status->setCode('404');
$response->status->setPhrase('Not Found');
$response->status->setVersion('1.1');

// get the full status line
$status = $response->status->get(); // "HTTP/1.1 404 Not Found"

// get the status values individually
$code    = $response->status->getCode();
$phrase  = $response->status->getPhrase();
$version = $response->status->getVersion();
?>
```

## Headers

The `$response->headers` object has these methods:

- `set()` to set a single header, resetting previous values on that header

- `get()` to get a single header, or to get all headers

```php
<?php
// X-Header-Value: foo
$response->headers->set('X-Header-Value', 'foo');

// get the X-Header-Value
$value = $response->headers->get('X-Header-Value');

// get all headers
$all_headers = $response->headers->get();
?>
```

Setting a header value to null, false, or an empty string will remove that
header; setting it to zero will *not* remove it.

## Cookies

The `$response->cookies` object has these methods:

- `setExpire()` sets the default expiration for cookies

- `setPath()` sets the default path for cookies

- `setDomain()` sets the default domain for cookies

- `setSecure()` sets the default secure value for cookies

- `setHttpOnly()` sets the default for whether or not cookies will be sent by
  HTTP only.

- `set()` sets a cookie name and value along with its meta-data. This method
  mimics the [setcookie()](http://php.net/setcookie) PHP function. If meta-
  data such as path, domain, secure, and httponly are missing, the defaults
  will be filled in for you.

- `get()` returns a cookie by name, or all the cookies at once.

```php
<?php
// set a default expire time to 10 minutes from now on a domain and path
$response->cookies->setDomain('example.com');
$response->cookies->setPath('/');
$response->cookies->setExpire('+600');

// set two cookie values
$response->cookies->set('foo', 'bar');
$response->cookies->set('baz', 'dib');

// get a cookie descriptor array from the response
$foo_cookie = $response->cookies->get('foo');

// get all the cookie descriptor arrays from the response, keyed by name
$cookies = $response->cookies->get();
?>
```

The cookie descriptor array looks like this:

```php
<?php
$cookies['foo'] = array(
    'value' => 'bar',
    'expire' => '+600', // will become a UNIX timestamp with strtotime()
    'path' => '/',
    'domain' => 'example.com',
    'secure' => false,
    'httponly' => true,
);
?>
```


## Content

The `$response->content` object has these convenience methods related to the
response content and content headers:

- `set()` sets the body content of the response (this can be anything at all,
  including an array, a callable, an object, or a string -- it is up to the
  sending mechanism to translate it properly)

- `get()` get the body content of the response which has been set via `set()`

- `setType()` sets the `Content-Type` header

- `getType()` returns the `Content-Type` (not including the charset)

- `setCharset()` sets the character set for the `Content-Type`

- `getCharset()` returns the `charset` portion of the `Content-Type` header

- `setDisposition()` sets the `Content-Disposition` type and filename

- `setEncoding()` sets the `Content-Encoding` header

```php
<?php
// set the response content, type, and charset
$response->content->set(array('foo' => 'bar', 'baz' => 'dib'));
$response->content->setType('application/json');

// elsewhere, before sending the response, modify the content based on type
switch ($response->content->getType()) {
    case 'application/json':
        $json = json_encode($response->content->get());
        $response->content->set($json);
        break;
    // ...
}
?>
```


## Cache

The `$response->cache` object has several convenience methods related to HTTP
cache headers.

- `reset()` removes all cache-related headers

- `disable()` turns off caching by removing all cache-related headers, then
  sets the following:
  
        Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate
        Expires: Mon, 01 Jan 0001 00:00:00 GMT
        Pragma: no-cache

- `setAge()` sets the `Age` header value in seconds

- `setControl()` sets an array of `Cache-Control` header directives all at
  once; alternatively, use the individual directive methods:

    - `setPublic()` and `setPrivate()` set the `public` and `private` cache
      control directives (each turns off the other)

    - `setMaxAge()` and `setSharedMaxAge()` set the `max-age` and `s-maxage`
      cache control directives (set to null or false to remove them)

    - `setNoCache()` and `setNoStore()` set the `no-cache` and `no-store`
      cache control directives (set to null or false to remove them)

    - `setMustRevalidate()` and `setProxyRevalidate()` to set the
      `must-revalidate` and `proxy-revalidate` directives (set to null or
      false to remove them)

- `setEtag()` and `setWeakEtag()` set the `ETag` header value

- `setExpires()` sets the `Expires` header value; will convert recognizable
  date formats and `DateTime` objects to a correctly formatted HTTP date

- `setLastModified()` sets the `Last-Modified` header value; will convert
  recognizable date formats and `DateTime` objects to a correctly formatted
  HTTP date

- `setVary()` sets the `Vary` header; pass an array for comma-separated values

For more information about caching headers, please consult the
[HTTP 1.1 headers spec][] along with these descriptions from [Palizine][].

  [HTTP 1.1 headers spec]: http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
  [Palizine]: http://palizine.plynt.com/issues/2008Jul/cache-control-attributes/


## Redirect

The `$response->redirect` object has several convenience methods related to
status and `Location` headers for redirection.

- `to($location, $code = 302, phrase = null)` sets the status and headers
  for redirection to an arbitrary location with an arbitrary status code and
  phrase

- `afterPost($location)` redirects to the `$location` with a `303 See
  Other` status; this automatically disables HTTP caching

- `created($location)` redirects to `$location` with `201 Created`

- `movedPermanently($location)` redirects to `$location` with `301 Moved Permanently`

- `found($location)` redirects to `$location` with `302 Found`

- `seeOther($location)` redirects to `$location` with `303 See Other`; this
  automatically disables HTTP caching

- `temporaryRedirect($location)` redirects to `$location` with `307 Temporary Redirect`

- `permanentRedirect($location)` redirects to `$location` with `308 Permanent Redirect`


## Sending The Response

Because the _Response_ object is not an HTTP reponse object proper, you will
need to use some other mechanism to convert it to an HTTP response. The
easiest way to do this is with plain PHP:

```php
<?php
// send status line
header($response->status->get(), true, $response->status->getCode());

// send non-cookie headers
foreach ($response->headers->get() as $label => $value) {
    header("{$label}: {$value}");
}

// send cookies
foreach ($response->cookies->get() as $name => $cookie) {
    setcookie(
        $name,
        $cookie['value'],
        $cookie['expire'],
        $cookie['path'],
        $cookie['domain'],
        $cookie['secure'],
        $cookie['httponly']
    );
}

// send content
echo $response->content->get();
?>
```
