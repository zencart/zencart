<?php

if (!defined('IS_ADMIN_FLAG')) {
 die('Illegal Access');
}
$autoLoadConfig[62][] = [
    'autoType'=>'class',

    // the filename, relative to the `classes` folder:
    'loadFile'=>'observers/class.admin_submenus.php',
    'classPath'=>DIR_WS_CLASSES
];
$autoLoadConfig[62][] = [
    'autoType'=>'classInstantiate',

    // the name of the class as declared inside the observer class file
    'className'=>'zcObserverAdminSubmenus',

    // the name of the global object into which the class is instantiated
    'objectName'=>'zcObserverAdminSubmenus'
];
