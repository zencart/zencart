<?php
/**
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt 2019 Aug 23 New in v1.5.7 $
 */

namespace Zencart\PageLoader;

use Zencart\Traits\Singleton;

class PageLoader
{
    use Singleton;

    public function init(array $installedPlugins, $mainPage, $fileSystem)
    {
        $this->installedPlugins = $installedPlugins;
        $this->mainPage = $mainPage;
        $this->fileSystem = $fileSystem;
    }

    public function findModulePageDirectory($context = 'catalog')
    {
        if (is_dir(DIR_WS_MODULES . 'pages/' . $this->mainPage)) {
            return DIR_WS_MODULES . 'pages/' . $this->mainPage;
        }
        foreach ($this->installedPlugins as $plugin) {
            $rootDir = DIR_FS_CATALOG . 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'] . '/' . $context ;
            $checkDir = $rootDir . '/includes/modules/pages/' . $this->mainPage;
            if (is_dir($checkDir)) return $checkDir;
        }
        return false;
    }

    function getTemplatePart($pageDirectory, $templatePart, $fileExtension = '.php')
    {
        //dump('page Directory = ' . $pageDirectory);
        //dump('template part = ' . $templatePart);
        $directoryArray = [];
        $directoryArray = $this->getTemplatePartFromDirectory($directoryArray, $pageDirectory, $templatePart,
                                                              $fileExtension);

        foreach ($this->installedPlugins as $plugin) {
            $checkDir = 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'] . '/catalog/';
            $checkDir .= $pageDirectory;
            $directoryArray = $this->getTemplatePartFromDirectory($directoryArray, $checkDir, $templatePart,
                                                                  $fileExtension);
        }
        sort($directoryArray);
        return $directoryArray;
    }

    public function getTemplatePartFromDirectory($directoryArray, $pageDirectory, $templatePart, $fileExtension)
    {
        if ($dir = @dir($pageDirectory)) {
            while ($file = $dir->read()) {
                if (!is_dir($pageDirectory . $file)) {
                    if (substr($file, strrpos($file, '.')) == $fileExtension && preg_match($templatePart, $file)) {
                        $directoryArray[] = $file;
                    }
                }
            }
            $dir->close();
        }
        return $directoryArray;
    }

    function getTemplateDir($templateCode, $currentTemplate, $currentPage, $templateDir)
    {
        if ($this->fileSystem->fileExistsInDirectory($currentTemplate . $currentPage, $templateCode)) {
            return $currentTemplate . $currentPage . '/';
        }
        if ($this->fileSystem->fileExistsInDirectory(
            DIR_WS_TEMPLATES . 'template_default/' . $currentPage, preg_replace('/\//', '', $templateCode))) {
            return DIR_WS_TEMPLATES . 'template_default/' . $currentPage;
        }
        if ($this->fileSystem->fileExistsInDirectory(
            $currentTemplate . $templateDir, preg_replace('/\//', '', $templateCode))) {
            return $currentTemplate . $templateDir;
        }
        if ($tplPluginDir = $this->getTemplatePluginDir($templateCode, $currentTemplate, $currentPage, $templateDir)) {
            return $tplPluginDir;
        }
        return DIR_WS_TEMPLATES . 'template_default/' . $templateDir;
    }

    public function getTemplatePluginDir($templateCode)
    {
        foreach ($this->installedPlugins as $plugin) {
            $checkDir = 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'] . '/catalog/includes/templates/default/';
            if (file_exists($checkDir . $templateCode )) {
                return $checkDir;
            }
        }
        return false;
    }

    public function getBodyCode()
    {
        if (file_exists(DIR_WS_MODULES . 'pages/' . $this->mainPage . '/main_template_vars.php')) {
            $bodyCode = DIR_WS_MODULES . 'pages/' . $this->mainPage . '/main_template_vars.php';
            return $bodyCode;
        }
        $bodyCode = $this->getTemplateDir('tpl_' . preg_replace('/.php/', '', $this->mainPage) . '_default.php', DIR_WS_TEMPLATE, $this->mainPage, 'templates') . '/tpl_' . $this->mainPage . '_default.php';
        return $bodyCode;
    }
}
