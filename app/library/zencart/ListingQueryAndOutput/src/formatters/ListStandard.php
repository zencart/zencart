<?php
/**
 * Class ListStandard
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: currencies.php 15880 2010-04-11 16:24:30Z wilt $
 */
namespace ZenCart\ListingQueryAndOutput\formatters;

/**
 * Class ListStandard
 * @package ZenCart\ListingQueryAndOutput\formatters
 */
class ListStandard extends AbstractFormatter implements FormatterInterface
{
    /**
     * @var
     */
    protected $displayValues;

    /**
     * @var
     */
    protected $prefix;

    /**
     * @var
     */
    protected $countQtyBoxItems;

    /**
     *
     */
    public function format()
    {
        $this->displayValues = array();
        $items = $this->itemList;
        if (count($items) == 0) {
            return;
        }
        $listBoxContents = $this->processItems($items);
        $this->formattedResults = $listBoxContents;
        $this->tplVars['countQtyBoxItems'] = $this->countQtyBoxItems;
    }

    /**
     * @param $items
     * @return array
     */
    protected function processItems($items)
    {
        $listBoxContents = [];
        $this->prefix = $this->outputLayout ['formatter']['params']['definePrefix'];
        $displayEntities = array('LIST_IMAGE' => array(),
                                 'LIST_NAME' => array(),
                                 'LIST_MODEL' => array('switchTest' => true, 'switchValue' => 'model'),
                                 'LIST_QUANTITY' => array('switchTest' => true,
                                                          'switchValue' => 'quantity'),
                                 'LIST_WEIGHT' => array('switchTest' => true,
                                                        'switchValue' => 'weight'),
                                 'LIST_DATE_ADDED' => array('switchTest' => true,
                                                            'switchValue' => 'date_added'),
                                 'LIST_MANUFACTURER' => array('switchTest' => true,
                                                              'switchValue' => 'manufacturer'),
                                 'LIST_PRICE' => array(),
                                 'LIST_DESCRIPTION' => array(),
                                 'BUY_NOW' => array()
        );
        $columnSql = $this->getColumnSql();
        $columnOneSortOrders = $this->dbConn->execute($columnSql['one']);
        $columnTwoSortOrders = $this->dbConn->execute($columnSql['two']);
        $this->countQtyBoxItems = 0;
        foreach ($items as $item) {
            $item = $this->setExtraParameters($item);
            $this->initDisplayEntities($item, $displayEntities);
            $item = $this->processColumnEntries($columnOneSortOrders, 'displayProductColOne', $item);
            $item = $this->processColumnEntries($columnTwoSortOrders, 'displayProductColTwo', $item);
            $listBoxContents [] = $item;
        }
        return $listBoxContents;
    }

    /**
     * @param $item
     * @return mixed
     */
    protected function setExtraParameters($item)
    {
        $item['product_info_page'] = zen_get_info_page($item ['products_id']);
        return $item;
    }

    /**
     * @param $item
     * @param $displayEntities
     */
    protected function initDisplayEntities($item, $displayEntities)
    {
        foreach ($displayEntities as $key => $value) {
            $this->displayValues[$key] = '';
            if ((defined($this->prefix . $key) && constant($this->prefix . $key) == 0)) {
                continue;
            }
            if ($this->productSwitchTest($value, $item)) {
                continue;
            }
            if (!isset($value['processor'])) {
                $method = 'processor' . self::camelize(strtolower($key), true);
                $this->$method($item, $key);
            }
        }
    }

    /**
     * @param $value
     * @param $item
     * @return bool
     */
    protected function productSwitchTest($value, $item)
    {
        if (!isset($value['switchTest'])) {
            return false;
        }
        if (zen_get_show_product_switch($item ['products_id'], $value['switchValue'])) {
            return false;
        }
        return true;
    }

