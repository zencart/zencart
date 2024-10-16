<?php
/**
 * System Inspection (formerly Mod List by That Software Guy)
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott Wilson 2024 Sep 29 New in v2.1.0-beta1 $
 */

use Zencart\FileSystem\FileSystem;
use Zencart\PluginSupport\ScriptedInstaller as ScriptedInstallBase;

class ScriptedInstaller extends ScriptedInstallBase
{
    protected function executeInstall()
    {
        if (!zen_page_key_exists('system_inspection')) {
            zen_register_admin_page('system_inspection', 'BOX_TOOLS_SYSTEM_INSPECTION', 'FILENAME_SYSTEM_INSPECTION', '', 'tools', 'Y');
        }

        // Delete old ModList plugin fragments
        $this->removeOldModListPlugin();

        return true;
    }

    /**
     * @return bool
     */
    protected function executeUninstall()
    {
        zen_deregister_admin_pages('system_inspection');
        return true;
    }

    /**
     * This uses some aggressive methods for deleting the old versions of ModList
     * Normally such approaches should be discouraged.
     */
    protected function removeOldModListPlugin(): void
    {
        // remove old "mod_list" from the Tools menu, to avoid confusion.
        if (zen_page_key_exists('mod_list')) {
            zen_deregister_admin_pages('mod_list');
        }

        // delete old mod_list files from filesystem
        $this->removeOldNonencapsulatedModList();

        // Forcefully remove old ModList plugin from zc_plugins dir (This is a very aggressive approach, not recommended.)
        (new FileSystem)->deleteDirectory($path = DIR_FS_CATALOG . 'zc_plugins/ModList');
        if (is_dir($path)) {
            $this->errorContainer->addError(0,
                sprintf(ERROR_UNABLE_TO_DELETE_FILE, ' (entire zc_plugins/ModList/ directory)'),
                true,
            );
        }
        // The admin Plugin Manager page will automatically clean up the database records after the directory disappears.
    }

    protected function removeOldNonencapsulatedModList(): void
    {
        if (zen_page_key_exists('mod_list')) {
            zen_deregister_admin_pages('mod_list');
        }

        $filesToDelete = [
            DIR_FS_ADMIN . 'includes/extra_configures/mod_list.php',
            DIR_FS_ADMIN . 'includes/languages/english/extra_definitions/mod_list.php',
            DIR_FS_ADMIN . 'includes/languages/english/mod_list.php',
            DIR_FS_ADMIN . 'mod_list.php',
            DIR_FS_ADMIN . 'mod_list.sql', // in case it got uploaded
            DIR_FS_CATALOG . 'mod_list.sql', // in case it got uploaded
        ];

        foreach ($filesToDelete as $key => $nextFile) {
            if (file_exists($nextFile)) {
                $result = unlink($nextFile);
                if (!$result && file_exists($nextFile)) {
                    $this->errorContainer->addError(
                        0,
                        sprintf(ERROR_UNABLE_TO_DELETE_FILE, $nextFile),
                        false,
                        // this str_replace has to do DIR_FS_ADMIN before CATALOG because catalog is contained within admin, so results are wrong.
                        // also, '[admin_directory]' is used to obfuscate the admin dir name, in case the user copy/pastes output to a public forum for help.
                        sprintf(ERROR_UNABLE_TO_DELETE_FILE, str_replace([DIR_FS_ADMIN, DIR_FS_CATALOG], ['[admin_directory]/', ''], $nextFile))
                    );
                }
            }
        }
    }

}
