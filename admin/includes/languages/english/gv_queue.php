<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
//  $Id: gv_queue.php 1105 2005-04-04 22:05:35Z birdbrain $
//

define('HEADING_TITLE', TEXT_GV_NAME . ' Release Queue');

define('TABLE_HEADING_CUSTOMERS', 'Customers');
define('TABLE_HEADING_ORDERS_ID', 'Order-No.');
define('TABLE_HEADING_VOUCHER_VALUE', TEXT_GV_NAME . ' Value');
define('TABLE_HEADING_DATE_PURCHASED', 'Date Purchased');
define('TABLE_HEADING_ACTION', 'Action');

define('TEXT_REDEEM_GV_MESSAGE_HEADER', 'You recently purchased a ' . TEXT_GV_NAME . ' from our online store.');
define('TEXT_REDEEM_GV_MESSAGE_RELEASED', 'For security reasons this was not made immediately available to you. ' .
                                          'However, this amount has now been released. You may now visit our store and send the value of the ' . TEXT_GV_NAME . ' via email to someone else, or use it yourself.' . "\n\n"
                                          );

define('TEXT_REDEEM_GV_MESSAGE_AMOUNT', 'The ' . TEXT_GV_NAME . '(s) you purchased are worth %s');
define('TEXT_REDEEM_GV_MESSAGE_THANKS', 'Thank you for shopping with us!');

define('TEXT_REDEEM_GV_MESSAGE_BODY', '');
define('TEXT_REDEEM_GV_MESSAGE_FOOTER', '');
define('TEXT_REDEEM_GV_SUBJECT', TEXT_GV_NAME . ' Purchase');
define('TEXT_REDEEM_GV_SUBJECT_ORDER',' Order #');

define('TEXT_EDIT_ORDER','Edit Order ID# ');
define('TEXT_GV_NONE','No ' . TEXT_GV_NAME . ' to release');
?>