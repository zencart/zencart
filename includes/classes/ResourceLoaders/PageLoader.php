<?php
/**
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt 2019 Aug 23 New in v1.5.7 $
 */

namespace Zencart\PageLoader;

class PageLoader
{
    public function __construct(array $installedPlugins, $mainPage, $fileSystem)
    {
        $this->installedPlugins = $installedPlugins;
        $this->mainPage = $mainPage;
        $this->fileSystem = $fileSystem;
    }

    public function findModulePageDirectory()
    {
        if (is_dir(DIR_WS_MODULES . 'pages/' . $this->mainPage)) {
            return DIR_WS_MODULES . 'pages/' . $this->mainPage;
        }
        foreach ($this->installedPlugins as $plugin) {
            $checkDir = 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'] . '/';
            $checkDir .= 'catalog/includes/modules/pages/' . $this->mainPage;
            if (is_dir($checkDir)) return $checkDir;
        }
        return false;
    }

    function getTemplatePart($pageDirectory, $templatePart, $fileExtension = '.php')
    {
        $directoryArray = array();
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
                        $directoryArray[] = $pageDirectory . '/'. $file;
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
            $checkDir = 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'] . '/catalog/includes/template/templates/';
            if (file_exists($checkDir . $templateCode )) {
                return $checkDir;
            }
        }
        return false;
    }

    public function getBodyCode($currentPage)
    {
        if (file_exists(DIR_WS_MODULES . 'pages/' . $currentPage . '/main_template_vars.php')) {
            $bodyCode = DIR_WS_MODULES . 'pages/' . $currentPage . '/main_template_vars.php';
            return $bodyCode;
        }
        $bodyCode = $this->getTemplateDir(
                'tpl_' . preg_replace('/.php/', '', $_GET['main_page']) . '_default.php', DIR_WS_TEMPLATE, $currentPage, 'templates') . '/tpl_' . $_GET['main_page'] . '_default.php';
        return $bodyCode;
    }
}