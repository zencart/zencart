<?php
/**
 * @package tests
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */

/**
 * Class createCouponsTest
 */
class createCouponsTest extends CommonTestResources
{
  function testCreateCouponsDo()
  {
      $this->url('https://' . DIR_WS_ADMIN);
      $this->assertTextPresent('Admin Login');
      $this->byId('admin_name')->value(WEBTEST_ADMIN_NAME_INSTALL);
      $this->byId('admin_pass')->value(WEBTEST_ADMIN_PASSWORD_INSTALL);
      $continue = $this->byId('btn_submit');
      $continue->click();
      $this->assertTextPresent('Add Widget');

      $this->url('https://' . DIR_WS_ADMIN . 'index.php?cmd=coupon_admin');
      $this->byName('couponInsert')->click();
      $this->byName('coupon_amount')->value("10%");
      $this->byName('coupon_name[1]')->clear();
      $this->byName('coupon_name[1]')->value("test10percent");
      $this->byName('coupon_uses_user')->clear();
      $this->byName('coupon_uses_user')->value("");
      $this->byName('coupon_code')->value("test10percent");
      $this->byCss("input[type='image']")->click();
      $this->byCss("input[type='image']")->click();
      $this->assertTextPresent('Discount Coupons');
      $this->assertTextPresent('Multiple Discount Coupons');
      $this->assertTextPresent('test10percent');

      $this->url('https://' . DIR_WS_ADMIN . 'index.php?cmd=coupon_admin');
      $this->byName('couponInsert')->click();
      $this->byName('coupon_amount')->value("10");
      $this->byName('coupon_name[1]')->clear();
      $this->byName('coupon_name[1]')->value("test10fixed");
      $this->byName('coupon_uses_user')->clear();
      $this->byName('coupon_uses_user')->value("");
      $this->byName('coupon_code')->value("test10fixed");
      $this->byCss("input[type='image']")->click();
      $this->byCss("input[type='image']")->click();
      $this->assertTextPresent('Discount Coupons');
      $this->assertTextPresent('Multiple Discount Coupons');
      $this->assertTextPresent('test10fixed');

      $this->url('https://' . DIR_WS_ADMIN . 'index.php?cmd=coupon_admin');
      $this->byName('couponInsert')->click();
      $this->byName('coupon_amount')->value("100");
      $this->byName('coupon_name[1]')->clear();
      $this->byName('coupon_name[1]')->value("test100fixed");
      $this->byName('coupon_uses_user')->clear();
      $this->byName('coupon_uses_user')->value("");
      $this->byName('coupon_code')->value("test100fixed");
      $this->byCss("input[type='image']")->click();
      $this->byCss("input[type='image']")->click();
      $this->assertTextPresent('Discount Coupons');
      $this->assertTextPresent('Multiple Discount Coupons');
      $this->assertTextPresent('test100fixed');

      $this->url('https://' . DIR_WS_ADMIN . 'index.php?cmd=coupon_admin');
      $this->byName('couponInsert')->click();
      $this->byName('coupon_amount')->value("100%");
      $this->byName('coupon_name[1]')->clear();
      $this->byName('coupon_name[1]')->value("test100percent");
      $this->byName('coupon_uses_user')->clear();
      $this->byName('coupon_uses_user')->value("");
      $this->byName('coupon_code')->value("test100percent");
      $this->byCss("input[type='image']")->click();
      $this->byCss("input[type='image']")->click();
      $this->assertTextPresent('Discount Coupons');
      $this->assertTextPresent('Multiple Discount Coupons');
      $this->assertTextPresent('test100percent');

      $this->url('https://' . DIR_WS_ADMIN . 'index.php?cmd=coupon_admin');
      $this->byName('couponInsert')->click();
      $this->byName('coupon_amount')->value("100%");
      $this->byName('coupon_name[1]')->clear();
      $this->byName('coupon_name[1]')->value("test100PercentIncludeShipping");
      $this->byName('coupon_uses_user')->clear();
      $this->byName('coupon_uses_user')->value("");
      $this->byName('coupon_code')->value("test100PercentIncludeShipping");
      $this->byCss("input[type='image']")->click();
      $this->byCss("input[type='image']")->click();
      $this->assertTextPresent('Discount Coupons');
      $this->assertTextPresent('Multiple Discount Coupons');
      $this->assertTextPresent('test100PercentIncludeShipping');

      $this->url('https://' . DIR_WS_ADMIN . 'index.php?cmd=coupon_admin');
      $this->byName('couponInsert')->click();
      $this->byName('coupon_name[1]')->clear();
      $this->byName('coupon_name[1]')->value("testFreeShipping");
      $this->byName('coupon_uses_user')->clear();
      $this->byName('coupon_free_ship')->click();
      $this->byName('coupon_code')->value("testFreeShipping");
      $this->byName('coupon_uses_user')->value("");
      $this->byCss("input[type='image']")->click();
      $this->byCss("input[type='image']")->click();
      $this->assertTextPresent('Discount Coupons');
      $this->assertTextPresent('Multiple Discount Coupons');
      $this->assertTextPresent('testFreeShipping');

      $this->url('https://' . DIR_WS_ADMIN . 'index.php?cmd=coupon_admin');
      $this->byName('couponInsert')->click();
      $this->byName('coupon_amount')->value("10%");
      $this->byName('coupon_name[1]')->clear();
      $this->byName('coupon_name[1]')->value("test10percentrestricted");
      $this->byName('coupon_uses_user')->clear();
      $this->byName('coupon_uses_user')->value("");
      $this->byName('coupon_code')->value("test10percentrestricted");
      $this->byCss("input[type='image']")->click();
      $this->byCss("input[type='image']")->click();
      $this->assertTextPresent('Discount Coupons');
      $this->assertTextPresent('Multiple Discount Coupons');
      $this->assertTextPresent('test10percentrestricted');
      $this->byCss('img[alt="Restrict Discount Coupon"]')->click();
      $this->select($this->byName('cPath_prod'))->selectOptionByValue(9);
      $this->select($this->byName('products_drop'))->selectOptionByValue(3);
      $this->select($this->byId('restrict_status_product'))->selectOptionByValue('Allow');
      $this->byXpath("//input[@name='add' and @value='Update']")->click();



      $this->url('https://' . DIR_WS_ADMIN . 'index.php?cmd=coupon_admin');
      $this->byName('couponInsert')->click();
      $this->byName('coupon_amount')->value("10%");
      $this->byName('coupon_name[1]')->clear();
      $this->byName('coupon_name[1]')->value("test10percentrestrictedminimum");
      $this->byName('coupon_uses_user')->clear();
      $this->byName('coupon_uses_user')->value("");
      $this->byName('coupon_min_order')->clear();
      $this->byName('coupon_min_order')->value("50");
      $this->byName('coupon_code')->value("test10percentrestrictedminimum");
      $this->byCss("input[type='image']")->click();
      $this->byCss("input[type='image']")->click();
      $this->assertTextPresent('Discount Coupons');
      $this->assertTextPresent('Multiple Discount Coupons');
      $this->assertTextPresent('test10percentrestrictedminimum');
      $this->byCss('img[alt="Restrict Discount Coupon"]')->click();
      $this->select($this->byId('restrict_status_category'))->selectOptionByValue('Deny');
      $this->byXpath("//input[@name='add' and @value='Add']")->click();
      $this->select($this->byName('cPath_prod'))->selectOptionByValue(9);
      $this->select($this->byName('products_drop'))->selectOptionByValue(3);
      $this->select($this->byId('restrict_status_product'))->selectOptionByValue('Allow');
      $this->byXpath("//input[@name='add' and @value='Update']")->click();

  }

}