    /**
     * @return array
     */
    protected function getColumnSql()
    {
        $groupId = constant($this->prefix . 'LIST_GROUP_ID');
        $sql = array();
        $sql['one'] = "SELECT configuration_key, configuration_value
                FROM " . TABLE_CONFIGURATION . " WHERE configuration_group_id='" . $groupId . "'
                AND (configuration_value >= 1000
                AND configuration_value <= 1999)
                ORDER BY LPAD(configuration_value,11,0)";
        $sql['two'] = "SELECT configuration_key, configuration_value
                FROM " . TABLE_CONFIGURATION . "
                WHERE configuration_group_id='" . $groupId . "'
                AND (configuration_value >= 2000
                AND configuration_value <= 2999)
                ORDER BY LPAD(configuration_value,11,0)";
        return $sql;
    }

    /**
     * @param $queryResult
     * @param $columnName
     * @param $item
     * @return mixed
     */
    protected function processColumnEntries($queryResult, $columnName, $item)
    {
        foreach ($queryResult as $dispSortOrder) {
            foreach ($this->displayValues as $key => $value) {
                if ($dispSortOrder ['configuration_key'] == $this->prefix . $key) {
                    $item [$columnName] [] = $value;
                    break;
                }
            }
        }
        return $item;
    }

    /**
     * @param $item
     * @param $key
     */
    protected function processorListImage($item, $key)
    {
        $imgWidth = $this->outputLayout ['formatter']['params']['imageListingWidth'];
        $imgHeight = $this->outputLayout ['formatter']['params']['imageListingHeight'];
        $result = '<a href="' . zen_href_link($item['product_info_page'], 'cPath=' .
                $item ['productCpath'] . '&products_id=' . $item ['products_id']) . '">' .
            zen_image(DIR_WS_IMAGES . $item ['products_image'], $item ['products_name'], $imgWidth, $imgHeight) .
            '</a>' . str_repeat('<br clear="all" />', substr(constant($this->prefix . $key), 3, 1));

        if ($item ['products_image'] == '' && PRODUCTS_IMAGE_NO_IMAGE_STATUS == 0) {
            $result = str_repeat('<br clear="all" />', substr(constant($this->prefix . 'LIST_IMAGE'), 3, 1));
        }
        $this->displayValues[$key] = $result;
    }

    /**
     * @param $item
     * @param $key
     */
    protected function processorListName($item, $key)
    {
        $this->displayValues[$key] = '<a href="' . zen_href_link($item['product_info_page'], 'cPath=' . $item ['productCpath'] . '&products_id=' . $item ['products_id']) . '"><strong>' . $item ['products_name'] . '</strong></a>' . str_repeat('<br clear="all" />', substr(constant($this->prefix . $key), 3, 1));
    }

    /**
     * @param $item
     * @param $key
     */
    protected function processorListModel($item, $key)
    {
        $this->displayValues[$key] = TEXT_PRODUCT_MODEL . $item ['products_model'] . str_repeat('<br clear="all" />', substr(constant($this->prefix . $key), 3, 1));
    }

    /**
     * @param $item
     * @param $key
     */
    protected function processorListWeight($item, $key)
    {
        $this->displayValues[$key] = TEXT_PRODUCTS_WEIGHT . $item ['products_weight'] . TEXT_SHIPPING_WEIGHT . str_repeat('<br clear="all" />', substr(constant($this->prefix . $key), 3, 1));
    }

    /**
     * @param $item
     * @param $key
     */
    protected function processorListQuantity($item, $key)
    {
        $result = TEXT_PRODUCTS_QUANTITY . $item ['products_quantity'] . str_repeat('<br clear="all" />', substr(constant($this->prefix . $key), 3, 1));
        if ($item ['products_quantity'] <= 0) {
            $result = TEXT_OUT_OF_STOCK . str_repeat('<br clear="all" />', substr(constant($this->prefix . $key), 3, 1));
        }
        $this->displayValues[$key] = $result;
    }

    /**
     * @param $item
     * @param $key
     */
    protected function processorListDateAdded($item, $key)
    {
        $this->displayValues[$key] = TEXT_DATE_ADDED . ' ' . zen_date_long($item ['products_date_added']) . str_repeat('<br clear="all" />', substr(constant($this->prefix . $key), 3, 1));
    }

