<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Routing\Router;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('login');
        }
    }
}
