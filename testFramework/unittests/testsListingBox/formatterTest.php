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

//use ZenCart\Platform\Paginator\adapters\QueryFactory;
/**
 * Testing Library
 */
class testFormatterCase extends zcTestCase
{
    public function setup()
    {
        parent::setup();
        define('PRODUCTS_IMAGE_NO_IMAGE_STATUS', 0);
//        define('TEST_LIST_IMAGE', 1);
//        define('TEST_LIST_NAME', 1);
//        define('TEST_LIST_MODEL', 1);
//        define('TEST_LIST_QUANTITY', 1);
//        define('TEST_LIST_WEIGHT', 1);
//        define('TEST_LIST_DATE_ADDED', 1);
//        define('TEST_LIST_PRICE', 1);
//        define('TEST_LISTING_MULTIPLE_ADD_TO_CART', 1);
//        define('TOPMOST_CATEGORY_PARENT_ID', 0);
//        if (!defined('PRODUCT_LISTING_MULTIPLE_ADD_TO_CART')) define('PRODUCT_LISTING_MULTIPLE_ADD_TO_CART', 0);
//
//
        require DIR_FS_CATALOG . 'includes/functions/html_output.php';
        require DIR_FS_CATALOG . 'includes/functions/functions_general.php';
//        require DIR_FS_CATALOG . 'includes/functions/functions_lookups.php';
        require_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'db/mysql/query_factory.php';
        $loader = new \Aura\Autoload\Loader;
        $loader->register();
        $loader->addPrefix('\ZenCart\ListingQueryAndOutput', DIR_CATALOG_LIBRARY . 'zencart/ListingQueryAndOutput/src');
        $loader->addPrefix('\Aura\Web', DIR_CATALOG_LIBRARY . 'aura/web/src');
        $loader->addPrefix('\ZenCart\Request', DIR_CATALOG_LIBRARY . 'zencart/Request/src');
    }

    public function testColumnarFormatterNoItems()
    {
        $outputLayout = array('formatter' => array('params' => array('columnCount' => 1, 'PRODUCTS_IMAGE_NO_IMAGE_STATUS' => 0)));
        $itemList = array();
        $f = new \ZenCart\ListingQueryAndOutput\formatters\Columnar($itemList, $outputLayout);
        $f->format();
        $r = $f->getFormattedResults();
        $this->assertTrue(count($r) === 0);
    }

    public function testColumnarFormatterMultiRow()
    {
        $outputLayout = array('formatter' => array('params' => array('columnCount' => 2, 'PRODUCTS_IMAGE_NO_IMAGE_STATUS' => 0)));
        $itemList = array(array('products_image' => ''), array('products_image' => ''), array('products_image' => ''));
        $f = new \ZenCart\ListingQueryAndOutput\formatters\Columnar($itemList, $outputLayout);
        $f->format();
        $r = $f->getFormattedResults();
        $this->assertTrue(count($r) === 2);
    }

    public function testColumnarFormatterSingleRow()
    {
        $outputLayout = array('formatter' => array('params' => array('columnCount' => 2, 'PRODUCTS_IMAGE_NO_IMAGE_STATUS' => 0)));
        $itemList = array(array('products_image' => ''));
        $f = new \ZenCart\ListingQueryAndOutput\formatters\Columnar($itemList, $outputLayout);
        $f->format();
        $r = $f->getFormattedResults();
        $this->assertTrue(count($r) === 1);
    }

    public function testListStandardFormatterNoItems()
    {
        define('TEST_BUY_NOW', 1);
        $qfr = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $db = $this->getMockBuilder('queryFactory')
            ->getMock();
        $GLOBALS['db'] = $db;
        $db->method('Execute')->willReturn($qfr);
        $outputLayout = array(
            'formatter' => array(
                'params' => array(
                    'columnCount' => 2,
                    'PRODUCTS_IMAGE_NO_IMAGE_STATUS' => 0,
                    'definePrefix' => 'TEST_',
                    'imageListingWidth' => '',
                    'imageListingHeight' => ''
                )
            )
        );
        $itemList = array();
        $f = new \ZenCart\ListingQueryAndOutput\formatters\ListStandard($itemList, $outputLayout);
        $f->setDBConnection($db);
        $f->format();
        $r = $f->getFormattedResults();
        $this->assertTrue(count($r) === 0);
    }

