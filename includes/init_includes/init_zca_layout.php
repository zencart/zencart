<?php
/**
 * 
 * init_zca_layout.php
 *
 * @package initSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: zcadditions.com  New in v1.5.5 $
 */
 
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

if ( isset($_GET['layoutType']) ) { 
  $_SESSION['layoutType'] = preg_replace('/[^a-z0-9_-]/i', '', $_GET['layoutType']);
}
