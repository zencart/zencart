<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Events\Dispatcher;
use Illuminate\Routing\Pipeline;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use Illuminate\Filesystem\Filesystem as IFilesystem;
use Illuminate\Contracts\Http\Kernel;


require __DIR__ . '/../laravel/vendor/autoload.php';
$app = require_once __DIR__ . '/../laravel/bootstrap/app.php';
$kernel = $app->make(Kernel::class);

try {
    $response = $kernel->handle(
        $request = Request::capture()
    )->send();

    $kernel->terminate($request, $response);

} catch (\Exception $e) {
}
