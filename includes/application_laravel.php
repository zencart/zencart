<?php

use Illuminate\Http\Request;
use Illuminate\Contracts\Http\Kernel;


require __DIR__ . '/../laravel/vendor/autoload.php';
$app = require_once __DIR__ . '/../laravel/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
try {
    $response = $kernel->handle(
        $request = Request::capture()
    )->send();

    $kernel->terminate($request, $response);

    exit();

} catch (\Exception $e) {
}
