<?php
/**
 * config.zca_layout.php
 *
 * @package initSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @author ZCAdditions.com, ZCA Responsive Template Default
 */
	
if (!defined('IS_ADMIN_FLAG')) {
 die('Illegal Access');
}

$autoLoadConfig[115][] = array('autoType'=>'init_script',
                               'loadFile'=> 'init_zca_layout.php');
