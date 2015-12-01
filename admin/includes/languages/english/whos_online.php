<?php
/**
 * @package admin
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: whos_online.php 18698 2011-05-04 14:50:06Z wilt $
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
define('TABLE_HEADING_SHOPPING_CART', 'Users Shopping Cart');
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

define('WHOIS_TIMER_REMOVE', 1200); // seconds when removed from whos_online table - 1200 default = 20 minutes
define('WHOIS_TIMER_INACTIVE', 180); // seconds when considered inactive - 180 default = 3 minutes
define('WHOIS_TIMER_DEAD', 540); // seconds when considered dead - 540 default = 9 minutes
define('WHOIS_SHOW_HOST', '1'); // show Last Clicked time and host name - 1 default
define('WHOIS_REPEAT_LEGEND_BOTTOM', '12'); // show legend on bottom when more than how many entries - 12 default

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
