<?php
/**
 * @package Installer
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  $
 */

$body_code = DIR_WS_INSTALL_TEMPLATE . 'templates/' . $current_page . '_default.php';
$body_id = str_replace('_', '', $_GET['main_page']);
