<?php
/**
 * sagepay form
 *
 * @package paymentMethod
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: zcwilt  Sat Nov 14 18:57:26 2015 +0000 New in v1.5.5 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

if (!defined('DB_PREFIX')) {
    define('DB_PREFIX', '');
}
define('TABLE_SAGEPAY_TRANSACTION', DB_PREFIX . 'sagepay_transaction');


