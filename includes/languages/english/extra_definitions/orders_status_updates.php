<?php
/**
 * Constants used by the zen_update_orders_history function.
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Steve 2020 May 27 Modified in v1.5.7 $
 */
define('OSH_EMAIL_SEPARATOR', '------------------------------------------------------');
define('OSH_EMAIL_TEXT_SUBJECT', 'Order Update');
define('OSH_EMAIL_TEXT_ORDER_NUMBER', 'Order Number:');
define('OSH_EMAIL_TEXT_INVOICE_URL', 'Order Details:');
define('OSH_EMAIL_TEXT_DATE_ORDERED', 'Date Ordered:');
define('OSH_EMAIL_TEXT_COMMENTS_UPDATE', '<em>The comments for your order are: </em>');
define('OSH_EMAIL_TEXT_STATUS_UPDATED', 'Your order\'s status has been updated:' . "\n");
define('OSH_EMAIL_TEXT_STATUS_NO_CHANGE', 'Your order\'s status has not changed:' . "\n");
define('OSH_EMAIL_TEXT_STATUS_LABEL', '<strong>Current status: </strong> %s' . "\n\n");
define('OSH_EMAIL_TEXT_STATUS_CHANGE', '<strong>Old status:</strong> %1$s, <strong>New status:</strong> %2$s' . "\n\n");
define('OSH_EMAIL_TEXT_STATUS_PLEASE_REPLY', 'Please reply to this email if you have any questions.' . "\n");

define('SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO_SUBJECT', '[ORDERS STATUS]');