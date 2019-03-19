<?php

namespace ZenCart\ConfigSettings\settingTypes;

use ZenCart\Request\Request;

class DefaultType
{

    public function __construct($modelFactory)
    {
        $this->modelFactory = $modelFactory;
    }

    public function getValueFromRequest(Request $request, array $setting)
    {
        return $request->readPost($setting['setting_key'], null);
    }

    public function transformSettingsFromDefinition(array $setting)
    {
        return $setting;
    }
}