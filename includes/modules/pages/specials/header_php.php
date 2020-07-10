<?php
/**
 * Specials
 *
 * @package page
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 Mon Jul 23 14:00:26 2018 -0400 Modified in v1.5.6 $
 */
$zco_notifier->notify('NOTIFY_HEADER_START_SPECIALS');
require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));
$breadcrumb->add(NAVBAR_TITLE);

//lines25-71 moved to main_template_vars
