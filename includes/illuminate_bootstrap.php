<?php

// Create main laravel application
$app = require_once DIR_FS_CATALOG . 'app/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
//dump($app);

//see if we can match any laravel routes
$response = $kernel->handle(
    $unsanitizedRequest = Illuminate\Http\Request::capture()
);
$route = Route::current();

// if the route is not a fallback then laravel matched something
// so we serve the laravel response and terminate
if (isset($route) && !$route->isFallback) {
    $response->send();
    $kernel->terminate($unsanitizedRequest, $response);
}

// route was a fallback so carry on and use Zen Cart


