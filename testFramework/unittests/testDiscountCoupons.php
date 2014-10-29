<?php
/**
 * File contains tests for discount coupons
 *
 * @package tests
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
require_once('support/zcDiscountCouponTestCase.php');
/**
 * Unit Tests for discount coupons
 */
class testDiscountCoupons extends zcDiscountCouponsTestCase
{
  public function setUp()
  {
    parent::setUp();
      require DIR_FS_CATALOG . 'includes/modules/order_total/ot_coupon.php';
      require DIR_FS_CATALOG . 'includes/classes/shopping_cart.php';
      require DIR_FS_CATALOG . 'includes/classes/currencies.php';
      require DIR_FS_CATALOG . 'includes/classes/db/mysql/query_factory.php';
      $_SESSION['currency'] = 'USD';
      $_SESSION['cc_id'] = '1';
      define('MODULE_ORDER_TOTAL_COUPON_HEADER', '');
      define('MODULE_ORDER_TOTAL_COUPON_TITLE', '');
      define('MODULE_ORDER_TOTAL_COUPON_DESCRIPTION', '');
      define('MODULE_ORDER_TOTAL_COUPON_SORT_ORDER', '');
      define('MODULE_ORDER_TOTAL_COUPON_INC_SHIPPING', '');
      define('MODULE_ORDER_TOTAL_COUPON_INC_TAX', 'false');
      define('MODULE_ORDER_TOTAL_COUPON_CALC_TAX', 'Standard');
      define('MODULE_ORDER_TOTAL_COUPON_TAX_CLASS', '');
      define('TABLE_COUPON_RESTRICT', '');
      define('TABLE_PRODUCTS', '');
      define('TABLE_COUPONS', '');

      $GLOBALS['currencies'] = $this->getMockBuilder('currencies')
          ->disableOriginalConstructor()
          ->getMock();
      $GLOBALS['currencies']->method('get_decimal_places')->willReturn(2);

      $GLOBALS['order'] = $this->getMockBuilder('order')
          ->disableOriginalConstructor()
          ->getMock();
      $_SESSION['cart'] = $this->getMockBuilder('shoppingCart')
          ->disableOriginalConstructor()
          ->getMock();
      $products[] = array (
          'id' => 27,
            'category' => 5,
            'name' => ' Packard LaserJet 1100Xi Linked',
            'model' => 'HPLJ1100XI',
            'image' => 'hewlett_packard/lj1100xi.gif',
            'price' => 499.99,
            'quantity' => 1,
            'weight' => 45,
            'final_price' => 499.99,
            'onetime_charges' => 0,
            'tax_class_id' => 1,
            'attributes' => '',
            'attributes_values' => '',
            'products_priced_by_attribute' => 0,
            'product_is_free' => 0,
            'products_discount_type' => 0,
            'products_discount_type_from' => 0,
            'products_virtual' => 0,
            'product_is_always_free_shipping' => 0,
            'products_quantity_order_min' => 1,
            'products_quantity_order_units' => 1,
            'products_quantity_order_max' => 0,
            'products_quantity_mixed' => 0,
            'products_mixed_discount_quantity' => 1
        );
      $_SESSION['cart']->method('get_products')->willReturn($products);
  }