    public function testListStandardFormatterMultiRow()
    {
        define('TEST_LIST_GROUP_ID', 1);
        define('TEST_BUY_NOW', 1);
        define('STORE_STATUS', 1);
        define('ENABLE_SSL', 1);
        define('SEARCH_ENGINE_FRIENDLY_URLS', 0);
        define('PROPORTIONAL_IMAGES_STATUS', 0);
        define('IMAGE_REQUIRED', 0);
        define('TEST_LIST_IMAGE', 1);
        define('TEST_LIST_NAME', 1);
        define('TEST_LIST_MODEL', 1);
        define('TEST_LIST_QUANTITY', 1);
        define('TEST_LIST_WEIGHT', 1);
        define('TEST_LIST_DATE_ADDED', 1);
        define('TEST_LIST_PRICE', 1);
        define('TEXT_PRICE', 'TEXT_PRICE');
        define('TEXT_SHOWCASE_ONLY', 'TEXT_SHOWCASE_ONLY');
        define('PRODUCTS_QUANTITY_MIN_TEXT_LISTING', 'PRODUCTS_QUANTITY_MIN_TEXT_LISTING');
        define('PRODUCTS_QUANTITY_UNIT_TEXT_LISTING', 'PRODUCTS_QUANTITY_UNIT_TEXT_LISTING');
        define('MORE_INFO_TEXT', 'MORE_INFO_TEXT');
        define('TEST_LISTING_MULTIPLE_ADD_TO_CART', 1);
        define('PRODUCTS_OPTIONS_TYPE_RADIO', 1);
        define('PRODUCTS_OPTIONS_TYPE_SELECT', 1);
        define('PRODUCTS_OPTIONS_TYPE_CHECKBOX', 1);
        define('PRODUCTS_OPTIONS_TYPE_FILE', 1);
        define('PRODUCTS_OPTIONS_TYPE_TEXT', 1);
        define('PRODUCTS_OPTIONS_TYPE_READONLY_IGNORED', 1);
        define('PRODUCTS_OPTIONS_TYPE_READONLY', 5);
        define('TEXT_PRODUCTS_MIX_ON', 'TEXT_PRODUCTS_MIX_ON');
        $_SESSION['languages_id'] = 1;
        $qfr = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockIterator($qfr, array(array('configuration_key' => 'TEST_LIST_IMAGE'), array('configuration_key' => 1)));
        $db = $this->getMockBuilder('queryFactory')
            ->getMock();
        $GLOBALS['db'] = $db;
        $db->method('Execute')->willReturn($qfr);
        $outputLayout = array(
            'formatter' => array(
                'params' => array(
                    'columnCount' => 2,
                    'PRODUCTS_IMAGE_NO_IMAGE_STATUS' => 0,
                    'definePrefix' => 'TEST_',
                    'imageListingWidth' => '',
                    'imageListingHeight' => ''
                )
            )
        );
        $itemList = array(
            array(
                'products_id' => '1',
                'products_image' => '',
                'productCpath' => '',
                'products_name' => '',
                'products_model' => '',
                'products_quantity' => '',
                'products_weight' => '',
                'products_date_added' => '',
                'manufacturers_name' => '',
                'displayPrice' => '',
                'priceBlock' => '',
                'products_description' => '',
                'products_qty_box_status' => ''
            ),
            array(
                'products_id' => '2',
                'products_image' => '',
                'productCpath' => '',
                'products_name' => '',
                'products_model' => '',
                'products_quantity' => '',
                'products_weight' => '',
                'products_date_added' => '',
                'manufacturers_name' => '',
                'displayPrice' => '',
                'priceBlock' => '',
                'products_description' => '',
                'products_qty_box_status' => ''
            ),
            array(
                'products_id' => '3',
                'products_image' => '',
                'productCpath' => '',
                'products_name' => '',
                'products_model' => '',
                'products_quantity' => '',
                'products_weight' => '',
                'products_date_added' => '',
                'manufacturers_name' => '',
                'displayPrice' => '',
                'priceBlock' => '',
                'products_description' => '',
                'products_qty_box_status' => ''
            )
        );
        $f = new \ZenCart\ListingQueryAndOutput\formatters\ListStandard($itemList, $outputLayout);
        $f->setDBConnection($db);
        $f->format();
        $r = $f->getFormattedResults();
        $this->assertTrue(count($r) === 3);
    }

