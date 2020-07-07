<?php

if (!app()->runningInConsole() && (!defined('ALLOW_LARAVEL_WEB_ROUTES') || ALLOW_LARAVEL_WEB_ROUTES == false)) {
    return;
}

Route::get('/welcome', function () {
    return view('admin');
});

// Should always be last. Do not remove this
Route::fallback(function () {
    return ;
});

