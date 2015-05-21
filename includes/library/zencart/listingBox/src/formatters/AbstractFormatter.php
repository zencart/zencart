<?php
/**
 * Class AbstractFormatter
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: currencies.php 15880 2010-04-11 16:24:30Z wilt $
 */
namespace ZenCart\ListingBox\formatters;

/**
 * Class AbstractFormatter
 * @package ZenCart\ListingBox\formatters
 */
class AbstractFormatter extends \base
{
    /**
     * @var array
     */
    protected $formattedResults = array();

    /**
     * @var array
     */
    protected $tplVars = array();

    /**
     * @var
     */
    protected $dbConn;

    /**
     * @var
     */
    protected $request;

    /**
     * @var array
     */
    protected $itemList;

    /**
     * @var array
     */
    protected $outputLayout;

    /**
     * @param array $itemList
     * @param array $outputLayout
     */
    public function __construct(array $itemList, array $outputLayout)
    {
        $this->itemList = $itemList;
        $this->outputLayout = $outputLayout;
        $this->tplVars = $outputLayout['formatter'];
    }

    protected function testIncrementCountQuantityBoxes($item)
    {
        $result = PRODUCT_LISTING_MULTIPLE_ADD_TO_CART != 0 && $item ['products_qty_box_status'] != 0 &&
                  zen_get_products_allow_add_to_cart($item ['products_id']) != 'N' && $item ['product_is_call'] == 0 &&
                  ($item ['products_quantity'] > 0 || SHOW_PRODUCTS_SOLD_OUT_IMAGE == 0);
        return $result;
    }

    protected function testHideQuantityBox($item)
    {
        $result = PRODUCT_LISTING_MULTIPLE_ADD_TO_CART != 0 && $item ['products_qty_box_status'] == 0;
        return $result;
    }

    /**
     * @param $item
     * @return bool
     */
    protected function testShowAddProduct($item)
    {
        $result = PRODUCT_LISTING_MULTIPLE_ADD_TO_CART == 0 && PRODUCT_LIST_PRICE_BUY_NOW == '2' && $item ['products_qty_box_status'] != 0;
        return $result;
    }

    /**
     * @param $item
     * @return bool
     */
    protected function testShowBuyNow($item)
    {
        $result = PRODUCT_LISTING_MULTIPLE_ADD_TO_CART == 0 && (PRODUCT_LIST_PRICE_BUY_NOW != '2' || $item ['products_qty_box_status'] == 0);
        return $result;
    }

    /**
     * @param $dbConn
     */
    public function setDBConnection($dbConn)
    {
        $this->dbConn = $dbConn;
    }

    /**
     * @param $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @return array
     */
    public function getFormattedResults()
    {
        return $this->formattedResults;
    }

    /**
     * @return array
     */
    public function getTplVars()
    {
        return $this->tplVars;
    }
}
