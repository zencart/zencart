<?php
/**
 * @package admin
 * @copyright Copyright 2003-2017 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */

namespace ZenCart\ConfigLoader;

/**
 * Class ConfigLoader
 * @package ZenCart\ConfigLoader
 */
class ConfigLoader
{
    /**
     * @var array
     */
    protected $parameterBag = [];

    /**
     *
     */
    public function load()
    {
        $this->loadMainConfigs();
//        $this->loadPluginConfigs();
    }

    /**
     *
     */
    private function loadMainConfigs()
    {
        $configPath = DIR_FS_CATALOG . 'app/config/';
        if (!$dir = @dir($configPath)) {
            return;
        }
        while ($file = $dir->read()) {
            $this->processConfigFile($configPath, $file);
        }
        $dir->close();
    }

    /**
     * @param $configPath
     * @param $file
     */
    private function processConfigFile($configPath, $file)
    {
        if (!preg_match('~^[^\._].*\.php$~i', $configPath . $file) > 0) {
            return;
        }
        $result = include $configPath . $file;
        $fileinfo = pathinfo($file);
        $this->addConfigSettings($fileinfo['filename'], $result);
        $this->parameterBag[$fileinfo['filename']] = $result;

    }

    /**
     * @param $mainKey
     * @param $entries
     */
    public function addConfigSettings($mainKey, $entries)
    {
        if (isset($this->parameterBag[$mainKey])) {
            $current = $this->parameterBag[$mainKey];
            $current = array_merge($current, $entries);
            $this->parameterBag[$mainKey] = $current;
        } else {
           $this->parameterBag[$mainKey] = $entries;
        }
    }

    /**
     * @param $index
     * @return mixed
     */
    public function get($index)
    {
        return array_get($this->parameterBag, $index);
    }
}
