<?php
/**
 * @package tests
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
require_once(__DIR__ . '/../support/zcTestCase.php');

/**
 * Class zcDiscountCouponTest
 */
abstract class zcDiscountCouponTest extends zcTestCase
{

    /**
     * @param $qfrResult
     */
    public function instantiateQfr($qfrResult)
    {
        $qfr = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $qfr->fields = $qfrResult;
        $qfr->method('RecordCount')->willReturn(1);

        $GLOBALS['db'] = $this->getMockBuilder('queryFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $GLOBALS['db']->method('execute')->willReturn($qfr);
    }

}

/**
 * Stubbed Function
 *
 * @return bool
 *
 */
function is_product_valid()
{
    return true;
}

/**
 * Stubbed Function
 * @return bool
 */
function is_coupon_valid_for_sales()
{
    return true;
}

/**
 * Stubbed Function
 *
 * @param $value
 * @param $precision
 * @return float
 */
function zen_round($value, $precision)
{
    $value = round($value * pow(10, $precision), 0);
    $value = $value / pow(10, $precision);

    return $value;
}
