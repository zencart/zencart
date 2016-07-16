<?php
/**
 * File contains paginator adapter test cases
 *
 * @package tests
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
require_once(__DIR__ . '/../support/zcTestCase.php');
use ZenCart\Request\Request;

/**
 * Testing Library
 */
class testFilterCase extends zcTestCase
{
    public function setup()
    {
        parent::setup();
        define('PRODUCT_LIST_ALPHA_SORTER', 1);
        define('PRODUCT_LIST_ALPHA_SORTER_LIST', 'A - C:A,B,C;D - F:D,E,F;G - I:G,H,I;J - L:J,K,L;M - N:M,N;O - Q:O,P,Q;R - T:R,S,T;U - W:U,V,W;X - Z:X,Y,Z;#:0,1,2,3,4,5,6,7,8,9');
        define('DOB_FORMAT_STRING', '##/##/##');
        define('TABLE_PRODUCT_PIECE_EXTRA', DB_PREFIX . 'product_piece_extra');
        define('TABLE_PIECE_GENRE', DB_PREFIX . 'piece_genre');
        define('TABLE_RECORD_COMPANY', DB_PREFIX . 'record_company');
        define('PRODUCT_LIST_FILTER', 1);
        $_SESSION['languages_id'] = 1;

//        require DIR_FS_CATALOG . 'includes/functions/functions_categories.php';
        require_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'db/mysql/query_factory.php';
        require DIR_FS_CATALOG . 'includes/functions/functions_general.php';
        require_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'currencies.php';
        $loader = new \Aura\Autoload\Loader;
        $loader->register();
        $loader->addPrefix('\ZenCart\QueryBuilderDefinitions', DIR_CATALOG_LIBRARY . 'zencart/QueryBuilderDefinitions/src');
        $loader->addPrefix('\Aura\Web', DIR_CATALOG_LIBRARY . 'aura/web/src');
        $loader->addPrefix('\ZenCart\Request', DIR_CATALOG_LIBRARY . 'zencart/Request/src');
    }

