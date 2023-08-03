<?php
/**
 * create_account header_php.php
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jul 10 Modified in v1.5.8-alpha $
 */

// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_CREATE_ACCOUNT');


require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));
include(DIR_WS_MODULES . zen_get_module_directory(FILENAME_CREATE_ACCOUNT));

$breadcrumb->add(NAVBAR_TITLE);

// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_CREATE_ACCOUNT');
?>