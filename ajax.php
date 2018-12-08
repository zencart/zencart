<?php
/**
 * ajax front controller
 *
 * @package core
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 Fri Oct 26 10:04:06 2018 -0400 Modified in v1.5.6 $
 */
// Abort if the request was not an AJAX call
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    http_response_code(400); // "Bad Request"
    exit();
}

// -----
// Since this request can also be initiated from the admin-side's ajax.php, need
// to ensure that we're bringing in the correct 'base' processing for the
// rest of the initialization.
//
if (empty($zc_ajax_base_dir)) {
    $zc_ajax_base_dir = '';
}
require $zc_ajax_base_dir . 'includes/application_top.php';

// deny ajax requests from spiders
if (isset($spider_flag) && $spider_flag === true) ajaxAbort();

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Allow-Headers: X-Requested-With");


// --- support functions ------------------
if (!function_exists('utf8_encode_recurse')) {
    function utf8_encode_recurse($mixed_value)
    {
        if (strtolower(CHARSET) == 'utf-8') {
            return $mixed_value;
        } elseif (!is_array($mixed_value)) {
            return utf8_encode((string)$mixed_value);
        } else {
            $result = array();
            foreach ($mixed_value as $key => $value) {
                $result[$key] = utf8_encode($value);
            }
            return $result;
        }
    }
}

function ajaxAbort($status = 400, $msg = null)
{
    http_response_code($status); // 400 = "Bad Request"
    if ($msg) echo $msg;
    require('includes/application_bottom.php');
    exit();
}
// --- support functions ------------------



if (!isset($_GET['act']) || !isset($_GET['method'])) {
    ajaxAbort();
}

$language_page_directory = DIR_WS_LANGUAGES . $_SESSION['language'] . '/';

$className = 'zc' . ucfirst($_GET['act']);
$classFile = $className . '.php';
$basePath  = DIR_FS_CATALOG . DIR_WS_CLASSES;

if (!file_exists(realpath($basePath . 'ajax/' . basename($classFile)))) {
    ajaxAbort();
}

require realpath($basePath . 'ajax/' . basename($classFile));
$class = new $className();
if (!method_exists($class, $_GET['method'])) {
    ajaxAbort(400, 'class method error');
}

// Accepted request, so execute and return appropriate response:
$result = call_user_func(array($class, $_GET['method']));
$result = utf8_encode_recurse($result);
echo json_encode($result);
require('includes/application_bottom.php');
