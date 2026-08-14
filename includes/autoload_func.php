<?php
/**
 * File contains the autoloader loop
 *
 * The autoloader loop takes the array from the auto_loaders directory
 * and uses it to construct the InitSystem.
 * see  {@link  https://docs.zen-cart.com/dev/code/init_system/} for more details.
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2022 Jul 05 Modified in v1.5.8-alpha $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
$debugAutoload = false;
if (defined('DEBUG_AUTOLOAD') && DEBUG_AUTOLOAD === true) $debugAutoload = true;
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
            /**
             * A classInstantiate entry is queued whether or not the matching class
             * entry found its file, and the two carry no reference to each other,
             * so the only place the mismatch can be caught is here.
             * Instantiating a class that was never loaded is a fatal, which we
             * want to avoid during bootstrapping.
             */
            if (!class_exists($className) && ($entry['loaderType'] ?? 'core') === 'plugin') {
                trigger_error('Autoloader: class "' . $className . '" was not loaded; skipping instantiation as "' . $objectName . '".', E_USER_WARNING);
                break;
            }
            // A missing core class still fails fast on the line below.
            $$objectName = new $className();
            break;
        case 'sessionClass':
            if ($debugAutoload) echo 'case "sessionClass": ' . $entry['class'] . "<br>\n";
            $objectName = $entry['object'];
            $className = $entry['class'];
            /**
             * Discard any incomplete decoded session and let the normal path below build a usable one.
             */
            if (isset($_SESSION[$objectName]) && $_SESSION[$objectName] instanceof __PHP_Incomplete_Class) {
                unset($_SESSION[$objectName]);
            }
            if (!class_exists($className) && ($entry['loaderType'] ?? 'core') === 'plugin') {
                trigger_error('Autoloader: class "' . $className . '" was not loaded; skipping session instantiation as "' . $objectName . '".', E_USER_WARNING);
                // Discard whatever was unserialized, whether succesful or not.
                unset($_SESSION[$objectName]);
                break;
            }
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
              /**
               * A plugin object skipped above leaves nothing to call, so skip.
               * A core object still fails fast on the final branch.
               */
              } elseif ((!isset(${$objectName}) || !is_object(${$objectName})) && ($entry['loaderType'] ?? 'core') === 'plugin') {
                  trigger_error('Autoloader: object "' . $objectName . '" is not available; skipping method "' . $methodName . '".', E_USER_WARNING);
              } else {
                  ${$objectName}->$methodName();
              }
            break;
    }
}
