<?php
/**
 * Initially load the splitPageResults class, if a class of that
 * name is not already loaded, thus enabling that base class to be
 * overridden.
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott Wilson 2024 Apr 07 New in v2.0.1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

if (!class_exists('splitPageResults')) {
    require DIR_WS_CLASSES . 'split_page_results.php';
}
