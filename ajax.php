<?php
/**
 * ajax front controller
 *
 * @package core
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson   Modified in v1.6.0 $
 */

require ('includes/application_top.php');

if (!function_exists('htmlentities_recurse')) {
    function htmlentities_recurse($mixed_value, $flags = ENT_QUOTES, $encoding = 'utf-8', $double_encode = true) {
        $result = array();
        if (!is_array ($mixed_value)) {
            return htmlentities ((string)$mixed_value, $flags, $encoding, $double_encode);
        }
        if (is_array($mixed_value)) {
            $result = array ();
            foreach ($mixed_value as $key => $value) {
                $result[$key] = htmlentities_recurse ($value, $flags, $encoding, $double_encode);
            }
        }
        return $result;
    }
}

$language_page_directory = DIR_WS_LANGUAGES.$_SESSION['language'].'/';
if (isset ($_GET['act'])&&isset ($_GET['method'])) {
  $className = 'zc'.ucfirst ($_GET['act']);
  $classFile = $className.'.php';
  $basePath = DIR_FS_CATALOG.DIR_WS_CLASSES;
  if (file_exists (realpath($basePath. 'ajax/' . basename($classFile)))) {
    require realpath($basePath .'ajax/' . basename($classFile));
    $class = new $className ();
    if (method_exists ($class, $_GET['method'])) {
      $result = call_user_func (array(
          $class,
          $_GET['method']
      ));
      $result = htmlentities_recurse($result, ENT_QUOTES, 'utf-8', FALSE);
      echo json_encode ($result);exit();
    } else {
      echo 'method error';
    }
  }
}

