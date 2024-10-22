<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott Wilson 2024 May 15 Modified in v2.0.1 $
 */

use Carbon\Carbon;
use Zencart\ModuleSupport\ShippingBase;
use Zencart\ModuleSupport\ShippingContract;
use Zencart\ModuleSupport\ShippingConcerns;

class flat extends ShippingBase implements ShippingContract
{
    use ShippingConcerns;

    public string $version = '1.0.0';
    public string $code = 'flat';
    public string $defineName = 'FLAT';

    protected function addCustomConfigurationKeys(): array
    {
        $configKeys = [];
        $key = $this->buildDefine('COST');
        $configKeys[$key] = [
            'configuration_value' => '5.0',
            'configuration_title' => 'Shipping Cost',
            'configuration_description' => 'Who should payments be made payable to?',
            'configuration_group_id' => 6,
            'sort_order' => 1,
            'date_added' => Carbon::now(),
        ];
        return $configKeys;
    }

    function quote($method = ''): array
    {
        global $order;

        $this->quotes = [
            'id' => $this->code,
            'module' => MODULE_SHIPPING_FLAT_TEXT_TITLE,
            'methods' => [
                [
                    'id' => $this->code,
                    'title' => MODULE_SHIPPING_FLAT_TEXT_WAY,
                    'cost' => MODULE_SHIPPING_FLAT_COST,
                ],
            ],
        ];
        if ($this->tax_class > 0) {
            $this->quotes['tax'] = zen_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
        }

        if (!empty($this->icon)) {
            $this->quotes['icon'] = zen_image($this->icon, $this->title);
        }

        return $this->quotes;
    }

}
