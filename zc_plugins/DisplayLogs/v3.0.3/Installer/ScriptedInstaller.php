<?php

use Zencart\PluginSupport\ScriptedInstaller as ScriptedInstallBase;

class ScriptedInstaller extends ScriptedInstallBase
{
    protected function executeInstall()
    {
        zen_deregister_admin_pages(['toolsDisplayLogs']);
        zen_register_admin_page('toolsDisplayLogs', 'BOX_TOOLS_DISPLAY_LOGS', 'FILENAME_DISPLAY_LOGS', '', 'tools', 'Y', 20);

        $this->addConfigurationKey('DISPLAY_LOGS_MAX_DISPLAY', [
            'configuration_title' => 'Display Logs: Display Maximum',
            'configuration_value' => '20',
            'configuration_description' => 'Identify the maximum number of logs to display.  (Default: <b>20</b>)',
            'configuration_group_id' => 10,
            'sort_order' => 100,
        ]);


        $this->addConfigurationKey('DISPLAY_LOGS_MAX_FILE_SIZE', [
            'configuration_title' => 'Display Logs: Maximum File Size',
            'configuration_value' => '80000',
            'configuration_description' => 'Identify the maximum size of any file to display.  (Default: <b>80000</b>)',
            'configuration_group_id' => 10,
            'sort_order' => 101,
        ]);


        $this->addConfigurationKey('DISPLAY_LOGS_INCLUDED_FILES', [
            'configuration_title' => 'Display Logs: Included File Prefixes',
            'configuration_value' =>  'myDEBUG-|AIM_Debug_|SIM_Debug_|FirstData_Debug_|Paypal|paypal|ipn_|zcInstall|notifier|usps|SHIP_usps',
            'configuration_description' => 'Identify the log-file <em>prefixes</em> to include in the display, separated by the pipe character (|).  Any intervening spaces are removed by the processing code.',
            'configuration_group_id' => 10,
            'sort_order' => 102,
        ]);


        $this->addConfigurationKey('DISPLAY_LOGS_EXCLUDED_FILES', [
            'configuration_title' => 'Display Logs: Excluded File Prefixes',
            'configuration_value' =>  '',
            'configuration_description' => 'Identify the log-file prefixes to <em>exclude</em> from the display, separated by the pipe character (|). Any intervening spaces are removed by the processing code.',
            'configuration_group_id' => 10,
            'sort_order' => 103,
        ]);
    }

    protected function executeUninstall()
    {
        zen_deregister_admin_pages(['toolsDisplayLogs']);

        $this->deleteConfigurationKeys(['DISPLAY_LOGS_MAX_DISPLAY', 'DISPLAY_LOGS_MAX_FILE_SIZE', 'DISPLAY_LOGS_INCLUDED_FILES', 'DISPLAY_LOGS_EXCLUDED_FILES']);
    }
}