    /**
     * 10% coupon - include shipping = true - no tax calculations
     */
    public function testPercentageOffCoupon1()
    {
        $GLOBALS['order']->info = array('tax_groups'=>array(), 'tax'=>0, 'total'=>502.49, 'shipping_cost'=>2.50, 'shipping_tax'=>0);
        define('DISPLAY_PRICE_WITH_TAX', 'false');
        $this->instantiateQfr(array('coupon_code'=>'test', 'coupon_total'=>0, 'coupon_minimum_order'=>0, 'coupon_amount'=>10, 'coupon_type'=>'P'));
        $this->coupon = new ot_coupon();
        $this->coupon->include_shipping = 'true';
        $this->coupon->process(null);
        $result = $this->coupon->output;
        $this->assertTrue($result[0]['value'] == 50.25);
    }
    /**
     * 10% coupon - include shipping = false - no tax calculations
     */
    public function testPercentageOffCoupon2()
    {
        $GLOBALS['order']->info = array('tax_groups'=>array(), 'tax'=>0, 'total'=>502.49, 'shipping_cost'=>2.50, 'shipping_tax'=>0);
        define('DISPLAY_PRICE_WITH_TAX', 'false');
        $this->instantiateQfr(array('coupon_code'=>'test', 'coupon_total'=>0, 'coupon_minimum_order'=>0, 'coupon_amount'=>10, 'coupon_type'=>'P'));
        $this->coupon = new ot_coupon();
        $this->coupon->include_shipping = 'false';
        $this->coupon->process(null);
        $result = $this->coupon->output;
        $this->assertTrue($result[0]['value'] == 50);
    }
    /**
     * 100% coupon - include shipping = true - no tax calculations
     */
    public function testPercentageOffCoupon3()
    {
        $GLOBALS['order']->info = array('tax_groups'=>array(), 'tax'=>0, 'total'=>502.49, 'shipping_cost'=>2.50, 'shipping_tax'=>0);
        define('DISPLAY_PRICE_WITH_TAX', 'false');
        $this->instantiateQfr(array('coupon_code'=>'test', 'coupon_total'=>0, 'coupon_minimum_order'=>0, 'coupon_amount'=>100, 'coupon_type'=>'P'));
        $this->coupon = new ot_coupon();
        $this->coupon->include_shipping = 'true';
        $this->coupon->process(null);
        $result = $this->coupon->output;
        $this->assertTrue($result[0]['value'] == 502.49);
     }
    /**
     * 100% coupon - include shipping = false - no tax calculations
     */
    public function testPercentageOffCoupon4()
    {
        $GLOBALS['order']->info = array('tax_groups'=>array(), 'tax'=>0, 'total'=>502.49, 'shipping_cost'=>2.50, 'shipping_tax'=>0);
        define('DISPLAY_PRICE_WITH_TAX', 'false');
        $this->instantiateQfr(array('coupon_code'=>'test', 'coupon_total'=>0, 'coupon_minimum_order'=>0, 'coupon_amount'=>100, 'coupon_type'=>'P'));
        $this->coupon = new ot_coupon();
        $this->coupon->include_shipping = 'false';
        $this->coupon->process(null);
        $result = $this->coupon->output;
        $this->assertTrue($result[0]['value'] == 499.99);
    }
    /**
     * Fixed coupon - include shipping = false - no tax calculations
     */
    public function testFixedOffCoupon1()
    {
        $GLOBALS['order']->info = array('tax_groups'=>array(), 'tax'=>0, 'total'=>502.49, 'shipping_cost'=>2.50, 'shipping_tax'=>0);
        define('DISPLAY_PRICE_WITH_TAX', 'false');
        $this->instantiateQfr(array('coupon_code'=>'test', 'coupon_total'=>0, 'coupon_minimum_order'=>0, 'coupon_amount'=>501, 'coupon_type'=>'F'));
        $this->coupon = new ot_coupon();
        $this->coupon->include_shipping = 'false';
        $this->coupon->process(null);
        $this->assertEquals($GLOBALS['order']->info['total'],2.50);
    }
    /**
     * Fixed coupon - include shipping = true - no tax calculations
     */
    public function testFixedOffCoupon2()
    {
        $GLOBALS['order']->info = array('tax_groups'=>array(), 'tax'=>0, 'total'=>502.49, 'shipping_cost'=>2.50, 'shipping_tax'=>0);
        define('DISPLAY_PRICE_WITH_TAX', 'false');
        $this->instantiateQfr(array('coupon_code'=>'test', 'coupon_total'=>0, 'coupon_minimum_order'=>0, 'coupon_amount'=>501, 'coupon_type'=>'F'));
        $this->coupon = new ot_coupon();
        $this->coupon->include_shipping = 'true';
        $this->coupon->process(null);
        $this->assertEquals($GLOBALS['order']->info['total'],1.49);
    }
    /**
     * Fixed coupon - include shipping = true - tax calculations
     */
    public function testFixedOffCoupon3()
    {
        $GLOBALS['order']->info = array('tax_groups'=>array('FL TAX 7.0%' => 34.9993), 'tax'=>34.9993, 'total'=>537.4893, 'shipping_cost'=>2.50, 'shipping_tax'=>0);
        define('DISPLAY_PRICE_WITH_TAX', 'false');
        $this->instantiateQfr(array('coupon_code'=>'test', 'coupon_total'=>0, 'coupon_minimum_order'=>0, 'coupon_amount'=>501, 'coupon_type'=>'F'));
        $this->coupon = new ot_coupon();
        $this->coupon->include_shipping = 'true';
        $this->coupon->process(null);
        $this->assertEquals($GLOBALS['order']->info['total'],1.5892999999999589);
    }
    /**
     * Fixed coupon - include shipping = false - tax calculations
     */
    public function testFixedOffCoupon4()
    {
        $GLOBALS['order']->info = array('tax_groups'=>array('FL TAX 7.0%' => 34.9993), 'tax'=>34.9993, 'total'=>537.4893, 'shipping_cost'=>2.50, 'shipping_tax'=>0);
        define('DISPLAY_PRICE_WITH_TAX', 'false');
        $this->instantiateQfr(array('coupon_code'=>'test', 'coupon_total'=>0, 'coupon_minimum_order'=>0, 'coupon_amount'=>501, 'coupon_type'=>'F'));
        $this->coupon = new ot_coupon();
        $this->coupon->include_shipping = 'false';
        $this->coupon->process(null);
        $this->assertEquals($GLOBALS['order']->info['total'],2.4992999999999483);
    }
}
