<?php
/**
 * Class TabularProduct
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: currencies.php 15880 2010-04-11 16:24:30Z wilt $
 */
namespace ZenCart\Platform\listingBox\formatters;
/**
 * Class TabularProduct
 * @package ZenCart\Platform\listingBox\formatters
 */
class TabularProduct extends AbstractFormatter implements FormatterInterface
{
    /**
     * @var
     */
    protected $countQtyBoxItems;

    /**
     *
     */
    public function format()
    {
        $defineList = $this->initDefineList();
        $columnList = $this->initColumnList($defineList);
        $items = $this->itemList;;
        $this->countQtyBoxItems = 0;
        $listBoxContents = $this->buildColumnOptions($items, $columnList);
        $this->formattedResults = $listBoxContents;
        $this->tplVars['countQtyBoxItems'] = $this->countQtyBoxItems;
    }

    /**
     * @param $items
     * @param $columnList
     * @return array
     */
    protected function buildColumnOptions($items, $columnList)
    {
        $listBoxContents = array();
        $headers = array();
        if (count($items) == 0) {
            $this->tplVars['caption'] = TEXT_NO_PRODUCTS;
            return array();
        }
        $headersLayoutMap = $this->getHeadersLayoutMap();
        for ($col = 0, $n = sizeof($columnList); $col < $n; $col++) {
            $result = $headersLayoutMap[$columnList [$col]]();
            $headers [] = array(
                'title' => $result['title'],
                'col_params' => $result['col_params']
            );
        }
        $this->tplVars['headers'] = $headers;
        $this->tplVars['maxColSpan'] = count($headers);

        foreach ($items as $item) {
            $row = array();
            $prodLink = $this->getProductLink($item);
            $rowLayoutMap = $this->getRowLayoutMap($item, $prodLink);
            for ($col = 0, $n = sizeof($columnList); $col < $n; $col++) {
                $result = $rowLayoutMap[$columnList [$col]]();
                $row [] = $result;
            }
            $listBoxContents [] = $row;
        }
        return $listBoxContents;
    }

    /**
     * @return array
     */
    protected function getHeadersLayoutMap()
    {
        $headersLayoutMap = array(
            'PRODUCT_LIST_MODEL' => function () {
                return array('title' => TABLE_HEADING_MODEL, 'col_params' => '');
            },
            'PRODUCT_LIST_NAME' => function () {
                return array('title' => TABLE_HEADING_PRODUCTS, 'col_params' => '');
            },
            'PRODUCT_LIST_MANUFACTURER' => function () {
                return array('title' => TABLE_HEADING_MANUFACTURER, 'col_params' => '');
            },
            'PRODUCT_LIST_PRICE' => function () {
                return array('title' => TABLE_HEADING_PRICE,
                             'col_params' => 'style="text-align:right;' . (PRODUCTS_LIST_PRICE_WIDTH > 0 ? 'width:' . PRODUCTS_LIST_PRICE_WIDTH . '"' : '"'));
            },
            'PRODUCT_LIST_QUANTITY' => function () {
                return array('title' => TABLE_HEADING_QUANTITY, 'col_params' => 'style="text-align:right"');
            },
            'PRODUCT_LIST_WEIGHT' => function () {
                return array('title' => TABLE_HEADING_WEIGHT, 'col_params' => 'style="text-align:right"');
            },
            'PRODUCT_LIST_IMAGE' => function () {
                return array('title' => TABLE_HEADING_IMAGE, 'col_params' => 'style="text-align:center"');
            },
        );
        return $headersLayoutMap;
    }

    /**
     * @param $item
     * @param $prodLink
     * @return array
     */
    protected function getRowLayoutMap($item, $prodLink)
    {
        $rowLayoutMap = array(
            'PRODUCT_LIST_MODEL' => function () use ($item, $prodLink) {
                return array('value' => $item ['products_model'], 'col_params' => '');
            },
            'PRODUCT_LIST_NAME' => function () use ($item, $prodLink) {
                $prodDesc = zen_get_products_description($item ['products_id'], $_SESSION ['languages_id']);
                $lc_text = '<h3 class="itemTitle"><a href="' .
                    $prodLink . '">' .
                    $item ['products_name'] . '</a></h3><div class="listingDescription">' .
                    zen_trunc_string(zen_clean_html(stripslashes($prodDesc)), PRODUCT_LIST_DESCRIPTION) . '</div>';
                return array('value' => $lc_text, 'col_params' => '');
            },
            'PRODUCT_LIST_MANUFACTURER' => function () use ($item, $prodLink) {
                $lc_text = '<a href="' . zen_href_link(FILENAME_DEFAULT, 'manufacturers_id=' . $item ['manufacturers_id']) . '">' . $item ['manufacturers_name'] . '</a>';
                return array('value' => $lc_text, 'col_params' => '');
            },
            'PRODUCT_LIST_PRICE' => function () use ($item, $prodLink) {
                $lc_text = zen_get_products_display_price($item ['products_id']) . '<br />';
                $lc_button = '<a href="' . $prodLink . '">' . MORE_INFO_TEXT . '</a>';
                if (!(zen_has_product_attributes($item ['products_id']) || PRODUCT_LIST_PRICE_BUY_NOW == '0')) {
                    $lc_button = $this->buildLcButton($item);
                }
                $the_button = $lc_button;
                $products_link = '<a href="' . $prodLink . '">' . MORE_INFO_TEXT . '</a>';
                $lc_text .= '<br />' . zen_get_buy_now_button($item ['products_id'], $the_button, $products_link) . '<br />' . zen_get_products_quantity_min_units_display($item ['products_id']);
                $lc_text .= '<br />' . (zen_get_show_product_switch($item ['products_id'], 'ALWAYS_FREE_SHIPPING_IMAGE_SWITCH') ? (zen_get_product_is_always_free_shipping($item ['products_id']) ? TEXT_PRODUCT_FREE_SHIPPING_ICON . '<br />' : '') : '');

                return array('value' => $lc_text,
                             'col_params' => 'style="text-align:right"');
            },
            'PRODUCT_LIST_QUANTITY' => function () use ($item, $prodLink) {
                return array('value' => $item ['products_quantity'], 'col_params' => 'style="text-align:right"');
            },
            'PRODUCT_LIST_WEIGHT' => function () use ($item, $prodLink) {
                return array('value' => $item ['products_weight'], 'col_params' => 'style="text-align:right"');
            },
            'PRODUCT_LIST_IMAGE' => function () use ($item, $prodLink) {
                if ($item ['products_image'] == '' && PRODUCTS_IMAGE_NO_IMAGE_STATUS == 0) {
                    return array('value' => '', 'col_params' => 'style="text-align:center"');
                }
                $lc_text = '<a href="' . $prodLink . '">' . zen_image(DIR_WS_IMAGES . $item ['products_image'], $item ['products_name'], IMAGE_PRODUCT_LISTING_WIDTH, IMAGE_PRODUCT_LISTING_HEIGHT, 'class="listingProductImage"') . '</a>';
                return array('value' => $lc_text, 'col_params' => 'style="text-align:center"');
            },
        );
        return $rowLayoutMap;
    }

