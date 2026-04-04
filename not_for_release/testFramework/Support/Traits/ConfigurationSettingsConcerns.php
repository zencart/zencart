<?php

namespace Tests\Support\Traits;

use Tests\Support\Database\TestDb;

trait ConfigurationSettingsConcerns
{

    protected static bool $compoundDone = false;

    public function setConfiguration($configKey, $configValue)
    {
        TestDb::update(
            'configuration',
            ['configuration_value' => $configValue],
            'configuration_key = :config_key',
            [':config_key' => $configKey]
        );
    }

    public function getConfigurationSetting($configKey)
    {
        return (string) TestDb::selectValue(
            'SELECT configuration_value FROM configuration WHERE configuration_key = :config_key LIMIT 1',
            [':config_key' => $configKey]
        );
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
        $taxClassId = (string) TestDb::selectValue(
            'SELECT tax_class_id FROM tax_class WHERE tax_class_title = :title LIMIT 1',
            [':title' => 'Taxable Shipping']
        );
        $this->setConfiguration('MODULE_SHIPPING_ITEM_TAX_CLASS', $mode == 'on' ? $taxClassId : '0');
    }

    public function switchFlatShippingTax($mode = 'on')
    {
        $this->setConfiguration('MODULE_SHIPPING_FLAT_TAX_CLASS', $mode == 'on' ? '2' : '0');
    }

    public function switchSplitTaxMode($mode = 'on')
    {
        $this->setConfiguration('SHOW_SPLIT_TAX_CHECKOUT', $mode == 'on' ? 'true' : 'false');
    }

    public function displaySignificantSettings()
    {
        $settings = ['DISPLAY_PRICE_WITH_TAX', 'MODULE_SHIPPING_ITEM_TAX_CLASS', 'MODULE_ORDER_TOTAL_LOWORDERFEE_LOW_ORDER_FEE'];
        foreach ($settings as $item) {
            var_dump($item, $this->getConfigurationSetting($item));
        }

    }
}
