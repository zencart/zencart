<?php

use Illuminate\Http\Request;
use Illuminate\Contracts\Http\Kernel;


require __DIR__ . '/../laravel/vendor/autoload.php';
$app = require_once __DIR__ . '/../laravel/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
if (!file_exists(__DIR__ . '/../laravel/.env')) {
    copy(__DIR__ . '/../laravel/.env.example', __DIR__ . '/../laravel/.env');
    exec('php ' . __DIR__ . '/../laravel/' . 'artisan key:generate ');
    //die('HERE');
}
try {
    $response = $kernel->handle(
        $request = Request::capture()
    )->send();

    $kernel->terminate($request, $response);

    exit();

} catch (\Exception $e) {
    // do nothing here as we want to drop through to let Zen Cart handle stuff.
}
