<?php
/**
 * File contains common unit/web test resources
 *
 * @package tests
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  $
 */

/**
 * Class GiftVoucherTrait
 */
trait GiftVoucherTrait
{

    function purchaseGiftVoucherQueueOn($customerEmail, $customerPass, $gvPurchase = 100)
    {
        $this->setConfigurationValue('MODULE_ORDER_TOTAL_GV_QUEUE', 'true');
        $this->loginStandardCustomer($customerEmail, $customerPass);
        $this->url('http://' . BASE_URL . 'index.php?main_page=shopping_cart&action=empty_cart');
        $this->url('http://' . BASE_URL . 'index.php?main_page=document_product_info&cPath=21&products_id=32');
        $this->byCss('input[name=cart_quantity]')->clear();
        $this->byCss('input[name=cart_quantity]')->value($gvPurchase);
        $this->byCss('#cartAdd > input[type=submit]')->click();
        $this->url('http://' . BASE_URL . 'index.php?main_page=shopping_cart');
        $this->assertTextPresent('Gift Certificate');
        $amount = number_format(100 * $gvPurchase, 2);
        $this->assertTextPresent('Amount: $' . $amount);
        $this->assertTextPresent('Sub-Total: $' . $amount);
        $this->url('https://' . BASE_URL . 'index.php?main_page=checkout_shipping');
        $this->assertTextPresent($amount);
        $this->assertTextPresent('Free Shipping');
        $this->assertTextPresent('Payment Information');
        $this->byCss('#paymentSubmit > input[type="submit"]')->click();
        $this->assertTextPresent($amount);
        $this->assertTextPresent('Free Shipping');
        $this->byId('btn_submit')->click();

        $this->loginStandardAdmin(WEBTEST_ADMIN_NAME_INSTALL, WEBTEST_ADMIN_PASSWORD_INSTALL);
        $this->url('https://' . DIR_WS_ADMIN . 'index.php?cmd=gv_queue');
        $this->assertTextPresent($amount);
        $this->byCss('.rowHandlerRelease_gv')->click();
        $this->byCss('#rowReleaseGvModal #rowGvReleaseConfirm')->click();
        $this->setConfigurationValue('MODULE_ORDER_TOTAL_GV_QUEUE', 'false');
    }

    public function getCouponBalanceCustomer($customerEmail)
    {
        $customerId = $this->getCustomerIdFromEmail($customerEmail);
        if ($customerId === false) {
            return 0;
        }
        $sql = "SELECT amount FROM " . DB_PREFIX . "coupon_gv_customer WHERE customer_id = '" . $customerId . "'";
        $q = $this->doDbQuery($sql);
        if ($q->num_rows == 0) {
            return 0;
        }
        $result = mysqli_fetch_assoc($q);

        return $result['amount'];
    }
}
