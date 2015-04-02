<?php
/**
 * File contains paginator adapter test cases
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
class testFilterCase extends zcListingBoxTestCase
{
    public function setup()
    {
        parent::setup();
    }

    public function testAlphaFilterNoRequestParams()
    {
        $request = $this->getMock('\\ZenCart\\Platform\\Request');
        $params = array();
        $productQuery = array();
        $f = new \ZenCart\Platform\listingBox\filters\AlphaFilter($request, $params);
        $f->filterItem($productQuery);
    }
    public function testAlphaFilterWithRequestParams()
    {
        $request = $this->getMock('\\ZenCart\\Platform\\Request');
        $request->method('has')->willReturn(true);
        $request->method('readGet')->willReturn(1);
        $params = array();
        $productQuery = array();
        $f = new \ZenCart\Platform\listingBox\filters\AlphaFilter($request, $params);
        $pq = $f->filterItem($productQuery);
        $this->assertTrue(count($pq['whereClauses']) == 1);
    }
    public function testCategoryFilterNoRequestParams()
    {
        $request = $this->getMock('\\ZenCart\\Platform\\Request');
        $params = array('new_products_category_id'=>0, 'cPath'=>'');
        $productQuery = array();
        $f = new \ZenCart\Platform\listingBox\filters\CategoryFilter($request, $params);
        $f->filterItem($productQuery);
        $pq = $f->filterItem($productQuery);
        $this->assertTrue(count($pq) === 0);
    }
    public function testCategoryFilterManufacturers()
    {
        $request = $this->getMock('\\ZenCart\\Platform\\Request');
        $request->method('readGet')
            ->will($this->onConsecutiveCalls(2));
        $params = array('new_products_category_id'=>0, 'cPath'=>'');
        $productQuery = array();
        $f = new \ZenCart\Platform\listingBox\filters\CategoryFilter($request, $params);
        $f->filterItem($productQuery);
        $pq = $f->filterItem($productQuery);
        $this->assertTrue(count($pq) === 0);
    }
    public function testCategoryFilterFilterId()
    {
        $qfr = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $db = $this->getMockBuilder('queryFactory')
            ->getMock();
        $GLOBALS['db'] = $db;
        $db->method('Execute')->willReturn($qfr);
        $request = $this->getMock('\\ZenCart\\Platform\\Request');
        $request->method('readGet')
            ->will($this->onConsecutiveCalls(2, 1, 1, 1));
        $params = array('new_products_category_id'=>0, 'cPath'=>'');
        $productQuery = array();
        $f = new \ZenCart\Platform\listingBox\filters\CategoryFilter($request, $params);
        $f->filterItem($productQuery);
        $pq = $f->filterItem($productQuery);
        $this->assertTrue(count($pq) === 0);
    }
    public function testCategoryFilterCPath()
    {
        $qfr = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $db = $this->getMockBuilder('queryFactory')
            ->getMock();
        $GLOBALS['db'] = $db;
        $db->method('Execute')->willReturn($qfr);
        $request = $this->getMock('\\ZenCart\\Platform\\Request');
        $request->method('readGet')
            ->will($this->onConsecutiveCalls(2, 1, 1, 1));
        $params = array('new_products_category_id'=>0, 'cPath'=>'1_3');
        $productQuery = array();
        $f = new \ZenCart\Platform\listingBox\filters\CategoryFilter($request, $params);
        $pq = $f->filterItem($productQuery);
        $this->assertTrue(count($pq) === 3);
    }
    public function testDisplaySorterNoRequestParams()
    {
        $request = $this->getMock('\\ZenCart\\Platform\\Request');
        $params = array('defaultSortOrder'=>0);
        $productQuery = array();
        $f = new \ZenCart\Platform\listingBox\filters\DisplayOrderSorter($request, $params);
        $f->filterItem($productQuery);
        $pq = $f->filterItem($productQuery);
        $this->assertTrue(count($pq) === 1);
    }
    public function testSearchResultsNoRequestParams()
    {
        define('DISPLAY_PRICE_WITH_TAX', 'false');
        $request = $this->getMock('\\ZenCart\\Platform\\Request');
        $params = array('defaultSortOrder'=>0, 'currencies'=>'');
        $productQuery = array();
        $f = new \ZenCart\Platform\listingBox\filters\SearchResults($request, $params);
        $f->filterItem($productQuery);
        $pq = $f->filterItem($productQuery);
        $this->assertTrue(count($pq) === 2);
    }
    public function testSearchResultsHandleTaxRates()
    {
        define('DISPLAY_PRICE_WITH_TAX', 'true');
        $qfr = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $qfr->EOF = true;
        $db = $this->getMockBuilder('queryFactory')
            ->getMock();
        $GLOBALS['db'] = $db;
        $db->method('Execute')->willReturn($qfr);
        $currencies = $this->getMock('currencies');
        $request = $this->getMock('\\ZenCart\\Platform\\Request');
        $map = array(array('pfrom', null, 1), array('pto', null, 10));
        $request->method('readGet')
            ->will($this->returnValueMap($map));
        $_SESSION ['customer_country_id'] = 223;
        $_SESSION ['customer_zone_id'] = 1;
        $_SESSION ['currency'] = "USD";
        $params = array('defaultSortOrder'=>0, 'currencies'=>$currencies);
        $productQuery = array();
        $f = new \ZenCart\Platform\listingBox\filters\SearchResults($request, $params);
        $f->filterItem($productQuery);
        $pq = $f->filterItem($productQuery);
        $this->assertTrue(count($pq) === 3);
    }
    public function testSearchResultsHandleTaxRatesPriceFail()
    {
        define('DISPLAY_PRICE_WITH_TAX', 'true');
        $qfr = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $qfr->EOF = true;
        $db = $this->getMockBuilder('queryFactory')
            ->getMock();
        $GLOBALS['db'] = $db;
        $db->method('Execute')->willReturn($qfr);
        $currencies = $this->getMock('currencies');
        $request = $this->getMock('\\ZenCart\\Platform\\Request');
        $_SESSION ['customer_country_id'] = 223;
        $_SESSION ['customer_zone_id'] = 1;
        $_SESSION ['currency'] = "USD";
        $params = array('defaultSortOrder'=>0, 'currencies'=>$currencies);
        $productQuery = array();
        $f = new \ZenCart\Platform\listingBox\filters\SearchResults($request, $params);
        $f->filterItem($productQuery);
        $pq = $f->filterItem($productQuery);
        //print_r($pq);
        $this->assertTrue(count($pq) === 2);
    }
    public function testSearchResultsHandleTaxRatesSessionFail()
    {
        define('DISPLAY_PRICE_WITH_TAX', 'true');
        $qfr = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $qfr->EOF = true;
        $db = $this->getMockBuilder('queryFactory')
            ->getMock();
        $GLOBALS['db'] = $db;
        $db->method('Execute')->willReturn($qfr);
        $currencies = $this->getMock('currencies');
        $request = $this->getMock('\\ZenCart\\Platform\\Request');
        $map = array(array('pfrom', null, 1), array('pto', null, 10));
        $request->method('readGet')
            ->will($this->returnValueMap($map));
        $_SESSION ['currency'] = "USD";
        $params = array('defaultSortOrder'=>0, 'currencies'=>$currencies);
        $productQuery = array();
        $f = new \ZenCart\Platform\listingBox\filters\SearchResults($request, $params);
        $f->filterItem($productQuery);
        $pq = $f->filterItem($productQuery);
        $this->assertTrue(count($pq) === 3);
    }
    public function testSearchResultsHandleCategories()
    {
        define('DISPLAY_PRICE_WITH_TAX', 'false');
        $qfr = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $qfr->EOF = true;
        $db = $this->getMockBuilder('queryFactory')
            ->getMock();
        $GLOBALS['db'] = $db;
        $db->method('Execute')->willReturn($qfr);
        $request = $this->getMock('\\ZenCart\\Platform\\Request');
        $map = array(array('categories_id', null, 1), array('inc_subcat', null, '1'));
        $request->method('readGet')
            ->will($this->returnValueMap($map));
        $_SESSION ['currency'] = "USD";
        $params = array('defaultSortOrder'=>0, 'currencies'=>'');
        $productQuery = array();
        $f = new \ZenCart\Platform\listingBox\filters\SearchResults($request, $params);
        $f->filterItem($productQuery);
        $pq = $f->filterItem($productQuery);
        $this->assertTrue(count($pq) === 2);
    }
    public function testSearchResultsHandleCategoriesNoIncSubcat()
    {
        define('DISPLAY_PRICE_WITH_TAX', 'false');
        $qfr = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $qfr->EOF = true;
        $db = $this->getMockBuilder('queryFactory')
            ->getMock();
        $GLOBALS['db'] = $db;
        $db->method('Execute')->willReturn($qfr);
        $request = $this->getMock('\\ZenCart\\Platform\\Request');
        $map = array(array('categories_id', null, 1), array('inc_subcat', null, ''));
        $request->method('readGet')
            ->will($this->returnValueMap($map));
        $_SESSION ['currency'] = "USD";
        $params = array('defaultSortOrder'=>0, 'currencies'=>'');
        $productQuery = array();
        $f = new \ZenCart\Platform\listingBox\filters\SearchResults($request, $params);
        $f->filterItem($productQuery);
        $pq = $f->filterItem($productQuery);
        //print_r($pq);
        $this->assertTrue(count($pq) === 2);
    }
    public function testSearchResultsHandleManufacturers()
    {
        define('DISPLAY_PRICE_WITH_TAX', 'false');
        $request = $this->getMock('\\ZenCart\\Platform\\Request');
        $map = array(array('manufacturers_id', null, 1));
        $request->method('readGet')
            ->will($this->returnValueMap($map));
        $_SESSION ['currency'] = "USD";
        $params = array('defaultSortOrder'=>0, 'currencies'=>'');
        $productQuery = array();
        $f = new \ZenCart\Platform\listingBox\filters\SearchResults($request, $params);
        $f->filterItem($productQuery);
        $pq = $f->filterItem($productQuery);
        $this->assertTrue(count($pq) === 2);
    }
    public function testSearchResultsHandleKeywordsNoKeyword()
    {
        define('DISPLAY_PRICE_WITH_TAX', 'false');
        $request = $this->getMock('\\ZenCart\\Platform\\Request');
        $_SESSION ['currency'] = "USD";
        $params = array('defaultSortOrder'=>0, 'currencies'=>'');
        $productQuery = array();
        $f = new \ZenCart\Platform\listingBox\filters\SearchResults($request, $params);
        $f->filterItem($productQuery);
        $pq = $f->filterItem($productQuery);
        $this->assertTrue(count($pq) === 2);
    }
    public function testSearchResultsHandleKeywords()
    {
        define('DISPLAY_PRICE_WITH_TAX', 'false');
        $request = $this->getMock('\\ZenCart\\Platform\\Request');
        $map = array(array('keyword', null, 'test'));
        $request->method('readGet')
            ->will($this->returnValueMap($map));
        $_SESSION ['currency'] = "USD";
        $params = array('defaultSortOrder'=>0, 'currencies'=>'');
        $productQuery = array();
        $f = new \ZenCart\Platform\listingBox\filters\SearchResults($request, $params);
        $f->filterItem($productQuery);
        $pq = $f->filterItem($productQuery);
        $this->assertTrue(count($pq) === 2);
    }
    public function testSearchResultsHandleKeywordsWithAnd()
    {
        define('DISPLAY_PRICE_WITH_TAX', 'false');
        $request = $this->getMock('\\ZenCart\\Platform\\Request');
        $map = array(array('keyword', null, 'test and testy'));
        $request->method('readGet')
            ->will($this->returnValueMap($map));
        $_SESSION ['currency'] = "USD";
        $params = array('defaultSortOrder'=>0, 'currencies'=>'');
        $productQuery = array();
        $f = new \ZenCart\Platform\listingBox\filters\SearchResults($request, $params);
        $f->filterItem($productQuery);
        $pq = $f->filterItem($productQuery);
        $this->assertTrue(count($pq) === 2);
    }
    public function testSearchResultsHandleKeywordsSearchDescription()
    {
        define('DISPLAY_PRICE_WITH_TAX', 'false');
        $request = $this->getMock('\\ZenCart\\Platform\\Request');
        $map = array(array('keyword', null, 'test and testy'), array('search_in_description', null, '1'));
        $request->method('readGet')
            ->will($this->returnValueMap($map));
        $_SESSION ['currency'] = "USD";
        $params = array('defaultSortOrder'=>0, 'currencies'=>'');
        $productQuery = array();
        $f = new \ZenCart\Platform\listingBox\filters\SearchResults($request, $params);
        $f->filterItem($productQuery);
        $pq = $f->filterItem($productQuery);
        $this->assertTrue(count($pq) === 2);
    }
    public function testTypeFilterNoRequestParams()
    {
        $qfr = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $qfr->EOF = true;
        $db = $this->getMockBuilder('queryFactory')
            ->getMock();
        $GLOBALS['db'] = $db;
        $db->method('Execute')->willReturn($qfr);
        $request = $this->getMock('\\ZenCart\\Platform\\Request');
        $params = array('currentCategoryId'=>1);
        $productQuery = array();
        $f = new \ZenCart\Platform\listingBox\filters\TypeFilter($request, $params);
        $f->setDBConnection($db);
        $f->filterItem($productQuery);
    }
    public function testTypeFilterWithManufacturers()
    {
        $qfr = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $qfr->EOF = true;
        $db = $this->getMockBuilder('queryFactory')
            ->getMock();
        $GLOBALS['db'] = $db;
        $db->method('Execute')->willReturn($qfr);
        $request = $this->getMock('\\ZenCart\\Platform\\Request');
        $map = array(array('manufacturers_id', '', '1'), array('filter_id', '', '1'));
        $request->method('readGet')
            ->will($this->returnValueMap($map));
        $params = array('currentCategoryId'=>1);
        $productQuery = array();
        $f = new \ZenCart\Platform\listingBox\filters\TypeFilter($request, $params);
        $f->setDBConnection($db);
        $f->filterItem($productQuery);
    }
    public function testTypeFilterWithFilterId()
    {
        $qfr = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $qfr->EOF = true;
        $db = $this->getMockBuilder('queryFactory')
            ->getMock();
        $GLOBALS['db'] = $db;
        $db->method('Execute')->willReturn($qfr);
        $request = $this->getMock('\\ZenCart\\Platform\\Request');
        $map = array(array('filter_id', '', '1'));
        $request->method('readGet')
            ->will($this->returnValueMap($map));
        $params = array('currentCategoryId'=>1);
        $productQuery = array();
        $f = new \ZenCart\Platform\listingBox\filters\TypeFilter($request, $params);
        $f->setDBConnection($db);
        $f->filterItem($productQuery);
    }
    public function testTypeFilterMusicGenre()
    {
        $qfr = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $qfr->EOF = true;
        $db = $this->getMockBuilder('queryFactory')
            ->getMock();
        $GLOBALS['db'] = $db;
        $db->method('Execute')->willReturn($qfr);
        $request = $this->getMock('\\ZenCart\\Platform\\Request');
        $map = array(array('typefilter', 'get', true));
        $request->method('has')
            ->will($this->returnValueMap($map
            ));
        $map = array(array('typefilter', null, 'music_genre'), array('filter_id', null, '1'));
        $request->method('readGet')
            ->will($this->returnValueMap($map));
        $params = array('currentCategoryId'=>1);
        $productQuery = array();
        $f = new \ZenCart\Platform\listingBox\filters\TypeFilter($request, $params);
        $f->setDBConnection($db);
        $f->filterItem($productQuery);
    }
    public function testTypeFilterMusicGenreWithId()
    {
        $qfr = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $qfr->EOF = true;
        $db = $this->getMockBuilder('queryFactory')
            ->getMock();
        $GLOBALS['db'] = $db;
        $db->method('Execute')->willReturn($qfr);
        $request = $this->getMock('\\ZenCart\\Platform\\Request');
        $map = array(array('typefilter', 'get', true));
        $request->method('has')
            ->will($this->returnValueMap($map
            ));
        $map = array(array('typefilter', null, 'music_genre'), array('music_genre_id', null, '1'), array('filter_id', null, '1'));
        $request->method('readGet')
            ->will($this->returnValueMap($map));
        $params = array('currentCategoryId'=>1);
        $productQuery = array();
        $f = new \ZenCart\Platform\listingBox\filters\TypeFilter($request, $params);
        $f->setDBConnection($db);
        $f->filterItem($productQuery);
    }
    public function testTypeFilterRecordCompany()
    {
        $qfr = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $qfr->EOF = true;
        $db = $this->getMockBuilder('queryFactory')
            ->getMock();
        $GLOBALS['db'] = $db;
        $db->method('Execute')->willReturn($qfr);
        $request = $this->getMock('\\ZenCart\\Platform\\Request');
        $map = array(array('typefilter', 'get', true));
        $request->method('has')
            ->will($this->returnValueMap($map
            ));
        $map = array(array('typefilter', null, 'record_company'), array('filter_id', null, '1'));
        $request->method('readGet')
            ->will($this->returnValueMap($map));
        $params = array('currentCategoryId'=>1);
        $productQuery = array();
        $f = new \ZenCart\Platform\listingBox\filters\TypeFilter($request, $params);
        $f->setDBConnection($db);
        $f->filterItem($productQuery);
    }
    public function testTypeFilterRecordCompanyWithId()
    {
        $qfr = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $qfr->EOF = true;
        $db = $this->getMockBuilder('queryFactory')
            ->getMock();
        $GLOBALS['db'] = $db;
        $db->method('Execute')->willReturn($qfr);
        $request = $this->getMock('\\ZenCart\\Platform\\Request');
        $map = array(array('typefilter', 'get', true));
        $request->method('has')
            ->will($this->returnValueMap($map
            ));
        $map = array(array('typefilter', null, 'record_company'), array('record_company_id', null, '1'), array('filter_id', null, '1'));
        $request->method('readGet')
            ->will($this->returnValueMap($map));
        $params = array('currentCategoryId'=>1);
        $productQuery = array();
        $f = new \ZenCart\Platform\listingBox\filters\TypeFilter($request, $params);
        $f->setDBConnection($db);
        $f->filterItem($productQuery);
    }
}
