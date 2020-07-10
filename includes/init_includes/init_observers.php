<?php
/**
 * auto-load and instantiate all /includes/classes/observers/auto.xxxxxxxxx.php classes
  *
 * This looks for any files in the DIR_WS_CLASSES/observers folder matching the naming convention of "auto.XXXXXX.php"
 * It then automatically "include"s those files.
 * And then it checks to see whether the XXXXXXXXX part of the filename matches a class name using "zcObserver" + the CamelCased XXXXXXXXX string.
 * ie: zcObserverTemplateFrameworkAbc would match auto.template_framework_abc.php
 * If the properly named class exists, then it instantiates that class using an object of the same name.  If the class inside the file is NOT properly named, it will NOT be instantiated, despite being loaded.
 *
 * The assumption is that the class is an observer class which properly extends the base class.
 * All normal observer class behavior applies.
 *
 * This fires at AutoLoader point 175, so all previously-processed system dependencies are in place.
 * If you need an observer class to fire at a much earlier point so it fires before other system processes, you'll need to add your own auto_loaders/config.yyyyy.php file with relevant rules to load those observers.
 *
 * @package initSystem
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson Wed Oct 10 07:03:50 2018 -0400 Modified in v1.5.6 $
 */
  if (!defined('IS_ADMIN_FLAG')) {
   die('Illegal Access');
  }

  // Find observer class files which follow the naming convention "auto.xxxxxxx.php"
  $directory_array = array();
  if ($dir = @dir(DIR_WS_CLASSES . 'observers/')) {
    while ($file = $dir->read()) {
      if (!is_dir($dir->path . '/' . $file)) {
        if (preg_match('~(^auto\..*\.php$)~', $file, $matches) > 0) {
          $directory_array[] = rtrim($dir->path, '/') . '/' . $file;
        }
      }
    }
    $dir->close();
  }

  // instantiate observer classes which follow the naming convention "zcObserver" + CamelCasedVersionOfXxxxxxFromFileName
  foreach ($directory_array as $file) {
    if (file_exists($file)) {
      include($file);
      $objectName = preg_replace('~(^.*/auto\.|\.php$)~', '', $file);
      $objectName = 'zcObserver' . base::camelize($objectName, TRUE);
      if (class_exists($objectName)) {
        $$objectName = new $objectName();
      } else {
        error_log('ERROR: Observer class ' . $objectName . ' could not be instantiated despite file ' . $file . ' being found. Please follow the correct naming convention for the class name inside the file.');
      }
    }
  }


