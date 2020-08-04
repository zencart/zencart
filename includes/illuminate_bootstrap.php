<?php

// Create main laravel application
$laravelApp = require_once DIR_FS_CATALOG . 'laravel/bootstrap/app.php';
//dump($laravelApp);
$laravelKernel = $laravelApp->make(Illuminate\Contracts\Http\Kernel::class);
//dump($laravelApp);

//see if we can match any laravel routes
$laravelResponse = $laravelKernel->handle(
    $unsanitizedRequest = Illuminate\Http\Request::capture()
);
$laravelRoute = Route::current();

// if the route is null or a fallback then laravel didn't
// match anything so return and let Zen Cart handle it.
if (!isset($laravelRoute) || $laravelRoute->isFallback) {
    set_exception_handler(null);
    set_error_handler(null);
    if (DEBUG_AUTOLOAD || (defined('STRICT_ERROR_REPORTING') && STRICT_ERROR_REPORTING == true)) {
        @ini_set('display_errors', TRUE);
        error_reporting(defined('STRICT_ERROR_REPORTING_LEVEL') ? STRICT_ERROR_REPORTING_LEVEL : E_ALL);
    } else {
        error_reporting(0);
    }
    return;
}
// use a 204 response to indicate that we want to use the response
// in Zen Cart
if ($laravelResponse->getStatusCode() == 204) {
    set_exception_handler(null);
    set_error_handler(null);
    if (DEBUG_AUTOLOAD || (defined('STRICT_ERROR_REPORTING') && STRICT_ERROR_REPORTING == true)) {
        @ini_set('display_errors', TRUE);
        error_reporting(defined('STRICT_ERROR_REPORTING_LEVEL') ? STRICT_ERROR_REPORTING_LEVEL : E_ALL);
    } else {
        error_reporting(0);
    }
    return;
}
$laravelResponse->send();
$laravelKernel->terminate($unsanitizedRequest, $laravelResponse);

// Note:$kernel->terminate doesn't actually exit the application.
exit();

