<?php
/**
 * @package admin
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */

// Entries in dashboard_widgets_groups
define('GENERAL_STATISTICS_GROUP', 'General Statistics');
define('ORDER_STATISTICS_GROUP', 'Order Statistics');
define('NEW_CUSTOMERS_GROUP', 'New Customers');
define('COUNTER_HISTORY_GROUP', 'Counter History');
define('NEW_ORDERS_GROUP', 'New Orders');
define('LOGS_GROUP', 'Debug Logs');
define('BANNER_STATISTICS_GROUP', 'Banner Statistics');

// Entries in dashboard_widgets_description
define('GENERAL_STATISTICS', 'General Statistics');
define('ORDER_STATISTICS', 'Order Statistics');
define('ORDER_SUMMARY', 'Order Summary');
define('NEW_CUSTOMERS', 'New Customers');
define('COUNTER_HISTORY', 'Counter History');
define('NEW_ORDERS', 'New Orders');
define('LOGS', 'Debug Logs');
define('BANNER_STATISTICS', 'Banner Statistics');

define('TEXT_TOTAL_LOGFILES_FOUND', '<br>Note: Total of %s log files found on server.');
define('TEXT_DISPLAYING_RECENT_COUNT', ' (Displaying only the most recent %s files.)');
define('TEXT_NO_LOGFILES_FOUND', 'No debug log files found.');
define('TEXT_CLEANUP_LOGFILES', 'Cleanup Log Files in Store Manager');
define('TEXT_ADMIN_LOG_SUFFIX', '(admin)'); 

/* NOTE: defines for additional contributed "plugin" widgets should be placed into
 * a new file in the extra_definitions folder, not in this file, since
 * altering this file makes upgrades more complicated
 * and also makes plugin installation far more complicated than it needs to be.
 */