    public function testListStandardFormatterMultiRow1()
    {
        define('TEST_LIST_GROUP_ID', 1);
        define('TEST_BUY_NOW', 0);
        define('SEARCH_ENGINE_FRIENDLY_URLS', 0);
        define('PROPORTIONAL_IMAGES_STATUS', 0);
        define('IMAGE_REQUIRED', 0);
        define('TEST_LIST_IMAGE', 1);
        define('TEST_LIST_NAME', 1);
        define('TEST_LIST_MODEL', 1);
        define('TEST_LIST_QUANTITY', 1);
        define('TEST_LIST_WEIGHT', 1);
        define('TEST_LIST_DATE_ADDED', 1);
        define('TEST_LIST_PRICE', 1);
        define('TEXT_PRICE', 'TEXT_PRICE');
        $qfr = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockIterator($qfr, array(array('configuration_key' => 'TEST_LIST_IMAGE'), array('configuration_key' => 1)));
        $db = $this->getMockBuilder('queryFactory')
            ->getMock();
        $GLOBALS['db'] = $db;
        $db->method('Execute')->willReturn($qfr);
        $outputLayout = array(
            'formatter' => array(
                'params' => array(
                    'columnCount' => 2,
                    'PRODUCTS_IMAGE_NO_IMAGE_STATUS' => 0,
                    'definePrefix' => 'TEST_',
                    'imageListingWidth' => '',
                    'imageListingHeight' => ''
                )
            )
        );
        $itemList = array(
            array(
                'products_id' => '1',
                'products_image' => '',
                'productCpath' => '',
                'products_name' => '',
                'products_model' => '',
                'products_quantity' => '',
                'products_weight' => '',
                'products_date_added' => '',
                'manufacturers_name' => '',
                'displayPrice' => '',
                'priceBlock' => '',
                'products_description' => '',
                'products_qty_box_status' => ''
            ),
            array(
                'products_id' => '2',
                'products_image' => '',
                'productCpath' => '',
                'products_name' => '',
                'products_model' => '',
                'products_quantity' => '',
                'products_weight' => '',
                'products_date_added' => '',
                'manufacturers_name' => '',
                'displayPrice' => '',
                'priceBlock' => '',
                'products_description' => '',
                'products_qty_box_status' => ''
            ),
            array(
                'products_id' => '3',
                'products_image' => '',
                'productCpath' => '',
                'products_name' => '',
                'products_model' => '',
                'products_quantity' => '',
                'products_weight' => '',
                'products_date_added' => '',
                'manufacturers_name' => '',
                'displayPrice' => '',
                'priceBlock' => '',
                'products_description' => '',
                'products_qty_box_status' => ''
            )
        );
        $f = new \ZenCart\ListingQueryAndOutput\formatters\ListStandard($itemList, $outputLayout);
        $f->setDBConnection($db);
        $f->format();
        $r = $f->getFormattedResults();
        $this->assertTrue(count($r) === 3);
    }

    public function testTabularCustomFormatterNoItems()
    {
        define('CAPTION_UPCOMING_PRODUCTS', 'CAPTION_UPCOMING_PRODUCTS');
        $outputLayout = array(
            'formatter' => array(
                'columns' => array(),
                'params' => array(
                    'columnCount' => 2,
                    'PRODUCTS_IMAGE_NO_IMAGE_STATUS' => 0
                )
            )
        );
        $itemList = array();
        $f = new \ZenCart\ListingQueryAndOutput\formatters\TabularCustom($itemList, $outputLayout);
        $f->format();
        $r = $f->getFormattedResults();
        $this->assertTrue(count($r) === 0);
    }

