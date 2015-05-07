<?php
/**
 * File contains framework test cases
 *
 * @package tests
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */

require_once('zcCatalogTestCase.php');

/**
 * Testing Library
 */
abstract class zcListingBoxTestCase extends zcCatalogTestCase
{
    public function setUp()
    {
        parent::setUp();
        require DIR_CATALOG_LIBRARY . 'aura/autoload/src/Loader.php';
        require_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'db/mysql/query_factory.php';
        require_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'currencies.php';
        $loader = new \Aura\Autoload\Loader;
        $loader->register();
        $loader->addPrefix('\Aura\Web', DIR_CATALOG_LIBRARY . 'aura/web/src');
        $loader->addPrefix('\Aura\Di', DIR_CATALOG_LIBRARY . 'aura/di/src');
        $loader->addPrefix('\ZenCart\Platform', DIR_CATALOG_LIBRARY . 'zencart/platform/src');

//        define('SHOW_NEW_PRODUCTS_LIMIT', '');
        define('CUSTOMERS_APPROVAL', '');
        define('CUSTOMERS_APPROVAL_AUTHORIZATION', '');
//        define('SHOW_NEW_PRODUCTS_UPCOMING_MASKED', '');
//        define('PRODUCT_ALL_LIST_SORT_DEFAULT', '');
//        define('MAX_DISPLAY_NEW_PRODUCTS', '');
        define('PRODUCTS_OPTIONS_TYPE_READONLY_IGNORED', 0);
        define('STORE_STATUS', 0);
        define('STORE_ZONE', 0);
        define('STORE_COUNTRY', 0);
        define('TEXT_AUTHORIZATION_PENDING_BUTTON_REPLACE', 0);
        define('TEXT_NO_PRODUCTS', 0);
        define('PRODUCTS_QUANTITY_MIN_TEXT_LISTING', 0);
        define('PRODUCTS_QUANTITY_UNIT_TEXT_LISTING', 0);
        define('TEXT_PRODUCTS_MIX_ON', 'TEXT_PRODUCTS_MIX_ON');
        define('CAPTION_UPCOMING_PRODUCTS', 'CAPTION_UPCOMING_PRODUCTS');
//        define('IMAGE_PRODUCT_ALL_LISTING_WIDTH', '');
//        define('IMAGE_PRODUCT_ALL_LISTING_HEIGHT', '');
        define('TABLE_PRODUCTS_DESCRIPTION', '');
        define('TABLE_MANUFACTURERS', '');
        define('TABLE_CURRENCIES', '');
        define('TABLE_PRODUCTS', '');
        define('TABLE_CATEGORIES', 'categories');
        define('TABLE_CONFIGURATION', '');
        define('TABLE_PRODUCTS_TO_CATEGORIES', '');
//        define('TABLE_FEATURED', 'featured');
        define('TABLE_MUSIC_GENRE', '');
        define('TABLE_PRODUCT_MUSIC_EXTRA', '');
        define('TABLE_RECORD_COMPANY', '');
        define('TABLE_TAX_RATES', '');
        define('TABLE_ZONES_TO_GEO_ZONES', '');
        define('TABLE_PRODUCT_TYPES', '');
        define('TABLE_PRODUCT_TYPE_LAYOUT', '');
        define('TABLE_PRODUCTS_ATTRIBUTES', '');
        define('TABLE_PRODUCTS_OPTIONS', '');
//
        define('TABLE_HEADING_PRODUCTS', 'TABLE_HEADING_PRODUCTS');
        define('TABLE_HEADING_MODEL', 'TABLE_HEADING_MODEL');
        define('TABLE_HEADING_MANUFACTURER', 'TABLE_HEADING_MANUFACTURER');
        define('TABLE_HEADING_WEIGHT', 'TABLE_HEADING_WEIGHT');
        define('TABLE_HEADING_IMAGE', 'TABLE_HEADING_IMAGE');
        define('TABLE_HEADING_QUANTITY', 'TABLE_HEADING_QUANTITY');
        define('TABLE_HEADING_PRICE', 'TABLE_HEADING_PRICE');
//
        define('DOB_FORMAT_STRING', '##/##/##');
//        define('TEXT_DISPLAY_NUMBER_OF_ENTRIES', 'Displaying <strong>%d</strong> to <strong>%d</strong> (of <strong>%d</strong> entries)');
        define('TEXT_AUTHORIZATION_PENDING_PRICE', '');
        define('TEXT_PRODUCTS_MODEL', 'TEXT_PRODUCTS_MODEL');
        define('TEXT_PRODUCTS_QUANTITY', 'TEXT_PRODUCTS_QUANTITY');
        define('TEXT_OUT_OF_STOCK', 'TEXT_OUT_OF_STOCK');
        define('TEXT_PRODUCTS_WEIGHT', 'TEXT_PRODUCTS_WEIGHT');
        define('TEXT_SHIPPING_WEIGHT', 'TEXT_SHIPPING_WEIGHT');
        define('TEXT_DATE_ADDED', 'TEXT_DATE_ADDED');
        define('TEXT_PRICE', 'TEXT_PRICE');
        define('MORE_INFO_TEXT', 'MORE_INFO_TEXT');
//        define('LIST_IMAGE', '');
//        define('LIST_DESCRIPTION', '');
//        define('TEST_LIST_DESCRIPTION', 1);
        define('PRODUCTS_IMAGE_NO_IMAGE_STATUS', 0);
//
        define('PRODUCTS_LIST_PRICE_WIDTH', 1);
        define('PRODUCT_LIST_DESCRIPTION', 1);
//
        define('PRODUCT_LIST_MODEL', 1);
        define('PRODUCT_LIST_NAME', 1);
        define('PRODUCT_LIST_MANUFACTURER', 1);
        define('PRODUCT_LIST_PRICE', 1);
        define('PRODUCT_LIST_QUANTITY', 1);
        define('PRODUCT_LIST_WEIGHT', 1);
        define('PRODUCT_LIST_IMAGE', 1);
        define('PRODUCT_LIST_FILTER', 1);
//
        if (!defined('PRODUCT_LIST_PRICE_BUY_NOW')) define('PRODUCT_LIST_PRICE_BUY_NOW', 0);
        if (!defined('PRODUCT_LISTING_MULTIPLE_ADD_TO_CART')) define('PRODUCT_LISTING_MULTIPLE_ADD_TO_CART', 0);
//
        define('PRODUCT_LIST_ALPHA_SORTER', 1);
        define('FILENAME_DEFAULT', 1);
        define('TEXT_PRODUCT_LISTING_MULTIPLE_ADD_TO_CART', 'Add');
//
//        define('TABLE_PRODUCT_MUSIC_EXTRA', 'product_music_extra');
        define('PRODUCT_LIST_ALPHA_SORTER_LIST', 'A - C:A,B,C;D - F:D,E,F;G - I:G,H,I;J - L:J,K,L;M - N:M,N;O - Q:O,P,Q;R - T:R,S,T;U - W:U,V,W;X - Z:X,Y,Z;#:0,1,2,3,4,5,6,7,8,9');
        $_SESSION['languages_id'] = 1;
        $_SESSION['customer_id'] = 1;
        $_SESSION['customers_authorization'] = 1;
    }

//    public function simpleInstantiation()
//    {
//        $zcGlobalRegistry = new arrayObject(array());
//        $zcGlobalRegistry['current_category_id'] = 1;
//        $request = $this->getMock('\\ZenCart\\Platform\\Request');
//        $qfr = $this->getMockBuilder('queryFactoryResult')
//            ->disableOriginalConstructor()
//            ->getMock();
////        $qfr->method('current')
////            ->will($this->onConsecutiveCalls(array('products_id' => 1)));
////        $qfr->method('RecordCount')
////            ->willReturn(1);
//        $db = $this->getMockBuilder('queryFactory')
//            ->getMock();
//        $db->method('Execute')->willReturn($qfr);
//        $GLOBALS['db'] = $db;
//        $zcDiContainer = new \Aura\Di\Container(new \Aura\Di\Factory);
//        $zcDiContainer->set('dbConn', $db);
//        $zcDiContainer->set('request', $request);
//        $zcDiContainer->set('globalRegistry', $zcGlobalRegistry);
//        return $zcDiContainer;
//    }
//    public function iteratorInstantiation()
//    {
//        $request = $this->getMock('\\ZenCart\\Platform\\Request');
//        $qfr = $this->getMockBuilder('queryFactoryResult')
//            ->disableOriginalConstructor()
//            ->getMock();
//        $this->mockIterator($qfr, array(array('products_id' => 1, 'master_categories_id'=>1), array('products_id' => 2, 'master_categories_id'=>1)));
//        $db = $this->getMockBuilder('queryFactory')
//            ->getMock();
//        $db->method('Execute')->willReturn($qfr);
//        $GLOBALS['db'] = $db;
//        $zcDiContainer = new \Aura\Di\Container(new \Aura\Di\Factory);
//        $zcDiContainer->set('dbConn', $db);
//        $zcDiContainer->set('request', $request);
//        return $zcDiContainer;
//    }
//
//    public function mockListingBox($outputLayout = null, $productQuery = null)
//    {
//        $box = $this->getMockBuilder('\ZenCart\ListingBox\Box\AllDefault')
//            ->getMock();
//        $box->method('getOutputLayout')
//            ->willReturn($outputLayout);
//        $box->method('getProductQuery')
//            ->willReturn($productQuery);
//        return $box;
//    }
//
//    public function mockPaginator()
//    {
//        $p = $this->getMockBuilder('\ZenCart\Paginator\Paginator')
//            ->disableOriginalConstructor()
//            ->getMock();
//        $p->method('getItems')->willReturn(array(array('product_id'=>1)));
//        return $p;
//    }
//    public function mockDerivedItemManager()
//    {
//        $dim = $this->getMockBuilder('\ZenCart\ListingBox\DerivedItemManager')
//            ->getMock();
////        $p->method('getItems')->willReturn(array(array('product_id'=>1)));
//        return $dim;
//    }
//

