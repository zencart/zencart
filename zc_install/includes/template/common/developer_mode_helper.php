<?php
/**
 * @package Installer
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: DrByte  Mon Dec 28 14:43:01 2015 -0500 New in v1.6.0 $
 */
   if (!defined('DEVELOPER_MODE') && ($adminConfigSettings['developer_mode'] = 'checked'))
  {
    define('DEVELOPER_MODE', true);
  } else {
    define('DEVELOPER_MODE', false);
  }
