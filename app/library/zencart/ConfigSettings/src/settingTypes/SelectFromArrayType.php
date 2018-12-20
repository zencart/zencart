<?php

namespace ZenCart\ConfigSettings\settingTypes;

class SelectFromArrayType extends DefaultType
{
    public function transformSettingsFromDefinition(array $setting)
    {
        $definition = json_decode($setting['setting_definition'], true);
        $options = $definition['options'];
        $newOptions = [];
        foreach ($options as $option) {
            $id = $option['id'];
            $text = $option['text'];
            if (defined($text)) {
                $text = constant($text);
            }
            $newOptions[] = ['id' => $id, 'text' => $text];
        }
        $definition['options'] = $newOptions;
        $definition = json_encode($definition, true);
        $setting['setting_definition'] = $definition;
        return $setting;
    }

}