    public function testAlphaFilterNoRequestParams()
    {
        //$request = $this->getMock('\\ZenCart\\Request\\Request');
        $request = $this->getMockBuilder('\ZenCart\Request\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $params = array();
        $listingQuery = array();
        $f = new \ZenCart\QueryBuilderDefinitions\filters\AlphaFilter($request, $params);
        $f->filterItem($listingQuery);
    }

    public function testAlphaFilterWithRequestParams()
    {
        $request = $this->getMockBuilder('\ZenCart\Request\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $request->method('has')->willReturn(true);
        $request->method('readGet')->willReturn(1);
        $params = array();
        $listingQuery = array();
        $f = new \ZenCart\QueryBuilderDefinitions\filters\AlphaFilter($request, $params);
        $pq = $f->filterItem($listingQuery);
        $this->assertTrue(count($pq['whereClauses']) == 1);
    }

    public function testCategoryFilterNoRequestParams()
    {
        $request = $this->getMockBuilder('\ZenCart\Request\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $params = array('new_products_category_id' => 0, 'cPath' => '');
        $listingQuery = array();
        $f = new \ZenCart\QueryBuilderDefinitions\filters\CategoryFilter($request, $params);
        $f->filterItem($listingQuery);
        $pq = $f->filterItem($listingQuery);
        $this->assertTrue(count($pq) === 0);
    }

    public function testCategoryFilterManufacturers()
    {
        $request = $this->getMockBuilder('\ZenCart\Request\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $request->method('readGet')
            ->will($this->onConsecutiveCalls(2));
        $params = array('new_products_category_id' => 0, 'cPath' => '');
        $listingQuery = array();
        $f = new \ZenCart\QueryBuilderDefinitions\filters\CategoryFilter($request, $params);
        $f->filterItem($listingQuery);
        $pq = $f->filterItem($listingQuery);
        $this->assertTrue(count($pq) === 0);
    }

    public function testCategoryFilterFilterId()
    {
        $qfr = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $db = $this->getMockBuilder('queryFactory')
            ->getMock();
        $db->method('execute')->willReturn($qfr);
        $GLOBALS['db'] = $db;
        $request = $this->getMockBuilder('\ZenCart\Request\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $request->method('readGet')
            ->will($this->onConsecutiveCalls(2, 1, 1, 1));
        $params = array('new_products_category_id' => 0, 'cPath' => '');
        $listingQuery = array();
        $f = new \ZenCart\QueryBuilderDefinitions\filters\CategoryFilter($request, $params);
        $f->filterItem($listingQuery);
        $pq = $f->filterItem($listingQuery);
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
        $request = $this->getMockBuilder('\ZenCart\Request\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $request->method('readGet')
            ->will($this->onConsecutiveCalls(2, 1, 1, 1));
        $params = array('new_products_category_id' => 0, 'cPath' => '1_3');
        $listingQuery = array();
        $f = new \ZenCart\QueryBuilderDefinitions\filters\CategoryFilter($request, $params);
        $pq = $f->filterItem($listingQuery);
        $this->assertTrue(count($pq) === 3);
    }

    public function testDisplaySorterNoRequestParams()
    {
        $request = $this->getMockBuilder('\ZenCart\Request\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $params = array('defaultSortOrder' => 0);
        $listingQuery = array();
        $f = new \ZenCart\QueryBuilderDefinitions\filters\DisplayOrderSorter($request, $params);
        $f->filterItem($listingQuery);
        $pq = $f->filterItem($listingQuery);
        $this->assertTrue(count($pq) === 1);
    }

    public function testSearchResultsNoRequestParams()
    {
        define('DISPLAY_PRICE_WITH_TAX', 'false');
        $request = $this->getMockBuilder('\ZenCart\Request\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $params = array('defaultSortOrder' => 0, 'currencies' => '');
        $listingQuery = array();
        $f = new \ZenCart\QueryBuilderDefinitions\filters\SearchResults($request, $params);
        $f->filterItem($listingQuery);
        $pq = $f->filterItem($listingQuery);
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
        $request = $this->getMockBuilder('\ZenCart\Request\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $map = array(array('pfrom', null, 1), array('pto', null, 10));
        $request->method('readGet')
            ->will($this->returnValueMap($map));
        $_SESSION ['customer_country_id'] = 223;
        $_SESSION ['customer_zone_id'] = 1;
        $_SESSION ['currency'] = "USD";
        $params = array('defaultSortOrder' => 0, 'currencies' => $currencies);
        $listingQuery = array();
        $f = new \ZenCart\QueryBuilderDefinitions\filters\SearchResults($request, $params);
        $f->filterItem($listingQuery);
        $pq = $f->filterItem($listingQuery);
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
        $request = $this->getMockBuilder('\ZenCart\Request\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $_SESSION ['customer_country_id'] = 223;
        $_SESSION ['customer_zone_id'] = 1;
        $_SESSION ['currency'] = "USD";
        $params = array('defaultSortOrder' => 0, 'currencies' => $currencies);
        $listingQuery = array();
        $f = new \ZenCart\QueryBuilderDefinitions\filters\SearchResults($request, $params);
        $f->filterItem($listingQuery);
        $pq = $f->filterItem($listingQuery);
        //print_r($pq);
        $this->assertTrue(count($pq) === 2);
    }

    public function testSearchResultsHandleTaxRatesSessionFail()
    {
        define('DISPLAY_PRICE_WITH_TAX', 'true');
        define('STORE_COUNTRY', 0);
        define('STORE_ZONE', 0);
        $qfr = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $qfr->EOF = true;
        $db = $this->getMockBuilder('queryFactory')
            ->getMock();
        $GLOBALS['db'] = $db;
        $db->method('Execute')->willReturn($qfr);
        $currencies = $this->getMock('currencies');
        $request = $this->getMockBuilder('\ZenCart\Request\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $map = array(array('pfrom', null, 1), array('pto', null, 10));
        $request->method('readGet')
            ->will($this->returnValueMap($map));
        $_SESSION ['currency'] = "USD";
        $params = array('defaultSortOrder' => 0, 'currencies' => $currencies);
        $listingQuery = array();
        $f = new \ZenCart\QueryBuilderDefinitions\filters\SearchResults($request, $params);
        $f->filterItem($listingQuery);
        $pq = $f->filterItem($listingQuery);
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
        $request = $this->getMockBuilder('\ZenCart\Request\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $map = array(array('categories_id', null, 1), array('inc_subcat', null, '1'));
        $request->method('readGet')
            ->will($this->returnValueMap($map));
        $_SESSION ['currency'] = "USD";
        $params = array('defaultSortOrder' => 0, 'currencies' => '');
        $listingQuery = array();
        $f = new \ZenCart\QueryBuilderDefinitions\filters\SearchResults($request, $params);
        $f->filterItem($listingQuery);
        $pq = $f->filterItem($listingQuery);
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
        $request = $this->getMockBuilder('\ZenCart\Request\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $map = array(array('categories_id', null, 1), array('inc_subcat', null, ''));
        $request->method('readGet')
            ->will($this->returnValueMap($map));
        $_SESSION ['currency'] = "USD";
        $params = array('defaultSortOrder' => 0, 'currencies' => '');
        $listingQuery = array();
        $f = new \ZenCart\QueryBuilderDefinitions\filters\SearchResults($request, $params);
        $f->filterItem($listingQuery);
        $pq = $f->filterItem($listingQuery);
        //print_r($pq);
        $this->assertTrue(count($pq) === 2);
    }

    public function testSearchResultsHandleManufacturers()
    {
        define('DISPLAY_PRICE_WITH_TAX', 'false');
        $request = $this->getMockBuilder('\ZenCart\Request\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $map = array(array('manufacturers_id', null, 1));
        $request->method('readGet')
            ->will($this->returnValueMap($map));
        $_SESSION ['currency'] = "USD";
        $params = array('defaultSortOrder' => 0, 'currencies' => '');
        $listingQuery = array();
        $f = new \ZenCart\QueryBuilderDefinitions\filters\SearchResults($request, $params);
        $f->filterItem($listingQuery);
        $pq = $f->filterItem($listingQuery);
        $this->assertTrue(count($pq) === 2);
    }

    public function testSearchResultsHandleKeywordsNoKeyword()
    {
        define('DISPLAY_PRICE_WITH_TAX', 'false');
        $request = $this->getMockBuilder('\ZenCart\Request\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $_SESSION ['currency'] = "USD";
        $params = array('defaultSortOrder' => 0, 'currencies' => '');
        $listingQuery = array();
        $f = new \ZenCart\QueryBuilderDefinitions\filters\SearchResults($request, $params);
        $f->filterItem($listingQuery);
        $pq = $f->filterItem($listingQuery);
        $this->assertTrue(count($pq) === 2);
    }

    public function testSearchResultsHandleKeywords()
    {
        define('DISPLAY_PRICE_WITH_TAX', 'false');
        $request = $this->getMockBuilder('\ZenCart\Request\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $map = array(array('keyword', null, 'test'));
        $request->method('readGet')
            ->will($this->returnValueMap($map));
        $_SESSION ['currency'] = "USD";
        $params = array('defaultSortOrder' => 0, 'currencies' => '');
        $listingQuery = array();
        $f = new \ZenCart\QueryBuilderDefinitions\filters\SearchResults($request, $params);
        $f->filterItem($listingQuery);
        $pq = $f->filterItem($listingQuery);
        $this->assertTrue(count($pq) === 2);
    }

    public function testSearchResultsHandleKeywordsWithAnd()
    {
        define('DISPLAY_PRICE_WITH_TAX', 'false');
        $request = $this->getMockBuilder('\ZenCart\Request\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $map = array(array('keyword', null, 'test and testy'));
        $request->method('readGet')
            ->will($this->returnValueMap($map));
        $_SESSION ['currency'] = "USD";
        $params = array('defaultSortOrder' => 0, 'currencies' => '');
        $listingQuery = array();
        $f = new \ZenCart\QueryBuilderDefinitions\filters\SearchResults($request, $params);
        $f->filterItem($listingQuery);
        $pq = $f->filterItem($listingQuery);
        $this->assertTrue(count($pq) === 2);
    }

    public function testSearchResultsHandleKeywordsSearchDescription()
    {
        define('DISPLAY_PRICE_WITH_TAX', 'false');
        $request = $this->getMockBuilder('\ZenCart\Request\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $map = array(array('keyword', null, 'test and testy'), array('search_in_description', null, '1'));
        $request->method('readGet')
            ->will($this->returnValueMap($map));
        $_SESSION ['currency'] = "USD";
        $params = array('defaultSortOrder' => 0, 'currencies' => '');
        $listingQuery = array();
        $f = new \ZenCart\QueryBuilderDefinitions\filters\SearchResults($request, $params);
        $f->filterItem($listingQuery);
        $pq = $f->filterItem($listingQuery);
        $this->assertTrue(count($pq) === 2);
    }

    public function testTypeFilterNoRequestParams()
    {
//        define('PRODUCT_LIST_FILTER', 1);
        $qfr = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $qfr->EOF = true;
        $db = $this->getMockBuilder('queryFactory')
            ->getMock();
        $GLOBALS['db'] = $db;
        $db->method('Execute')->willReturn($qfr);
        $request = $this->getMockBuilder('\ZenCart\Request\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $params = array('currentCategoryId' => 1);
        $listingQuery = array();
        $f = new \ZenCart\QueryBuilderDefinitions\filters\TypeFilter($request, $params);
        $f->setDBConnection($db);
        $f->filterItem($listingQuery);
    }

    public function testTypeFilterWithManufacturers()
    {
//        define('PRODUCT_LIST_FILTER', 1);
        $qfr = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $qfr->EOF = true;
        $db = $this->getMockBuilder('queryFactory')
            ->getMock();
        $GLOBALS['db'] = $db;
        $db->method('Execute')->willReturn($qfr);
        $request = $this->getMockBuilder('\ZenCart\Request\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $map = array(array('manufacturers_id', '', '1'), array('filter_id', '', '1'));
        $request->method('readGet')
            ->will($this->returnValueMap($map));
        $params = array('currentCategoryId' => 1);
        $listingQuery = array();
        $f = new \ZenCart\QueryBuilderDefinitions\filters\TypeFilter($request, $params);
        $f->setDBConnection($db);
        $f->filterItem($listingQuery);
    }

    public function testTypeFilterWithFilterId()
    {
//        define('PRODUCT_LIST_FILTER', 1);
        $qfr = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $qfr->EOF = true;
        $db = $this->getMockBuilder('queryFactory')
            ->getMock();
        $GLOBALS['db'] = $db;
        $db->method('Execute')->willReturn($qfr);
        $request = $this->getMockBuilder('\ZenCart\Request\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $map = array(array('filter_id', '', '1'));
        $request->method('readGet')
            ->will($this->returnValueMap($map));
        $params = array('currentCategoryId' => 1);
        $listingQuery = array();
        $f = new \ZenCart\QueryBuilderDefinitions\filters\TypeFilter($request, $params);
        $f->setDBConnection($db);
        $f->filterItem($listingQuery);
    }

    public function testTypeFilterPieceGenre()
    {
        $qfr = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $qfr->EOF = true;
        $db = $this->getMockBuilder('queryFactory')
            ->getMock();
        $GLOBALS['db'] = $db;
        $db->method('Execute')->willReturn($qfr);
        $request = $this->getMockBuilder('\ZenCart\Request\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $map = array(array('typefilter', 'get', true));
        $request->method('has')
            ->will($this->returnValueMap($map
            ));
        $map = array(array('typefilter', null, 'piece_genre'), array('filter_id', null, '1'));
        $request->method('readGet')
            ->will($this->returnValueMap($map));
        $params = array('currentCategoryId' => 1);
        $listingQuery = array();
        $f = new \ZenCart\QueryBuilderDefinitions\filters\TypeFilter($request, $params);
        $f->setDBConnection($db);
        $f->filterItem($listingQuery);
    }

    public function testTypeFilterPieceGenreWithId()
    {
        $qfr = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $qfr->EOF = true;
        $db = $this->getMockBuilder('queryFactory')
            ->getMock();
        $GLOBALS['db'] = $db;
        $db->method('Execute')->willReturn($qfr);
        $request = $this->getMockBuilder('\ZenCart\Request\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $map = array(array('typefilter', 'get', true));
        $request->method('has')
            ->will($this->returnValueMap($map
            ));
        $map = array(array('typefilter', null, 'piece_genre'), array('piece_genre_id', null, '1'), array('filter_id', null, '1'));
        $request->method('readGet')
            ->will($this->returnValueMap($map));
        $params = array('currentCategoryId' => 1);
        $listingQuery = array();
        $f = new \ZenCart\QueryBuilderDefinitions\filters\TypeFilter($request, $params);
        $f->setDBConnection($db);
        $f->filterItem($listingQuery);
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
        $request = $this->getMockBuilder('\ZenCart\Request\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $map = array(array('typefilter', 'get', true));
        $request->method('has')
            ->will($this->returnValueMap($map
            ));
        $map = array(array('typefilter', null, 'record_company'), array('filter_id', null, '1'));
        $request->method('readGet')
            ->will($this->returnValueMap($map));
        $params = array('currentCategoryId' => 1);
        $listingQuery = array();
        $f = new \ZenCart\QueryBuilderDefinitions\filters\TypeFilter($request, $params);
        $f->setDBConnection($db);
        $f->filterItem($listingQuery);
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
        $request = $this->getMockBuilder('\ZenCart\Request\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $map = array(array('typefilter', 'get', true));
        $request->method('has')
            ->will($this->returnValueMap($map
            ));
        $map = array(
            array('typefilter', null, 'record_company'),
            array('record_company_id', null, '1'),
            array('filter_id', null, '1')
        );
        $request->method('readGet')
            ->will($this->returnValueMap($map));
        $params = array('currentCategoryId' => 1);
        $listingQuery = array();
        $f = new \ZenCart\QueryBuilderDefinitions\filters\TypeFilter($request, $params);
        $f->setDBConnection($db);
        $f->filterItem($listingQuery);
    }
}
