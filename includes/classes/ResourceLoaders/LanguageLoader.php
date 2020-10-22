<?php
/**
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt 2020 May 20 New in v1.5.7 $
 */

namespace Zencart\LanguageLoader;

class LanguageLoader
{

    public function __construct($pluginList, $currentPage)
    {
        $this->pluginList = $pluginList;
        $this->currentPage = $currentPage;
    }

    public function loadLanguageDefines()
    {
        $this->loadLanguageForView();
        $this->loadLanguageExtraDefinitions();
        $this->loadBaseLanguageFile();
    }


    protected function loadLanguageForView()
    {
        if (is_file(DIR_WS_LANGUAGES . $_SESSION['language'] . '/' . $this->currentPage)) {
            include(DIR_WS_LANGUAGES . $_SESSION['language'] . '/' . $this->currentPage);
        }
        foreach ($this->pluginList as $plugin) {
            $pluginDir = DIR_FS_CATALOG . 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'];
            $langFile = $pluginDir . '/admin/includes/languages/'  . $_SESSION['language'] . '/' . $this->currentPage;
            if (is_file($langFile)) {
                include_once($langFile);
            }
        }
    }

    protected function loadLanguageExtraDefinitions()
    {
        $this->loadFilesFromDirectory(DIR_WS_LANGUAGES . $_SESSION['language'] . '/extra_definitions', '~^[^\._].*\.php$~i');
        foreach ($this->pluginList as $plugin) {
            $pluginDir = DIR_FS_CATALOG . 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'];
            $extrasDir = $pluginDir . '/admin/includes/languages/' . $_SESSION['language'] . '/extra_definitions';
            $this->loadFilesFromDirectory($extrasDir, '~^[^\._].*\.php$~i');
        }

    }

    protected function loadBaseLanguageFile()
    {
        require_once(DIR_WS_LANGUAGES . $_SESSION['language'] . '.php');
    }


////////// move below to filesystemclass ////////////////////

    public function loadFilesFromDirectory($rootDir, $fileRegx)
    {
        if (!$dir = @dir($rootDir)) return;
        while ($file = $dir->read()) {
            if (preg_match($fileRegx, $file) > 0) {
                require_once($rootDir . '/' . $file);
            }
        }
        $dir->close();
    }
}