    public function testTabularCustomFormatterMultiRow()
    {
        define('CAPTION_UPCOMING_PRODUCTS', 'CAPTION_UPCOMING_PRODUCTS');
        $zenDateShort = function ($parameters) {
            return true;
        };

        $outputLayout = array(
            'formatter' => array(
                'class' => 'TabularCustom',
                'template' => 'tpl_listingbox_tabular.php',
            ),
            'columns' => array(
                'products_name' => array(
                    'title' => '',
                    'col_params' => 'style="text-align:left"',
                ),
                'products_date_available' => array(
                    'title' => '',
                    'col_params' => 'style="text-align:right"',
                    'formatter' => $zenDateShort
                )
            )
        );

        $itemList = array(
            array('products_name' => '', 'products_date_available' => ''),
            array('products_name' => '', 'products_date_available' => ''),
            array('products_name' => '', 'products_date_available' => '')
        );
        $f = new \ZenCart\ListingQueryAndOutput\formatters\TabularCustom($itemList, $outputLayout);
        $f->format();
        $r = $f->getFormattedResults();
        $this->assertTrue(count($r) === 3);
    }

    public function testTabularProductFormatterMultiRow()
    {
        $_SESSION['languages_id'] = 1;
        $_SESSION['customer_id'] = 1;
        $_SESSION['customers_authorization'] = 1;
        define('CUSTOMERS_APPROVAL', '');
        define('CUSTOMERS_APPROVAL_AUTHORIZATION', '');
        define('SEARCH_ENGINE_FRIENDLY_URLS', 0);
        define('PRODUCT_LIST_MODEL', 1);
        define('PRODUCT_LIST_NAME', 1);
        define('PRODUCT_LIST_MANUFACTURER', 1);
        define('PRODUCT_LIST_PRICE', 1);
        define('PRODUCT_LIST_QUANTITY', 1);
        define('PRODUCT_LIST_WEIGHT', 1);
        define('PRODUCT_LIST_IMAGE', 1);
        define('TABLE_HEADING_PRODUCTS', 'TABLE_HEADING_PRODUCTS');
        define('TABLE_HEADING_MODEL', 'TABLE_HEADING_MODEL');
        define('TABLE_HEADING_MANUFACTURER', 'TABLE_HEADING_MANUFACTURER');
        define('TABLE_HEADING_WEIGHT', 'TABLE_HEADING_WEIGHT');
        define('TABLE_HEADING_IMAGE', 'TABLE_HEADING_IMAGE');
        define('TABLE_HEADING_QUANTITY', 'TABLE_HEADING_QUANTITY');
        define('TABLE_HEADING_PRICE', 'TABLE_HEADING_PRICE');
        define('PRODUCTS_LIST_PRICE_WIDTH', 1);
        define('PRODUCT_LIST_DESCRIPTION', 1);
        define('TOPMOST_CATEGORY_PARENT_ID', 0);
        define('TEXT_AUTHORIZATION_PENDING_PRICE', '');
        define('MORE_INFO_TEXT', 'MORE_INFO_TEXT');
        if (!defined('PRODUCTS_OPTIONS_TYPE_SELECT')) {
            define('PRODUCTS_OPTIONS_TYPE_SELECT', '0');
        }
        if (!defined('PRODUCTS_OPTIONS_TYPE_TEXT')) {
            define('PRODUCTS_OPTIONS_TYPE_TEXT', '1');
        }
        if (!defined('PRODUCTS_OPTIONS_TYPE_RADIO')) {
            define('PRODUCTS_OPTIONS_TYPE_RADIO', '2');
        }
        if (!defined('PRODUCTS_OPTIONS_TYPE_CHECKBOX')) {
            define('PRODUCTS_OPTIONS_TYPE_CHECKBOX', '3');
        }
        if (!defined('PRODUCTS_OPTIONS_TYPE_FILE')) {
            define('PRODUCTS_OPTIONS_TYPE_FILE', '4');
        }
        if (!defined('PRODUCTS_OPTIONS_TYPE_READONLY')) {
            define('PRODUCTS_OPTIONS_TYPE_READONLY', '5');
        }
        if (!defined('PRODUCT_LIST_PRICE_BUY_NOW')) {
            define('PRODUCT_LIST_PRICE_BUY_NOW', 0);
        }
        if (!defined('PRODUCT_LISTING_MULTIPLE_ADD_TO_CART')) {
            define('PRODUCT_LISTING_MULTIPLE_ADD_TO_CART', 0);
        }
        define('STORE_STATUS', 0);
        define('PRODUCTS_OPTIONS_TYPE_READONLY_IGNORED', 0);
        define('TEXT_AUTHORIZATION_PENDING_BUTTON_REPLACE', 0);
        define('TEXT_NO_PRODUCTS', 0);
        define('PRODUCTS_QUANTITY_MIN_TEXT_LISTING', 0);
        define('PRODUCTS_QUANTITY_UNIT_TEXT_LISTING', 0);
        if (!defined('TEXT_PRODUCTS_MIX_ON')) {
            define('TEXT_PRODUCTS_MIX_ON', 'TEXT_PRODUCTS_MIX_ON');
        }
        $request = $this->getMockBuilder('\ZenCart\Request\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $request->method('readGet')->willReturn(1);
        $qfr = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $db = $this->getMockBuilder('queryFactory')
            ->getMock();
        $db->method('Execute')->willReturn($qfr);
        $GLOBALS['db'] = $db;
        $outputLayout = array(
            'formatter' => array(
                'columns' => array(),
                'params' => array(
                    'columnCount' => 2,
                    'PRODUCTS_IMAGE_NO_IMAGE_STATUS' => 0
                )
            )
        );
        $itemList = array(
            array(
                'products_id' => '1',
                'products_image' => '',
                'productCpath' => '',
                'products_name' => '',
                'products_model' => '',
                'products_quantity' => '',
                'products_weight' => '',
                'products_date_added' => '',
                'manufacturers_name' => '',
                'displayPrice' => '',
                'products_description' => '',
                'products_qty_box_status' => '',
                'master_categories_id' => '',
                'manufacturers_id' => ''
            ),
            array(
                'products_id' => '1',
                'products_image' => '',
                'productCpath' => '',
                'products_name' => '',
                'products_model' => '',
                'products_quantity' => '',
                'products_weight' => '',
                'products_date_added' => '',
                'manufacturers_name' => '',
                'displayPrice' => '',
                'products_description' => '',
                'products_qty_box_status' => '',
                'master_categories_id' => '',
                'manufacturers_id' => ''
            ),
            array(
                'products_id' => '1',
                'products_image' => '',
                'productCpath' => '',
                'products_name' => '',
                'products_model' => '',
                'products_quantity' => '',
                'products_weight' => '',
                'products_date_added' => '',
                'manufacturers_name' => '',
                'displayPrice' => '',
                'products_description' => '',
                'products_qty_box_status' => '',
                'master_categories_id' => '',
                'manufacturers_id' => ''
            )
        );
        $f = new \ZenCart\ListingQueryAndOutput\formatters\TabularProduct($itemList, $outputLayout);
        $f->setRequest($request);
        $f->format();
        $r = $f->getFormattedResults();
        $this->assertTrue(count($r) === 3);
    }

    public function testTabularProductFormatterNoItems()
    {
        define('PRODUCT_LIST_MODEL', 1);
        define('PRODUCT_LIST_NAME', 1);
        define('PRODUCT_LIST_MANUFACTURER', 1);
        define('PRODUCT_LIST_PRICE', 1);
        define('PRODUCT_LIST_QUANTITY', 1);
        define('PRODUCT_LIST_WEIGHT', 1);
        define('PRODUCT_LIST_IMAGE', 1);
        define('TEXT_NO_PRODUCTS', 0);
        $request = $this->getMockBuilder('\ZenCart\Request\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $qfr = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $db = $this->getMockBuilder('queryFactory')
            ->getMock();
        $db->method('Execute')->willReturn($qfr);
        $GLOBALS['db'] = $db;
        $outputLayout = array(
            'formatter' => array(
                'columns' => array(),
                'params' => array(
                    'columnCount' => 2,
                    'PRODUCTS_IMAGE_NO_IMAGE_STATUS' => 0
                )
            )
        );
        $itemList = array();
        $f = new \ZenCart\ListingQueryAndOutput\formatters\TabularProduct($itemList, $outputLayout);
        $f->setRequest($request);
        $f->format();
        $r = $f->getFormattedResults();
        $this->assertTrue(count($r) === 0);
    }
}
