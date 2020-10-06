<?php
/**
 * zcAjaxPayment
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2019 Jul 23 Modified in v1.5.7 $
 */
class zcAjaxPayment extends base
{
  /**
   * Test whether the selected payment module "does" the "CollectsCardDataOnsite" method
   */
  public function doesCollectsCardDataOnsite()
  {
    require_once (DIR_WS_CLASSES.'payment.php');
    $retVal = false;
    $payment = new payment ($_POST['paymentValue']);
    if (isset ($payment->selected_module)) {
      if ($payment->paymentClass->collectsCardDataOnsite===true) {
        $retVal = true;
      }
    }
    return (array(
        'data' => $retVal
    ));
  }

  /**
   * Build replacement confirmation page, which doesn't transmit sensitive CHD
   */
  public function prepareConfirmation()
  {
    global $messageStack, $template, $breadcrumb, $template_dir_select, $template_dir, $language_page_directory, $currencies, $order, $zco_notifier, $db, $current_page_base, $order_total_modules, $credit_covers;
    $_GET['main_page'] = $current_page_base = $current_page = FILENAME_CHECKOUT_CONFIRMATION;
    if ($_SESSION['cart']->count_contents ()<=0) {
      zen_redirect (zen_href_link (FILENAME_TIME_OUT));
    }
    if (!zen_is_logged_in()) {
      $_SESSION['navigation']->set_snapshot (array(
          'mode' => 'SSL',
          'page' => FILENAME_CHECKOUT_PAYMENT
      ));
      zen_redirect (zen_href_link (FILENAME_LOGIN, '', 'SSL'));
    } else {
      // validate customer
      if (zen_get_customer_validate_session ($_SESSION['customer_id'])==false) {
        $_SESSION['navigation']->set_snapshot ();
        zen_redirect (zen_href_link (FILENAME_LOGIN, '', 'SSL'));
      }
    }

    // avoid hack attempts during the checkout procedure by checking the internal cartID
    if (isset ($_SESSION['cart']->cartID)&&$_SESSION['cartID']) {
      if ($_SESSION['cart']->cartID!=$_SESSION['cartID']) {
        zen_redirect (zen_href_link (FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
      }
    }

    // if no shipping method has been selected, redirect the customer to the shipping method selection page
    if (!isset ($_SESSION['shipping'])) {
      zen_redirect (zen_href_link (FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
    }
    if (isset ($_SESSION['shipping']['id']) && $_SESSION['shipping']['id']=='free_free'
      && $_SESSION['cart']->get_content_type () != 'virtual'
      && defined ('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true'
      && defined ('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER') && $_SESSION['cart']->show_total () < MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER) {
      zen_redirect (zen_href_link (FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
    }

    if (isset ($_POST['payment'])) $_SESSION['payment'] = $_POST['payment'];

    $_SESSION['comments'] = $_POST['comments'];

    if (DISPLAY_CONDITIONS_ON_CHECKOUT=='true') {
      if (!isset ($_POST['conditions'])||($_POST['conditions']!='1')) {
        $messageStack->add_session ('checkout_payment', ERROR_CONDITIONS_NOT_ACCEPTED, 'error');
      }
    }
    // load the selected payment module
    require (DIR_WS_CLASSES.'payment.php');
    $payment_modules = new payment ($_POST['payment']);
    $payment_modules->update_status ();
    if (($_POST['payment']==''||!is_object ($payment_modules->paymentClass))&&$credit_covers===FALSE) {
      $messageStack->add_session ('checkout_payment', ERROR_NO_PAYMENT_MODULE_SELECTED, 'error');
    }
    $GLOBALS[$_POST['payment']] = $payment_modules->paymentClass;

    require (DIR_WS_CLASSES.'order.php');
    $order = new order ();
    // load the selected shipping module
    require (DIR_WS_CLASSES.'shipping.php');
    $shipping_modules = new shipping ($_SESSION['shipping']);

    require (DIR_WS_CLASSES.'order_total.php');
    $order_total_modules = new order_total ();
    $order_total_modules->collect_posts ();
    $order_total_modules->pre_confirmation_check ();

    if (!isset ($credit_covers)) $credit_covers = FALSE;
    if ($credit_covers) {
      unset ($_SESSION['payment']);
      $_SESSION['payment'] = '';
    }

    if (is_array ($payment_modules->modules)) {
      $payment_modules->pre_confirmation_check ();
    }

    if ($messageStack->size ('checkout_payment')>0) {
      zen_redirect (zen_href_link (FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
    }

    // Stock Check
    $flagAnyOutOfStock = false;
    $stock_check = array();
    if (STOCK_CHECK=='true') {
      for($i = 0, $n = sizeof ($order->products); $i<$n; $i++) {
        if ($stock_check[$i] = zen_check_stock ($order->products[$i]['id'], $order->products[$i]['qty'])) {
          $flagAnyOutOfStock = true;
        }
      }
      // Out of Stock
      if ((STOCK_ALLOW_CHECKOUT!='true')&&($flagAnyOutOfStock==true)) {
        zen_redirect (zen_href_link (FILENAME_SHOPPING_CART));
      }
    }

    // update customers_referral with $_SESSION['gv_id']
    if ($_SESSION['cc_id']) {
      $discount_coupon_query = "SELECT coupon_code
                            FROM ".TABLE_COUPONS."
                            WHERE coupon_id = :couponID";

      $discount_coupon_query = $db->bindVars ($discount_coupon_query, ':couponID', $_SESSION['cc_id'], 'integer');
      $discount_coupon = $db->Execute ($discount_coupon_query);

      $customers_referral_query = "SELECT customers_referral
                               FROM ".TABLE_CUSTOMERS."
                               WHERE customers_id = :customersID";

      $customers_referral_query = $db->bindVars ($customers_referral_query, ':customersID', $_SESSION['customer_id'], 'integer');
      $customers_referral = $db->Execute ($customers_referral_query);

      // only use discount coupon if set by coupon
      if ($customers_referral->fields['customers_referral']=='' and CUSTOMERS_REFERRAL_STATUS==1) {
        $sql = "UPDATE ".TABLE_CUSTOMERS."
            SET customers_referral = :customersReferral
            WHERE customers_id = :customersID";

        $sql = $db->bindVars ($sql, ':customersID', $_SESSION['customer_id'], 'integer');
        $sql = $db->bindVars ($sql, ':customersReferral', $discount_coupon->fields['coupon_code'], 'string');
        $db->Execute ($sql);
      } else {
        // do not update referral was added before
      }
    }

    if (isset (${$_SESSION['payment']}->form_action_url)) {
      $form_action_url = ${$_SESSION['payment']}->form_action_url;
    } else {
      $form_action_url = zen_href_link (FILENAME_CHECKOUT_PROCESS, '', 'SSL');
    }

    // if shipping-edit button should be overridden, do so
    $editShippingButtonLink = zen_href_link (FILENAME_CHECKOUT_SHIPPING, '', 'SSL');
    if (method_exists (${$_SESSION['payment']}, 'alterShippingEditButton')) {
      $theLink = ${$_SESSION['payment']}->alterShippingEditButton ();
      if ($theLink)
        $editShippingButtonLink = $theLink;
    }
    // deal with billing address edit button
    $flagDisablePaymentAddressChange = false;
    if (isset (${$_SESSION['payment']}->flagDisablePaymentAddressChange)) {
      $flagDisablePaymentAddressChange = ${$_SESSION['payment']}->flagDisablePaymentAddressChange;
    }

    $current_page_base = FILENAME_CHECKOUT_CONFIRMATION;
    require_once(zen_get_file_directory(DIR_FS_CATALOG . DIR_WS_LANGUAGES, $_SESSION['language'].'.php', 'false'));
    require_once (DIR_WS_MODULES.zen_get_module_directory ('require_languages.php'));
    require_once (DIR_WS_MODULES.zen_get_module_directory ('meta_tags.php'));
    $breadcrumb->add (NAVBAR_TITLE_1, zen_href_link (FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
    $breadcrumb->add (NAVBAR_TITLE_2);

    $breadCrumbHtml = $breadcrumb->trail (BREAD_CRUMBS_SEPARATOR);
    $body_code = DIR_FS_CATALOG.$template->get_template_dir ('tpl_ajax_checkout_confirmation_default.php', DIR_WS_TEMPLATE, $current_page_base, 'templates').'/tpl_ajax_checkout_confirmation_default.php';
    ob_start ();
    require_once ($body_code);
    $confirmationHtml = ob_get_clean ();
    ob_flush ();

    return (array(
        'breadCrumbHtml' => $breadCrumbHtml,
        'confirmationHtml' => $confirmationHtml,
        'pageTitle' => META_TAG_TITLE
    ));
  }
}
