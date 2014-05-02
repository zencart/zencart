<?php
/**
 * ot_gv order-total module
 *
 * @package orderTotal
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Tue Apr 15 17:32:27 2014 -0400 Modified in v1.5.3 $
 */
/**
 * Enter description here...
 *
 */
class ot_gv {
  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  var $title;
  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  var $output;
  /**
   * Enter description here...
   *
   * @return ot_gv
   */
  function ot_gv() {
    global $currencies;
    $this->code = 'ot_gv';
    $this->title = MODULE_ORDER_TOTAL_GV_TITLE;
    $this->header = MODULE_ORDER_TOTAL_GV_HEADER;
    $this->description = MODULE_ORDER_TOTAL_GV_DESCRIPTION;
    $this->user_prompt = MODULE_ORDER_TOTAL_GV_USER_PROMPT;
    $this->sort_order = MODULE_ORDER_TOTAL_GV_SORT_ORDER;
    $this->include_shipping = MODULE_ORDER_TOTAL_GV_INC_SHIPPING;
    $this->include_tax = MODULE_ORDER_TOTAL_GV_INC_TAX;
    $this->calculate_tax = MODULE_ORDER_TOTAL_GV_CALC_TAX;
    $this->credit_tax = MODULE_ORDER_TOTAL_GV_CREDIT_TAX;
    $this->tax_class  = MODULE_ORDER_TOTAL_GV_TAX_CLASS;
    $this->show_redeem_box = MODULE_ORDER_TOTAL_GV_REDEEM_BOX;
    $this->credit_class = true;
    if (!zen_not_null(ltrim($_SESSION['cot_gv'], ' 0')) || $_SESSION['cot_gv'] == '0') $_SESSION['cot_gv'] = '0.00';
    if (IS_ADMIN_FLAG !== true) {
      $this->checkbox = $this->user_prompt . '<input type="text" size="6" onkeyup="submitFunction()" name="cot_gv" value="' . number_format($_SESSION['cot_gv'], 2) . '" onfocus="if (this.value == \'' . number_format($_SESSION['cot_gv'], 2) . '\') this.value = \'\';" />' . ($this->user_has_gv_account($_SESSION['customer_id']) > 0 ? '<br />' . MODULE_ORDER_TOTAL_GV_USER_BALANCE . $currencies->format($this->user_has_gv_account($_SESSION['customer_id'])) : '');
    }
    $this->output = array();
    if (IS_ADMIN_FLAG === true) {
      if ($this->include_tax == 'true' && $this->calculate_tax != "None") {
        $this->title .= '<span class="alert">' . MODULE_ORDER_TOTAL_GV_INCLUDE_ERROR . '</span>';
      }
    }
  }
  /**
   * Enter description here...
   *
   */
  function process() {
    global $order, $currencies;
    if ($_SESSION['cot_gv']) {
      $od_amount = $this->calculate_deductions($this->get_order_total());
      $this->deduction = $od_amount['total'];
      if ($od_amount['total'] > 0) {
        reset($order->info['tax_groups']);
        $tax = 0;
        while (list($key, $value) = each($order->info['tax_groups'])) {
          if ($od_amount['tax_groups'][$key]) {
            $order->info['tax_groups'][$key] -= $od_amount['tax_groups'][$key];
            $tax += $od_amount['tax_groups'][$key];
          }
        }
        $order->info['total'] = $order->info['total'] - $od_amount['total'];
        if ($this->calculate_tax == "Standard") $order->info['total'] -= $tax;
        if ($order->info['total'] < 0) $order->info['total'] = 0;
        $order->info['tax'] = $order->info['tax'] - $od_amount['tax'];
        // prepare order-total output for display and storing to invoice
        $this->output[] = array('title' => $this->title . ':',
                                'text' => '-' . $currencies->format($od_amount['total']),
                                'value' => $od_amount['total']);
      }
    }
  }
  /**
   * This is called to reset any GV values, effectively cancelling all GV's applied during current login session
   */
  function clear_posts() {
    unset($_SESSION['cot_gv']);
  }
  /**
   * This just checks to see whether the currently-logged-in customer has any GV credits on their account
   */
  function selection_test() {
    if ($this->user_has_gv_account($_SESSION['customer_id'])) {
      return true;
    } else {
      return false;
    }
  }
  /**
   * Check for validity of redemption amounts and recalculate order totals to include proposed GV redemption deductions
   */
  function pre_confirmation_check($order_total) {
    global $order, $currencies, $messageStack;
    // clean out negative values and strip common currency symbols
    $_SESSION['cot_gv'] = preg_replace('/[^0-9,.%]/', '', $_SESSION['cot_gv']);
    $_SESSION['cot_gv'] = abs($_SESSION['cot_gv']);

    if ($_SESSION['cot_gv'] > 0) {
      // if cot_gv value contains any nonvalid characters, throw error
      if (preg_match('/[^0-9\,.]/', trim($_SESSION['cot_gv']))) {
        $messageStack->add_session('checkout_payment', TEXT_INVALID_REDEEM_AMOUNT, error);
        zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
      }
      // if requested redemption amount is greater than value of credits on account, throw error
      if ($_SESSION['cot_gv'] > $currencies->value($this->user_has_gv_account($_SESSION['customer_id']))) {
        $messageStack->add_session('checkout_payment', TEXT_INVALID_REDEEM_AMOUNT, error);
        zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
      }
      $od_amount = $this->calculate_deductions($order_total);
      $order->info['total'] = $order->info['total'] - $od_amount['total'];
      if (DISPLAY_PRICE_WITH_TAX != 'true') {
        $order->info['total'] -= $tax;
      }
      return $od_amount['total'] + $od_amount['tax'];
    }
    return 0;
  }
  /**
   * if customer has a GV balance, then we display the input field to allow entry of desired GV redemption amount
   */
  function use_credit_amount() {
    if ($this->selection_test()) {
      $output_string = $this->checkbox;
    }
    return $output_string;
  }
  /**
   * queue or release newly-purchased GV's
   */
  function update_credit_account($i) {
    global $db, $order, $insert_id;
    // only act on newly-purchased gift certificates
    if (preg_match('/^GIFT/', addslashes($order->products[$i]['model']))) {
      // determine how much GV was purchased
      // check if GV was purchased on Special
      $gv_original_price = zen_products_lookup((int)$order->products[$i]['id'], 'products_price');
       // if prices differ assume Special ang get Special Price
       // Do not use this on GVs Priced by Attribute
      if (MODULE_ORDER_TOTAL_GV_SPECIAL == 'true' && ($gv_original_price != 0 && $gv_original_price != $order->products[$i]['final_price'] && !zen_get_products_price_is_priced_by_attributes((int)$order->products[$i]['id']))) {
        $gv_order_amount = ($gv_original_price * $order->products[$i]['qty']);
      } else {
      $gv_order_amount = ($order->products[$i]['final_price'] * $order->products[$i]['qty']);
      }
      // if tax is to be calculated on purchased GVs, calculate it
      if ($this->credit_tax=='true') $gv_order_amount = $gv_order_amount * (100 + $order->products[$i]['tax']) / 100;
      $gv_order_amount = $gv_order_amount * 100 / 100;

      if (MODULE_ORDER_TOTAL_GV_QUEUE == 'false') {
        // GV_QUEUE is false so release amount to account immediately
        $gv_result = $this->user_has_gv_account($_SESSION['customer_id']);
        $customer_gv = false;
        $total_gv_amount = 0;
        if ($gv_result) {
          $total_gv_amount = $gv_result;
          $customer_gv = true;
        }
        $total_gv_amount = $total_gv_amount + $gv_order_amount;
        if ($customer_gv) {
          $db->Execute("update " . TABLE_COUPON_GV_CUSTOMER . " set amount = '" . $total_gv_amount . "' where customer_id = '" . (int)$_SESSION['customer_id'] . "'");
        } else {
          $db->Execute("insert into " . TABLE_COUPON_GV_CUSTOMER . " (customer_id, amount) values ('" . (int)$_SESSION['customer_id'] . "', '" . $total_gv_amount . "')");
        }
      } else {
        // GV_QUEUE is true - so queue the gv for release by store owner
        $db->Execute("insert into " . TABLE_COUPON_GV_QUEUE . " (customer_id, order_id, amount, date_created, ipaddr) values ('" . (int)$_SESSION['customer_id'] . "', '" . (int)$insert_id . "', '" . $gv_order_amount . "', NOW(), '" . $_SERVER['REMOTE_ADDR'] . "')");
      }
    }
  }
  /**
   * check system to see if GVs should be made available or not. If true, then supply GV-selection fields on checkout pages
   */
  function credit_selection() {
    global $db, $currencies;
    $gv_query = $db->Execute("select coupon_id from " . TABLE_COUPONS . " where coupon_type = 'G' and coupon_active='Y'");
    // checks to see if any GVs are in the system and active or if the current customer has any GV balance
    if ($gv_query->RecordCount() > 0 || $this->use_credit_amount()) {
      $selection = array('id' => $this->code,
                         'module' => $this->title,
                         'redeem_instructions' => MODULE_ORDER_TOTAL_GV_REDEEM_INSTRUCTIONS,
                         'checkbox' => $this->use_credit_amount(),
                         'fields' => array(array('title' => MODULE_ORDER_TOTAL_GV_TEXT_ENTER_CODE,
                         'field' => zen_draw_input_field('gv_redeem_code', '', 'id="disc-'.$this->code.'" onkeyup="submitFunction(0,0)"'),
                         'tag' => 'disc-'.$this->code
                         )));

    }
    return $selection;
  }
  /**
   * Verify that the customer has entered a valid redemption amount, and return the amount that can be applied to this order
   */
  function apply_credit() {
    global $db, $order, $messageStack;
    // check for valid redemption amount vs available credit for current customer
    if ($_SESSION['cot_gv'] != 0) {
      $gv_result = $db->Execute("select amount from " . TABLE_COUPON_GV_CUSTOMER . " where customer_id = '" . (int)$_SESSION['customer_id'] . "'");
      // obtain final "deduction" amount
      $gv_payment_amount = $this->deduction;
      // determine amount of GV to redeem based on available balance minus qualified/calculated deduction suitable to this order
      $gv_amount = $gv_result->fields['amount'] - $gv_payment_amount;
      // reduce customer's GV balance by the amount redeemed
      $db->Execute("update " . TABLE_COUPON_GV_CUSTOMER . " set amount = '" . $gv_amount . "' where customer_id = '" . (int)$_SESSION['customer_id'] . "'");
    }
    // clear GV redemption flag since it's already been claimed and deducted
    $_SESSION['cot_gv'] = false;
    // send back the amount of GV used for payment on this order
    return $gv_payment_amount;
  }
  /**
   * Check to see if redemption code has been entered and redeem if valid
   */
  function collect_posts() {
    global $db, $currencies, $messageStack;
    // if we have no GV amount selected, set it to 0
    if (!$_POST['cot_gv']) $_SESSION['cot_gv'] = '0.00';
    // if we have a GV redemption code submitted, process it
    if ($_POST['gv_redeem_code']) {
      // check for validity
      $_POST['gv_redeem_code'] = preg_replace('/[^0-9a-zA-Z]/', '', $_POST['gv_redeem_code']);
      $gv_result = $db->Execute("select coupon_id, coupon_type, coupon_amount from " . TABLE_COUPONS . " where coupon_code = '" . zen_db_prepare_input($_POST['gv_redeem_code']) . "' and coupon_type = 'G'");
      if ($gv_result->RecordCount() > 0) {
        $redeem_query = $db->Execute("select * from " . TABLE_COUPON_REDEEM_TRACK . " where coupon_id = '" . (int)$gv_result->fields['coupon_id'] . "'");
        // if already redeemed, throw error
        if ( ($redeem_query->RecordCount() > 0) && ($gv_result->fields['coupon_type'] == 'G')  ) {
          $messageStack->add_session('checkout_payment', ERROR_NO_INVALID_REDEEM_GV, error);
          zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
        }
      } else {
        // if not valid redemption code, throw error
        $messageStack->add_session('checkout_payment', ERROR_NO_INVALID_REDEEM_GV, error);
        zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
      }
      // if valid, add redeemed amount to customer's GV balance and mark as redeemed
      if ($gv_result->fields['coupon_type'] == 'G') {
        $gv_amount = $gv_result->fields['coupon_amount'];
        // Things to set
        // ip address of claimant
        // customer id of claimant
        // date
        // redemption flag
        // now update customer account with gv_amount
        $gv_amount_result=$db->Execute("select amount from " . TABLE_COUPON_GV_CUSTOMER . " where customer_id = '" . (int)$_SESSION['customer_id'] . "'");
        $customer_gv = false;
        $total_gv_amount = $gv_amount;;
        if ($gv_amount_result->RecordCount() > 0) {
          $total_gv_amount = $gv_amount_result->fields['amount'] + $gv_amount;
          $customer_gv = true;
        }
        $db->Execute("update " . TABLE_COUPONS . " set coupon_active = 'N' where coupon_id = '" . $gv_result->fields['coupon_id'] . "'");
        $db->Execute("insert into  " . TABLE_COUPON_REDEEM_TRACK . " (coupon_id, customer_id, redeem_date, redeem_ip) values ('" . $gv_result->fields['coupon_id'] . "', '" . (int)$_SESSION['customer_id'] . "', now(),'" . $_SERVER['REMOTE_ADDR'] . "')");
        if ($customer_gv) {
          // already has gv_amount so update
          $db->Execute("update " . TABLE_COUPON_GV_CUSTOMER . " set amount = '" . $total_gv_amount . "' where customer_id = '" . (int)$_SESSION['customer_id'] . "'");
        } else {
          // no gv_amount so insert
          $db->Execute("insert into " . TABLE_COUPON_GV_CUSTOMER . " (customer_id, amount) values ('" . (int)$_SESSION['customer_id'] . "', '" . $total_gv_amount . "')");
        }
        //          zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(ERROR_REDEEMED_AMOUNT. $currencies->format($gv_amount)), 'SSL'));
        $messageStack->add_session('redemptions',ERROR_REDEEMED_AMOUNT. $currencies->format($gv_amount), 'success' );
        zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL',true, false));
      }
    }
    if ($_POST['submit_redeem_x'] && $gv_result->fields['coupon_type'] == 'G') zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(ERROR_NO_REDEEM_CODE), 'SSL'));
  }
  /**
   * Calculate GV claim amount (GV amounts are always based on the STORE's default currency value)
   */
  function calculate_credit($save_total_cost) {
    global $db, $order, $currencies;
    // calculate value based on default currency
    $gv_payment_amount = $currencies->value($_SESSION['cot_gv'], true, DEFAULT_CURRENCY);
    $full_cost = $save_total_cost - $gv_payment_amount;
    if ($full_cost < 0) {
      $full_cost = 0;
      $gv_payment_amount = $save_total_cost;
    }
    return zen_round($gv_payment_amount,2);
  }
  function calculate_deductions($order_total) {
    global $db, $order, $messageStack;
    $od_amount = array();
    $deduction = $this->calculate_credit($this->get_order_total());
    $od_amount['total'] = $deduction;
    switch ($this->calculate_tax) {
      case 'None':
        $remainder = $order->info['total'] - $od_amount['total'];
        $tax_deduct = $order->info['tax'] - $remainder;
        // division by 0
        if ($order->info['tax'] <= 0) {
          $ratio_tax = 0;
        } else {
          $ratio_tax = $tax_deduct/$order->info['tax'];
        }
        $tax_deduct = 0;
/*
        if ($this->include_tax) {
          reset($order->info['tax_groups']);
          foreach ($order->info['tax_groups'] as $key=>$value) {
            $od_amount['tax_groups'][$key] = $order->info['tax_groups'][$key] * $ratio_tax;
            $tax_deduct += $od_amount['tax_groups'][$key];
          }
        }
*/
      $od_amount['tax'] = $tax_deduct;
      break;
      case 'Standard':
      if ($od_amount['total'] >= $order_total) {
        $ratio = 1;
      } else {
        $ratio = ($od_amount['total'] / ($order_total - $order->info['tax']));
      }
      reset($order->info['tax_groups']);
      $tax_deduct = 0;
      foreach ($order->info['tax_groups'] as $key=>$value) {
        $od_amount['tax_groups'][$key] = $order->info['tax_groups'][$key] * $ratio;
        $tax_deduct += $od_amount['tax_groups'][$key];
      }
      $od_amount['tax'] = $tax_deduct;
      break;
      case 'Credit Note':
        $od_amount['total'] = $deduction;
        $tax_rate = zen_get_tax_rate($this->tax_class);
        $od_amount['tax'] = zen_calculate_tax($deduction, $tax_rate);
        $tax_description = zen_get_tax_description($this->tax_class);
        $od_amount['tax_groups'][$tax_description] = $od_amount['tax'];
      break;
      default:
    }
    return $od_amount;
  }
  /**
   * Check to see whether current customer has a GV balance available
   * Returns amount of GV balance on account
   */
  function user_has_gv_account($c_id) {
    global $db;
    $gv_result = $db->Execute("select amount from " . TABLE_COUPON_GV_CUSTOMER . " where customer_id = '" . (int)$c_id . "'");
    if ($gv_result->RecordCount() > 0) {
      return $gv_result->fields['amount'];
    }
    return 0; // use 0 because 'false' was preventing checkout_payment from continuing
  }
  /**
   * Recalculates base order-total amount for use in deduction calculations
   */
  function get_order_total() {
    global $order;
    $order_total = $order->info['total'];
    // if we are not supposed to include tax in credit calculations, subtract it out
    if ($this->include_tax != 'true') $order_total -= $order->info['tax'];
    // if we are not supposed to include shipping amount in credit calcs, subtract it out
    if ($this->include_shipping != 'true') $order_total -= $order->info['shipping_cost'];
    $order_total = $order->info['total'];

    // check gv_amount in cart and do not allow GVs to pay for GVs
    $chk_gv_amount = 0;
    $chk_products = $_SESSION['cart']->get_products();
    for ($i=0, $n=sizeof($chk_products); $i<$n; $i++) {
      if (preg_match('/^GIFT/', addslashes($chk_products[$i]['model']))) {
        // determine how much GV was purchased
        $chk_gv_amount += ($chk_products[$i]['price'] * $chk_products[$i]['quantity']);
      }
    }
    // reduce Order Total less GVs
    $order_total = ($order_total - $chk_gv_amount);
//echo 'GV chk_gv_amount: ' . $chk_gv_amount . ' $order_total: ' . $order_total . '<br>';

    return $order_total;
  }
  /**
   * Enter description here...
   *
   * @return unknown
   */
  function check() {
    global $db;
    if (!isset($this->check)) {
      $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_GV_STATUS'");
      $this->check = $check_query->RecordCount();
    }

    return $this->check;
  }
  /**
   * Enter description here...
   *
   * @return unknown
   */
  function keys() {
    return array('MODULE_ORDER_TOTAL_GV_STATUS', 'MODULE_ORDER_TOTAL_GV_SORT_ORDER', 'MODULE_ORDER_TOTAL_GV_QUEUE', 'MODULE_ORDER_TOTAL_GV_INC_SHIPPING', 'MODULE_ORDER_TOTAL_GV_INC_TAX', 'MODULE_ORDER_TOTAL_GV_CALC_TAX', 'MODULE_ORDER_TOTAL_GV_TAX_CLASS', 'MODULE_ORDER_TOTAL_GV_CREDIT_TAX',  'MODULE_ORDER_TOTAL_GV_ORDER_STATUS_ID');
  }
  /**
   * Enter description here...
   *
   */
  function install() {
    global $db;
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('This module is installed', 'MODULE_ORDER_TOTAL_GV_STATUS', 'true', '', '6', '1','zen_cfg_select_option(array(\'true\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ORDER_TOTAL_GV_SORT_ORDER', '840', 'Sort order of display.', '6', '2', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Queue Purchases', 'MODULE_ORDER_TOTAL_GV_QUEUE', 'true', 'Do you want to queue purchases of the Gift Voucher?', '6', '3','zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function ,date_added) values ('Include Shipping', 'MODULE_ORDER_TOTAL_GV_INC_SHIPPING', 'true', 'Include Shipping in calculation', '6', '5', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function ,date_added) values ('Include Tax', 'MODULE_ORDER_TOTAL_GV_INC_TAX', 'false', 'Include Tax in calculation.', '6', '6','zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function ,date_added) values ('Re-calculate Tax', 'MODULE_ORDER_TOTAL_GV_CALC_TAX', 'None', 'Re-Calculate Tax', '6', '7','zen_cfg_select_option(array(\'None\', \'Standard\', \'Credit Note\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Tax Class', 'MODULE_ORDER_TOTAL_GV_TAX_CLASS', '0', 'Use the following tax class when treating Gift Voucher as Credit Note.', '6', '0', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function ,date_added) values ('Credit including Tax', 'MODULE_ORDER_TOTAL_GV_CREDIT_TAX', 'false', 'Add tax to purchased Gift Voucher when crediting to Account', '6', '8','zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_ORDER_TOTAL_GV_ORDER_STATUS_ID', '0', 'Set the status of orders made where GV covers full payment', '6', '0', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
  }
  /**
   * Enter description here...
   *
   */
  function remove() {
    global $db;
    $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
  }
}
