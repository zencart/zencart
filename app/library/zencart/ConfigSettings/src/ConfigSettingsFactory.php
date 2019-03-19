<?php


namespace ZenCart\ConfigSettings;

use App\Model\ModelFactory;

class ConfigSettingsFactory
{

    public function make($settingsType)
    {
        $fileName = DIR_APP_LIBRARY . 'zencart/ConfigSettings/src/settingTypes/' . ucfirst($settingsType) . 'Type.php';
        $class = '\\ZenCart\\ConfigSettings\settingTypes\\' . ucfirst($settingsType) . 'Type';
        if (!file_exists($fileName)) {
            $class = '\\ZenCart\\ConfigSettings\settingTypes\\DefaultType';
        }
        $object = new $class(new ModelFactory($GLOBALS['db']));
        return $object;
    }
}