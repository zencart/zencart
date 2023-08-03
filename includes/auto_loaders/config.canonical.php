<?php
/**
 * autoloader activation point for canonical url handling script
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jul 10 Modified in v1.5.8-alpha $
 */
if (!defined('IS_ADMIN_FLAG')) {
 die('Illegal Access');
}
/**
 * point 161 was selected specifically based on dependancies
 */
  $autoLoadConfig[161][] = array('autoType'=>'init_script',
                                 'loadFile'=> 'init_canonical.php');
