<?php
/**
 * @package admin
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: zcwilt Thu Apr 07 13:08:52 2015 -0400 New in v1.5.5 $
 */
if (!defined('IS_ADMIN_FLAG')) { die('Illegal Access'); }

$autoLoadConfig[0][] = array(
	'autoType'=>'class',
	'loadFile'=>'AdminRequestSanitizer.php',
	'classPath'=> DIR_FS_ADMIN . DIR_WS_CLASSES
);
