<?php
/**
 * @package tests
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  $
 */

/**
 * Class ZenCartCouponHelpersTrait
 */
trait ZenCartCouponHelpersTrait
{
    protected function createCoupon($couponCode)
    {
        if ($this->doesCouponExist($couponCode)) {
            return;
        }
        $couponMap = array(
            'test10percent' => array('coupon_amount' => "10%", 'coupon_name' => 'test10percent'),
            'test10fixed' => array('coupon_amount' => "10", 'coupon_name' => 'test10fixed'),
            'test100fixed' => array('coupon_amount' => "100", 'coupon_name' => 'test100fixed'),
            'test100percent' => array('coupon_amount' => "100%", 'coupon_name' => 'test100percent'),
            'testfreeshipping' => array('coupon_amount' => "", 'coupon_name' => 'testfreeshipping', 'coupon_free_ship' => true),
            'test10percentrestricted' => array(
                'coupon_amount' => "10%",
                'coupon_name' => 'test10percentrestricted',
                'restrict' => 'true'
            ),
        );
        if (!isset($couponMap[$couponCode])) {
            throw new \InvalidArgumentException(sprintf('Could not find coupon code in map: "%s"', $couponCode));
        }
        $this->iDoAStandardAdminLoginWithParamParam('admin_user_main', 'admin_password_main');
        $this->createCouponFromMap($couponMap[$couponCode]);

    }

    public function doesCouponExist($couponCode)
    {
        $sql = "SELECT coupon_code FROM " . $this->configParams['db_prefix'] . "coupons WHERE coupon_code = '" . $couponCode . "'";
        $q = $this->doDbQuery($sql);
        if ($q === false || $q->num_rows == 0) {
            return false;
        }

        return true;
    }

    protected function createCouponFromMap($couponDetail)
    {

        $this->iVisit('admin/index.php?cmd=coupon_admin&action=new');
        $this->fillField('coupon_amount', $couponDetail['coupon_amount']);
        $this->fillField('coupon_name[1]', $couponDetail['coupon_name']);
        $this->fillField('coupon_uses_user', "");
        $this->fillField('coupon_code', $couponDetail['coupon_name']);
        if (isset($couponDetail['coupon_free_ship']) && $couponDetail['coupon_free_ship'] === true) {
            $this->iClickOnTheElementWithCss("input[name='coupon_free_ship']");
        }
        $this->iClickOnTheElementWithCss("input[type='image']");
        $this->iClickOnTheElementWithCss("input[type='image']");
        $this->assertPageContainsText('Multiple Discount Coupons');
        $this->assertPageContainsText($couponDetail['coupon_name']);

        if (!isset($couponDetail['restrict'])) {
            return;
        }
        $this->iClickOnTheElementWithCss("img[alt=\"Restrict Discount Coupon\"]");
        $method = 'couponRestrict' . ucfirst($couponDetail['coupon_name']);
        if (!method_exists($this, $method)) {
            throw new \InvalidArgumentException(sprintf('Could not find method: "%s"', $method));
        }
        $this->$method();
    }


    /**
     *
     */
    public function couponRestrictTest10percentrestricted()
    {
        $this->selectOption('cPath_prod', 9);
        $this->selectOption('products_drop', 3);
        $this->selectOption('restrict_status_product', 'Allow');
        $this->iClickOnTheElementWithXPath("//input[@name='add' and @value='Update']");
    }

    /**
     * @param $customerEmail
     * @return int
     */
    public function getGVBalanceCustomer($customerEmail)
    {
        $customerId = $this->getCustomerIdFromEmail($customerEmail);
        if ($customerId === false) {
            return 0;
        }
        $sql = "SELECT amount FROM " . $this->configParams['db_prefix'] . "coupon_gv_customer WHERE customer_id = '" . $customerId . "'";
        $q = $this->doDbQuery($sql);
        if ($q->num_rows == 0) {
            return 0;
        }
        $result = mysqli_fetch_assoc($q);

        return $result['amount'];
    }

}
