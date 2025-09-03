<?php
/**
 * Pop up coupon Help
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jul 10 Modified in v1.5.8-alpha $
 */
if (!isset($_GET['cID'], $_SESSION['cc_id']) || (int)$_GET['cID'] !== (int)$_SESSION['cc_id']) {
    $_SESSION['cart']->reset(true);
    zen_session_destroy();

    die('Illegal Access');
}

$_SESSION['navigation']->remove_current_page();
require DIR_WS_MODULES . zen_get_module_directory('require_languages.php');
