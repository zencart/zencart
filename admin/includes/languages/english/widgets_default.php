<?php
/**
 * @package admin
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */

// Note: The widgets_groups and widgets_description values 
// are built in code; you can't search for them.
// See includes/library/zencart/DashboardWidget/src

// Entries in dashboard_widgets_groups
define('GENERAL_STATISTICS_GROUP', 'General Statistics');
define('ORDER_STATISTICS_GROUP', 'Order Statistics');
define('NEW_CUSTOMERS_GROUP', 'New Customers');
define('COUNTER_HISTORY_GROUP', 'Counter History');
define('NEW_ORDERS_GROUP', 'New Orders');
define('LOGS_GROUP', 'Debug Logs');
define('BANNER_STATISTICS_GROUP', 'Banner Statistics');
define('WHOSONLINE_GROUP', 'Active Visitors');

// Entries in dashboard_widgets_description
define('GENERAL_STATISTICS', 'General Statistics');
define('ORDER_STATISTICS', 'Order Statistics');
define('ORDER_SUMMARY', 'Order Summary');
define('NEW_CUSTOMERS', 'New Customers');
define('COUNTER_HISTORY', 'Counter History');
define('NEW_ORDERS', 'New Orders');
define('LOGS', 'Debug Logs');
define('BANNER_STATISTICS', 'Banner Statistics');
define('WHOSONLINE_ACTIVITY', 'Active Visitors');

define('TEXT_TOTAL_LOGFILES_FOUND', '<br>Note: Total of %s log files found on server.');
define('TEXT_DISPLAYING_RECENT_COUNT', ' (Displaying only the most recent %s files.)');
define('TEXT_NO_LOGFILES_FOUND', 'No debug log files found.');
define('TEXT_CLEANUP_LOGFILES', 'Cleanup Log Files in Store Manager');
define('TEXT_ADMIN_LOG_SUFFIX', '(admin)');


// ====> Who's Online <====
define('WO_FULL_DETAILS', 'See Detailed Activity...');
define('WO_REGISTERED', 'Customer:');
define('WO_GUEST', 'Guest:');
define('WO_SPIDER', 'Spider:');
define('WO_TOTAL', 'Total currently online:');
define('WHOS_ONLINE_ACTIVE_TEXT', 'Active cart');
define('WHOS_ONLINE_INACTIVE_TEXT', 'Inactive cart');
define('WHOS_ONLINE_ACTIVE_NO_CART_TEXT', 'Active no cart');
define('WHOS_ONLINE_INACTIVE_NO_CART_TEXT', 'Inactive no cart');




/* NOTE: defines for additional contributed "plugin" widgets should be placed into
 * a new file in the extra_definitions folder, not in this file, since
 * altering this file makes upgrades more complicated
 * and also makes plugin installation far more complicated than it needs to be.
 */
