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
        $loader->addPrefix('\ZenCart\Paginator', DIR_CATALOG_LIBRARY . 'zencart/paginator/src');
        $loader->addPrefix('\ZenCart\Platform', DIR_CATALOG_LIBRARY . 'zencart/platform/src');
        $loader->addPrefix('\ZenCart\ListingBox', DIR_CATALOG_LIBRARY . 'zencart/listingBox/src');

        define('SHOW_NEW_PRODUCTS_LIMIT', '');
        define('CUSTOMERS_APPROVAL', '');
        define('CUSTOMERS_APPROVAL_AUTHORIZATION', '');
        define('SHOW_NEW_PRODUCTS_UPCOMING_MASKED', '');
        define('PRODUCT_ALL_LIST_SORT_DEFAULT', '');
        define('MAX_DISPLAY_NEW_PRODUCTS', '');
        define('IMAGE_PRODUCT_ALL_LISTING_WIDTH', '');
        define('IMAGE_PRODUCT_ALL_LISTING_HEIGHT', '');
        define('TABLE_PRODUCTS_DESCRIPTION', 'products_description');
        define('TABLE_MANUFACTURERS', '');
        define('TABLE_CURRENCIES', '');
        define('TABLE_PRODUCTS', 'products');
        define('TABLE_CATEGORIES', 'categories');
        define('TABLE_PRODUCTS_TO_CATEGORIES', 'products_to_categories');
        define('TABLE_FEATURED', 'featured');
        define('TABLE_MUSIC_GENRE', 'music_genre');
        define('TABLE_TAX_RATES', 'tax_rates');
        define('TABLE_ZONES_TO_GEO_ZONES', 'zones_to_geo_zones');
        define('DOB_FORMAT_STRING', '##/##/##');
        define('TEXT_DISPLAY_NUMBER_OF_ENTRIES', 'Displaying <strong>%d</strong> to <strong>%d</strong> (of <strong>%d</strong> entries)');
        define('TEXT_AUTHORIZATION_PENDING_PRICE', '');


        define('TABLE_PRODUCT_MUSIC_EXTRA', 'product_music_extra');
        define('PRODUCT_LIST_ALPHA_SORTER_LIST', 'A - C:A,B,C;D - F:D,E,F;G - I:G,H,I;J - L:J,K,L;M - N:M,N;O - Q:O,P,Q;R - T:R,S,T;U - W:U,V,W;X - Z:X,Y,Z;#:0,1,2,3,4,5,6,7,8,9');
        $_SESSION['languages_id'] = 1;
        $_SESSION['customer_id'] = 1;
        $_SESSION['customers_authorization'] = 1;
    }

    public function simpleInstantiation()
    {
        $zcGlobalRegistry = new arrayObject(array());
        $zcGlobalRegistry['current_category_id'] = 1;
        $request = $this->getMock('\\ZenCart\\Platform\\Request');
        $qfr = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
//        $qfr->method('current')
//            ->will($this->onConsecutiveCalls(array('products_id' => 1)));
//        $qfr->method('RecordCount')
//            ->willReturn(1);
        $db = $this->getMockBuilder('queryFactory')
            ->getMock();
        $db->method('Execute')->willReturn($qfr);
        $GLOBALS['db'] = $db;
        $zcDiContainer = new \Aura\Di\Container(new \Aura\Di\Factory);
        $zcDiContainer->set('dbConn', $db);
        $zcDiContainer->set('request', $request);
        $zcDiContainer->set('globalRegistry', $zcGlobalRegistry);
        return $zcDiContainer;
    }
    public function iteratorInstantiation()
    {
        $request = $this->getMock('\\ZenCart\\Platform\\Request');
        $qfr = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockIterator($qfr, array(array('products_id' => 1, 'master_categories_id'=>1), array('products_id' => 2, 'master_categories_id'=>1)));
        $db = $this->getMockBuilder('queryFactory')
            ->getMock();
        $db->method('Execute')->willReturn($qfr);
        $GLOBALS['db'] = $db;
        $zcDiContainer = new \Aura\Di\Container(new \Aura\Di\Factory);
        $zcDiContainer->set('dbConn', $db);
        $zcDiContainer->set('request', $request);
        return $zcDiContainer;
    }

    public function mockListingBox($outputLayout = null, $productQuery = null)
    {
        $box = $this->getMockBuilder('\ZenCart\ListingBox\Box\AllDefault')
            ->getMock();
        $box->method('getOutputLayout')
            ->willReturn($outputLayout);
        $box->method('getProductQuery')
            ->willReturn($productQuery);
        return $box;
    }

    public function mockPaginator()
    {
        $p = $this->getMockBuilder('\ZenCart\Paginator\Paginator')
            ->disableOriginalConstructor()
            ->getMock();
        $p->method('getItems')->willReturn(array(array('product_id'=>1)));
        return $p;
    }
    public function mockDerivedItemManager()
    {
        $dim = $this->getMockBuilder('\ZenCart\ListingBox\DerivedItemManager')
            ->getMock();
//        $p->method('getItems')->willReturn(array(array('product_id'=>1)));
        return $dim;
    }

    protected function mockIterator(Iterator $iterator, array $items, $includeCallsToKey = FALSE)
    {
        $iterator->expects($this->at(0))
            ->method('rewind');
        $iterator->expects($this->at(1))
            ->method('move');
        $counter = 2;
        foreach ($items as $k => $v) {
            $iterator->expects($this->at($counter++))
                ->method('valid')
                ->will($this->returnValue(TRUE));
            $iterator->expects($this->at($counter++))
                ->method('current')
                ->will($this->returnValue($v));
            if ($includeCallsToKey) {
                $iterator->expects($this->at($counter++))
                    ->method('key')
                    ->will($this->returnValue($k));
            }
            $iterator->expects($this->at($counter++))
                ->method('next');
        }
        $iterator->expects($this->at($counter))
            ->method('valid')
            ->will($this->returnValue(FALSE));
    }
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
