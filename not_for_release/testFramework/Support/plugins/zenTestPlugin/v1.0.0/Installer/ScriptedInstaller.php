<?php

use Zencart\PluginSupport\ScriptedInstaller as ScriptedInstallBase;

class ScriptedInstaller extends ScriptedInstallBase
{
    protected function executeInstall()
    {
        zen_deregister_admin_pages(['toolsZenTestPlugin']);
        zen_register_admin_page('toolsZenTestPlugin', 'BOX_TOOLS_ZEN_TEST_PLUGIN', 'FILENAME_ZEN_TEST_PLUGIN', '', 'tools', 'Y', 20);

    }

    protected function executeUninstall()
    {
        zen_deregister_admin_pages(['toolsZenTestPlugin']);

    }
}
