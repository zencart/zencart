<?php
/**
 * ot_total order-total module
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version 
 */

use Zencart\ModuleSupport\OrderTotalBase;
use Zencart\ModuleSupport\OrderTotalConcerns;
use Zencart\ModuleSupport\OrderTotalContract;
/**
 * ot_total order-total module
 *
 * @copyright Copyright 2003-2023 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2022 Oct 16 Modified in v1.5.8a $
 */
  class ot_total extends OrderTotalBase implements OrderTotalContract
  {
    use OrderTotalConcerns;

    public string $code = 'ot_total';
    public string $defineName = 'TOTAL';

    
     public function process(): void
     {
      global $order, $currencies;
      $this->output[] = array('title' => $this->title . ':',
                              'text' => $currencies->format($order->info['total'], true, $order->info['currency'], $order->info['currency_value']),
                              'value' => $order->info['total']);
    }

    public function remove() {
      global $messageStack;
      if (!isset($_GET['override']) && $_GET['override'] != '1') {
        $messageStack->add('header', ERROR_MODULE_REMOVAL_PROHIBITED . $this->code);
        return false;
      }
      parent::remove();
    }
  }