    /**
     * @param $item
     * @return string
     */
    protected function buildLcButton($item)
    {
        if ($this->testIncrementCountQuantityBoxes($item)) {
            $this->countQtyBoxItems++;
        }
        $result = TEXT_PRODUCT_LISTING_MULTIPLE_ADD_TO_CART . "<input type=\"text\" name=\"products_id[" . $item ['products_id'] . "]\" value=\"0\" size=\"4\" />";
        if ($this->testHideQuantityBox($item)) {
            $result = '<a href="' . zen_href_link($this->request->readGet('main_page'), zen_get_all_get_params(array(
                        'action'
                    )) . 'action=buy_now&products_id=' . $item ['products_id']) . '">' . zen_image_button(BUTTON_IMAGE_BUY_NOW, BUTTON_BUY_NOW_ALT, 'class="listingBuyNowButton"') . '</a>';
        }
        if ($this->testShowBuyNow($item)) {
            $result = zen_draw_form('cart_quantity', zen_href_link($this->request->readGet('main_page'), zen_get_all_get_params(array(
                        'action'
                    )) . 'action=add_product&products_id=' . $item ['products_id']), 'post', 'enctype="multipart/form-data"') . '<input type="text" name="cart_quantity" value="' . (zen_get_buy_now_qty($item ['products_id'])) . '" maxlength="6" size="4" /><br />' . zen_draw_hidden_field('products_id', $item ['products_id']) . zen_image_submit(BUTTON_IMAGE_IN_CART, BUTTON_IN_CART_ALT) . '</form>';
        }
        if ($this->testShowAddProduct($item)) {

            $result = '<a href="' . zen_href_link($this->request->readGet('main_page'), zen_get_all_get_params(array(
                        'action'
                    )) . 'action=buy_now&products_id=' . $item ['products_id']) . '">' . zen_image_button(BUTTON_IMAGE_BUY_NOW, BUTTON_BUY_NOW_ALT, 'class="listingBuyNowButton"') . '</a>';
        }

        return $result;
    }

    /**
     * @param $item
     * @return string|void
     */
    protected function getProductLink($item)
    {
        $infoPage = zen_get_info_page($item ['products_id']);
        $mfId = $this->request->readGet('manufacturers_id', 0);
        $filterId = $this->request->readGet('filter_id', 0);
        $cPath = $this->request->readGet('cPath', '');
        $cPathGenOpt = ($mfId > 0 && $filterId > 0) ? $filterId : ($cPath != '') ? $cPath : $item ['master_categories_id'];
        $cPathGen = zen_get_generated_category_path_rev($cPathGenOpt);
        $link = zen_href_link($infoPage, 'cPath=' . $cPathGen . '&products_id=' . $item ['products_id']);
        return $link;
    }

    /**
     * @return array
     */
    public function initDefineList()
    {
        $defineList = array(
            'PRODUCT_LIST_MODEL' => PRODUCT_LIST_MODEL,
            'PRODUCT_LIST_NAME' => PRODUCT_LIST_NAME,
            'PRODUCT_LIST_MANUFACTURER' => PRODUCT_LIST_MANUFACTURER,
            'PRODUCT_LIST_PRICE' => PRODUCT_LIST_PRICE,
            'PRODUCT_LIST_QUANTITY' => PRODUCT_LIST_QUANTITY,
            'PRODUCT_LIST_WEIGHT' => PRODUCT_LIST_WEIGHT,
            'PRODUCT_LIST_IMAGE' => PRODUCT_LIST_IMAGE
        );

        asort($defineList);
        return $defineList;
    }

    /**
     * @param $defineList
     * @return array
     */
    public function initColumnList($defineList)
    {
        $columnList = array();
        foreach ($defineList as $key => $value) {
            if ($value > 0) {
                $columnList [] = $key;
            }
        }
        return $columnList;
    }
}
