<?php
/**
 * autoloader activation point for canonical url handling script
 *
 * @package initSystem
 * @copyright Copyright 2003-2010 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: config.canonical.php 15763 2010-03-31 20:05:22Z drbyte $
 */
if (!defined('IS_ADMIN_FLAG')) {
 die('Illegal Access');
}
/**
 * point 161 was selected specifically based on dependancies
 */
  $autoLoadConfig[161][] = array('autoType'=>'init_script',
                                 'loadFile'=> 'init_canonical.php');
