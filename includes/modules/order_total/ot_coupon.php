<?php
/**
 * ot_coupon order-total module
 *
 * @package orderTotal
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: ot_coupon.php 19063 2011-07-08 20:57:09Z wilt $
 */
/**
 * Order Total class  to handle discount coupons
 *
 */
class ot_coupon {
  /**
   * coupon title
   *
   * @var unknown_type
   */
  var $title;
  /**
   * Output used on checkout pages
   *
   * @var unknown_type
   */
  var $output;
  /**
   * Enter description here...
   *
   * @return ot_coupon
   */
  function ot_coupon() {
    $this->code = 'ot_coupon';
    $this->header = MODULE_ORDER_TOTAL_COUPON_HEADER;
    $this->title = MODULE_ORDER_TOTAL_COUPON_TITLE;
    $this->description = MODULE_ORDER_TOTAL_COUPON_DESCRIPTION;
    $this->user_prompt = '';
    $this->sort_order = MODULE_ORDER_TOTAL_COUPON_SORT_ORDER;
    $this->include_shipping = MODULE_ORDER_TOTAL_COUPON_INC_SHIPPING;
    $this->include_tax = MODULE_ORDER_TOTAL_COUPON_INC_TAX;
    $this->calculate_tax = MODULE_ORDER_TOTAL_COUPON_CALC_TAX;
    $this->tax_class  = MODULE_ORDER_TOTAL_COUPON_TAX_CLASS;
    $this->credit_class = true;
    $this->output = array();
    if (IS_ADMIN_FLAG === true) {
      if ($this->include_tax == 'true' && $this->calculate_tax != "None") {
        $this->title .= '<span class="alert">' . MODULE_ORDER_TOTAL_COUPON_INCLUDE_ERROR . '</span>';
      }
    }
  }
  /**
   * Method used to produce final figures for deductions. This information is used to produce the output<br>
   * shown on the checkout pages
   *
   */
  function process() {
    global $order, $currencies;
    $order_total = $this->get_order_total(isset($_SESSION['cc_id']) ? $_SESSION['cc_id'] : '');
    $od_amount = $this->calculate_deductions($order_total['total']);
    $this->deduction = $od_amount['total'];
    if ($od_amount['total'] > 0) {
      reset($order->info['tax_groups']);
      $tax = 0;
      while (list($key, $value) = each($order->info['tax_groups'])) {
        if ($od_amount['tax_groups'][$key]) {
          $order->info['tax_groups'][$key] -= $od_amount['tax_groups'][$key];
          $order->info['tax_groups'][$key] = zen_round($order->info['tax_groups'][$key], $currencies->get_decimal_places($_SESSION['currency']));
          $tax += $od_amount['tax_groups'][$key];
        }
      }
      // free shipping for free shipping 'S' or percentage off and free shipping 'E' or amount off and free shipping 'O'
      if ($od_amount['type'] == 'S' || $od_amount['type'] == 'E' || $od_amount['type'] == 'O') $order->info['shipping_cost'] = 0;
      $order->info['total'] = $order->info['total'] - $od_amount['total'];
      if (DISPLAY_PRICE_WITH_TAX != 'true') {
        $order->info['total'] -= $tax;
      }
      $order->info['tax'] = $order->info['tax'] - $tax;
      //      if ($this->calculate_tax == "Standard") $order->info['total'] -= $tax;
      if ($order->info['total'] < 0) $order->info['total'] = 0;
      $this->output[] = array('title' => $this->title . ': ' . $this->coupon_code . ' :',
                              'text' => '-' . $currencies->format($od_amount['total']),
                              'value' => $od_amount['total']);
    }
  }
  /**
   * Enter description here...
   *
   * @return unknown
   */
  function selection_test() {
    return false;
  }
  /**
   * Enter description here...
   *
   */
  function clear_posts() {
    unset($_POST['dc_redeem_code']);
    unset($_SESSION['cc_id']);
  }
  /**
   * Enter description here...
   *
   * @param unknown_type $order_total
   * @return unknown
   */
  function pre_confirmation_check($order_total) {
    global $order;
    $od_amount = $this->calculate_deductions($order_total);
//    print_r($od_amount);
    $order->info['total'] = $order->info['total'] - $od_amount['total'];
    if (DISPLAY_PRICE_WITH_TAX != 'true') {
      $order->info['total'] -= $tax;
    }
    return $od_amount['total'] + (DISPLAY_PRICE_WITH_TAX == 'true' ? 0 : $od_amount['tax']);
  }
  /**
   * Enter description here...
   *
   * @return unknown
   */
  function use_credit_amount() {
    return false;
  }
  /**
   * Enter description here...
   *
   * @return unknown
   */
  function credit_selection() {
    global $discount_coupon;
    // note the placement of the redeem code can be moved within the array on the instructions or the title
    $selection = array('id' => $this->code,
                       'module' => $this->title,
                       'redeem_instructions' => MODULE_ORDER_TOTAL_COUPON_REDEEM_INSTRUCTIONS . ($discount_coupon->fields['coupon_code'] != '' ? MODULE_ORDER_TOTAL_COUPON_REMOVE_INSTRUCTIONS : '') . ($discount_coupon->fields['coupon_code'] != '' ? MODULE_ORDER_TOTAL_COUPON_TEXT_CURRENT_CODE . '<a href="javascript:couponpopupWindow(\'' . zen_href_link(FILENAME_POPUP_COUPON_HELP, 'cID=' . $_SESSION['cc_id']) . '\')">' . $discount_coupon->fields['coupon_code'] . '</a><br /><br />' : ''),
                       'fields' => array(array('title' => MODULE_ORDER_TOTAL_COUPON_TEXT_ENTER_CODE,
                                               'field' => zen_draw_input_field('dc_redeem_code', '', 'id="disc-' . $this->code . '" onkeyup="submitFunction(0,0)"'),
                                               'tag' => 'disc-'.$this->code
                       )));
    return $selection;
  }
  /**
   * Enter description here...
   *
   */
  function collect_posts() {
    global $db, $currencies, $messageStack, $order;
    global $discount_coupon;
    // remove discount coupon by request
    if (isset($_POST['dc_redeem_code']) && strtoupper($_POST['dc_redeem_code']) == 'REMOVE') {
      unset($_POST['dc_redeem_code']);
      unset($_SESSION['cc_id']);
      $messageStack->add_session('checkout_payment', TEXT_REMOVE_REDEEM_COUPON, 'caution');
    }
//    print_r($_SESSION);
    // bof: Discount Coupon zoned always validate coupon for payment address changes
    // eof: Discount Coupon zoned always validate coupon for payment address changes
    if ((isset($_POST['dc_redeem_code']) && $_POST['dc_redeem_code'] != '') || (isset($discount_coupon->fields['coupon_code']) && $discount_coupon->fields['coupon_code'] != '')) {
      // set current Discount Coupon based on current or existing
      if (isset($_POST['dc_redeem_code']) && $discount_coupon->fields['coupon_code'] == '') {
        $dc_check = $_POST['dc_redeem_code'];
      } else {
        $dc_check = $discount_coupon->fields['coupon_code'];
      }



      $sql = "select coupon_id, coupon_amount, coupon_type, coupon_minimum_order, uses_per_coupon, uses_per_user,
              restrict_to_products, restrict_to_categories, coupon_zone_restriction
              from " . TABLE_COUPONS . "
              where coupon_code= :couponCodeEntered
              and coupon_active='Y'
              and coupon_type !='G'";

      $sql = $db->bindVars($sql, ':couponCodeEntered', $dc_check, 'string');

      $coupon_result=$db->Execute($sql);

      if ($coupon_result->fields['coupon_type'] != 'G') {

        if ($coupon_result->RecordCount() < 1 ) {
          $messageStack->add_session('redemptions', TEXT_INVALID_REDEEM_COUPON,'caution');
          $this->clear_posts();
          zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL',true, false));
        }
        $order_total = $this->get_order_total($coupon_result->fields['coupon_id']);

// left for total order amount vs qualified order amount just switch the commented lines
//        if ($order_total['totalFull'] < $coupon_result->fields['coupon_minimum_order']) {
        if (strval($order_total['orderTotal']) < $coupon_result->fields['coupon_minimum_order']) {

          $messageStack->add_session('redemptions', sprintf(TEXT_INVALID_REDEEM_COUPON_MINIMUM, $currencies->format($coupon_result->fields['coupon_minimum_order'])),'caution');
          $this->clear_posts();
          zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL',true, false));
        }

        // JTD - added missing code here to handle coupon product restrictions
        // look through the items in the cart to see if this coupon is valid for any item in the cart
        $products = $_SESSION['cart']->get_products();
        $foundvalid = true;

        if ($foundvalid == true) {
          $foundvalid = false;
          for ($i=0; $i<sizeof($products); $i++) {
            if (is_product_valid($products[$i]['id'], $coupon_result->fields['coupon_id'])) {
              $foundvalid = true;
              continue;
            }
          }
        }

        if (!$foundvalid) {
          $this->clear_posts();
        }

        if (!$foundvalid) zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'credit_class_error_code=' . $this->code . '&credit_class_error=' . urlencode(TEXT_INVALID_COUPON_PRODUCT . ' ' . $dc_check), 'SSL',true, false));
        // JTD - end of additions of missing code to handle coupon product restrictions

        $date_query=$db->Execute("select coupon_start_date from " . TABLE_COUPONS . "
                                  where coupon_start_date <= now() and
                                  coupon_code='" . zen_db_prepare_input($dc_check) . "'");

        if ($date_query->RecordCount() < 1 ) {
          $messageStack->add_session('redemptions', TEXT_INVALID_STARTDATE_COUPON,'caution');
          $this->clear_posts();
          zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
        }

        $date_query=$db->Execute("select coupon_expire_date from " . TABLE_COUPONS . "
                                  where coupon_expire_date >= now() and
                                  coupon_code='" . zen_db_prepare_input($dc_check) . "'");

        if ($date_query->RecordCount() < 1 ) {
          $messageStack->add_session('redemptions', TEXT_INVALID_FINISHDATE_COUPON,'caution');
          $this->clear_posts();
          zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
        }

        $coupon_count = $db->Execute("select coupon_id from " . TABLE_COUPON_REDEEM_TRACK . "
                                      where coupon_id = '" . (int)$coupon_result->fields['coupon_id']."'");

        $coupon_count_customer = $db->Execute("select coupon_id from " . TABLE_COUPON_REDEEM_TRACK . "
                                               where coupon_id = '" . $coupon_result->fields['coupon_id']."' and
                                               customer_id = '" . (int)$_SESSION['customer_id'] . "'");

        if ($coupon_count->RecordCount() >= $coupon_result->fields['uses_per_coupon'] && $coupon_result->fields['uses_per_coupon'] > 0) {
          $messageStack->add_session('redemptions', TEXT_INVALID_USES_COUPON . $coupon_result->fields['uses_per_coupon'] . TIMES ,'caution');
          $this->clear_posts();
          zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
        }

        if ($coupon_count_customer->RecordCount() >= $coupon_result->fields['uses_per_user'] && $coupon_result->fields['uses_per_user'] > 0) {
          $messageStack->add_session('redemptions', sprintf(TEXT_INVALID_USES_USER_COUPON, $dc_check) . $coupon_result->fields['uses_per_user'] . ($coupon_result->fields['uses_per_user'] == 1 ? TIME : TIMES) ,'caution');
          $this->clear_posts();
          zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
        }

        $_SESSION['cc_id'] = $coupon_result->fields['coupon_id'];
        if ($_SESSION['cc_id'] > 0) {
          $sql = "select coupon_id, coupon_amount, coupon_type, coupon_minimum_order, uses_per_coupon, uses_per_user,
                  restrict_to_products, restrict_to_categories, coupon_zone_restriction, coupon_code
                  from " . TABLE_COUPONS . "
                  where coupon_id= :couponIDEntered
                  and coupon_active='Y'";
          $sql = $db->bindVars($sql, ':couponIDEntered', $_SESSION['cc_id'], 'string');

          $coupon_result=$db->Execute($sql);

          $foundvalid = true;

          $check_flag = false;

          // base restrictions zone restrictions for Delivery or Billing address
          switch($coupon_result->fields['coupon_type']) {
            case 'S': // shipping
              // use delivery address
              $check_zone_country_id = $order->delivery['country']['id'];
              break;
            case 'F': // amount
              // use billing address
              $check_zone_country_id = $order->billing['country']['id'];
              break;
            case 'O': // amount off and free shipping
              // use delivery address
              $check_zone_country_id = $order->delivery['country']['id'];
              break;
            case 'P': // percentage
              // use billing address
              $check_zone_country_id = $order->billing['country']['id'];
              break;
            case 'E': // percentage and Free Shipping
              // use delivery address
              $check_zone_country_id = $order->delivery['country']['id'];
              break;
            default:
              // use billing address
              $check_zone_country_id = $order->billing['country']['id'];
              break;
          }

//          $sql = "select zone_id, zone_country_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . $coupon_result->fields['coupon_zone_restriction'] . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id";
          $sql = "select zone_id, zone_country_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . $coupon_result->fields['coupon_zone_restriction'] . "' and zone_country_id = '" . $check_zone_country_id . "' order by zone_id";
          $check = $db->Execute($sql);

          // base restrictions zone restrictions for Delivery or Billing address
          switch($coupon_result->fields['coupon_type']) {
            case 'S': // shipping
              // use delivery address
              $check_zone_id = $order->delivery['zone_id'];
              break;
            case 'F': // amount
              // use billing address
              $check_zone_id = $order->billing['zone_id'];
              break;
            case 'O': // amount off and free shipping
              // use delivery address
              $check_zone_id = $order->delivery['zone_id'];
              break;
            case 'P': // percentage
              // use billing address
              $check_zone_id = $order->billing['zone_id'];
              break;
            case 'E': // percentage and free shipping
              // use delivery address
              $check_zone_id = $order->delivery['zone_id'];
              break;
            default:
              // use billing address
              $check_zone_id = $order->billing['zone_id'];
              break;
          }

          if ($coupon_result->fields['coupon_zone_restriction'] > 0) {
            while (!$check->EOF) {
              if ($check->fields['zone_id'] < 1) {
                $check_flag = true;
                break;
//              } elseif ($check->fields['zone_id'] == $order->billing['zone_id']) {
              } elseif ($check->fields['zone_id'] == $check_zone_id) {
                $check_flag = true;
                break;
              }
              $check->MoveNext();
            }
            $foundvalid = $check_flag;
          }
          // remove if fails address validation
          if (!$foundvalid) {
            $messageStack->add_session('checkout_payment', TEXT_REMOVE_REDEEM_COUPON_ZONE, 'caution');
            $this->clear_posts();
            if (!$foundvalid) zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL',true, false));
          } else {
          //      if ($_POST['submit_redeem_coupon_x'] && !$_POST['gv_redeem_code']) zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'credit_class_error_code=' . $this->code . '&credit_class_error=' . urlencode(TEST_NO_REDEEM_CODE), 'SSL', true, false));
            $messageStack->add('checkout', TEXT_VALID_COUPON,'success');
          }
        }
      } else {
        $messageStack->add_session('redemptions', TEXT_INVALID_REDEEM_COUPON,'caution');
        $this->clear_posts();
        zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL',true, false));
      }

    }
  }
  /**
   * Enter description here...
   *
   * @param unknown_type $i
   * @return unknown
   */
  function update_credit_account($i) {
    return false;
  }
  /**
   * Enter description here...
   *
   */
  function apply_credit() {
    global $db, $insert_id;
    $cc_id = $_SESSION['cc_id'];
    if ($this->deduction !=0) {
      $db->Execute("insert into " . TABLE_COUPON_REDEEM_TRACK . "
                    (coupon_id, redeem_date, redeem_ip, customer_id, order_id)
                    values ('" . (int)$cc_id . "', now(), '" . $_SERVER['REMOTE_ADDR'] . "', '" . (int)$_SESSION['customer_id'] . "', '" . (int)$insert_id . "')");
    }
    $_SESSION['cc_id'] = "";
  }
  function calculate_deductions($order_total) {
    global $db, $order, $messageStack, $currencies;
    $currencyDecimalPlaces = $currencies->get_decimal_places($_SESSION['currency']);
    $od_amount = array('tax'=>0, 'total'=>0);
    if ($_SESSION['cc_id'])
    {
      $coupon = $db->Execute("select * from " . TABLE_COUPONS . " where coupon_id = '" . (int)$_SESSION['cc_id'] . "'");
      $this->coupon_code = $coupon->fields['coupon_code'];
      $orderTotalDetails = $this->get_order_total($_SESSION['cc_id']);
      if ($coupon->RecordCount() > 0 && $orderTotalDetails['orderTotal'] != 0 )
      {
// left for total order amount vs qualified order amount just switch the commented lines
//        if (strval($orderTotalDetails['totalFull']) >= $coupon->fields['coupon_minimum_order'])
        if (strval($orderTotalDetails['orderTotal']) >= $coupon->fields['coupon_minimum_order'])
        {
          switch($coupon->fields['coupon_type'])
          {
            case 'S': // Free Shipping
              $od_amount['total'] = $orderTotalDetails['shipping'];
              $od_amount['type'] = 'S';
              $od_amount['tax'] = ($this->calculate_tax == 'Standard') ? $orderTotalDetails['shippingTax'] : 0;
              if (isset($_SESSION['shipping_tax_description']) && $_SESSION['shipping_tax_description'] != '') {
                $od_amount['tax_groups'][$_SESSION['shipping_tax_description']] = $od_amount['tax'];
              }
              return $od_amount;
              break;
            case 'P': // percentage
              $od_amount['total'] = zen_round($orderTotalDetails['orderTotal']*($coupon->fields['coupon_amount']/100), $currencyDecimalPlaces);
              $od_amount['type'] = $coupon->fields['coupon_type'];
              $ratio = $od_amount['total']/$orderTotalDetails['orderTotal'];
              break;
            case 'E': // percentage & Free Shipping
              $od_amount['total'] = zen_round($orderTotalDetails['orderTotal']*($coupon->fields['coupon_amount']/100), $currencyDecimalPlaces);
              $od_amount['type'] = $coupon->fields['coupon_type'];
              $ratio = $od_amount['total']/$orderTotalDetails['orderTotal'];
              // add in Free Shipping
              $od_amount['total'] = $od_amount['total'] + $orderTotalDetails['shipping'];
              $od_amount['tax'] = ($this->calculate_tax == 'Standard') ? $orderTotalDetails['shippingTax'] : 0;
              if (isset($_SESSION['shipping_tax_description']) && $_SESSION['shipping_tax_description'] != '') {
                $od_amount['tax_groups'][$_SESSION['shipping_tax_description']] = $od_amount['tax'];
              }
              break;
            case 'F': // amount Off
              $od_amount['total'] = zen_round($coupon->fields['coupon_amount'] * ($orderTotalDetails['orderTotal']>0), $currencyDecimalPlaces);
              $od_amount['type'] = $coupon->fields['coupon_type']; // amount off 'F' or amount off and free shipping 'O'
              $ratio = $od_amount['total']/$orderTotalDetails['orderTotal'];
              break;
            case 'O': // amount off & Free Shipping
              $od_amount['total'] = zen_round($coupon->fields['coupon_amount'] * ($orderTotalDetails['orderTotal']>0), $currencyDecimalPlaces);
              $od_amount['type'] = $coupon->fields['coupon_type']; // amount off 'F' or amount off and free shipping 'O'
              $ratio = $od_amount['total']/$orderTotalDetails['orderTotal'];
              // add in Free Shipping
              $od_amount['total'] = $od_amount['total'] + $orderTotalDetails['shipping'];
              $od_amount['tax'] = ($this->calculate_tax == 'Standard') ? $orderTotalDetails['shippingTax'] : 0;
              if (isset($_SESSION['shipping_tax_description']) && $_SESSION['shipping_tax_description'] != '') {
                $od_amount['tax_groups'][$_SESSION['shipping_tax_description']] = $od_amount['tax'];
              }
              break;
          }
          switch ($this->calculate_tax)
          {
            case 'None':
              break;
            case 'Standard':
              if ($od_amount['total'] >= $orderTotalDetails['orderTotal']) $ratio = 1;
              foreach ($orderTotalDetails['orderTaxGroups'] as $key=>$value)
              {
                $od_amount['tax_groups'][$key] = zen_round($orderTotalDetails['orderTaxGroups'][$key] * $ratio, $currencyDecimalPlaces);
                $od_amount['tax'] += $od_amount['tax_groups'][$key];
                if ($od_amount['tax_groups'][$key] == 0) unset($od_amount['tax_groups'][$key]);
              }
              if (DISPLAY_PRICE_WITH_TAX == 'true' && $coupon->fields['coupon_type'] == 'F') $od_amount['total'] = $od_amount['total'] + $od_amount['tax'];
              break;
            case 'Credit Note':
              $tax_rate = zen_get_tax_rate($this->tax_class);
              $od_amount['tax'] = zen_calculate_tax($od_amount['total'], $tax_rate);
              $tax_description = zen_get_tax_description($this->tax_class);
              $od_amount['tax_groups'][$tax_description] = $od_amount['tax'];
          }
        }
      }
    }

//    print_r($order->info);
//    print_r($orderTotalDetails);echo "<br><br>";
//    echo 'RATIo = '. $ratio;
//    print_r($od_amount);
      return $od_amount;
  }
  function get_order_total($couponCode)
  {
    global $order;
    $orderTaxGroups = $order->info['tax_groups'];
    $orderTotalTax = $order->info['tax'];
    $orderTotal = $order->info['total'];
// left for total order amount vs qualified order amount just switch the commented lines
    $orderTotalFull = $orderTotal;
    $products = $_SESSION['cart']->get_products();
    for ($i=0; $i<sizeof($products); $i++) {
      if (!is_product_valid($products[$i]['id'], $couponCode)) {
        $products_tax = zen_get_tax_rate($products[$i]['tax_class_id']);
        $productsTaxAmount = (zen_calculate_tax($products[$i]['final_price'], $products_tax))   * $products[$i]['quantity'];
        $orderTotal -= $products[$i]['final_price'] * $products[$i]['quantity'];
        if ($this->include_tax == 'true') {
         $orderTotal -= $productsTaxAmount;
        }
        if (DISPLAY_PRICE_WITH_TAX == 'true')
        {
          $orderTotal -= $productsTaxAmount;
        }
        $orderTaxGroups[zen_get_tax_description($products[$i]['tax_class_id'])] -= $productsTaxAmount;
        $orderTotalTax -= (zen_calculate_tax($products[$i]['final_price'], zen_get_tax_rate($products[$i]['tax_class_id'])))   * $products[$i]['quantity'];
      }
    }
    if ($this->include_shipping != 'true')
    {
      $orderTotal -= $order->info['shipping_cost'];
      if (isset($_SESSION['shipping_tax_description']) && $_SESSION['shipping_tax_description'] != '')
      {
         $orderTaxGroups[$_SESSION['shipping_tax_description']] -= $order->info['shipping_tax'];
         $orderTotalTax -= $order->info['shipping_tax'];
      }
    }
    if (DISPLAY_PRICE_WITH_TAX != 'true')
    {
      $orderTotal -= $order->info['tax'];
    }
// left for total order amount vs qualified order amount - $orderTotalFull
    return array('totalFull'=>$orderTotalFull, 'orderTotal'=>$orderTotal, 'orderTaxGroups'=>$orderTaxGroups, 'orderTax'=>$orderTotalTax, 'shipping'=>$order->info['shipping_cost'], 'shippingTax'=>$order->info['shipping_tax']);
  }
  /**
   * Enter description here...
   *
   * @return unknown
   */
  function check() {
    global $db;
    if (!isset($this->check)) {
      $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_COUPON_STATUS'");
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
    return array('MODULE_ORDER_TOTAL_COUPON_STATUS', 'MODULE_ORDER_TOTAL_COUPON_SORT_ORDER', 'MODULE_ORDER_TOTAL_COUPON_INC_SHIPPING', 'MODULE_ORDER_TOTAL_COUPON_INC_TAX', 'MODULE_ORDER_TOTAL_COUPON_CALC_TAX', 'MODULE_ORDER_TOTAL_COUPON_TAX_CLASS');
  }
  /**
   * Enter description here...
   *
   */
  function install() {
    global $db;
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('This module is installed', 'MODULE_ORDER_TOTAL_COUPON_STATUS', 'true', '', '6', '1','zen_cfg_select_option(array(\'true\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ORDER_TOTAL_COUPON_SORT_ORDER', '280', 'Sort order of display.', '6', '2', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function ,date_added) values ('Include Shipping', 'MODULE_ORDER_TOTAL_COUPON_INC_SHIPPING', 'true', 'Include Shipping in calculation', '6', '5', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function ,date_added) values ('Include Tax', 'MODULE_ORDER_TOTAL_COUPON_INC_TAX', 'false', 'Include Tax in calculation.', '6', '6','zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function ,date_added) values ('Re-calculate Tax', 'MODULE_ORDER_TOTAL_COUPON_CALC_TAX', 'Standard', 'Re-Calculate Tax', '6', '7','zen_cfg_select_option(array(\'None\', \'Standard\', \'Credit Note\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Tax Class', 'MODULE_ORDER_TOTAL_COUPON_TAX_CLASS', '0', 'Use the following tax class when treating Discount Coupon as Credit Note.', '6', '0', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
  }
  /**
   * Enter description here...
   *
   */
  function remove() {
    global $db;
    $keys = '';
    $keys_array = $this->keys();
    for ($i=0; $i<sizeof($keys_array); $i++) {
      $keys .= "'" . $keys_array[$i] . "',";
    }
    $keys = substr($keys, 0, -1);

    $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in (" . $keys . ")");
  }
}
