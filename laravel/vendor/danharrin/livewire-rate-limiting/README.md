<p align="center">
    <img src="https://user-images.githubusercontent.com/41773797/104827274-ddb3a580-5853-11eb-9d53-eaf3e7776734.png" alt="Package banner" style="width: 100%; max-width: 800px;" />
</p>

<p align="center">
    <a href="https://github.com/danharrin/livewire-rate-limiting/actions"><img alt="Tests passing" src="https://img.shields.io/badge/Tests-passing-green?style=for-the-badge&logo=github"></a>
    <a href="https://laravel.com"><img alt="Laravel v8.x, v9.x, v10.x" src="https://img.shields.io/badge/Laravel-v8.x, v9.x, v10.x-FF2D20?style=for-the-badge&logo=laravel"></a>
    <a href="https://laravel.com"><img alt="PHP 8" src="https://img.shields.io/badge/PHP-8-777BB4?style=for-the-badge&logo=php"></a>
</p>

This package allows you to apply rate limiters to Laravel Livewire actions. This is useful for throttling login attempts and other brute force attacks, reducing spam, and more.

## Installation

You can use Composer to install this package into your application:

```
composer require danharrin/livewire-rate-limiting
```

This package requires at least Laravel v8.x, when rate limiting improvements were introduced.

This package is tested to support the `file` and `redis` cache drivers, but not `array`.

## Usage

Apply the `DanHarrin\LivewireRateLimiting\WithRateLimiting` trait to your Livewire component:

```php
<?php

namespace App\Http\Livewire\Login;

use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Livewire\Component;

class Login extends Component
{
    use WithRateLimiting;
    
    // ...
}
```

In this example, we will set up rate limiting on the `submit` action.

The user will only be able to call this action 10 times every minute.

If this limit is exceeded, a `TooManyRequestsException` will be thrown. The user is presented with a validation error and instructed how long they have until the limit is lifted:

```php
<?php

namespace App\Http\Livewire\Login;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Login extends Component
{
    use WithRateLimiting;
    
    public function submit()
    {
        try {
            $this->rateLimit(10);
        } catch (TooManyRequestsException $exception) {
            throw ValidationException::withMessages([
                'email' => "Slow down! Please wait another {$exception->secondsUntilAvailable} seconds to log in.",
            ])
        }
        
        // ...
    }
}
```

## API Reference

### Component Methods

```php
use DanHarrin\LivewireRateLimiting\WithRateLimiting;

/**
 * Rate limit a Livewire method, `$maxAttempts` times every `$decaySeconds` seconds.
 * 
 * @throws DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException
 */
$this->rateLimit(
    $maxAttempts, // The number of times that the rate limit can be hit in the given decay period.
    $decaySeconds = 60, // The length of the decay period in seconds. By default, this is a minute.
    $method, // The name of the method that is being rate limited. By default, this is set to the method that `$this->rateLimit()` is called from.
);

/**
 * Hit a method's rate limiter without consequence.
 */
$this->hitRateLimiter(
    $method, // The name of the method that is being rate limited. By default, this is set to the method that `$this->hitRateLimiter()` is called from.
    $decaySeconds = 60, // The length of the decay period in seconds. By default, this is a minute.
);

/**
 * Clear a method's rate limiter.
 */
$this->clearRateLimiter(
    $method, // The name of the method that is being rate limited. By default, this is set to the method that `$this->clearRateLimiter()` is called from.
);
```

### Exceptions

```php
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;

try {
    $this->rateLimit(10);
} catch (TooManyRequestsException $exception) {
    $exception->component; // Class of the component that the rate limit was hit within.
    $exception->ip; // IP of the user that has hit the rate limit.
    $exception->method; // Name of the method that has hit the rate limit.
    $exception->minutesUntilAvailable; // Number of minutes until the rate limit is lifted, rounded up.
    $exception->secondsUntilAvailable; // Number of seconds until the rate limit is lifted.
}
```

## Need Help?

🐞 If you spot a bug with this package, please [submit a detailed issue](https://github.com/danharrin/livewire-rate-limiting/issues/new), and wait for assistance.

🤔 If you have a question or feature request, please [start a new discussion](https://github.com/danharrin/livewire-rate-limiting/discussions/new).

🔐 If you discover a vulnerability within the package, please review our [security policy](https://github.com/danharrin/livewire-rate-limiting/blob/main/SECURITY.md).
