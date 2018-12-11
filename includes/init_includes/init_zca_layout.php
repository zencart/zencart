<?php
/**
 * 
 * init_zca_layout.php
 *
 * @package initSystem
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: rbarbour zcadditions.com Tue May 8 00:42:18 2018 -0400 Modified in v1.5.6 $
 */
 
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

if ( isset($_GET['layoutType']) ) { 
  $_SESSION['layoutType'] = preg_replace('/[^a-z0-9_-]/i', '', $_GET['layoutType']);
}

if (!isset($_SESSION['layoutType'])) {
  $_SESSION['layoutType'] = 'legacy';
}
