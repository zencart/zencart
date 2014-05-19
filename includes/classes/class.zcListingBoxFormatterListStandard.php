<?php
/**
 * zcListingBoxFormatterListStandard
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
/**
 * class zcListingBoxFormatterListStandard
 *
 * @package classes
 */
class zcListingBoxFormatterListStandard extends base
{
  /**
   *
   * @param zcAbstractListingBoxBase $listingbox
   * @return array
   */
  public function format(zcAbstractListingBoxBase $listingbox)
  {
    global $db;
    $items = $listingbox->getItems();
    $outputLayout = $listingbox->getOutputLayout();

    $definePrefix = $outputLayout ['definePrefix'];
    $imageListingWidth = $outputLayout ['imageListingWidth'];
    $imageListingHeight = $outputLayout ['imageListingHeight'];

    $groupId = zen_get_configuration_key_value($definePrefix . 'LIST_GROUP_ID');
    $listBoxContents = array();
    $countQuantityBoxItems = 0;
    if (count($items) > 0) {
      foreach ( $items as $item ) {
        $item ['imageListingWidth'] = $imageListingWidth;
        $item ['imageListingHeight'] = $imageListingHeight;

        if (! defined($definePrefix . 'LIST_IMAGE') || (defined($definePrefix . 'LIST_IMAGE') && constant($definePrefix . 'LIST_IMAGE') != 0)) {
          if ($item ['products_image'] == '' and PRODUCTS_IMAGE_NO_IMAGE_STATUS == 0) {
            $displayProductsImage = str_repeat('<br clear="all" />', substr(constant($definePrefix . 'LIST_IMAGE'), 3, 1));
          } else {
            $displayProductsImage = '<a href="' . zen_href_link(zen_get_info_page($item ['products_id']), 'cPath=' . $item ['productCpath'] . '&products_id=' . $item ['products_id']) . '">' . zen_image(DIR_WS_IMAGES . $item ['products_image'], $item ['products_name'], $imageListingWidth, $imageListingHeight) . '</a>' . str_repeat('<br clear="all" />', substr(constant($definePrefix . 'LIST_IMAGE'), 3, 1));
          }
        } else {
          $displayProductsImage = '';
        }
        if (! defined($definePrefix . 'LIST_NAME') || (defined($definePrefix . 'LIST_NAME') && constant($definePrefix . 'LIST_NAME') != '0')) {
          $displayProductsName = '<a href="' . zen_href_link(zen_get_info_page($item ['products_id']), 'cPath=' . $item ['productCpath'] . '&products_id=' . $item ['products_id']) . '"><strong>' . $item ['products_name'] . '</strong></a>' . str_repeat('<br clear="all" />', substr(constant($definePrefix . 'LIST_NAME'), 3, 1));
        } else {
          $displayProductsName = '';
        }
        if (! defined($definePrefix . 'LIST_MODEL') || (defined($definePrefix . 'LIST_MODEL') && constant($definePrefix . 'LIST_MODEL') != '0' and zen_get_show_product_switch($item ['products_id'], 'model'))) {
          $displayProductsModel = TEXT_PRODUCTS_MODEL . $item ['products_model'] . str_repeat('<br clear="all" />', substr(constant($definePrefix . 'LIST_MODEL'), 3, 1));
        } else {
          $displayProductsModel = '';
        }
        if (! defined($definePrefix . 'LIST_WEIGHT') || (defined($definePrefix . 'LIST_WEIGHT') && constant($definePrefix . 'LIST_WEIGHT') != '0' and zen_get_show_product_switch($item ['products_id'], 'weight'))) {
          $displayProductsWeight = TEXT_PRODUCTS_WEIGHT . $item ['products_weight'] . TEXT_SHIPPING_WEIGHT . str_repeat('<br clear="all" />', substr(constant($definePrefix . 'LIST_WEIGHT'), 3, 1));
        } else {
          $displayProductsWeight = '';
        }
        if (! defined($definePrefix . 'LIST_QUANTITY') || (defined($definePrefix . 'LIST_QUANTITY') && constant($definePrefix . 'LIST_QUANTITY') != '0' and zen_get_show_product_switch($item ['products_id'], 'quantity'))) {
          if ($item ['products_quantity'] <= 0) {
            $displayProductsQuantity = TEXT_OUT_OF_STOCK . str_repeat('<br clear="all" />', substr(constant($definePrefix . 'LIST_QUANTITY'), 3, 1));
          } else {
            $displayProductsQuantity = TEXT_PRODUCTS_QUANTITY . $item ['products_quantity'] . str_repeat('<br clear="all" />', substr(constant($definePrefix . 'LIST_QUANTITY'), 3, 1));
          }
        } else {
          $displayProductsQuantity = '';
        }

        if (! defined($definePrefix . 'LIST_DATE_ADDED') || (defined($definePrefix . 'LIST_DATE_ADDED') && constant($definePrefix . 'LIST_DATE_ADDED') != '0' and zen_get_show_product_switch($item ['products_id'], 'date_added'))) {
          $displayProductsDateAdded = TEXT_DATE_ADDED . ' ' . zen_date_long($item ['products_date_added']) . str_repeat('<br clear="all" />', substr(constant($definePrefix . 'LIST_DATE_ADDED'), 3, 1));
        } else {
          $displayProductsDateAdded = '';
        }

        if (! defined($definePrefix . 'LIST_MANUFACTURER') || (defined($definePrefix . 'LIST_MANUFACTURER') && constant($definePrefix . 'LIST_MANUFACTURER') != '0' and zen_get_show_product_switch($item ['products_id'], 'manufacturer'))) {
          $displayProductsManufacturersName = ($item['manufacturers_name'] != '' ? TEXT_MANUFACTURER . ' ' . $item ['manufacturers_name'] . str_repeat('<br clear="all" />', substr(constant($definePrefix . 'LIST_MANUFACTURER'), 3, 1)) : '');
        } else {
          $displayProductsManufacturersName = '';
        }
        if (! defined($definePrefix . 'LIST_PRICE') || (defined($definePrefix . 'LIST_PRICE') && (constant($definePrefix . 'LIST_PRICE') != '0' and zen_get_products_allow_add_to_cart($item ['products_id']) == 'Y') and zen_check_show_prices() == true)) {
          $displayProductsPrice = TEXT_PRICE . ' ' . $item ['displayPrice'] . str_repeat('<br clear="all" />', substr(constant($definePrefix . 'LIST_PRICE'), 3, 1)) . (zen_get_show_product_switch($item ['products_id'], 'ALWAYS_FREE_SHIPPING_IMAGE_SWITCH') ? (zen_get_product_is_always_free_shipping($item ['products_id']) ? TEXT_PRODUCT_FREE_SHIPPING_ICON . '<br />' : '') : '');
        } else {
          $displayProductsPrice = '';
        }

        if (! defined($definePrefix . 'BUY_NOW') || (defined($definePrefix . 'BUY_NOW') && constant($definePrefix . 'BUY_NOW') != '0' and zen_get_products_allow_add_to_cart($item ['products_id']) == 'Y')) {
          if (zen_has_product_attributes($item ['products_id'])) {
            $link = '<a href="' . zen_href_link(zen_get_info_page($item ['products_id']), 'cPath=' . $item ['mproductCpath'] . '&products_id=' . $item ['products_id']) . '">' . MORE_INFO_TEXT . '</a>';
          } else {
            if (constant($definePrefix . 'LISTING_MULTIPLE_ADD_TO_CART') > 0 && $item ['products_qty_box_status'] != 0) {
              $countQuantityBoxItems ++;
              $link = constant('TEXT_' . $definePrefix . 'LISTING_MULTIPLE_ADD_TO_CART') . "<input type=\"text\" name=\"products_id[" . $item ['products_id'] . "]\" value=\"0\" size=\"4\" />";
            } else {
              $link = '<a href="' . zen_href_link(zcRequest::readGet('main_page'), zen_get_all_get_params(array(
                  'action'
              )) . 'action=buy_now&products_id=' . $item ['products_id']) . '">' . zen_image_button(BUTTON_IMAGE_BUY_NOW, BUTTON_BUY_NOW_ALT) . '</a>&nbsp;';
            }
          }

          $the_button = $link;
          $products_link = '<a href="' . zen_href_link(zen_get_info_page($item ['products_id']), 'cPath=' . $item ['productCpath'] . '&products_id=' . $item ['products_id']) . '">' . MORE_INFO_TEXT . '</a>';
          $displayProductsButton = zen_get_buy_now_button($item ['products_id'], $the_button, $products_link) . '<br />' . zen_get_products_quantity_min_units_display($item ['products_id']) . str_repeat('<br clear="all" />', substr(constant($definePrefix . 'BUY_NOW'), 3, 1));
        } else {
          $link = '<a href="' . zen_href_link(zen_get_info_page($item ['products_id']), 'cPath=' . $item ['productCpath'] . '&products_id=' . $item ['products_id']) . '">' . MORE_INFO_TEXT . '</a>';
          $the_button = $link;
          $products_link = '<a href="' . zen_href_link(zen_get_info_page($item ['products_id']), 'cPath=' . $item ['productCpath']) . '&products_id=' . $item ['products_id'] . '">' . MORE_INFO_TEXT . '</a>';
          $displayProductsButton = zen_get_buy_now_button($item ['products_id'], $the_button, $products_link) . '<br />' . zen_get_products_quantity_min_units_display($item ['products_id']) . str_repeat('<br clear="all" />', substr(constant($definePrefix . 'BUY_NOW'), 3, 1));
        }

        if (! defined($definePrefix . 'LIST_DESCRIPTION') || (defined($definePrefix . 'LIST_DESCRIPTION') && constant($definePrefix . 'LIST_DESCRIPTION') > '0')) {
          $disp_text = $item ['products_description'];
          $disp_text = zen_clean_html($disp_text);

          $item ['displayProductsDescription'] = stripslashes(zen_trunc_string($disp_text, constant($definePrefix . 'LIST_DESCRIPTION'), '<a href="' . zen_href_link(zen_get_info_page($item ['products_id']), 'cPath=' . $item ['productCpath'] . '&products_id=' . $item ['products_id']) . '"> ' . MORE_INFO_TEXT . '</a>'));
        } else {
          $item ['displayProductsDescription'] = '';
        }

        $sql = "select configuration_key, configuration_value from " . TABLE_CONFIGURATION . " where configuration_group_id='" . $groupId . "' and (configuration_value >= 1000 and configuration_value <= 1999) order by LPAD(configuration_value,11,0)";
        $dispSortOrder = $db->Execute($sql);
        // print_r($dispSortOrder);
        while ( ! $dispSortOrder->EOF ) {
          // echo($dispSortOrder->fields['configuration_key'] . "<br>");
          // echo $definePrefix . 'LIST_IMAGE' . "<br>";
          if ($dispSortOrder->fields ['configuration_key'] == $definePrefix . 'LIST_IMAGE') {
            $item ['displayProductColOne'] [] = $displayProductsImage;
          }
          if ($dispSortOrder->fields ['configuration_key'] == $definePrefix . 'LIST_QUANTITY') {
            $item ['displayProductColOne'] [] = $displayProductsQuantity;
          }
          if ($dispSortOrder->fields ['configuration_key'] == $definePrefix . 'BUY_NOW') {
            $item ['displayProductColOne'] [] = $displayProductsButton;
          }
          if ($dispSortOrder->fields ['configuration_key'] == $definePrefix . 'LIST_NAME') {
            $item ['displayProductColOne'] [] = $displayProductsName;
          }
          if ($dispSortOrder->fields ['configuration_key'] == $definePrefix . 'LIST_MODEL') {
            $item ['displayProductColOne'] [] = $displayProductsModel;
          }
          if ($dispSortOrder->fields ['configuration_key'] == $definePrefix . 'LIST_MANUFACTURER') {
            $item ['displayProductColOne'] [] = $displayProductsManufacturersName;
          }
          if ($dispSortOrder->fields ['configuration_key'] == $definePrefix . 'LIST_PRICE') {
            $item ['displayProductColOne'] [] = $displayProductsPrice;
          }
          if ($dispSortOrder->fields ['configuration_key'] == $definePrefix . 'LIST_WEIGHT') {
            $item ['displayProductColOne'] [] = $displayProductsWeight;
          }
          if ($dispSortOrder->fields ['configuration_key'] == $definePrefix . 'LIST_DATE_ADDED') {
            $item ['displayProductColOne'] [] = $displayProductsDateAdded;
          }
          $dispSortOrder->moveNext();
        }
        $sql = "select configuration_key, configuration_value from " . TABLE_CONFIGURATION . " where configuration_group_id='" . $groupId . "' and (configuration_value >= 2000 and configuration_value <= 2999) order by LPAD(configuration_value,11,0)";
        $dispSortOrder = $db->Execute($sql);
        // print_r($dispSortOrder);
        while ( ! $dispSortOrder->EOF ) {
          // echo $definePrefix . 'LIST_IMAGE';
          if ($dispSortOrder->fields ['configuration_key'] == $definePrefix . 'LIST_IMAGE') {
            $item ['displayProductColTwo'] [] = $displayProductsImage;
          }
          if ($dispSortOrder->fields ['configuration_key'] == $definePrefix . 'LIST_QUANTITY') {
            $item ['displayProductColTwo'] [] = $displayProductsQuantity;
          }
          if ($dispSortOrder->fields ['configuration_key'] == $definePrefix . 'BUY_NOW') {
            $item ['displayProductColTwo'] [] = $displayProductsButton;
          }
          if ($dispSortOrder->fields ['configuration_key'] == $definePrefix . 'LIST_NAME') {
            $item ['displayProductColTwo'] [] = $displayProductsName;
          }
          if ($dispSortOrder->fields ['configuration_key'] == $definePrefix . 'LIST_MODEL') {
            $item ['displayProductColTwo'] [] = $displayProductsModel;
          }
          if ($dispSortOrder->fields ['configuration_key'] == $definePrefix . 'LIST_MANUFACTURER') {
            $item ['displayProductColTwo'] [] = $displayProductsManufacturersName;
          }
          if ($dispSortOrder->fields ['configuration_key'] == $definePrefix . 'LIST_PRICE') {
            $item ['displayProductColTwo'] [] = $displayProductsPrice;
          }
          if ($dispSortOrder->fields ['configuration_key'] == $definePrefix . 'LIST_WEIGHT') {
            $item ['displayProductColTwo'] [] = $displayProductsWeight;
          }
          if ($dispSortOrder->fields ['configuration_key'] == $definePrefix . 'LIST_DATE_ADDED') {
            $item ['displayProductColTwo'] [] = $displayProductsDateAdded;
          }
          $dispSortOrder->moveNext();
        }

        // print_r($item['displayProductColOne']);
        $listBoxContents [] = $item;
      }
    }
    // print_r($listBoxContents);
    $showSubmit = zen_run_normal();
    if ((($countQuantityBoxItems > 0 and $showSubmit == true and count($listBoxContents) > 0) and (constant($definePrefix . 'LISTING_MULTIPLE_ADD_TO_CART') == 1 or constant($definePrefix . 'LISTING_MULTIPLE_ADD_TO_CART') == 3))) {
      $showTopSubmit = true;
    } else {
      $showTopSubmit = false;
    }
    if ((($countQuantityBoxItems > 0 and $showSubmit == true and count($listBoxContents) > 0) and (constant($definePrefix . 'LISTING_MULTIPLE_ADD_TO_CART') >= 2))) {
      $showBottomSubmit = true;
    } else {
      $showBottomSubmit = false;
    }
    $showForm = ($showTopSubmit || $showBottomSubmit);
    $listingbox->setTemplateVariable('showTopSubmit', $showTopSubmit);
    $listingbox->setTemplateVariable('showBottomSubmit', $showBottomSubmit);
    $listingbox->setTemplateVariable('showForm', $showForm);

    $this->notify('NOTIFY_LISTING_BOX_FORMATTER_COLUMNAR_FORMAT_END', NULL, $listBoxContents);
    return $listBoxContents;
  }
  public function getDefaultTemplate()
  {
    global $template;
    return ($template->get_template_dir('tpl_listingbox_productliststd_default.php', DIR_WS_TEMPLATE, $current_page_base, 'listingboxes') . '/tpl_listingbox_productliststd_default.php');
  }
}
