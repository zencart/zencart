<?php

namespace Tests\Support\Traits;

use App\Models\Configuration;
use App\Models\TaxClass;

trait ConfigurationSettingsConcerns
{

    protected static bool $compoundDone = false;

    public function setConfiguration($configKey, $configValue)
    {
        $config = Configuration::where('configuration_key', $configKey)->first();
        $config->configuration_value = $configValue;
        $config->save();
    }

    public function getConfigurationSetting($configKey)
    {
        return (string)Configuration::select('configuration_value')->where('configuration_key', $configKey)->first();
    }

    public function switchToTaxInclusive()
    {
        $this->setConfiguration('DISPLAY_PRICE_WITH_TAX', 'true');
    }
    public function switchToTaxNonInclusive()
    {
        $this->setConfiguration('DISPLAY_PRICE_WITH_TAX', 'false');
    }

    public function switchItemShippingTax($mode = 'on')
    {
        $taxClass = TaxClass::where('tax_class_title', 'Taxable Shipping')->first();
        $this->setConfiguration('MODULE_SHIPPING_ITEM_TAX_CLASS', $mode == 'on' ? $taxClass->tax_class_id : '0');
    }

    public function switchFlatShippingTax($mode = 'on')
    {
        $this->setConfiguration('MODULE_SHIPPING_FLAT_TAX_CLASS', $mode == 'on' ? '2' : '0');
    }

    public function switchSplitTaxMode($mode = 'on')
    {
        $this->setConfiguration('SHOW_SPLIT_TAX_CHECKOUT', $mode == 'on' ? 'true' : 'false');
    }

    public function removePaymentModule($moduleKey)
    {
        $define = 'MODULE_PAYMENT_' . $moduleKey . '_%';
        Configuration::where('configuration_key', 'LIKE', $define)->delete();
    }
    public function installPaymentModule($moduleKey, $moduleClass)
    {
        require_once ROOTCWD . 'includes/modules/payment/' . $moduleClass . '.php';
        $module = new $moduleClass();
        try {
            $module->install();
            $this->setConfiguration('MODULE_PAYMENT_' . $moduleKey . '_STATUS', 'True');
            $this->updatePaymentModuleInstalled($moduleKey);
        } catch (\Exception $e) {
            
        }
    }
    public function updatePaymentModuleInstalled($moduleKey)
    {
        $current = Configuration::where('configuration_key', '=', 'MODULE_PAYMENT_INSTALLED')->first();
        $current->configuration_value = strtolower($moduleKey) . '.php';
        $current->save();}

    public function displaySignificantSettings()
    {
        $settings = ['DISPLAY_PRICE_WITH_TAX', 'MODULE_SHIPPING_ITEM_TAX_CLASS', 'MODULE_ORDER_TOTAL_LOWORDERFEE_LOW_ORDER_FEE'];
        foreach ($settings as $item) {
            var_dump($item, $this->getConfigurationSetting($item));
        }
    }
    public function removeAllPaymentModules()
    {
        $settings = Configuration::where('configuration_key', 'like', 'MODULE_PAYMENT_%')->get();
        foreach ($settings as $item) {
            $item->delete();
        }
        Configuration::updateOrCreate(['configuration_key' => 'MODULE_PAYMENT_INSTALLED'], ['configuration_value' => '', 'configuration_title' => '', 'configuration_description' => '', 'use_function' => '', 'set_function' => '']);
    }
    public function displayModuleSettings($module = 'PAYMENT', $code = 'MONEYORDER')
    {
        $settings = Configuration::where('configuration_key', 'like', 'MODULE_' . $module . '_' . $code . '%')->get();
        $installed = Configuration::where('configuration_key', '=', 'MODULE_PAYMENT_INSTALLED')->first();
        dump($installed);
    }

    public function setAdminWizardSettings()
    {
        $this->setConfiguration('STORE_OWNER', 'ZenCart Store Owner');
        $this->setConfiguration('STORE_NAME', 'ZenCart Store Name');
    }
}
