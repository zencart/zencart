<?php
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Pluralizer;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Routing\Pipeline;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use Illuminate\Filesystem\Filesystem as IFilesystem;
use Illuminate\Routing\Router;


$pathsToViews = [DIR_FS_CATALOG . 'app/resources/views'];
$pathToCompiledViews = DIR_FS_CATALOG . 'app/resources/compiled';

$container = new Container;
$request = Request::capture();
$container->instance('Illuminate\Http\Request', $request);
$events = new Dispatcher($container);
$pipeline = new Pipeline($container);
$Ifilesystem = new IFilesystem;

// Boot Laravel Eloquent
$capsule = new Capsule;
$capsule->addConnection(
    [
        'driver'    => DB_TYPE,
        'host'      => DB_SERVER,
        'database'  => DB_DATABASE,
        'username'  => DB_SERVER_USERNAME,
        'password'  => DB_SERVER_PASSWORD,
        'charset'   => DB_CHARSET,
        'prefix'    => DB_PREFIX,
    ]);
$capsule->setEventDispatcher($events);
$capsule->setAsGlobal();
$capsule->bootEloquent();

// Create View Factory capable of rendering PHP and Blade templates
$viewResolver = new EngineResolver;
$bladeCompiler = new BladeCompiler($Ifilesystem, $pathToCompiledViews);


$viewResolver->register('blade', function () use ($bladeCompiler) {
    return new CompilerEngine($bladeCompiler);
});

$viewResolver->register('php', function () {
    return new PhpEngine;
});

$viewFinder = new FileViewFinder($Ifilesystem, $pathsToViews);
$factory = new Factory($viewResolver, $viewFinder, $events);

// Save in container for DI in controllers
$container->instance('Illuminate\View\Factory', $factory);

// Set up Illuminate Router and try to match routes from route file
// If a route is matched then it is handed of to Illuminate
// otherwise code will continue and try legacy Zen Cart to serve request
$router = new Router($events, $container);
$request = Request::capture();
$globalMiddleware = [
];
require DIR_FS_CATALOG . 'app/routes/web.php';
try {
    $response = ($pipeline)
        ->send($request)
        ->through($globalMiddleware)
        ->then(
            function ($request) use ($router) {
                return $router->dispatch($request);
            });

    $response->send();
    exit(0);

} catch (Exception $e) {
    // nothing to do here. Just means Illuminate did not match any routes
}
//@todo
// Probably need to set up more stuff to allow Illuminate to handle request
// and put some more stuff in Container for DI


//
///*
// * Support examples
// */
//$messageBag = new MessageBag;
//$person = [
//    'name' => [
//        'first' => 'Jill',
//        'last' => 'Schmoe'
//    ]
//];
//echo 'name.first is ' . Arr::get($person, 'name.first') . "\n";
//
//$messageBag->add('notice', 'Array dot notation displayed.');
//$people = new Collection(['Declan', 'Abner', 'Mitzi']);
//
//$people->map(function ($person) {
//    return "<i>$person</i>";
//})->each(function ($person) {
//    echo "Collection person: $person\n";
//});
//
//$messageBag->add('notice', 'Collection displayed.');
//$personRecord = [
//    'first_name' => 'Mohammad',
//    'last_name' => 'Gufran'
//];
//$record = new Fluent($personRecord);
//
//$record->address('hometown, street, house');
//
//echo $record->first_name . "\n";
//echo $record->address . "\n";
//
//$messageBag->add('notice', 'Fluent displayed.');
//
//
//$item = 'goose';
//echo "One $item, two " . Pluralizer::plural($item) . "\n";
//$item = 'moose';
//echo "One $item, two " . Pluralizer::plural($item) . "\n";
//
//if (Str::contains('This is my fourteenth visit', 'first')) {
//    echo 'Howdy!';
//} else {
//    echo 'Nice to see you again.';
//}
//
//echo "MessageBag ({$messageBag->count()})\n";
//foreach ($messageBag->all() as $message) {
//    echo " - $message\n";
//}
