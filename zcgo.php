#!/usr/bin/env php
<?php
/**
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  $
 */

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/includes/configure.php';

use Zencart\Go\MakeDefinesCommand;
use Zencart\Go\MakeIdeHelperCommand;
use Zencart\Go\DumpAdminLogsCommand;
use Symfony\Component\Console\Application;
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
        'collation' => 'utf8mb4_unicode_ci',
        'prefix'    => DB_PREFIX,
    ]);

$capsule->setEventDispatcher(new Dispatcher(new Container));
$capsule->setAsGlobal();
$capsule->bootEloquent();


$application = new Application();
$command = new MakeDefinesCommand();
$application->add($command);
$command = new MakeIdeHelperCommand();
$application->add($command);
$command = new DumpAdminlogsCommand();
$application->add($command);

$application->run();

