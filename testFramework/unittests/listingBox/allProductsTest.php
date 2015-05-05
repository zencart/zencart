<?php
/**
 *
 * @package tests
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
require_once(__DIR__ . '/../support/zcListingBoxTestCase.php');
/**
 * Testing Library
 */
class testAllDefaultCase extends zcListingBoxTestCase
{
    public function setup()
    {
        parent::setup();
        define('PRODUCT_ALL_LIST_SORT_DEFAULT', 0);
        define('MAX_DISPLAY_PRODUCTS_ALL', 0);
        define('IMAGE_PRODUCT_ALL_LISTING_WIDTH', '');
        define('IMAGE_PRODUCT_ALL_LISTING_HEIGHT', '');
        define('TABLE_HEADING_ALL_PRODUCTS', '');
        define('EXCLUDE_ADMIN_IP_FOR_MAINTENANCE', '');
        define('DOWN_FOR_MAINTENANCE', '');
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $GLOBALS['new_products_category_id'] = 1;
    }
    public function testInstantiate()
    {
        $scroller = $this->getMock('paginator', array('getResults'));
        $scroller->method('getResults')->willReturn(array('resultList'=>array()));
        $paginator = $this->getMock('paginator', array('doPagination', 'getScroller'));
        $paginator->method('getScroller')->willReturn($scroller);
        $r = $this->getMock('\\ZenCart\\Platform\\Request');
        $qfr = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $db = $this->getMockBuilder('queryFactory')
            ->getMock();
        $db->method('Execute')->willReturn($qfr);
        $GLOBALS['db'] = $db;
        $qb = $this->getMockBuilder('queryBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array('processQuery', 'getQuery'))
            ->getMock();
        $qb->method('getQuery')->willReturn(array('mainSql'=>'', 'countSql'=>''));
        $lb = new ZenCart\Platform\listingBox\boxes\AllDefault($r);
        $lb->buildResults($qb, $db, $paginator);
    }
}
