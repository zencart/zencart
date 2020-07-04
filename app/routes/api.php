<?php

if (!defined('ALLOW_LARAVEL_API_ROUTES') || ALLOW_LARAVEL_API_ROUTES == false) {
    return;
}

Route::resource('customer', 'Api\CustomersController');
Route::delete('customer', 'Api\CustomersController@destroyByQuery');
Route::put('customer', 'Api\CustomersController@updateByQuery');

Route::resource('adminactivity', 'Api\AdminActivityController');
Route::delete('adminactivity', 'Api\AdminActivityController@destroyByQuery');
Route::put('adminactivity', 'Api\AdminActivityController@updateByQuery');