    /**
     * @param $item
     * @param $key
     */
    protected function processorListManufacturer($item, $key)
    {
        $this->displayValues[$key] = ($item['manufacturers_name'] != '' ? TEXT_MANUFACTURER . ' ' . $item ['manufacturers_name'] . str_repeat('<br clear="all" />', substr(constant($this->prefix . $key), 3, 1)) : '');
    }

    /**
     * @param $item
     * @param $key
     */
    protected function processorListPrice($item, $key)
    {
        $this->displayValues[$key] = TEXT_PRICE . ' ' . $item ['priceBlock'] . str_repeat('<br clear="all" />', substr(constant($this->prefix . $key), 3, 1)) . (zen_get_show_product_switch($item ['products_id'], 'ALWAYS_FREE_SHIPPING_IMAGE_SWITCH') ? (zen_get_product_is_always_free_shipping($item ['products_id']) ? TEXT_PRODUCT_FREE_SHIPPING_ICON . '<br />' : '') : '');

    }

    /**
     * @param $item
     * @param $key
     */
    protected function processorBuyNow($item, $key)
    {
        $allowAddCart = (zen_get_products_allow_add_to_cart($item ['products_id']) == 'Y');
        $multiAddCart = (constant($this->prefix . 'LISTING_MULTIPLE_ADD_TO_CART') > 0 && $item ['products_qty_box_status'] != 0);
        $requiresAttributeChoices = zen_requires_attribute_selection($item ['products_id']) == 1;

        $link = '<a href="' . zen_href_link($item['product_info_page'], 'cPath=' . $item ['productCpath'] . '&products_id=' . $item ['products_id']) . '">' . MORE_INFO_TEXT . '</a>';

        $link = $this->buildBuyNowLink($link, $allowAddCart, $requiresAttributeChoices, $multiAddCart, $item);

        $productsLink = '<a href="' . zen_href_link($item['product_info_page'], 'cPath=' . $item ['productCpath']) . '&products_id=' . $item ['products_id'] . '">' . MORE_INFO_TEXT . '</a>';
        $itemEntry = zen_get_buy_now_button($item ['products_id'], $link, $productsLink) . '<br />' . zen_get_products_quantity_min_units_display($item ['products_id']) . str_repeat('<br clear="all" />', substr(constant($this->prefix . $key), 3, 1));
        $this->displayValues[$key] = $itemEntry;
    }

    /**
     * @param $link
     * @param $allowAddCart
     * @param $hasAttributes
     * @param $multiAddCart
     * @param $item
     * @return string
     */
    protected function buildBuyNowLink($link, $allowAddCart, $requiresAttributeChoices, $multiAddCart, $item)
    {
        if ($allowAddCart && !$requiresAttributeChoices && $multiAddCart) {
            $link = constant('TEXT_' . $this->prefix . 'LISTING_MULTIPLE_ADD_TO_CART') . "<input type=\"text\" name=\"products_id[" . $item ['products_id'] . "]\" value=\"0\" size=\"4\" />";
            $this->countQtyBoxItems++;
        }
        if ($allowAddCart && !$requiresAttributeChoices && !$multiAddCart) {
            $link = '<a href="' . zen_href_link($this->request->readGet('main_page'), zen_get_all_get_params(array(
                        'action'
                    )) . 'action=buy_now&products_id=' . $item ['products_id']) . '">' . zen_image_button(BUTTON_IMAGE_BUY_NOW, BUTTON_BUY_NOW_ALT) . '</a>&nbsp;';
        }

        if (!$allowAddCart) {
            $link = '<a href="' . zen_href_link($item['product_info_page'], 'cPath=' . $item ['productCpath'] . '&products_id=' . $item ['products_id']) . '">' . MORE_INFO_TEXT . '</a>';
        }
        return $link;
    }

    /**
     * @param $item
     * @param $key
     */
    protected function processorListDescription($item, $key)
    {
        $displayText = zen_clean_html($item ['products_description']);
        $this->displayValues[$key] = $displayText;
    }
}
