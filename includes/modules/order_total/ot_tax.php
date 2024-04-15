<?php
/**
 * ot_tax order-total module
 *
 * @copyright Copyright 2003-2023 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license   http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2022 Oct 16 Modified in v1.5.8a $
 */

use Zencart\ModuleSupport\OrderTotalBase;
use Zencart\ModuleSupport\OrderTotalConcerns;
use Zencart\ModuleSupport\OrderTotalContract;

    class ot_tax  extends OrderTotalBase implements OrderTotalContract
    {

        use OrderTotalConcerns;

        public string $code = 'ot_tax';
        public string $defineName = 'TAX';


        public function process(): void
        {
            global $order, $currencies;

            $taxDescription = '';
            $taxValue = 0;
            if (STORE_TAX_DISPLAY_STATUS === '1') {
                $taxAddress = zen_get_tax_locations();
                $result = zen_get_all_tax_descriptions($taxAddress['country_id'], $taxAddress['zone_id']);
                if (count($result) !== 0) {
                    foreach ($result as $description) {
                        if (!isset($order->info['tax_groups'][$description])) {
                            $order->info['tax_groups'][$description] = 0;
                        }
                    }
                }
            }
            if (count($order->info['tax_groups']) > 1 && isset($order->info['tax_groups'][0])) {
                unset($order->info['tax_groups'][0]);
            }
            foreach ($order->info['tax_groups'] as $key => $value) {
                if (SHOW_SPLIT_TAX_CHECKOUT === 'true') {
                    if ($value > 0 || (abs($value) < PHP_FLOAT_EPSILON && STORE_TAX_DISPLAY_STATUS === '1')) {
                        $this->output[] = [
                            'title' => ((is_numeric($key) && $key == 0) ? TEXT_UNKNOWN_TAX_RATE : $key) . ':',
                            'text' => $currencies->format($value, true, $order->info['currency'], $order->info['currency_value']),
                            'value' => $value,
                        ];
                    }
                } else {
                    if ($value > 0 || (abs($value) < PHP_FLOAT_EPSILON && STORE_TAX_DISPLAY_STATUS === '1')) {
                        $taxDescription .= ((is_numeric($key) && $key == 0) ? TEXT_UNKNOWN_TAX_RATE : $key) . ' + ';
                        $taxValue += $value;
                    }
                }
            }
            if (SHOW_SPLIT_TAX_CHECKOUT !== 'true' && ($taxValue > 0 || STORE_TAX_DISPLAY_STATUS === '1')) {
                $this->output[] = [
                    'title' => substr($taxDescription, 0, strlen($taxDescription) - 3) . ':',
                    'text' => $currencies->format($taxValue, true, $order->info['currency'], $order->info['currency_value']),
                    'value' => $taxValue,
                ];
            }
        }
    }
