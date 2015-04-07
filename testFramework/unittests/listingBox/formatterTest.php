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
//use ZenCart\Platform\Paginator\adapters\QueryFactory;
/**
 * Testing Library
 */
class testFormatterCase extends zcListingBoxTestCase
{
    public function setup()
    {
        parent::setup();
        define('TEST_LIST_IMAGE', 1);
        define('TEST_LIST_NAME', 1);
        define('TEST_LIST_MODEL', 1);
        define('TEST_LIST_QUANTITY', 1);
        define('TEST_LIST_WEIGHT', 1);
        define('TEST_LIST_DATE_ADDED', 1);
        define('TEST_LIST_PRICE', 1);
        define('TEST_LISTING_MULTIPLE_ADD_TO_CART', 1);
        define('TOPMOST_CATEGORY_PARENT_ID', 0);


    }
    public function testColumnarFormatterNoItems()
    {
        $outputLayout = array('formatter'=>array('params'=>array('columnCount'=>1, 'PRODUCTS_IMAGE_NO_IMAGE_STATUS'=>0)));
        $itemList = array();
        $f = new \ZenCart\Platform\listingBox\formatters\Columnar($itemList, $outputLayout);
        $f->format();
        $r = $f->getFormattedResults();
        $this->assertTrue(count($r)=== 0);
    }
    public function testColumnarFormatterMultiRow()
    {
        $outputLayout = array('formatter'=>array('params'=>array('columnCount'=>2, 'PRODUCTS_IMAGE_NO_IMAGE_STATUS'=>0)));
        $itemList = array(array('products_image'=>''), array('products_image'=>''), array('products_image'=>''));
        $f = new \ZenCart\Platform\listingBox\formatters\Columnar($itemList, $outputLayout);
        $f->format();
        $r = $f->getFormattedResults();
        $this->assertTrue(count($r) === 2);
    }
    public function testColumnarFormatterSingleRow()
    {
        $outputLayout = array('formatter'=>array('params'=>array('columnCount'=>2, 'PRODUCTS_IMAGE_NO_IMAGE_STATUS'=>0)));
        $itemList = array(array('products_image'=>''));
        $f = new \ZenCart\Platform\listingBox\formatters\Columnar($itemList, $outputLayout);
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
        $outputLayout = array('formatter' => array('params' => array('columnCount' => 2,
                                                                     'PRODUCTS_IMAGE_NO_IMAGE_STATUS' => 0,
                                                                     'definePrefix' => 'TEST_',
                                                                     'imageListingWidth' => '',
                                                                     'imageListingHeight' => '')));
        $itemList = array();
        $f = new \ZenCart\Platform\listingBox\formatters\ListStandard($itemList, $outputLayout);
        $f->setDBConnection($db);
        $f->format();
        $r = $f->getFormattedResults();
        $this->assertTrue(count($r) === 0);
    }
    public function testListStandardFormatterMultiRow()
    {
        define('TEST_BUY_NOW', 1);
        $qfr = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockIterator($qfr, array(array('configuration_key' => 'TEST_LIST_IMAGE'), array('configuration_key' => 1)));
        $db = $this->getMockBuilder('queryFactory')
            ->getMock();
        $GLOBALS['db'] = $db;
        $db->method('Execute')->willReturn($qfr);
        $outputLayout = array('formatter' => array('params' => array('columnCount' => 2,
                                                                     'PRODUCTS_IMAGE_NO_IMAGE_STATUS' => 0,
                                                                     'definePrefix' => 'TEST_',
                                                                     'imageListingWidth' => '',
                                                                     'imageListingHeight' => '')));
        $itemList = array(array('products_id' => '1', 'products_image' => '', 'productCpath' => '',
                                'products_name' => '', 'products_model' => '', 'products_quantity' => '',
                                'products_weight' => '', 'products_date_added' => '', 'manufacturers_name' => '',
                                'displayPrice' => '', 'products_description' => '', 'products_qty_box_status' => ''),
                          array('products_id' => '1', 'products_image' => '', 'productCpath' => '',
                                'products_name' => '', 'products_model' => '', 'products_quantity' => '',
                                'products_weight' => '', 'products_date_added' => '', 'manufacturers_name' => '',
                                'displayPrice' => '', 'products_description' => '', 'products_qty_box_status' => ''),
                          array('products_id' => '1', 'products_image' => '', 'productCpath' => '',
                                'products_name' => '', 'products_model' => '', 'products_quantity' => '',
                                'products_weight' => '', 'products_date_added' => '', 'manufacturers_name' => '',
                                'displayPrice' => '', 'products_description' => '', 'products_qty_box_status' => ''));
        $f = new \ZenCart\Platform\listingBox\formatters\ListStandard($itemList, $outputLayout);
        $f->setDBConnection($db);
        $f->format();
        $r = $f->getFormattedResults();
        $this->assertTrue(count($r) === 3);
    }
    public function testListStandardFormatterMultiRow1()
    {
        define('TEST_BUY_NOW', 0);
        $qfr = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockIterator($qfr, array(array('configuration_key' => 'TEST_LIST_IMAGE'), array('configuration_key' => 1)));
        $db = $this->getMockBuilder('queryFactory')
            ->getMock();
        $GLOBALS['db'] = $db;
        $db->method('Execute')->willReturn($qfr);
        $outputLayout = array('formatter' => array('params' => array('columnCount' => 2,
                                                                     'PRODUCTS_IMAGE_NO_IMAGE_STATUS' => 0,
                                                                     'definePrefix' => 'TEST_',
                                                                     'imageListingWidth' => '',
                                                                     'imageListingHeight' => '')));
        $itemList = array(array('products_id' => '1', 'products_image' => '', 'productCpath' => '',
                                'products_name' => '', 'products_model' => '', 'products_quantity' => '',
                                'products_weight' => '', 'products_date_added' => '', 'manufacturers_name' => '',
                                'displayPrice' => '', 'products_description' => '', 'products_qty_box_status' => ''),
                          array('products_id' => '1', 'products_image' => '', 'productCpath' => '',
                                'products_name' => '', 'products_model' => '', 'products_quantity' => '',
                                'products_weight' => '', 'products_date_added' => '', 'manufacturers_name' => '',
                                'displayPrice' => '', 'products_description' => '', 'products_qty_box_status' => ''),
                          array('products_id' => '1', 'products_image' => '', 'productCpath' => '',
                                'products_name' => '', 'products_model' => '', 'products_quantity' => '',
                                'products_weight' => '', 'products_date_added' => '', 'manufacturers_name' => '',
                                'displayPrice' => '', 'products_description' => '', 'products_qty_box_status' => ''));
        $f = new \ZenCart\Platform\listingBox\formatters\ListStandard($itemList, $outputLayout);
        $f->setDBConnection($db);
        $f->format();
        $r = $f->getFormattedResults();
        $this->assertTrue(count($r) === 3);
    }

