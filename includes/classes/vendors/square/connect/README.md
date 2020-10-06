![Square logo]

# Square Connect PHP SDK - RETIRED

---

[![Build Status](https://travis-ci.org/square/connect-php-sdk.svg?branch=master)](https://travis-ci.org/square/connect-php-sdk)
[![PHP version](https://badge.fury.io/ph/square%2Fconnect.svg)](https://badge.fury.io/ph/square%2Fconnect)
[![Apache-2 license](https://img.shields.io/badge/license-Apache2-brightgreen.svg)](https://www.apache.org/licenses/LICENSE-2.0)
==================

## NOTICE: Square Connect PHP SDK retired

The Square Connect PHP SDK is retired (EOL) as of 2020-06-10 and will no longer
receive bug fixes or product updates. To continue receiving API and SDK
improvements, please follow the instructions below to migrate to the new
[Square PHP SDK].

NOTE THAT THE NEW PHP SDK REQUIRES PHP 7.1+, and will NOT work on PHP 5


The old Connect SDK documentation is available under the
[`/docs` folder](./docs/README.md).

<br/>

---

* [Migrate to the Square PHP SDK](#migrate-to-the-square-php-sdk)
  * [Update your code](#update-your-code)
* [Example code migration](#example-code-migration)
* [Ask the Community](#ask-the-community)

---

<br/>

## Migrate to the Square PHP SDK

Follow the instructions below to migrate your apps from the deprecated
`square/connect` sdk to the new library.

### Option 1: With composer.json
You need to update your app to use the Square PHP SDK instead of the Connect PHP SDK
The Square PHP SDK uses the `square/square` identifier.

1. On the command line, run:
```
$ php composer.phar require square/square
```
*-or-*

2. Update your composer.json:
```
"require": {
    ...
    "square/square": "*",
    ...
}
```

### Option 2: From GitHub
Clone the Square PHP SDK repository or download the zip into your project folder and
then update the following line in your code from

```php
require('connect-php-sdk/autoload.php');
```
to:

```php
require('square-php-sdk/autoload.php');
```

### Update your code

1. Change all instances of `use SquareConnect\...` to `use Square\...`.
1. Replace `SquareConnect` models with the new `Square` equivalents
1. Update client instantiation to follow the method outlined below.
1. Update code for accessing response data to follow the method outlined below.
1. Check `$apiResponse->isSuccess()` or `$apiResponse->isError()` to determine if the call was a success.

To simplify your code, we also recommend that you use method chaining to access
APIs instead of explicitly instantiating multiple clients.

#### Client instantiation

Connect SDK
```php
require 'vendor/autoload.php';

use SquareConnect\Configuration;
use SquareConnect\ApiClient;

$access_token = 'YOUR_ACCESS_TOKEN';
# setup authorization
$api_config = new Configuration();
$api_config->setHost("https://connect.squareup.com");
$api_config->setAccessToken($access_token);
$api_client = new ApiClient($api_config);

```
Square SDK

```php
require 'vendor/autoload.php';

use Square\Client;

$access_token = 'YOUR_ACCESS_TOKEN';

// Initialize the Square client.
$api_client = new SquareClient([
  'accessToken' => $access_token,
  'environment' => 'sandbox'
]); // In production, the environment arg is 'production'
```

## Example code migration

As a specific example, consider the following code for creating a new payment
from the following nonce:

```php
# Fail if the card form didn't send a value for `nonce` to the server
$nonce = $_POST['nonce'];
if (is_null($nonce)) {
  echo "Invalid card data";
  http_response_code(422);
  return;
}
```

With the deprecated `square/connect` library, this is how you instantiate a client
for the Payments API, format the request, and call the endpoint:

```php
use SquareConnect\Api\PaymentsApi;
use SquareConnect\ApiException;

$payments_api = new PaymentsApi($api_client);
$request_body = array (
  "source_id" => $nonce,
  "amount_money" => array (
    "amount" => 100,
    "currency" => "USD"
  ),
  "idempotency_key" => uniqid()
);
try {
  $result = $payments_api->createPayment($request_body);
  echo "<pre>";
  print_r($result);
  echo "</pre>";
} catch (ApiException $e) {
  echo "Caught exception!<br/>";
  print_r("<strong>Response body:</strong><br/>");
  echo "<pre>"; var_dump($e->getResponseBody()); echo "</pre>";
  echo "<br/><strong>Response headers:</strong><br/>";
  echo "<pre>"; var_dump($e->getResponseHeaders()); echo "</pre>";
}
```

Now consider equivalent code using the new `square/square` library:

```php
$payments_api = $api_client->getPaymentsApi();

$money = new Money();
$money->setAmount(100);
$money->setCurrency('USD');
$create_payment_request = new CreatePaymentRequest($nonce, uniqid(), $money);
try {
  $response = $payments_api->createPayment($create_payment_request);
  if ($response->isError()) {
    echo 'Api response has Errors';
    $errors = $response->getErrors();
    exit();
  }
  echo '<pre>';
  print_r($response);
  echo '</pre>';
} catch (ApiException $e) {
  echo 'Caught exception!<br/>';
  exit();
}
```

That's it!



<br/>

---

<br/>

## Ask the community

Please join us in our [Square developer community] if you have any questions!


[//]: # "Link anchor definitions"
[Square Logo]: https://docs.connect.squareup.com/images/github/github-square-logo.svg
[Square PHP SDK]: https://github.com/square/square-php-sdk
[Square developer community]: https://squ.re/slack