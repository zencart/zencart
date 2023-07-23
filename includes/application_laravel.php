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


$pathsToViews = [DIR_FS_CATALOG . 'laravel/resources/views'];
$pathToCompiledViews = DIR_FS_CATALOG . 'laravel/resources/compiled';


$capsule = new Capsule;
$capsule->addConnection([
    'driver' => DB_TYPE,
    'host' => DB_SERVER,
    'database' => DB_DATABASE,
    'username' => DB_SERVER_USERNAME,
    'password' => DB_SERVER_PASSWORD,
    'charset' => DB_CHARSET,
    // do not pass prefix; this is included in the table definition
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$container = new Container;
$lRequest = Request::capture();
$container->instance('Illuminate\Http\Request', $lRequest);

$events = new Dispatcher($container);
$pipeline = new Pipeline($container);
$Ifilesystem = new IFilesystem;

$viewResolver = new EngineResolver;
$bladeCompiler = new BladeCompiler($Ifilesystem, $pathToCompiledViews);


$viewResolver->register('blade', function () use ($bladeCompiler) {
    return new CompilerEngine($bladeCompiler);
});

$viewResolver->register('php', function ($Ifilesystem) {
    return new PhpEngine($Ifilesystem);
});

$viewFinder = new FileViewFinder($Ifilesystem, $pathsToViews);
$factory = new Factory($viewResolver, $viewFinder, $events);

// Save in container for DI in controllers
$container->instance('Illuminate\View\Factory', $factory);


$router = new Router($events, $container);
$globalMiddleware = [
    \App\Http\Middleware\StartSession::class,
];

// Array middlewares
$routeMiddleware = [
    'auth' => \App\Http\Middleware\Authenticate::class,
    'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
];

// Load middlewares to router
foreach ($routeMiddleware as $key => $middleware) {
    $router->aliasMiddleware($key, $middleware);
}


require_once DIR_FS_CATALOG . 'laravel/routes/web.php';
require_once DIR_FS_CATALOG . 'laravel/routes/auth.php';

try {
    $response = ($pipeline)
        ->send($lRequest)
        ->through($globalMiddleware)
        ->then(
            function ($request) use ($router) {
                return $router->dispatch($request);
            });

    $response->send();
    exit(0);

} catch (Exception $e) {
    dd($e);
}

function view($view = null, $data = [], $mergeData = [])
{
    $factory = app(Factory::class);

    if (func_num_args() === 0) {
        return $factory;
    }

    return $factory->make($view, $data, $mergeData);
}

function redirect($to = null, $status = 302, $headers = [], $secure = null)
{
    if (is_null($to)) {
        return app('redirect');
    }

    return app('redirect')->to($to, $status, $headers, $secure);
}
function app($abstract = null, array $parameters = [])
{
    if (is_null($abstract)) {
        return Container::getInstance();
    }

    return Container::getInstance()->make($abstract, $parameters);
}

