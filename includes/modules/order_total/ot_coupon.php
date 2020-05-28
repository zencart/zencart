<?php
/**
 * ot_coupon order-total module
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2020 May 18 Modified in v1.5.7 $
 */
/**
 * Order Total class  to handle discount coupons
 *
 */
class ot_coupon {
  /**
   * coupon title
   *
   * @var string
   */
  var $title;
  /**
   * Output used on checkout pages
   *
   * @var string
   */
  var $output;
  /**
   * Enter description here...
   *
   * @return ot_coupon
   */
  function __construct() {
    $this->code = 'ot_coupon';
    $this->header = MODULE_ORDER_TOTAL_COUPON_HEADER;
    $this->title = MODULE_ORDER_TOTAL_COUPON_TITLE;
    $this->description = MODULE_ORDER_TOTAL_COUPON_DESCRIPTION;
    $this->user_prompt = '';
    $this->sort_order = defined('MODULE_ORDER_TOTAL_COUPON_SORT_ORDER') ? MODULE_ORDER_TOTAL_COUPON_SORT_ORDER : null;
    if (null === $this->sort_order) return false;

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
    $od_amount = array('tax'=>0, 'total'=>0);
    if ($order_total > 0) {
       $od_amount = $this->calculate_deductions();
    }
    $this->deduction = $od_amount['total'];
    if ($od_amount['total'] > 0) {
      $tax = 0;
      foreach($order->info['tax_groups'] as $key => $value) {
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
    $od_amount = array('tax'=>0, 'total'=>0);
    if ($order_total > 0) {
       $od_amount = $this->calculate_deductions();
    }
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
    function credit_selection()
    {
        global $discount_coupon, $request_type;

        $couponLink = '';
        if (isset($discount_coupon->fields['coupon_code']) && isset($_SESSION['cc_id'])) {
            $coupon_code = $discount_coupon->fields['coupon_code'];
            $couponLink = '<a href="javascript:couponpopupWindow(\'' . 
              zen_href_link(FILENAME_POPUP_COUPON_HELP, 'cID=' . $_SESSION['cc_id'], $request_type) .
              '\')">' . $coupon_code . '</a>';
        }
        // note the placement of the redeem code can be moved within the array on the instructions or the title
        $selection = array(
            'id' => $this->code,
            'module' => $this->title,
            'redeem_instructions' => MODULE_ORDER_TOTAL_COUPON_REDEEM_INSTRUCTIONS .
                MODULE_ORDER_TOTAL_COUPON_REMOVE_INSTRUCTIONS .
                '<p>' . MODULE_ORDER_TOTAL_COUPON_TEXT_CURRENT_CODE . $couponLink . '</p><br />',
            'fields' => array(
                array(
                    'title' => MODULE_ORDER_TOTAL_COUPON_TEXT_ENTER_CODE,
                    'field' => zen_draw_input_field('dc_redeem_code', '', 'id="disc-' . $this->code . '" onkeyup="submitFunction(0,0)"'),
                    'tag' => 'disc-' . $this->code
                )
            )
        );

        return $selection;
    }
  /**
   * Enter description here...
   *
   */
  function collect_posts() {
    global $db, $currencies, $messageStack, $order;
    global $discount_coupon;
    $_POST['dc_redeem_code'] = isset($_POST['dc_redeem_code']) ? trim($_POST['dc_redeem_code']) : '';

    if (!defined('TEXT_COMMAND_TO_DELETE_CURRENT_COUPON_FROM_ORDER')) define('TEXT_COMMAND_TO_DELETE_CURRENT_COUPON_FROM_ORDER', 'REMOVE');
    // remove discount coupon by request
    if (isset($_POST['dc_redeem_code']) && strtoupper($_POST['dc_redeem_code']) == TEXT_COMMAND_TO_DELETE_CURRENT_COUPON_FROM_ORDER) {
      unset($_POST['dc_redeem_code']);
      unset($_SESSION['cc_id']);

      $GLOBALS['zco_notifier']->notify(
          'NOTIFY_OT_COUPON_COUPON_REMOVED'
       );

      $messageStack->add_session('checkout_payment', TEXT_REMOVE_REDEEM_COUPON, 'caution');
    }

    if ((isset($_POST['dc_redeem_code']) && $_POST['dc_redeem_code'] != '') || (isset($discount_coupon->fields['coupon_code']) && $discount_coupon->fields['coupon_code'] != '')) {
      // set current Discount Coupon based on current or existing
      if ($discount_coupon != null) {
        if (isset($_POST['dc_redeem_code']) && $discount_coupon->fields['coupon_code'] == '') {
          $dc_check = $_POST['dc_redeem_code'];
        } else {
          $dc_check = $discount_coupon->fields['coupon_code'];
        }
      } else {
        if (isset($_POST['dc_redeem_code'])) {
          $dc_check = $_POST['dc_redeem_code'];
        } else if ($discount_coupon != null) {
          $dc_check = $discount_coupon->fields['coupon_code'];
        } else {
          $dc_check = "UNKNOWN_COUPON";
        }
      }



      $sql = "SELECT coupon_id, coupon_amount, coupon_type, coupon_minimum_order, uses_per_coupon, uses_per_user,
              restrict_to_products, restrict_to_categories, coupon_zone_restriction, coupon_calc_base, coupon_order_limit
              FROM " . TABLE_COUPONS . "
              WHERE coupon_code= :couponCodeEntered
              AND coupon_active='Y'
              AND coupon_type !='G'";

      $sql = $db->bindVars($sql, ':couponCodeEntered', $dc_check, 'string');

      $coupon_result=$db->Execute($sql);

      if (!$coupon_result->EOF) {

        if ($coupon_result->RecordCount() < 1 ) {
          $messageStack->add_session('redemptions', sprintf(TEXT_INVALID_REDEEM_COUPON, $dc_check),'caution');
          $this->clear_posts();
          zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL',true, false));
        }

        $GLOBALS['zco_notifier']->notify(
            'NOTIFY_OT_COUPON_COUPON_INFO',
            array(
                'coupon_result' => $coupon_result->fields,
                'code' => $dc_check
            )
        );

        //$order_total = $this->get_order_total($coupon_result->fields['coupon_id']);

        // display all error messages at once
        $error_issues = 0;
        $dc_link_count = 0;
        $dc_link = '<a href="javascript:couponpopupWindow(\'' . zen_href_link(FILENAME_POPUP_COUPON_HELP, 'cID=' . $coupon_result->fields['coupon_id']) . '\')" title="' . TEXT_COUPON_LINK_TITLE . '">' . $dc_check . '</a>';
        $orderTotalDetails = $this->get_order_total($coupon_result->fields['coupon_id']);
        if ($coupon_result->fields['coupon_calc_base'] == 0) {
          $coupon_total_minimum = $orderTotalDetails['orderTotal']; // restricted products
          $coupon_total = $orderTotalDetails['orderTotal']; // restricted products
        }
        if ($coupon_result->fields['coupon_calc_base'] == 1) {
          $coupon_total_minimum = $orderTotalDetails['orderTotal']; // restricted products
          $coupon_total = $orderTotalDetails['totalFull']; // all products
        }
//echo 'Product: ' . $orderTotalDetails['orderTotal'] . ' Order: ' . $orderTotalDetails['totalFull'] . ' $coupon_total: ' . $coupon_total . '<br>';
// left for total order amount vs qualified order amount just switch the commented lines
//        if ($order_total['totalFull'] < $coupon_result->fields['coupon_minimum_order'])
//        if (strval($order_total['orderTotal']) > 0 && strval($order_total['orderTotal']) < $coupon_result->fields['coupon_minimum_order'])
//        if ($coupon_result->fields['coupon_minimum_order'] > 0 && strval($order_total['orderTotal']) < $coupon_result->fields['coupon_minimum_order'])
//        if (strval($coupon_total) > 0 && strval($coupon_total) < $coupon_result->fields['coupon_minimum_order'])
        if (strval($coupon_total) > 0 && strval($coupon_total_minimum) < $coupon_result->fields['coupon_minimum_order'])
        {
          // $order_total['orderTotal'] . ' vs ' . $order_total['totalFull']
          $messageStack->add_session('redemptions', sprintf(TEXT_INVALID_REDEEM_COUPON_MINIMUM, ($dc_link_count === 0 ? $dc_link : $dc_check), $currencies->format($coupon_result->fields['coupon_minimum_order'])), 'caution');
          $error_issues ++;
          $dc_link_count ++;

//          $this->clear_posts();
//          zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL',true, false));
        }

        // JTD - handle coupon product restrictions
        // look through the items in the cart to see if this coupon is valid for any item in the cart
        $products = $_SESSION['cart']->get_products();
        $foundvalid = true;

        if ($foundvalid == true) {
          $foundvalid = false;
          for ($i=0; $i<count($products); $i++) {
            if (is_product_valid($products[$i]['id'], $coupon_result->fields['coupon_id'])) {
              $foundvalid = true;
              continue;
            }
          }
        }
        if ($foundvalid == true) {
          // check if products on special or sale are valid
          $foundvalid = false;
          for ($i=0, $n=count($products); $i<$n; $i++) {
            if (is_coupon_valid_for_sales($products[$i]['id'], $coupon_result->fields['coupon_id'])) {
              $foundvalid = true;
              continue;
            }
          }
        }
        if (!$foundvalid) {
          $this->clear_posts();
        }
//        if (!$foundvalid) zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'credit_class_error_code=' . $this->code . '&credit_class_error=' . urlencode(TEXT_INVALID_COUPON_PRODUCT . ' ' . $dc_check), 'SSL',true, false));
        if (!$foundvalid) {
          $messageStack->add_session('redemptions', sprintf(TEXT_INVALID_COUPON_PRODUCT, ($dc_link_count === 0 ? $dc_link : $dc_check)), 'caution');
          $error_issues ++;
          $dc_link_count ++;
        }

// validate number of Orders
        if ($coupon_result->fields['coupon_order_limit'] > 0) {
          $sql = "SELECT orders_id FROM " . TABLE_ORDERS . " WHERE customers_id = '" . $_SESSION['customer_id'] . "'";
          $chk_customer_orders = $db->Execute($sql);
          if ($chk_customer_orders->RecordCount() > $coupon_result->fields['coupon_order_limit']) {
            $messageStack->add_session('redemptions', sprintf(TEXT_INVALID_COUPON_ORDER_LIMIT, ($dc_link_count === 0 ? $dc_link : $dc_check), $coupon_result->fields['coupon_order_limit']), 'caution');
            $error_issues ++;
            $dc_link_count ++;
          }
        }

        // JTD - end of handling coupon product restrictions

        $date_query=$db->Execute("SELECT coupon_start_date FROM " . TABLE_COUPONS . "
                                  WHERE coupon_code='" . zen_db_prepare_input($dc_check) . "' LIMIT 1");

        if (date("Y-m-d H:i:s") < $date_query->fields['coupon_start_date']) {
          $messageStack->add_session('redemptions', sprintf(TEXT_INVALID_STARTDATE_COUPON, ($dc_link_count === 0 ? $dc_link : $dc_check), zen_date_short($date_query->fields['coupon_start_date'])),'caution');
          $error_issues ++;
          $dc_link_count ++;
//          $this->clear_posts();
//          zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
        }

        $date_query=$db->Execute("SELECT coupon_expire_date FROM " . TABLE_COUPONS . "
                                  WHERE coupon_code='" . zen_db_prepare_input($dc_check) . "' LIMIT 1");

          if (date("Y-m-d H:i:s") >= $date_query->fields['coupon_expire_date']) {
          $messageStack->add_session('redemptions', sprintf(TEXT_INVALID_FINISHDATE_COUPON, ($dc_link_count === 0 ? $dc_link : $dc_check), zen_date_short($date_query->fields['coupon_expire_date'])),'caution');
          $error_issues ++;
          $dc_link_count ++;
//          $this->clear_posts();
//          zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
        }

        $coupon_count = $db->Execute("SELECT coupon_id FROM " . TABLE_COUPON_REDEEM_TRACK . "
                                      WHERE coupon_id = '" . (int)$coupon_result->fields['coupon_id']."'");

        $coupon_count_customer = $db->Execute("SELECT coupon_id FROM " . TABLE_COUPON_REDEEM_TRACK . "
                                               WHERE coupon_id = '" . $coupon_result->fields['coupon_id']."' AND
                                               customer_id = '" . (int)$_SESSION['customer_id'] . "'");

        $coupon_uses_per_user_exceeded = ($coupon_count_customer->RecordCount() >= $coupon_result->fields['uses_per_user'] && $coupon_result->fields['uses_per_user'] > 0);
        $GLOBALS['zco_notifier']->notify('NOTIFY_OT_COUPON_USES_PER_USER_CHECK', $coupon_result->fields, $coupon_uses_per_user_exceeded);
        if ($coupon_uses_per_user_exceeded) {
          $messageStack->add_session('redemptions', sprintf(TEXT_INVALID_USES_COUPON , ($dc_link_count === 0 ? $dc_link : $dc_check), $coupon_result->fields['uses_per_coupon']) ,'caution');
          $error_issues ++;
          $dc_link_count ++;
//          $this->clear_posts();
//          zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
        }

        if ($coupon_count_customer->RecordCount() >= $coupon_result->fields['uses_per_user'] && $coupon_result->fields['uses_per_user'] > 0) {
          $messageStack->add_session('redemptions', sprintf(TEXT_INVALID_USES_USER_COUPON, ($dc_link_count === 0 ? $dc_link : $dc_check), $coupon_result->fields['uses_per_user']) ,'caution');
          $error_issues ++;
          $dc_link_count ++;
//          $this->clear_posts();
//          zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
        }

        $_SESSION['cc_id'] = $coupon_result->fields['coupon_id'];
        if ($_SESSION['cc_id'] > 0) {
          $sql = "SELECT coupon_id, coupon_amount, coupon_type, coupon_minimum_order, uses_per_coupon, uses_per_user,
                  restrict_to_products, restrict_to_categories, coupon_zone_restriction, coupon_code
                  FROM " . TABLE_COUPONS . "
                  WHERE coupon_id= :couponIDEntered
                  AND coupon_active='Y'";
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

          $sql = "SELECT zone_id, zone_country_id FROM " . TABLE_ZONES_TO_GEO_ZONES . " WHERE geo_zone_id = '" . $coupon_result->fields['coupon_zone_restriction'] . "' AND zone_country_id = '" . (int)$check_zone_country_id . "' ORDER BY zone_id";
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
            $messageStack->add_session('redemptions', sprintf(TEXT_REMOVE_REDEEM_COUPON_ZONE , ($dc_link_count === 0 ? $dc_link : $dc_check)), 'caution');
            $dc_link_count ++;
          }
          // display all error messages
          if ($error_issues > 0 || !$foundvalid) {
            $this->clear_posts();
            zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
          }

          // remove if fails address validation
          if ($foundvalid) {
          //      if ($_POST['submit_redeem_coupon_x'] && !$_POST['gv_redeem_code']) zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'credit_class_error_code=' . $this->code . '&credit_class_error=' . urlencode(TEST_NO_REDEEM_CODE), 'SSL', true, false));
            $messageStack->add('checkout', TEXT_VALID_COUPON,'success');
          }
        }
      } else {
        $messageStack->add_session('redemptions', sprintf(TEXT_INVALID_REDEEM_COUPON, $dc_check),'caution');
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
    $cc_id = empty($_SESSION['cc_id']) ? 0 : $_SESSION['cc_id'];
    if ($this->deduction !=0) {
      $db->Execute("INSERT INTO " . TABLE_COUPON_REDEEM_TRACK . "
                    (coupon_id, redeem_date, redeem_ip, customer_id, order_id)
                    VALUES ('" . (int)$cc_id . "', now(), '" . $_SERVER['REMOTE_ADDR'] . "', '" . (int)$_SESSION['customer_id'] . "', '" . (int)$insert_id . "')");
    }
    $_SESSION['cc_id'] = "";
  }
  function calculate_deductions() {
    global $db, $order, $messageStack, $currencies;
    $currencyDecimalPlaces = $currencies->get_decimal_places($_SESSION['currency']);
    $od_amount = array('tax'=>0, 'total'=>0);
    if (!empty($_SESSION['cc_id']))
    {
      $coupon = $db->Execute("SELECT * FROM " . TABLE_COUPONS . " WHERE coupon_id = '" . (int)$_SESSION['cc_id'] . "'");
      $this->coupon_code = $coupon->fields['coupon_code'];
      $orderTotalDetails = $this->get_order_total($_SESSION['cc_id']);
// left for total order amount ($orderTotalDetails['totalFull']) vs qualified order amount ($order_total['orderTotal']) just switch the commented lines 2 of 3
      if ($coupon->fields['coupon_calc_base'] == 0) {
        $coupon_total_minimum = $orderTotalDetails['orderTotal']; // restricted products
        $coupon_total = $orderTotalDetails['orderTotal']; // restricted products
      }
      if ($coupon->fields['coupon_calc_base'] == 1) {
        $coupon_total_minimum = $orderTotalDetails['orderTotal']; // restricted products
        $coupon_total = $orderTotalDetails['totalFull']; // all products
      }
//echo 'ot_coupon coupon_total: ' . $coupon->fields['coupon_calc_base'] . '<br>$orderTotalDetails[orderTotal]: ' . $orderTotalDetails['orderTotal'] . '<br>$orderTotalDetails[totalFull]: ' . $orderTotalDetails['totalFull'] . '<br>$coupon_total: ' . $coupon_total . '<br><br>$coupon->fields[coupon_minimum_order]: ' . $coupon->fields['coupon_minimum_order'] . '<br>$coupon_total_minimum: ' . $coupon_total_minimum . '<br>';
// @@TODO - adjust all Totals to use $coupon_total but strong review for what total applies where for Percentage, Amount, etc.
      if ($coupon->RecordCount() > 0 && $orderTotalDetails['orderTotal'] != 0 ) {

          if (strval($coupon_total_minimum) >= $coupon->fields['coupon_minimum_order']) {
          $coupon_product_count = 1;
          if ($coupon->fields['coupon_product_count'] && ($coupon->fields['coupon_type'] == 'F' || $coupon->fields['coupon_type'] == 'O')) {
            $products = $_SESSION['cart']->get_products();
            $coupon_product_count = 0;
            for ($i=0, $n=count($products); $i<$n; $i++) {
              if (is_product_valid($products[$i]['id'], $coupon->fields['coupon_id'])) {
                $coupon_product_count += $_SESSION['cart']->get_quantity($products[$i]['id']);
              }
            }
          //  $messageStack->add_session('checkout_payment', 'Coupon cont: ' . $coupon_product_count, 'caution');
          }
          $coupon_is_free_shipping = false;
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
//              $od_amount['total'] = zen_round($orderTotalDetails['orderTotal']*($coupon->fields['coupon_amount']/100), $currencyDecimalPlaces);
              $od_amount['total'] = zen_round($coupon_total*($coupon->fields['coupon_amount']/100), $currencyDecimalPlaces);
              $od_amount['type'] = $coupon->fields['coupon_type'];
//              $ratio = $od_amount['total']/$orderTotalDetails['orderTotal'];
              $ratio = $od_amount['total']/$coupon_total;
              break;
            case 'E': // percentage & Free Shipping
//              $od_amount['total'] = zen_round($orderTotalDetails['orderTotal']*($coupon->fields['coupon_amount']/100), $currencyDecimalPlaces);
              $od_amount['total'] = zen_round($coupon_total*($coupon->fields['coupon_amount']/100), $currencyDecimalPlaces);
              $od_amount['type'] = $coupon->fields['coupon_type'];
              // add in Free Shipping
              $coupon_is_free_shipping = true;
              $od_amount['tax'] = ($this->calculate_tax == 'Standard') ? $orderTotalDetails['shippingTax'] : 0;
              $ratio = $od_amount['total']/$coupon_total;
              if (isset($_SESSION['shipping_tax_description']) && $_SESSION['shipping_tax_description'] != '') {
                $od_amount['tax_groups'][$_SESSION['shipping_tax_description']] = $od_amount['tax'];
              }
              break;
            case 'F': // amount Off
//              $od_amount['total'] = zen_round($coupon->fields['coupon_amount'] * ($orderTotalDetails['orderTotal']>0), $currencyDecimalPlaces);
                $od_amount['total'] = zen_round(($coupon->fields['coupon_amount'] > $orderTotalDetails['orderTotal'] ? $orderTotalDetails['orderTotal'] : $coupon->fields['coupon_amount']) * ($orderTotalDetails['orderTotal']>0) * $coupon_product_count, $currencyDecimalPlaces);
                $od_amount['type'] = $coupon->fields['coupon_type']; // amount off 'F' or amount off and free shipping 'O'
//              $ratio = $od_amount['total']/$orderTotalDetails['orderTotal'];
              $ratio = $od_amount['total']/$coupon_total;
              break;
            case 'O': // amount off & Free Shipping
//              $od_amount['total'] = zen_round($coupon->fields['coupon_amount'] * ($orderTotalDetails['orderTotal']>0), $currencyDecimalPlaces);
              $od_amount['total'] = zen_round(($coupon->fields['coupon_amount'] > $orderTotalDetails['orderTotal'] ? $orderTotalDetails['orderTotal'] : $coupon->fields['coupon_amount']) * ($orderTotalDetails['orderTotal']>0) * $coupon_product_count, $currencyDecimalPlaces);
              //$od_amount['total'] = zen_round($coupon->fields['coupon_amount'] * ($coupon_total>0), $currencyDecimalPlaces);
              $od_amount['type'] = $coupon->fields['coupon_type']; // amount off 'F' or amount off and free shipping 'O'
              // add in Free Shipping
              $coupon_is_free_shipping = true;
              $od_amount['tax'] = ($this->calculate_tax == 'Standard') ? $orderTotalDetails['shippingTax'] : 0;
              $ratio = $od_amount['total']/$coupon_total;
              if (isset($_SESSION['shipping_tax_description']) && $_SESSION['shipping_tax_description'] != '') {
                $od_amount['tax_groups'][$_SESSION['shipping_tax_description']] = $od_amount['tax'];
              }

              break;
          }
//@@TODO - Standard and Credit_Note
          switch ($this->calculate_tax)
          {
            case 'None':
              break;
            case 'Standard':
              if ($od_amount['total'] >= $orderTotalDetails['orderTotal']) $ratio = 1;
              foreach ($orderTotalDetails['orderTaxGroups'] as $key=>$value)
              {
                $this_tax = $orderTotalDetails['orderTaxGroups'][$key]; 
                if ($this->include_shipping != 'true') {
                   if (isset($_SESSION['shipping_tax_description']) && $_SESSION['shipping_tax_description'] == $key) {
                     $this_tax -= $orderTotalDetails['shippingTax']; 
                   }
                }
                $od_amount['tax_groups'][$key] = zen_round($this_tax * $ratio, $currencyDecimalPlaces); 
                $od_amount['tax'] += $od_amount['tax_groups'][$key];
              }
              if (DISPLAY_PRICE_WITH_TAX == 'true' && $coupon->fields['coupon_type'] == 'F') $od_amount['total'] = $od_amount['total'] + $od_amount['tax'];
              break;
            case 'Credit Note':
              $tax_rate = zen_get_tax_rate($this->tax_class);
              $od_amount['tax'] = zen_calculate_tax($od_amount['total'], $tax_rate);
              $tax_description = zen_get_tax_description($this->tax_class);
              $od_amount['tax_groups'][$tax_description] = $od_amount['tax'];
          }
          if ($coupon_is_free_shipping) {
              $od_amount['total'] += $orderTotalDetails['shipping'];
          }
        }
      }

      // -----
      // Let an observer know that the coupon-related calculations have finished, providing read-only
      // copies of (a) the base coupon information, (b) the results from 'get_order_total' and this
      // method's return values.
      //
      $GLOBALS['zco_notifier']->notify(
        'NOTIFY_OT_COUPON_CALCS_FINISHED',
        array(
            'coupon' => $coupon,
            'order_totals' => $orderTotalDetails,
            'od_amount' => $od_amount,
        )
      );
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
    $products = $_SESSION['cart']->get_products();
    for ($i=0, $n=count($products); $i<$n; $i++) {
      $is_product_valid = (is_product_valid($products[$i]['id'], $couponCode) && is_coupon_valid_for_sales($products[$i]['id'], $couponCode));
      $GLOBALS['zco_notifier']->notify(
        'NOTIFY_OT_COUPON_PRODUCT_VALIDITY',
        array(
            'is_product_valid' => $is_product_valid,
            'i' => $i
        )
      );
      if (!$is_product_valid) {
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
         $orderTotalTax -= $order->info['shipping_tax'];
      }
    }
    if (DISPLAY_PRICE_WITH_TAX != 'true')
    {
      $orderTotal -= $order->info['tax'];
    }

    // change what total is used for Discount Coupon Minimum
    $orderTotalFull = $order->info['total'];
    //echo 'Current $orderTotalFull: ' . $orderTotalFull . ' shipping_cost: ' . $order->info['shipping_cost'] . '<br>';
    $orderTotalFull -= $order->info['shipping_cost'];
    //echo 'Current $orderTotalFull less shipping: ' . $orderTotalFull . '<br>';
    $orderTotalFull -= $orderTotalTax;
    //echo 'Current $orderTotalFull less taxes: ' . $orderTotalFull . '<br>';
    // left for total order amount ($orderTotalDetails['totalFull']) vs qualified order amount ($order_total['orderTotal']) - to include both in array
    // add total order amount ($orderTotalFull) to array for $order_total['totalFull'] vs $order_total['orderTotal']
    return array('totalFull'=>$orderTotalFull,'orderTotal'=>$orderTotal, 'orderTaxGroups'=>$orderTaxGroups, 'orderTax'=>$orderTotalTax, 'shipping'=>$order->info['shipping_cost'], 'shippingTax'=>$order->info['shipping_tax']);
  }
  /**
   * Enter description here...
   *
   * @return unknown
   */
  function check() {
    global $db;
    if (!isset($this->check)) {
      $check_query = $db->Execute("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_ORDER_TOTAL_COUPON_STATUS'");
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
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('This module is installed', 'MODULE_ORDER_TOTAL_COUPON_STATUS', 'true', '', '6', '1','zen_cfg_select_option(array(\'true\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Sort Order', 'MODULE_ORDER_TOTAL_COUPON_SORT_ORDER', '280', 'Sort order of display.', '6', '2', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function ,date_added) VALUES ('Include Shipping', 'MODULE_ORDER_TOTAL_COUPON_INC_SHIPPING', 'true', 'Include Shipping in calculation', '6', '5', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function ,date_added) VALUES ('Include Tax', 'MODULE_ORDER_TOTAL_COUPON_INC_TAX', 'false', 'Include Tax in calculation.', '6', '6','zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function ,date_added) VALUES ('Re-calculate Tax', 'MODULE_ORDER_TOTAL_COUPON_CALC_TAX', 'Standard', 'Re-Calculate Tax', '6', '7','zen_cfg_select_option(array(\'None\', \'Standard\', \'Credit Note\'), ', now())");
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Tax Class', 'MODULE_ORDER_TOTAL_COUPON_TAX_CLASS', '0', 'Use the following tax class when treating Discount Coupon as Credit Note.', '6', '0', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
  }
  /**
   * Enter description here...
   *
   */
  function remove() {
    global $db;
    $keys = '';
    $keys_array = $this->keys();
    for ($i=0, $n=count($keys_array); $i<$n; $i++) {
      $keys .= "'" . $keys_array[$i] . "',";
    }
    $keys = substr($keys, 0, -1);

    $db->Execute("DELETE FROM " . TABLE_CONFIGURATION . " where configuration_key IN (" . $keys . ")");
  }
}
