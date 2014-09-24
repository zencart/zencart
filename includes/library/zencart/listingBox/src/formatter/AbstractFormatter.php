<?php
/**
 * Class AbstractFormatter
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: currencies.php 15880 2010-04-11 16:24:30Z wilt $
 */
namespace ZenCart\ListingBox\formatter;
/**
 * Class AbstractFormatter
 * @package ZenCart\ListingBox\formatter
 */
class AbstractFormatter extends \base
{

    /**
     * @var
     */
    protected  $formatterTemplate;
    /**
     * @var
     */
    protected $diContainer;
    /**
     * @var
     */
    protected $request;
    /**
     * @var
     */
    protected $countQuantityBoxItems;
    /**
     * @var
     */
    protected $defineList;
    /**
     * @var
     */
    protected $columnList;

    /**
     * @param $diContainer
     */
    public function __construct($diContainer)
    {
        $this->diContainer = $diContainer;
        $this->request = $diContainer->get('request');
    }

    /**
     * @param $listBoxContents
     */
    public function setTemplateVars($listBoxContents)
    {
        $showSubmit = zen_run_normal();
        $showTopSubmit = false;
        $showBottomSubmit = false;
        if ($this->showTopBottomSubmit($showSubmit, $listBoxContents)) {
            $showTopSubmit = (PRODUCT_LISTING_MULTIPLE_ADD_TO_CART == 1 || PRODUCT_LISTING_MULTIPLE_ADD_TO_CART == 3) ? true : false;
            $showBottomSubmit = (PRODUCT_LISTING_MULTIPLE_ADD_TO_CART == 2) ? true : false;
        }
        $showForm = ($showTopSubmit || $showBottomSubmit);
        $this->diContainer->get('listingBox')->setTemplateVariable('showTopSubmit', $showTopSubmit);
        $this->diContainer->get('listingBox')->setTemplateVariable('showBottomSubmit', $showBottomSubmit);
        $this->diContainer->get('listingBox')->setTemplateVariable('showForm', $showForm);

    }
    /**
     *
     */
    public function initDefineList()
    {
        $this->defineList = array(
            'PRODUCT_LIST_MODEL' => PRODUCT_LIST_MODEL,
            'PRODUCT_LIST_NAME' => PRODUCT_LIST_NAME,
            'PRODUCT_LIST_MANUFACTURER' => PRODUCT_LIST_MANUFACTURER,
            'PRODUCT_LIST_PRICE' => PRODUCT_LIST_PRICE,
            'PRODUCT_LIST_QUANTITY' => PRODUCT_LIST_QUANTITY,
            'PRODUCT_LIST_WEIGHT' => PRODUCT_LIST_WEIGHT,
            'PRODUCT_LIST_IMAGE' => PRODUCT_LIST_IMAGE
        );

        asort($this->defineList);
        reset($this->defineList);
    }

    public function getDefaultTemplate()
    {
        $template = $this->diContainer->get('globalRegistry')['template'];
        $current_page_base = $this->diContainer->get('globalRegistry')['current_page_base'];
        $formatterTemplate = $this->diContainer->get('listingBox')->getOutputLayout()['formatter']['template'];
        return ($this->mainTemplate = $template->get_template_dir($formatterTemplate, DIR_WS_TEMPLATE, $current_page_base, 'listingboxes') . '/' . $formatterTemplate);
    }

    /**
     *
     */
    public function initColumnList()
    {
        $this->columnList = array();
        foreach ($this->defineList as $key => $value) {
            if ($value > 0)
            {
                $this->columnList [] = $key;
            }
        }
    }

    /**
     * @param $showSubmit
     * @param $listBoxContents
     * @return bool
     */
    protected function showTopBottomSubmit($showSubmit, $listBoxContents)
    {
        $retVal = false;
        if ($this->countQuantityBoxItems > 0 and $showSubmit == true and count($listBoxContents) > 0) {
            $retVal = true;
        }
        return $retVal;
    }

    /**
     * @param $item
     * @return bool
     */
    protected function testIncrementCountQuantityBoxes($item)
    {
        $result = PRODUCT_LISTING_MULTIPLE_ADD_TO_CART != 0 && $item ['products_qty_box_status'] != 0 && zen_get_products_allow_add_to_cart($item ['products_id']) != 'N' && $item ['product_is_call'] == 0 && ($item ['products_quantity'] > 0 || SHOW_PRODUCTS_SOLD_OUT_IMAGE == 0);
        return $result;
    }

    /**
     * @param $item
     * @return bool
     */
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
}
