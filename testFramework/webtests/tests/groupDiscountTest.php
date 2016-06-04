<?php
/**
 * @package tests
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */

/**
 * Class groupDiscountTest
 */
class groupDiscountTest extends CommonTestResources
{
    function testGroupDiscountsDo()
    {
        $this->setCustomerGroupDiscount(WEBTEST_DEFAULT_CUSTOMER_EMAIL, 1);

        $this->switchToTaxNonInclusive();
        $this->createCoupon('test10percent');
        $this->loginStandardCustomer(WEBTEST_DEFAULT_CUSTOMER_EMAIL, WEBTEST_DEFAULT_CUSTOMER_PASSWORD);
        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
        $this->byId('ship-storepickup-storepickup0')->click();
        $this->byName('checkout_address')->submit();
        $this->byId('pmt-cod')->click();
        $this->byCss('#paymentSubmit')->submit();
        $this->assertTextPresent('39.99'); //net price
        $this->assertTextPresent('-$4.00');//group discount
        $this->assertTextPresent('2.52');//tax
        $this->assertTextPresent('38.51');//total
        $this->byId('btn_submit')->click();


        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
        $this->byId('ship-storepickup-storepickup0')->click();
        $this->byName('checkout_address')->submit();
        $this->byId('pmt-cod')->click();
        $this->byName('dc_redeem_code')->value('test10percent');
        $this->byCss('#paymentSubmit')->submit();
        $this->assertTextPresent('39.99');//net price
        $this->assertTextPresent('-$4.00');//coupon discount
        $this->assertTextPresent('-$3.60');//group discount
        $this->assertTextPresent('2.27');//tax
        $this->assertTextPresent('34.66');//total
        $this->byId('btn_submit')->click();

        $this->switchToTaxInclusive();

        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
        $this->byId('ship-storepickup-storepickup0')->click();
        $this->byName('checkout_address')->submit();
        $this->byId('pmt-cod')->click();
        $this->byCss('#paymentSubmit')->submit();
        $this->assertTextPresent('42.79');//net price
        $this->assertTextPresent('-$4.28');//group discount
        $this->assertTextPresent('2.52');//tax
        $this->assertTextPresent('38.51');//total
        $this->byId('btn_submit')->click();

        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
        $this->byId('ship-storepickup-storepickup0')->click();
        $this->byName('checkout_address')->submit();
        $this->byId('pmt-cod')->click();
        $this->byName('dc_redeem_code')->value('test10percent');
        $this->byCss('#paymentSubmit')->submit();
        $this->assertTextPresent('42.79');//net price
        $this->assertTextPresent('-$4.28');//coupon discount
        $this->assertTextPresent('-$3.85');//group discount
        $this->assertTextPresent('2.27');//tax
        $this->assertTextPresent('34.66');//total
        $this->byId('btn_submit')->click();

        $this->switchFlatShippingTax('on');

        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
        $this->byId('ship-flat-flat')->click();
        $this->byName('checkout_address')->submit();
        $this->byId('pmt-cod')->click();
        $this->byCss('#paymentSubmit')->submit();
        $this->assertTextPresent('$5.95');//shipping
        $this->assertTextPresent('-$4.28');//group discount
        $this->assertTextPresent('3.47');//tax
        $this->assertTextPresent('44.46');//total
        $this->byId('btn_submit')->click();

        $this->switchSplitTaxMode('on');
        $this->url('http://' . BASE_URL . 'index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now');
        $this->url('http://' . BASE_URL . 'index.php?main_page=checkout_shipping');
        $this->byId('ship-flat-flat')->click();
        $this->byName('checkout_address')->submit();
        $this->byId('pmt-cod')->click();
        $this->byCss('#paymentSubmit')->submit();
        $this->assertTextPresent('42.79');//net price
        $this->assertTextPresent('5.95');//shipping
        $this->assertTextPresent('-$4.28');//group discount
        $this->assertTextPresent('2.5');//tax product
        $this->assertTextPresent('0.9');//tax shipping
        $this->assertTextPresent('44.4');//total
        $this->byId('btn_submit')->click();
        $this->switchFlatShippingTax('off');
        $this->switchSplitTaxMode('off');
        $this->switchToTaxNonInclusive();

        $this->setCustomerGroupDiscount(WEBTEST_DEFAULT_CUSTOMER_EMAIL, 0);
    }
}
