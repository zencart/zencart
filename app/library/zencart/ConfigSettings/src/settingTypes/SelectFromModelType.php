<?php

namespace ZenCart\ConfigSettings\settingTypes;

class SelectFromModelType extends DefaultType
{
    public function transformSettingsFromDefinition(array $setting)
    {
        $definition = json_decode($setting['setting_definition'], true);
        $model = $this->modelFactory->make($definition['model']);
        $id = $definition['id'];
        $text = $definition['text'];
        $result = $model->select($id.' as id', $text . ' as text')->get()->toArray();
        $definition['options'] = $result;
        $definition = json_encode($definition, true);
        $setting['setting_definition'] = $definition;
        return $setting;
    }

}