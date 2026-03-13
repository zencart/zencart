<?php
use Zencart\PluginSupport\ScriptedInstaller as ScriptedInstallBase;

class ScriptedInstaller extends ScriptedInstallBase
{
    protected function executeInstall()
    {
        // -----
        // Disallow installation on versions of Zen Cart prior to v2.2.0.
        //
        if (version_compare(zen_get_zcversion(), '2.1.0', '<=')) {
            $this->errorContainer->addError(0, ERROR_UNSUPPORTED_ZC_VERSION, false, ERROR_UNSUPPORTED_ZC_VERSION);
            return false;
        }

        if ($this->purgeOldFiles() === false) {
            return false;
        }

        parent::executeInstall();

        return true;
    }

    protected function executeUpgrade($oldVersion)
    {
        parent::executeUpgrade($oldVersion);
    }

    protected function executeUninstall()
    {
        parent::executeUninstall();
    }

    protected function purgeOldFiles(): bool
    {
        // -----
        // First, look for and remove the non-encapsulated versions' admin-directory
        // file.
        //
        $files_to_check = [
            'includes/classes/observers/' => [
                'auto.PaypalRestAdmin.php',
            ],
        ];

        $errorOccurred = false;
        foreach ($files_to_check as $dir => $files) {
            $current_dir = DIR_FS_ADMIN . $dir;
            foreach ($files as $next_file) {
                $current_file = $current_dir . $next_file;
                if (file_exists($current_file)) {
                    unlink($current_file);
                    if (file_exists($current_file)) {
                        $errorOccurred = true;
                        $this->errorContainer->addError(
                            0,
                            sprintf(ERROR_UNABLE_TO_DELETE_FILE, $current_file),
                            false,
                            // this str_replace has to do DIR_FS_ADMIN before CATALOG because catalog is contained within admin, so results are wrong.
                            // also, '[admin_directory]' is used to obfuscate the admin dir name, in case the user copy/pastes output to a public forum for help.
                            sprintf(ERROR_UNABLE_TO_DELETE_FILE, str_replace([DIR_FS_ADMIN, DIR_FS_CATALOG], ['[admin_directory]/', ''], $current_file))
                        );
                    }
                }
            }
        }

        // -----
        // Next, locate and attempt to remove the storefront files.
        //
        $files_to_check = [
            '' => [
                'ppr_listener.php',
                'ppr_webhook.php',
                'ppr_webhook_main.php',
            ],
            'includes/classes/observers/' => [
                'auto.paypalrestful.php',
            ],
            'includes/extra_configures/' => [
                'php_polyfills.php',
            ],
            'includes/languages/english/' => [
                'extra_definitions/lang.paypalr_redirect_listener_definitions.php',
                'modules/payment/lang.paypalr.php',
                'modules/payment/paypalr.php',
            ],
            'includes/modules/payment/' => [
                'paypalr.php',
                'paypal/pprAutoload.php',
                'paypal/PayPalRestful/*.*',
            ],
        ];
        foreach ($files_to_check as $dir => $files) {
            $current_dir = DIR_FS_CATALOG . $dir;
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
                            sprintf(ERROR_UNABLE_TO_DELETE_FILE, $current_file),
                            false,
                            // this str_replace has to do DIR_FS_ADMIN before CATALOG because catalog is contained within admin, so results are wrong.
                            // also, '[admin_directory]' is used to obfuscate the admin dir name, in case the user copy/pastes output to a public forum for help.
                            sprintf(ERROR_UNABLE_TO_DELETE_FILE, str_replace([DIR_FS_ADMIN, DIR_FS_CATALOG], ['[admin_directory]/', ''], $current_file))
                        );
                    }
                }
            }
        }

        return !$errorOccurred;
    }

    // -----
    // Removes all files from the specified directory and its sub-directories.
    //
    // Returns a boolean indication as to whether (true) or not (false) all files
    // and sub-directories were removed.
    //
    protected function removeDirectoryAndFiles(string $dir_name): bool
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
                        sprintf(ERROR_UNABLE_TO_DELETE_FILE, $next_entry),
                        false,
                        // this str_replace has to do DIR_FS_ADMIN before CATALOG because catalog is contained within admin, so results are wrong.
                        // also, '[admin_directory]' is used to obfuscate the admin dir name, in case the user copy/pastes output to a public forum for help.
                        sprintf(ERROR_UNABLE_TO_DELETE_FILE, str_replace([DIR_FS_ADMIN, DIR_FS_CATALOG], ['[admin_directory]/', ''], $next_entry))
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
                    sprintf(ERROR_UNABLE_TO_REMOVE_DIR, $dir_name),
                    false,
                    // this str_replace has to do DIR_FS_ADMIN before CATALOG because catalog is contained within admin, so results are wrong.
                    // also, '[admin_directory]' is used to obfuscate the admin dir name, in case the user copy/pastes output to a public forum for help.
                    sprintf(ERROR_UNABLE_TO_REMOVE_DIR, str_replace([DIR_FS_ADMIN, DIR_FS_CATALOG], ['[admin_directory]/', ''], $dir_name))
                );
            }
        }

        return !$errorOccurred;
    }
}
