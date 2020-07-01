<?php
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

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
$capsule->setEventDispatcher(new Dispatcher(new Container));
$capsule->setAsGlobal();
$capsule->bootEloquent();
