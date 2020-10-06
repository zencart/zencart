<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 06 Modified in v1.5.7 $
 */

define('HEADING_TITLE', 'Who\'s Online');
define('TABLE_HEADING_ONLINE', 'Online');
define('TABLE_HEADING_CUSTOMER_ID', 'ID');
define('TABLE_HEADING_FULL_NAME', 'Full Name');
define('TABLE_HEADING_IP_ADDRESS', 'IP Address');
define('TABLE_HEADING_SESSION_ID', 'Session');
define('TABLE_HEADING_ENTRY_TIME', 'Entry Time');
define('TABLE_HEADING_LAST_CLICK', 'Last Click Time');
define('TIME_PASSED_LAST_CLICKED', '<strong>Time Since Clicked:</strong> ');
define('TABLE_HEADING_LAST_PAGE_URL', 'Last URL Viewed');
define('TABLE_HEADING_ACTION', 'Action');
define('TABLE_HEADING_SHOPPING_CART', 'Visitor\'s Shopping Cart');
define('TEXT_SHOPPING_CART_SUBTOTAL', 'Subtotal');
define('TEXT_NUMBER_OF_CUSTOMERS', 'Currently there are %s customers online');

define('WHOS_ONLINE_REFRESH_LIST_TEXT', 'REFRESH LIST');
define('WHOS_ONLINE_LEGEND_TEXT', 'Legend:');
define('WHOS_ONLINE_ACTIVE_TEXT', 'Active cart');
define('WHOS_ONLINE_INACTIVE_TEXT', 'Inactive cart');
define('WHOS_ONLINE_ACTIVE_NO_CART_TEXT', 'Active no cart');
define('WHOS_ONLINE_INACTIVE_NO_CART_TEXT', 'Inactive no cart');
define('WHOS_ONLINE_INACTIVE_LAST_CLICK_TEXT', 'Inactive is Last Click >=');
define('WHOS_ONLINE_INACTIVE_ARRIVAL_TEXT', 'Inactive since arrival >');
define('WHOS_ONLINE_REMOVED_TEXT', 'will be removed');

define('TEXT_SESSION_ID', '<strong>Session ID:</strong> ');
define('TEXT_HOST', '<strong>Host:</strong> ');
define('TEXT_USER_AGENT', '<strong>User Agent:</strong> ');
define('TEXT_EMPTY_CART', '<strong>Empty Cart</strong>');
define('TEXT_WHOS_ONLINE_FILTER_SPIDERS', 'Exclude Spiders?');
define('TEXT_WHOS_ONLINE_FILTER_ADMINS', 'Exclude Admin IP Addresses?');

// show Last Clicked time and host name - 1 both(default), 0=time-only
if (!defined('WHOIS_SHOW_HOST')) define('WHOIS_SHOW_HOST', '1');

define('TEXT_DUPLICATE_IPS', 'Duplicate IP Addresses: ');
define('TEXT_TOTAL_UNIQUE_USERS', 'Total Unique Users: ');

define('TEXT_WHOS_ONLINE_TIMER_UPDATING', 'Updating ');
define('TEXT_WHOS_ONLINE_TIMER_EVERY', 'every %s seconds.&nbsp;&nbsp;');
define('TEXT_WHOS_ONLINE_TIMER_DISABLED', 'Manually');
define('TEXT_WHOS_ONLINE_TIMER_FREQ0', 'OFF');
define('TEXT_WHOS_ONLINE_TIMER_FREQ1', '5s');
define('TEXT_WHOS_ONLINE_TIMER_FREQ2', '15s');
define('TEXT_WHOS_ONLINE_TIMER_FREQ3', '30s');
define('TEXT_WHOS_ONLINE_TIMER_FREQ4', '1m');
define('TEXT_WHOS_ONLINE_TIMER_FREQ5', '5m');
define('TEXT_WHOS_ONLINE_TIMER_FREQ6', '10m');
define('TEXT_WHOS_ONLINE_TIMER_FREQ7', '14m');
