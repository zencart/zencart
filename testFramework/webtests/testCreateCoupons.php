<?php
/**
 * File contains create coupon tests
 *
 * @package tests
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: testCreateCoupons.php 19030 2011-07-06 12:27:13Z wilt $
 */
/**
 *
 * @package tests
 */
class testCreateCoupons extends zcCommonTestResources
{
  function testCreateCouponsDo()
  {
    $this->open('http://' . BASE_URL . 'admin/');
    $this->waitForPageToLoad(10000);
    $this->type("admin_name", WEBTEST_ADMIN_NAME_INSTALL);
    $this->type("admin_pass", WEBTEST_ADMIN_PASSWORD_INSTALL_1);
    $this->clickAndWait("submit");

    $this->clickAndWait("link=Coupon Admin");
    $this->clickAndWait("couponInsert");
    $this->type("coupon_amount", "10%");
    $this->type("coupon_name[1]", "test10percent");
    $this->type("coupon_uses_user", "");
    $this->type("coupon_code", "test10percent");
    $this->clickAndWait("//input[@type='image']");
    $this->clickAndWait("//input[@type='image']");
    $this->assertTextPresent('glob:*Discount Coupons*');
    $this->assertTextPresent('glob:*test10percent*');

    $this->clickAndWait("link=Coupon Admin");
    $this->clickAndWait("couponInsert");
    $this->type("coupon_amount", "10%");
    $this->type("coupon_name[1]", "test10percentrestricted");
    $this->type("coupon_uses_user", "");
    $this->type("coupon_code", "test10percentrestricted");
    $this->clickAndWait("//input[@type='image']");
    $this->clickAndWait("//input[@type='image']");
    $this->assertTextPresent('glob:*Discount Coupons*');
    $this->assertTextPresent('glob:*test10percentrestricted*');
    $this->open('http://' . BASE_URL . 'admin/coupon_restrict.php?cid=2&page=1');
    $this->waitForPageToLoad(10000);
    $this->selectAndWait('cPath_prod', 'value=9');
    $this->select('products_drop', 'value=3');
    $this->select('document.forms[2].elements[2]', 'value=Allow');
    $this->clickAndWait("//input[@name='add' and @value='Update']");

    $this->clickAndWait("link=Coupon Admin");
    $this->clickAndWait("couponInsert");
    $this->type("coupon_amount", "10%");
    $this->type("coupon_name[1]", "test10percentrestrictedminimum");
    $this->type("coupon_uses_user", "");
    $this->type("coupon_min_order", "40");
    $this->type("coupon_code", "test10percentrestrictedminimum");
    $this->clickAndWait("//input[@type='image']");
    $this->clickAndWait("//input[@type='image']");
    $this->assertTextPresent('glob:*Discount Coupons*');
    $this->assertTextPresent('glob:*test10percentrestrictedminimum*');
    $this->open('http://' . BASE_URL . 'admin/coupon_restrict.php?cid=3&page=1');
    $this->waitForPageToLoad(10000);
    $this->selectAndWait('cPath_prod', 'value=9');
    $this->select('products_drop', 'value=3');
    $this->select('document.forms[2].elements[2]', 'value=Allow');
    $this->clickAndWait("//input[@name='add' and @value='Update']");

    $this->clickAndWait("link=Coupon Admin");
    $this->clickAndWait("couponInsert");
    $this->type("coupon_amount", "10");
    $this->type("coupon_name[1]", "test10fixed");
    $this->type("coupon_uses_user", "");
    $this->type("coupon_code", "test10fixed");
    $this->clickAndWait("//input[@type='image']");
    $this->clickAndWait("//input[@type='image']");
    $this->assertTextPresent('glob:*Discount Coupons*');
    $this->assertTextPresent('glob:*test10fixed*');

    $this->clickAndWait("link=Coupon Admin");
    $this->clickAndWait("couponInsert");
    $this->type("coupon_amount", "100%");
    $this->type("coupon_name[1]", "test100percent");
    $this->type("coupon_uses_user", "");
    $this->type("coupon_code", "test100percent");
    $this->clickAndWait("//input[@type='image']");
    $this->clickAndWait("//input[@type='image']");
    $this->assertTextPresent('glob:*Discount Coupons*');
    $this->assertTextPresent('glob:*test100percent*');

    $this->clickAndWait("link=Coupon Admin");
    $this->clickAndWait("couponInsert");
    $this->type("coupon_amount", "100");
    $this->type("coupon_name[1]", "test100fixed");
    $this->type("coupon_uses_user", "");
    $this->type("coupon_code", "test100fixed");
    $this->clickAndWait("//input[@type='image']");
    $this->clickAndWait("//input[@type='image']");
    $this->assertTextPresent('glob:*Discount Coupons*');
    $this->assertTextPresent('glob:*test100fixed*');

    $this->clickAndWait("link=Coupon Admin");
    $this->clickAndWait("couponInsert");
    $this->type("coupon_amount", "100%");
    $this->type("coupon_name[1]", "test100PercentIncludeShipping");
    $this->type("coupon_uses_user", "");
    $this->type("coupon_code", "test100PercentIncludeShipping");
    $this->clickAndWait("//input[@type='image']");
    $this->clickAndWait("//input[@type='image']");
    $this->assertTextPresent('glob:*Discount Coupons*');
    $this->assertTextPresent('glob:*test100PercentIncludeShipping*');

    $this->clickAndWait("link=Coupon Admin");
    $this->clickAndWait("couponInsert");
    $this->type("coupon_name[1]", "testFreeShipping");
    $this->type("coupon_uses_user", "");
    $this->type("coupon_code", "testFreeShipping");
    $this->check("name=coupon_free_ship");
    $this->clickAndWait("//input[@type='image']");
    $this->clickAndWait("//input[@type='image']");
    $this->assertTextPresent('glob:*Discount Coupons*');
    $this->assertTextPresent('glob:*test100PercentIncludeShipping*');
  }

}
