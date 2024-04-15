<?php
/**
 * ot_subtotal order-total module
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version 
 */

use Zencart\ModuleSupport\OrderTotalBase;
use Zencart\ModuleSupport\OrderTotalConcerns;
use Zencart\ModuleSupport\OrderTotalContract;

  class ot_subtotal extends OrderTotalBase implements OrderTotalContract
  {
    use OrderTotalConcerns;

    public string $code = 'ot_subtotal';
    public string $defineName = 'SUBTOTAL';


    function process(): void
    {
      global $order, $currencies;

      $this->output[] = array('title' => $this->title . ':',
                              'text' => $currencies->format($order->info['subtotal'], true, $order->info['currency'], $order->info['currency_value']),
                              'value' => $order->info['subtotal']);
    }
  }
