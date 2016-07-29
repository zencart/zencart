<?php
/**
 * Class AbstractFormatter
 *
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: New in v1.6.0 $
 */
namespace ZenCart\QueryBuilderDefinitions\formatters;

/**
 * Class AbstractFormatter
 * @package ZenCart\QueryBuilderDefinitions\formatters
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
    protected $formattedTotals = array();

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
    public function getFormattedTotals($outputLayout)
    {
        if (sizeof($this->formattedResults[0]) == 0) { 
           return null; 
        }
        $hasTotal = false; 
        foreach ($this->outputLayout['listMap'] as $id => $key) {
           $fieldset = false; 
           if ( isset($this->outputLayout['fields'][$key]) && 
                isset($this->outputLayout['fields'][$key]['total']) ) {
              $total = 0; 
              if ($this->outputLayout['fields'][$key]['total'] == 'currencySum') {
                 foreach ($this->formattedResults as $rec) { 
                    $total += preg_replace("/([^0-9\.])/i", "", $rec[$key]); 
                 }
                 $currencies = new \currencies();
                 $total = $currencies->format($total); 
              } else if ($this->outputLayout['fields'][$key]['total'] == 'sum') {
                 foreach ($this->formattedResults as $rec) { 
                    $total += $rec[$key]; 
                 }
              } else if ($this->outputLayout['fields'][$key]['total'] == 'count') {
                $total = count($this->formattedResults); 
              } 
              $fieldset = true; 
              $hasTotal = true; 
              $this->formattedTotals[] = $total; 
           }
           if (!$fieldset) { 
             $this->formattedTotals[] = '&nbsp;'; 
           }
        }
        if (!$hasTotal) return null; 
        return $this->formattedTotals;
    }

    /**
     * @return array
     */
    public function getTplVars()
    {
        return $this->tplVars;
    }
}
