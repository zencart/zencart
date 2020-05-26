<?php

use Zencart\PluginSupport\ScriptedInstaller as ScriptedInstallBase;

class ScriptedInstaller extends ScriptedInstallBase
{
    protected function executeInstall()
    {
        zen_deregister_admin_pages(['toolsDisplayLogs']);
        zen_register_admin_page(
            'toolsDisplayLogs', 'BOX_TOOLS_DISPLAY_LOGS', 'FILENAME_DISPLAY_LOGS', '', 'tools', 'Y', 20);
        $sql =
            "INSERT INTO " . TABLE_CONFIGURATION . " 
            ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function ) 
         VALUES 
            
            ('Display Logs: Display Maximum', 'DISPLAY_LOGS_MAX_DISPLAY', '20', 'Identify the maximum number of logs to display.  (Default: <b>20</b>)', 10, 100, now(), NULL, NULL),
            
            ('Display Logs: Maximum File Size', 'DISPLAY_LOGS_MAX_FILE_SIZE', '80000', 'Identify the maximum size of any file to display.  (Default: <b>80000</b>)', 10, 101, now(), NULL, NULL),
            
            ('Display Logs: Included File Prefixes', 'DISPLAY_LOGS_INCLUDED_FILES', 'myDEBUG-|AIM_Debug_|SIM_Debug_|FirstData_Debug_|Paypal|paypal|ipn_|zcInstall|notifier|usps|SHIP_usps', 'Identify the log-file <em>prefixes</em> to include in the display, separated by the pipe character (|).  Any intervening spaces are removed by the processing code.', 10, 102, now(), NULL, NULL),
            
            ('Display Logs: Excluded File Prefixes', 'DISPLAY_LOGS_EXCLUDED_FILES', '', 'Identify the log-file prefixes to <em>exclude</em> from the display, separated by the pipe character (|). Any intervening spaces are removed by the processing code.', 10, 103, now(), NULL, NULL)";

        $this->executeInstallerSql($sql);
    }

    protected function executeUninstall()
    {
        zen_deregister_admin_pages(['toolsDisplayLogs']);

        $deleteMap = "'DISPLAY_LOGS_MAX_DISPLAY', 'DISPLAY_LOGS_MAX_FILE_SIZE', 'DISPLAY_LOGS_INCLUDED_FILES', 'DISPLAY_LOGS_EXCLUDED_FILES'";

        $sql = "DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN (" . $deleteMap . ")";

        $this->executeInstallerSql($sql);
    }
}