    public function testTabularCustomFormatterNoItems()
    {
        $outputLayout = array('formatter' => array('columns'=>array(), 'params' => array('columnCount' => 2,
                                                                                         'PRODUCTS_IMAGE_NO_IMAGE_STATUS' => 0)));
        $itemList = array();
        $f = new \ZenCart\Platform\listingBox\formatters\TabularCustom($itemList, $outputLayout);
        $f->format();
        $r = $f->getFormattedResults();
        $this->assertTrue(count($r) === 0);
    }
    public function testTabularCustomFormatterMultiRow()
    {
        $zenDateShort = function ($parameters) {
            return true;
        };

        $outputLayout = array(
            'formatter' => array('class' => 'TabularCustom',
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

        $itemList = array(array('products_name' => '', 'products_date_available' => ''), array('products_name' => '', 'products_date_available' => ''), array('products_name' => '', 'products_date_available' => ''));
        $f = new \ZenCart\Platform\listingBox\formatters\TabularCustom($itemList, $outputLayout);
        $f->format();
        $r = $f->getFormattedResults();
        $this->assertTrue(count($r) === 3);
    }

    public function testTabularProductFormatterMultiRow()
    {
        $request = $this->getMock('\\ZenCart\\Platform\\Request');
        $qfr = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $db = $this->getMockBuilder('queryFactory')
            ->getMock();
        $db->method('Execute')->willReturn($qfr);
        $GLOBALS['db'] = $db;
        $outputLayout = array('formatter' => array('columns'=>array(), 'params' => array('columnCount' => 2,
                                                                                         'PRODUCTS_IMAGE_NO_IMAGE_STATUS' => 0)));
        $itemList = array(array('products_id' => '1', 'products_image' => '', 'productCpath' => '',
                                'products_name' => '', 'products_model' => '', 'products_quantity' => '',
                                'products_weight' => '', 'products_date_added' => '', 'manufacturers_name' => '',
                                'displayPrice' => '', 'products_description' => '', 'products_qty_box_status' => '', 'master_categories_id'=>'', 'manufacturers_id'=>''),
                          array('products_id' => '1', 'products_image' => '', 'productCpath' => '',
                                'products_name' => '', 'products_model' => '', 'products_quantity' => '',
                                'products_weight' => '', 'products_date_added' => '', 'manufacturers_name' => '',
                                'displayPrice' => '', 'products_description' => '', 'products_qty_box_status' => '', 'master_categories_id'=>'', 'manufacturers_id'=>''),
                          array('products_id' => '1', 'products_image' => '', 'productCpath' => '',
                                'products_name' => '', 'products_model' => '', 'products_quantity' => '',
                                'products_weight' => '', 'products_date_added' => '', 'manufacturers_name' => '',
                                'displayPrice' => '', 'products_description' => '', 'products_qty_box_status' => '', 'master_categories_id'=>'', 'manufacturers_id'=>''));
        $f = new \ZenCart\Platform\listingBox\formatters\TabularProduct($itemList, $outputLayout);
        $f->setRequest($request);
        $f->format();
        $r = $f->getFormattedResults();
        $this->assertTrue(count($r) === 3);
    }
    public function testTabularProductFormatterNoItems()
    {
        $request = $this->getMock('\\ZenCart\\Platform\\Request');
        $qfr = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $db = $this->getMockBuilder('queryFactory')
            ->getMock();
        $db->method('Execute')->willReturn($qfr);
        $GLOBALS['db'] = $db;
        $outputLayout = array('formatter' => array('columns'=>array(), 'params' => array('columnCount' => 2,
                                                                                         'PRODUCTS_IMAGE_NO_IMAGE_STATUS' => 0)));
        $itemList = array();
        $f = new \ZenCart\Platform\listingBox\formatters\TabularProduct($itemList, $outputLayout);
        $f->setRequest($request);
        $f->format();
        $r = $f->getFormattedResults();
        $this->assertTrue(count($r) === 0);
    }
}
