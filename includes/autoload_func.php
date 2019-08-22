<?php
/**
 * File contains the autoloader loop
 *
 * The autoloader loop takes the array from the auto_loaders directory
 * and uses this this to constuct the InitSysytem.
 * see {@link http://www.zen-cart.com/wiki/index.php/Developers_API_Tutorials#InitSystem} for more details.
 *
 * @package initSystem
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: mc12345678 2019 Jan 20 Modified in v1.5.6b $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

foreach ($initSystemList as $entry) {
    switch ($entry['type']) {
        case 'include':
            //echo 'include ' . $entry['filePath'] . "\n";
            include $entry['filePath'];
            break;
        case 'require':
            //echo 'require ' . $entry['filePath'] . "\n";
            require $entry['filePath'];
            break;
        case 'class':
            //echo 'class ' . $entry['class'] . "\n";
            $objectName = $entry['object'];
            $className = $entry['class'];
            $$objectName = new $className();
            break;
        case 'sessionClass':
            //echo 'sessionClass ' . $entry['class'] . "\n";
            $objectName = $entry['object'];
            $className = $entry['class'];
            $_SESSION[$objectName] = new $className();
            break;
        case 'sessionObjectMethod':
            //echo 'sessionObjectMethod ' . $entry['class'] . "\n";
            $objectName = $entry['object'];
            $methodName = $entry['method'];
            $_SESSION[$objectName]->$methodName();
            break;
        case 'objectMethod':
            //echo 'objectMethod ' . $entry['class'] . "\n";
            $objectName = $entry['object'];
            $methodName = $entry['method'];
            ${$objectName}->$methodName();
            break;

    }
}