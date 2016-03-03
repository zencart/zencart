<?php
/**
 * @package Installer
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: zcwilt  Wed Sep 23 20:04:38 2015 +0100 New in v1.5.5 $
 */

$body_code = DIR_WS_INSTALL_TEMPLATE . 'templates/' . $current_page . '_default.php';
$body_id = str_replace('_', '', $_GET['main_page']);
