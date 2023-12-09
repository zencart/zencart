<?php

namespace Tests\Support\Traits;

use App\Models\Configuration;

trait ConfigurationSettingsConcerns
{

    public function setConfiguration($configKey, $configValue)
    {
        $config = Configuration::where('configuration_key', $configKey)->first();
        $config->configuration_value = $configValue;
        $config->save();
    }
}
