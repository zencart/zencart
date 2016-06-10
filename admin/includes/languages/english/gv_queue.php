<?php
/**
 * @package admin
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:$
 */

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

define('TEXT_LINK_RELEASE','Release GV');
