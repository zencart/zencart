<?php

use Zencart\PluginSupport\ScriptedInstaller as ScriptedInstallBase;

class ScriptedInstaller extends ScriptedInstallBase
{
    protected function executeInstall()
    {
        zen_deregister_admin_pages(['toolsAidba']);
        zen_register_admin_page('toolsAidba', 'BOX_TOOLS_CONVERT_AIDBA', 'FILENAME_AIDBA', '', 'tools', 'Y', 20);
    }

    protected function executeUninstall()
    {
        zen_deregister_admin_pages(['toolsAidba']);

    }
}
