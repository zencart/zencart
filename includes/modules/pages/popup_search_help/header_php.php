<?php
/**
 * pop up search help
 * 
 * @package page
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: header_php.php 2982 2006-02-07 07:56:41Z birdbrain $
 */

$_SESSION['navigation']->remove_current_page();

require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));
?>