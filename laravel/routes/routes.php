<?php

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Router;

/** @var $router Router */

$router->get('/admin/', function () {
    return 'Hello World';
});
