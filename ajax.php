<?php
/**
 * ajax front controller
 *
 * @package core
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: zcwilt  Thu Dec 31 19:12:00 2015 +0000 Modified in v1.5.5 $
 */

require ('includes/application_top.php');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Allow-Headers: X-Requested-With");

if (!function_exists('utf8_encode_recurse')) {
    function utf8_encode_recurse($mixed_value) {
        if (strtolower(CHARSET) == 'utf-8') {
            return $mixed_value;
        } elseif (!is_array ($mixed_value)) {
            return utf8_encode ((string)$mixed_value);
        } else {
            $result = array ();
            foreach ($mixed_value as $key => $value) {
                $result[$key] = utf8_encode ($value);
            }
            return $result;
        }
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
            $result = utf8_encode_recurse ($result);
            echo json_encode ($result);exit();
        } else {
            echo 'method error';
        }
    }
}
