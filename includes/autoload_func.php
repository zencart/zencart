<?php
/**
 * File contains the autoloader loop
 *
 * The autoloader loop takes the array from the auto_loaders directory
 * and uses it to construct the InitSystem.
 * see  {@link  https://docs.zen-cart.com/dev/code/init_system/} for more details.
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt 2020 Jun 09 Modified in v1.5.7 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
$debugAutoload = false;
if (defined('DEBUG_AUTOLOAD') && DEBUG_AUTOLOAD == true) $debugAutoload = true;
if ($debugAutoload) {
    echo '<pre>$initSystemList=<br>';
    print_r($initSystemList);
    echo '</pre>';
}
foreach ($initSystemList as $entry) {
    switch ($entry['type']) {
        case 'include':
            if ($entry['forceLoad']) {
                if ($debugAutoload) echo 'case "include": ' . $entry['filePath'] . "<br>\n";
                include $entry['filePath'];
            } else {
                if ($debugAutoload) echo 'case "include_once": ' . $entry['filePath'] . "<br>\n";
                include_once $entry['filePath'];
            }
            break;
        case 'require':
            if ($entry['forceLoad']) {
                if ($debugAutoload) echo 'case "require": ' . $entry['filePath'] . "<br>\n";
                require $entry['filePath'];
            } else {
                if ($debugAutoload) echo 'case "require_once": ' . $entry['filePath'] . "<br>\n";
                require_once $entry['filePath'];
            }
            break;
        case 'class':
            if ($debugAutoload) echo 'case "class": ' . $entry['class'] . "<br>\n";
            $objectName = $entry['object'];
            $className = $entry['class'];
            $$objectName = new $className();
            break;
        case 'sessionClass':
            if ($debugAutoload)  'case "sessionClass": ' . $entry['class'] . "<br>\n";
            $objectName = $entry['object'];
            $className = $entry['class'];
            if (!$entry['checkInstantiated'] || !isset($_SESSION[$objectName])) {
                $_SESSION[$objectName] = new $className();
            }
            break;
        case 'objectMethod':
            if ($debugAutoload) echo 'case "objectMethod": ' . '$entry[\'method\']=' . $entry['method'] . ', $entry[\'object\']=' . $entry['object'] . "<br>\n";
            $objectName = $entry['object'];
            $methodName = $entry['method'];
              if (isset($_SESSION[$objectName]) && is_object($_SESSION[$objectName])) {
                  $_SESSION[$objectName]->$methodName();
              } else {
                  ${$objectName}->$methodName();
              }
            break;
    }
}
