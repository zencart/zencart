<?php

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Router;

/** @var $router Router */

//$router->group(['middleware' => 'guest'], function (Router $router) {
//    $router->get('/develop/login', function () {
//        $_SESSION['user'] = true;
//        return 'Success auth! <a href="/develop">Return home</a>';
//    });
//});

$router->group(['middleware' => 'auth'], function (Router $router) {
//    $router->get('/develop', function () {
//        return 'hello world!';
//    });
//
//    $router->get('/develop/logout', function () {
//        unset($_SESSION['user']);
//
//        return 'Success logout! <a href="/">Return home</a>';
//    });

    $router->group(['namespace' => 'App\Http\Controllers'], function (Router $router) {
        $router->get('/develop', ['name' => 'dashboard.index', 'uses' => 'DashboardController@index']);
    });
});

