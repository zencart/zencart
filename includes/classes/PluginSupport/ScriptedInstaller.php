<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 */

namespace Zencart\PluginSupport;

use queryFactory;

/**
 * @since ZC v1.5.7
 */
class ScriptedInstaller
{
    use ScriptedInstallHelpers;

    // Extended classes can access these variables to understand what version/etc they are operating on.
    protected string $pluginDir;
    protected string $pluginKey;
    protected string $version;
    protected ?string $oldVersion; // null if not in upgrade mode

    public function __construct(protected queryFactory $dbConn, protected PluginErrorContainer $errorContainer)
    {
    }

    /***** THESE ARE THE 3 METHODS FOR IMPLEMENTATION IN EXTENDED CLASSES *********/
    /***** There is no need to implement any other methods in extended classes ****/

    /**
     * @return bool
     * @since ZC v1.5.7
     */
    protected function executeInstall()
    {
        return true;
    }

    /**
     * @return bool
     * @since ZC v1.5.7
     */
    protected function executeUninstall()
    {
        return true;
    }

    /**
     * @return bool
     * @since ZC v1.5.8
     */
    protected function executeUpgrade($oldVersion)
    {
        return true;
    }

    /******** Internal methods ***********/
    /**
     * @since ZC v2.1.0
     */
    public function setVersionDetails(array $versionDetails): void
    {
        $this->pluginKey = $versionDetails['pluginKey'];
        $this->pluginDir = $versionDetails['pluginDir'];
        $this->version = $versionDetails['version'];
        $this->oldVersion = $versionDetails['oldVersion'];
    }

    /**
     * @since ZC v1.5.7
     */
    public function doInstall(): ?bool
    {
        $installed = $this->executeInstall();
        return $installed;
    }

    /**
     * @since ZC v1.5.7
     */
    public function doUninstall(): ?bool
    {
        $uninstalled = $this->executeUninstall();
        $this->uninstallZenCoreDbFields();
        return $uninstalled;
    }

    /**
     * @since ZC v1.5.8
     */
    public function doUpgrade($oldVersion): ?bool
    {
        $upgraded = $this->executeUpgrade($oldVersion);
        $this->updateZenCoreDbFields($oldVersion);
        return $upgraded;
    }

    /**
     * @since ZC v1.5.7
     */
    protected function executeInstallerSql($sql): bool
    {
        $this->dbConn->dieOnErrors = false;
        $this->dbConn->Execute($sql);
        if ($this->dbConn->error_number !== 0) {
            $this->errorContainer->addError(0, $this->dbConn->error_text, true, PLUGIN_INSTALL_SQL_FAILURE);
            return false;
        }
        $this->dbConn->dieOnErrors = true;
        return true;
    }

    /**
     * Removes, if they exist, a list of files from either the 'admin' or
     * 'catalog' side.
     *
     * The $files_to_remove input is an associative array with each key being
     * an admin/catalog sub-directory and the value(s) being an array of files
     * to be removed from the key directory.
     *
     * If a file-name is identified as '*.*', then **ALL** files and sub-directories
     * as well as the upper, keyed, directory are removed.
     *
     * @since ZC v3.0.0
     */
    protected function removeFiles(array $files_to_remove, string $context): bool
    {
        if (!in_array($context, ['admin', 'catalog'])) {
            $error_message = sprintf(ERROR_REMOVE_FILES_CONTEXT, $context);
            $this->errorContainer->addError(0, $error_message, true, $error_message);
            return false;
        }

        $base_dir = ($context === 'admin') ? DIR_FS_ADMIN : DIR_FS_CATALOG;
        foreach ($files_to_remove as $dir => $files) {
            $current_dir = $base_dir . $dir;
            foreach ($files as $next_file) {
                $current_file = $current_dir . $next_file;
                if (str_ends_with($current_file, '*.*')) {
                    if ($this->removeDirectoryAndFiles(str_replace('/*.*', '', $current_file)) === false) {
                        $errorOccurred = true;
                    }
                    continue;
                }

                if (file_exists($current_file)) {
                    unlink($current_file);
                    if (file_exists($current_file)) {
                        $errorOccurred = true;
                        $this->errorContainer->addError(
                            0,
                            sprintf(ERROR_REMOVE_FILES_CANT_DELETE, $current_file),
                            false,
                            // this str_replace has to do DIR_FS_ADMIN before CATALOG because catalog is contained within admin, so results are wrong.
                            // also, '[admin_directory]' is used to obfuscate the admin dir name, in case the user copy/pastes output to a public forum for help.
                            sprintf(ERROR_REMOVE_FILES_CANT_DELETE, str_replace([DIR_FS_ADMIN, DIR_FS_CATALOG], ['[admin_directory]/', ''], $current_file))
                        );
                    }
                }
            }
        }

        return !$errorOccurred;
    }

    /**
     * Removes all files from the specified directory and its sub-directories, recursively.
     *
     * Returns a boolean indication as to whether (true) or not (false) all files
     * and sub-directories were removed.
     * 
     * @since ZC v3.0.0
     */
    protected function removeDirectoryAndFilesRecursive(string $dir_name): bool
    {
        $errorOccurred = false;
        if ($dir_name === '.' || $dir_name === '..' || !is_dir($dir_name)) {
            return true;
        }

        $dir_files = scandir($dir_name);
        foreach ($dir_files as $next_file) {
            if ($next_file === '.' || $next_file === '..') {
                continue;
            }

            $next_entry = $dir_name . '/' . $next_file;
            if (is_file($next_entry)) {
                unlink($next_entry);
                if (file_exists($next_entry)) {
                    $errorOccurred = true;
                    $this->errorContainer->addError(
                        0,
                        sprintf(ERROR_REMOVE_FILES_CANT_DELETE, $next_entry),
                        false,
                        // this str_replace has to do DIR_FS_ADMIN before CATALOG because catalog is contained within admin, so results are wrong.
                        // also, '[admin_directory]' is used to obfuscate the admin dir name, in case the user copy/pastes output to a public forum for help.
                        sprintf(ERROR_REMOVE_FILES_CANT_DELETE, str_replace([DIR_FS_ADMIN, DIR_FS_CATALOG], ['[admin_directory]/', ''], $next_entry))
                    );
                }
            } elseif ($this->removeDirectoryAndFiles($next_entry) === false) {
                $errorOccurred = true;
            }
        }

        if (is_dir($dir_name)) {
            rmdir($dir_name);
            if (is_dir($dir_name)) {
                $errorOccurred = true;
                $this->errorContainer->addError(
                    0,
                    sprintf(ERROR_CANT_REMOVE_DIR, $dir_name),
                    false,
                    // this str_replace has to do DIR_FS_ADMIN before CATALOG because catalog is contained within admin, so results are wrong.
                    // also, '[admin_directory]' is used to obfuscate the admin dir name, in case the user copy/pastes output to a public forum for help.
                    sprintf(ERROR_CANT_REMOVE_DIR, str_replace([DIR_FS_ADMIN, DIR_FS_CATALOG], ['[admin_directory]/', ''], $dir_name))
                );
            }
        }

        return !$errorOccurred;
    }
}
