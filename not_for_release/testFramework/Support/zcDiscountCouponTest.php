<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Support;

use Tests\Support\zcUnitTestCase;

/**
 * Class zcDiscountCouponTest
 */
abstract class zcDiscountCouponTest extends zcUnitTestCase
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
