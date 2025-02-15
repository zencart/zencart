<?php
/**
 * ot_shipping order-total module
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2023 Aug 18 Modified in v2.0.0-alpha1 $
 */

use Zencart\ModuleSupport\OrderTotalBase;
use Zencart\ModuleSupport\OrderTotalConcerns;
use Zencart\ModuleSupport\OrderTotalContract;
use Carbon\Carbon;

class ot_shipping extends OrderTotalBase implements OrderTotalContract
{
    use OrderTotalConcerns;

    public string $code = 'ot_shipping';
    public string $defineName = 'SHIPPING';


    public function process(): void
    {
        global $order, $currencies;
 
        $this->output = [];
        unset($_SESSION['shipping_tax_description']);
        
        if (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING === 'true') {
                                    $pass = false;
            switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
                case 'national':
                    if ($order->delivery['country_id'] == STORE_COUNTRY) {
                        $pass = true; 
                    }
                    break;
              case 'international':
                    if ($order->delivery['country_id'] != STORE_COUNTRY) {
                        $pass = true; 
                    }
                    break;
              case 'both':
                    $pass = true; 
                    break;
              default:
                    break;
            }

            if ($pass &&  ($order->info['total'] - $order->info['shipping_cost']) >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER) {
                $order->info['shipping_method'] = $this->title;
                $order->info['total'] -= $order->info['shipping_cost'];
                $order->info['shipping_cost'] = 0;
            }
        }
        $module = (isset($_SESSION['shipping']['id'])) ? substr($_SESSION['shipping']['id'], 0, strpos($_SESSION['shipping']['id'], '_')) : '';
        if (is_object($order) && !empty($order->info['shipping_method'])) {
            // -----
            // Give an external tax-handler to make modifications to the shipping tax.
            //
            $external_shipping_tax_handler = false;
            $shipping_tax = 0;
            $shipping_tax_description = '';
            $this->notify(
                'NOTIFY_OT_SHIPPING_TAX_CALCS', 
                [], 
                $external_shipping_tax_handler, 
                $shipping_tax, 
                $shipping_tax_description
            );

            if ($external_shipping_tax_handler === true || (!empty($module) && $module !== 'free' && isset($GLOBALS[$module]->tax_class) && $GLOBALS[$module]->tax_class > 0)) {
                if ($external_shipping_tax_handler !== true) {
                    if (!isset($GLOBALS[$module]->tax_basis)) {
                        $shipping_tax_basis = STORE_SHIPPING_TAX_BASIS;
                    } else {
                        $shipping_tax_basis = $GLOBALS[$module]->tax_basis;
                    }

                    if ($shipping_tax_basis === 'Billing') {
                        $shipping_tax = zen_get_tax_rate($GLOBALS[$module]->tax_class, $order->billing['country']['id'], $order->billing['zone_id']);
                        $shipping_tax_description = zen_get_tax_description($GLOBALS[$module]->tax_class, $order->billing['country']['id'], $order->billing['zone_id']);
                    } elseif ($shipping_tax_basis === 'Shipping') {
                        $shipping_tax = zen_get_tax_rate($GLOBALS[$module]->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
                        $shipping_tax_description = zen_get_tax_description($GLOBALS[$module]->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
                    } else {
                        if (STORE_ZONE == $order->billing['zone_id']) {
                            $shipping_tax = zen_get_tax_rate($GLOBALS[$module]->tax_class, $order->billing['country']['id'], $order->billing['zone_id']);
                            $shipping_tax_description = zen_get_tax_description($GLOBALS[$module]->tax_class, $order->billing['country']['id'], $order->billing['zone_id']);
                        } elseif (STORE_ZONE == $order->delivery['zone_id']) {
                            $shipping_tax = zen_get_tax_rate($GLOBALS[$module]->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
                            $shipping_tax_description = zen_get_tax_description($GLOBALS[$module]->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
                        } else {
                            $shipping_tax = 0;
                        }
                    }
                }
                $shipping_tax_amount = zen_calculate_tax($order->info['shipping_cost'], $shipping_tax);
                $order->info['shipping_tax'] += $shipping_tax_amount;
                $order->info['tax'] += $shipping_tax_amount;
                if (!isset($order->info['tax_groups'][$shipping_tax_description])) {
                    $order->info['tax_groups'][$shipping_tax_description] = 0;
                }
                $order->info['tax_groups'][$shipping_tax_description] += $shipping_tax_amount;
                $order->info['total'] += $shipping_tax_amount;
                $_SESSION['shipping_tax_description'] = $shipping_tax_description;
                $_SESSION['shipping_tax_amount'] =  $shipping_tax_amount;
                if (DISPLAY_PRICE_WITH_TAX === 'true') {
                    $order->info['shipping_cost'] += $shipping_tax_amount;
                }
            }

            $order->info['shipping_tax_rate'] = ($order->content_type === 'virtual') ? null : $shipping_tax;

            if (isset($_SESSION['shipping']['id']) && $_SESSION['shipping']['id'] === 'free_free') {
                $order->info['shipping_method'] = FREE_SHIPPING_TITLE;
                $order->info['shipping_cost'] = 0;
            }

            $this->output[] = [
                'title' => $order->info['shipping_method'] . ':',
                'text' => $currencies->format($order->info['shipping_cost'], true, $order->info['currency'], $order->info['currency_value']),
                'value' => $order->info['shipping_cost']
            ];
        }
    }

    protected function addCustomConfigurationKeys(): array
    {
        $configKeys = [];
        $key = $this->buildDefine('FREE_SHIPPING');
        $configKeys[$key] = [
            'configuration_value' => 'false',
            'configuration_title' => 'Allow Free Shipping',
            'configuration_description' => 'Do you want to allow free shipping?',
            'configuration_group_id' => 6,
            'sort_order' => 1,
            'date_added' => Carbon::now(),
            'set_function' => 'zen_cfg_select_option([\'true\', \'false\'], ',
        ];
        $configKeys = [];
        $key = $this->buildDefine('FREE_SHIPPING_OVER');
        $configKeys[$key] = [
            'configuration_value' => '50',
            'configuration_title' => 'Free Shipping For Orders Over',
            'configuration_description' => 'Provide free shipping for orders over the set amount',
            'configuration_group_id' => 6,
            'sort_order' => 1,
            'date_added' => Carbon::now(),
            'use_function' => 'currencies->format',
        ];
        $key = $this->buildDefine('DESTINATION');
        $configKeys[$key] = [
            'configuration_value' => 'national',
            'configuration_title' => 'Provide Free Shipping For Orders Made',
            'configuration_description' => 'Provide free shipping for orders sent to the set destination.',
            'configuration_group_id' => 6,
            'sort_order' => 1,
            'date_added' => Carbon::now(),
            'set_function' => 'zen_cfg_select_option([\'national\', \'international\', \'both\'], ',
        ];
      return $configKeys;
    }
}
