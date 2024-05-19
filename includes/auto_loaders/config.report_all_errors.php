<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Apr 10 Modified in v2.0.1 $
 */
/**
 * Autoloader to instantiate initialization, just after the database configuration constants have been initialized
 */
$autoLoadConfig[41][] = array('autoType'=>'init_script',
                              'loadFile'=>'init_report_all_errors.php');
