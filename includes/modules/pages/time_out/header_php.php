<?php
/**
 * Time out page
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Aug 08 Modified in v1.5.8-alpha $
 */

// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_LOGIN_TIMEOUT');

require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));


$breadcrumb->add(NAVBAR_TITLE);
// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_LOGIN_TIMEOUT');
