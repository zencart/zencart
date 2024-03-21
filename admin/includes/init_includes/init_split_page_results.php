<?php
/**
 * Initially load the splitPageResults class, if a class of that
 * name is not already loaded, thus enabling that base class to be
 * overridden.
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Mar 07 Modified in v2.0.0-rc1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

if (!class_exists('splitPageResults')) {
    require DIR_WS_CLASSES . 'split_page_results.php';
}
