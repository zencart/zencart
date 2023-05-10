<?php
/**
 * @copyright Copyright 2003-2023 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2022 Nov 03 Modified in v1.5.8a $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

// calculate category path
if (isset($_POST['cPath'])) {
    $cPath = $_POST['cPath'];
} elseif (isset($_GET['cPath'])) {
    $cPath = $_GET['cPath'];
} else {
    $cPath = '';
}

if (zen_not_null($cPath)) {
    $cPath_array = zen_parse_category_path($cPath);
    $cPath = implode('_', $cPath_array);
    $current_category_id = $cPath_array[(count($cPath_array) - 1)];
} else {
    $cPath_array = [];
    $current_category_id = TOPMOST_CATEGORY_PARENT_ID;
}
