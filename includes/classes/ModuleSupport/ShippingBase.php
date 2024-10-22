<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license https://opensource.org/license/mit MIT License
 * @version 
 */

namespace Zencart\ModuleSupport;

use Aura\Autoload\Loader;
use Zencart\Logger\Loggers\ModuleLogger;

abstract class ShippingBase extends ModuleBase
{

    public string $tax_class = '';
    public string $tax_basis = '';

    public $quotes;

    public function __construct()
    {
        /**
         * @var \Order $order
         */

        global $order;
        parent::__construct();
        $this->tax_class = $this->getDEfine('TAX_CLASS');
        $this->tax_basis = $this->getDEfine('TAX_BASIS');
        $this->zone = $this->getZone();
        $this->enabled = $this->isEnabled();
        $this->title = $this->getTitle();
        $this->logger->log('info', $this->messagePrefix('Called Constructor'));
        if ((int)$this->getDefine('ORDER_STATUS_ID', 0) > 0) {
            $this->order_status = (int)$this->getDefine('ORDER_STATUS_ID');
        }
        if (is_object($order)) $this->update_status();
    }

    protected function setCommonConfigurationKeys(): array
    {
        $configKeys = [];
        $key = $this->buildDefine('STATUS');
        $configKeys[$key] = [
            'configuration_value' => 'False',
            'configuration_title' => 'Enable this module',
            'configuration_description' => 'Do you want to accept payments using this module',
            'configuration_group_id' => 6,
            'sort_order' => 1,
            'set_function' => "zen_cfg_select_option(array('True', 'False'), ",
        ];
        $key = $this->buildDefine('SORT_ORDER');
        $configKeys[$key] = [
            'configuration_value' => 0,
            'configuration_title' => 'Sort order of display.',
            'configuration_description' => 'Sort order of display. Lowest is displayed first.',
            'configuration_group_id' => 6,
            'sort_order' => 1,
        ];
        $key = $this->buildDefine('TAX_CLASS');
        $configKeys[$key] = [
            'configuration_value' => 0,
            'configuration_title' => 'Tax Class',
            'configuration_description' => 'Use the following tax class on the shipping fee.',
            'configuration_group_id' => 6,
            'sort_order' => 1,
            'use_function' => 'zen_get_tax_class_title',
            'set_function' => "zen_cfg_pull_down_tax_classes(",
        ];
        $key = $this->buildDefine('TAX_BASIS');
        $configKeys[$key] = [
            'configuration_value' => 'Shipping',
            'configuration_title' => 'Tax Basis',
            'configuration_description' => 'On what basis is Shipping Tax calculated. Options are<br>Shipping - Based on customers Shipping Address<br>Billing Based on customers Billing address<br>Store - Based on Store address if Billing/Shipping Zone equals Store zone',
            'configuration_group_id' => 6,
            'sort_order' => 1,
            'use_function' => 'zen_get_tax_class_title',
            'set_function' => 'zen_cfg_select_option(array(\'Shipping\', \'Billing\', \'Store\'), ',
        ];
       $key = $this->buildDefine('ZONE');
        $configKeys[$key] = [
            'configuration_value' => 0,
            'configuration_title' => 'Payment Zone',
            'configuration_description' => 'If a zone is selected, only enable this shipping method for that zone.',
            'configuration_group_id' => 6,
            'sort_order' => 1,
            'set_function' => "zen_cfg_pull_down_zone_classes(",
        ];

        $key = $this->buildDefine('DEBUG_MODE');
        $configKeys[$key] = [
            'configuration_value' => '--none--',
            'configuration_title' => 'Use debug mode',
            'configuration_description' => 'Debug Mode adds extra logging to file, email and console output',
            'configuration_group_id' => 6,
            'sort_order' => 1,
            'set_function' => "zen_cfg_select_multioption(array('File', 'Email', 'BrowserConsole'), ",
        ];
        return $configKeys;
    }

    protected function getModuleContext($toUpper = true): string
    {
        return ($toUpper) ? 'SHIPPING' : 'shipping';
    }
}
