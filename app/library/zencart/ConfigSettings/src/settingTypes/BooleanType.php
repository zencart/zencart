<?php

namespace ZenCart\ConfigSettings\settingTypes;

use ZenCart\Request\Request;

class BooleanType extends DefaultType
{

    public function getValueFromRequest(Request $request, array $setting)
    {
        return $request->readPost($setting['setting_key'], 'off');
    }
}