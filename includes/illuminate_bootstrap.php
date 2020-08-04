<?php

// Create main laravel application
$laravelApp = require_once DIR_FS_CATALOG . 'laravel/bootstrap/app.php';
$laravelKernel = $laravelApp->make(Illuminate\Contracts\Http\Kernel::class);

//see if we can match any laravel routes
$laravelResponse = $laravelKernel->handle(
    $unsanitizedRequest = Illuminate\Http\Request::capture()
);
$laravelRoute = Route::current();

// if the route is null or a fallback then laravel didn't
// match anything so return and let Zen Cart handle it.
// use a 204 response to indicate that we want to use the response
// in Zen Cart
if (!isset($laravelRoute) || $laravelRoute->isFallback || $laravelResponse->getStatusCode() == 204) {
    return;
}

$laravelResponse->send();
$laravelKernel->terminate($unsanitizedRequest, $laravelResponse);

// Note:$kernel->terminate doesn't actually exit the application.
exit();