    public function mockIterator(PHPUnit_Framework_MockObject_MockObject $iteratorMock, array $items)
    {
        $iteratorData = new \stdClass();
        $iteratorData->array = $items;
        $iteratorData->position = 0;

        $iteratorMock->expects($this->any())
            ->method('rewind')
            ->will(
                $this->returnCallback(
                    function() use ($iteratorData) {
                        $iteratorData->position = 0;
                    }
                )
            );

        $iteratorMock->expects($this->any())
            ->method('current')
            ->will(
                $this->returnCallback(
                    function() use ($iteratorData) {
                        return $iteratorData->array[$iteratorData->position];
                    }
                )
            );

        $iteratorMock->expects($this->any())
            ->method('key')
            ->will(
                $this->returnCallback(
                    function() use ($iteratorData) {
                        return $iteratorData->position;
                    }
                )
            );

        $iteratorMock->expects($this->any())
            ->method('next')
            ->will(
                $this->returnCallback(
                    function() use ($iteratorData) {
                        $iteratorData->position++;
                    }
                )
            );

        $iteratorMock->expects($this->any())
            ->method('valid')
            ->will(
                $this->returnCallback(
                    function() use ($iteratorData) {
                        return isset($iteratorData->array[$iteratorData->position]);
                    }
                )
            );

        $iteratorMock->expects($this->any())
            ->method('count')
            ->will(
                $this->returnCallback(
                    function() use ($iteratorData) {
                        return sizeof($iteratorData->array);
                    }
                )
            );

        return $iteratorMock;
    }
//    protected function mockIterator(Iterator $iterator, array $items, $includeCallsToKey = FALSE)
//    {
//        echo 'HERER';
//        $iterator->expects($this->at(0))
//            ->method('rewind');
//        $iterator->expects($this->at(1))
//            ->method('move');
//        $counter = 2;
//        foreach ($items as $k => $v) {
//            $iterator->expects($this->at($counter++))
//                ->method('valid')
//                ->will($this->returnValue(TRUE));
//            $iterator->expects($this->at($counter++))
//                ->method('current')
//                ->will($this->returnValue($v));
//            if ($includeCallsToKey) {
//                $iterator->expects($this->at($counter++))
//                    ->method('key')
//                    ->will($this->returnValue($k));
//            }
//            $iterator->expects($this->at($counter++))
//                ->method('next');
//        }
//        $iterator->expects($this->at($counter))
//            ->method('valid')
//            ->will($this->returnValue(FALSE));
//    }
}

if (!function_exists('zen_date_raw')) {
    function zen_date_raw($date, $reverse = false)
    {
        if ($reverse) {
            return substr($date, 3, 2) . substr($date, 0, 2) . substr($date, 6, 4);
        } else {
            return substr($date, 6, 4) . substr($date, 0, 2) . substr($date, 3, 2);
        }
    }
}
if (!function_exists('zen_href_link')) {
    function zen_href_link()
    {
    }
}
if (!function_exists('zen_image')) {
    function zen_image()
    {
    }
}
