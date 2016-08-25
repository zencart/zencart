<?php
/**
 * @package admin
 * @copyright Copyright 2003-2016 Zen Cart Development Team
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
define('COUNTER_HISTORY_GROUP', 'Counter History Table');
define('NEW_ORDERS_GROUP', 'New Orders');
define('LOGS_GROUP', 'Debug Logs');
define('BANNER_STATISTICS_GROUP', 'Banner Statistics');
define('WHOSONLINE_GROUP', 'Active Visitors');
define('COUNTER_HISTORY_GRAPH_GROUP', 'Counter History Graph');
define('SALES_GRAPH_REPORT_GROUP', 'Sales Activity');

// Entries in dashboard_widgets_description
define('GENERAL_STATISTICS', 'General Statistics');
define('GENERAL_STATISTICS_DESCRIPTION', 'Current value of various counters');
define('ORDER_SUMMARY', 'Order Summary');
define('ORDER_SUMMARY', 'Counts of orders in each state');
define('NEW_CUSTOMERS', 'New Customers');
define('NEW_CUSTOMERS', 'Customers who have created accounts recently');
define('COUNTER_HISTORY', 'Counter History');
define('COUNTER_HISTORY', 'Hit counter over last 14 days');
define('NEW_ORDERS', 'Recent Orders');
define('NEW_ORDERS', 'Recently received orders with names and amounts');
define('LOGS', 'Debug Logs');
define('LOGS', 'Shows debug logs if any exist');
define('BANNER_STATISTICS', 'Banner Statistics');
define('BANNER_STATISTICS_DESCRIPTION', 'Shows statistics for last 12 months');
define('WHOSONLINE_ACTIVITY', 'Active Visitors');
define('WHOSONLINE_ACTIVITY_DESCRIPTION', 'Counts of active visitors with and without carts');
define('COUNTER_HISTORY_GRAPH', 'Counter History Graph');
define('COUNTER_HISTORY_GRAPH_DESCRIPTION', 'Shows counter history graph for last 14 days');
define('SALES_GRAPH_REPORT', 'Sales Activity');
define('SALES_GRAPH_REPORT_DESCRIPTION', 'Graph of recent sales activity');

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

// ===== Sales Graph Widget
define('SALES_GRAPH_TEXT_MONTHLY', 'Monthly Sales (excludes shipping)');
define('SALES_GRAPH_TEXT_CLICK', 'Click here for complete details...');
define('SALES_GRAPH_COLUMN_MONTH', 'Month');
define('SALES_GRAPH_COLUMN_SALES', 'Sales');



/* NOTE: defines for additional contributed "plugin" widgets should be placed into
 * a new file in the extra_definitions folder, not in this file, since
 * altering this file makes upgrades more complicated
 * and also makes plugin installation far more complicated than it needs to be.
 */
