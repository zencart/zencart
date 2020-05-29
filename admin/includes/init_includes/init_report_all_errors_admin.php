<?php
// -----
// Part of the Report All Errors plugin, provided by lat9@vinosdefrutastropicales.com
//
// @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
//

// -----
// If the plugin's not yet installed, add its configuration settings; otherwise, if the admin-level
// setting is enabled, enable it!
//
if (!defined('REPORT_ALL_ERRORS_ADMIN')) {
    $db->Execute(
        "INSERT INTO " . TABLE_CONFIGURATION . " 
            (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function)
         VALUES 
            ('Report All Errors (Admin)?', 'REPORT_ALL_ERRORS_ADMIN', 'No', 'Do you want to create debug-log files for <b>all</b> PHP errors, even warnings, that occur during your Zen Cart admin\'s processing?  If you want to log all PHP errors <b>except</b> duplicate-language definitions, choose <em>IgnoreDups</em>.', 10, 40, now(), NULL, 'zen_cfg_select_option(array(\'Yes\', \'No\', \'IgnoreDups\'),')"
    );
    
    $db->Execute(
        "INSERT INTO " . TABLE_CONFIGURATION . " 
            (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function)
         VALUES 
            ('Report All Errors (Store)?', 'REPORT_ALL_ERRORS_STORE', 'No', 'Do you want to create debug-log files for <b>all</b> PHP errors, even warnings, that occur during your Zen Cart store\'s processing?  If you want to log all PHP errors <b>except</b> duplicate-language definitions, choose <em>IgnoreDups</em>.<br /><br /><strong>Note:</strong> Choosing \'Yes\' is not suggested for a <em>live</em> store, since it will reduce performance significantly!', 10, 41, now(), NULL, 'zen_cfg_select_option(array(\'Yes\', \'No\', \'IgnoreDups\'),')"
    );
} elseif (REPORT_ALL_ERRORS_ADMIN != 'No') {
    @ini_set('error_reporting', E_ALL);
    set_error_handler('zen_debug_error_handler', E_ALL);
}

if (!defined('REPORT_ALL_ERRORS_NOTICE_BACKTRACE')) {
    $db->Execute(
        "INSERT INTO " . TABLE_CONFIGURATION . " 
            (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function)
         VALUES 
            ('Report All Errors: Backtrace on Notices?', 'REPORT_ALL_ERRORS_NOTICE_BACKTRACE', 'No', 'Include backtrace information on Notices?  These are usually isolated to the identified file and the backtrace information just fills the logs. Default (<b>No</b>).', 10, 42, now(), NULL, 'zen_cfg_select_option(array(\'Yes\', \'No\'),')"
    );
}
