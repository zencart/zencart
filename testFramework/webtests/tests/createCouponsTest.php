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
        $this->createCoupon('test10percent');
        $this->createCoupon('test10fixed');
        $this->createCoupon('test100fixed');
        $this->createCoupon('test100percent');
        $this->createCoupon('test100PercentIncludeShipping');
        $this->createCoupon('testFreeShipping');
        $this->createCoupon('test10percentrestricted');
        $this->createCoupon('test10percentrestrictedminimum');
    }
}
