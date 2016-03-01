<?php
/**
 * 
 * init_zca_layout.php
 *
 * @package initSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: rbarbour zcadditions.com Sun Dec 13 16:32:43 2015 -0500 New in v1.5.5 $
 */
 
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

if ( isset($_GET['layoutType']) ) { 
  $_SESSION['layoutType'] = preg_replace('/[^a-z0-9_-]/i', '', $_GET['layoutType']);
}
