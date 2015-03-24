<?php
/**
 * zcListingBoxFormatterTabularProduct
 *
 * @package classes
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: wilt  New in v1.6.0 $
 */
/**
 * class zcListingBoxFormatterTabularProduct
 *
 * @package classes
 */
class zcListingBoxFormatterTabularProduct extends base
{
  /**
   *
   * @param zcAbstractListingBoxBase $listingbox
   * @return array
   */
  public function format(zcAbstractListingBoxBase $listingbox)
  {
    $filterOutputVariables = $listingbox->getFilterOutputVariables();
    $header = array();
    $listBoxContents = array();
    $zc_col_count_description = 0;
    $items = $listingbox->getItems();
    $countQuantityBoxItems = 0;
    if (count($items) == 0) {
      $listingbox->setTemplateVariable('caption', TEXT_NO_PRODUCTS);
    } else {
      for($col = 0, $n = sizeof($filterOutputVariables ['columnList']); $col < $n; $col ++) {
        switch ($filterOutputVariables ['columnList'] [$col]) {
          case 'PRODUCT_LIST_MODEL' :
            $lc_text = TABLE_HEADING_MODEL;
            $lc_align = '';
            $zc_col_count_description ++;
            break;
          case 'PRODUCT_LIST_NAME' :
            $lc_text = TABLE_HEADING_PRODUCTS;
            $lc_align = '';
            $zc_col_count_description ++;
            break;
          case 'PRODUCT_LIST_MANUFACTURER' :
            $lc_text = TABLE_HEADING_MANUFACTURER;
            $lc_align = '';
            $zc_col_count_description ++;
            break;
          case 'PRODUCT_LIST_PRICE' :
            $lc_text = TABLE_HEADING_PRICE;
            $lc_align = 'style="text-align:right;' . (PRODUCTS_LIST_PRICE_WIDTH > 0 ? 'width:' . PRODUCTS_LIST_PRICE_WIDTH . '"' : '"');
            $zc_col_count_description ++;
            break;
          case 'PRODUCT_LIST_QUANTITY' :
            $lc_text = TABLE_HEADING_QUANTITY;
            $lc_align = 'style="text-align:right"';
            $zc_col_count_description ++;
            break;
          case 'PRODUCT_LIST_WEIGHT' :
            $lc_text = TABLE_HEADING_WEIGHT;
            $lc_align = 'style="text-align:right"';
            $zc_col_count_description ++;
            break;
          case 'PRODUCT_LIST_IMAGE' :
            $lc_text = TABLE_HEADING_IMAGE;
            $lc_align = 'style="text-align:center"';
            $zc_col_count_description ++;
            break;
        }

        $header [] = array(
            'title' => $lc_text,
            'col_params' => $lc_align
        );
      }
      $listingbox->setTemplateVariable('maxColSpan', count($headers));

      foreach ( $items as $item ) {
        $row = array();

        for($col = 0, $n = sizeof($filterOutputVariables ['columnList']); $col < $n; $col ++) {

          switch ($filterOutputVariables ['columnList'] [$col]) {
            case 'PRODUCT_LIST_MODEL' :
              $lc_align = '';
              $lc_text = $item ['products_model'];
              break;
            case 'PRODUCT_LIST_NAME' :
              $lc_align = '';
              $lc_text = '<h3 class="itemTitle"><a href="' . zen_href_link(zen_get_info_page($item ['products_id']), 'cPath=' . ((zcRequest::readGet('manufacturers_id', 0) > 0 and zcRequest::readGet('filter_id', 0) > 0) ? zen_get_generated_category_path_rev(zcRequest::readGet('filter_id')) : (zcRequest::readGet('cPath', '') != '' ? zen_get_generated_category_path_rev(zcRequest::readGet('cPath')) : zen_get_generated_category_path_rev($item ['master_categories_id']))) . '&products_id=' . $item ['products_id']) . '">' . $item ['products_name'] . '</a></h3><div class="listingDescription">' . zen_trunc_string(zen_clean_html(stripslashes(zen_get_products_description($item ['products_id'], $_SESSION ['languages_id']))), PRODUCT_LIST_DESCRIPTION) . '</div>';
              break;
            case 'PRODUCT_LIST_MANUFACTURER' :
              $lc_align = '';
              $lc_text = '<a href="' . zen_href_link(FILENAME_DEFAULT, 'manufacturers_id=' . $item ['manufacturers_id']) . '">' . $item ['manufacturers_name'] . '</a>';
              break;
            case 'PRODUCT_LIST_PRICE' :
              $lc_price = zen_get_products_display_price($item ['products_id']) . '<br />';
              $lc_align = 'right';
              $lc_text = $lc_price;

              // more info in place of buy now
              $lc_button = '';
              if (zen_has_product_attributes($item ['products_id'], false, true) > (PRODUCT_LISTING_MULTIPLE_ADD_TO_CART != 0 ? 0 : 1) or PRODUCT_LIST_PRICE_BUY_NOW == '0') {
                $lc_button = '<a href="' . zen_href_link(zen_get_info_page($item ['products_id']), 'cPath=' . ((zcRequest::readGet('manufacturers_id', 0) > 0 and zcRequest::readGet('filter_id', 0)) > 0 ? zen_get_generated_category_path_rev(zcRequest::readGet('filter_id')) : (zcRequest::readGet('cPath', '') != '' ? zcRequest::readGet('cPath') : zen_get_generated_category_path_rev($item ['master_categories_id']))) . '&products_id=' . $item ['products_id']) . '">' . MORE_INFO_TEXT . '</a>';
              } else {
                if (PRODUCT_LISTING_MULTIPLE_ADD_TO_CART != 0) {
                  if ($item ['products_qty_box_status'] != 0 && zen_get_products_allow_add_to_cart($item ['products_id']) != 'N' && $item ['product_is_call'] == 0 && ($item ['products_quantity'] > 0 || SHOW_PRODUCTS_SOLD_OUT_IMAGE == 0)) {
                    $countQuantityBoxItems ++;
                  }
                  // hide quantity box
                  if ($item ['products_qty_box_status'] == 0) {
                    $lc_button = '<a href="' . zen_href_link(zcRequest::readGet('main_page'), zen_get_all_get_params(array(
                        'action'
                    )) . 'action=buy_now&products_id=' . $item ['products_id']) . '">' . zen_image_button(BUTTON_IMAGE_BUY_NOW, BUTTON_BUY_NOW_ALT, 'class="listingBuyNowButton"') . '</a>';
                  } else {
                    $lc_button = TEXT_PRODUCT_LISTING_MULTIPLE_ADD_TO_CART . "<input type=\"text\" name=\"products_id[" . $item ['products_id'] . "]\" value=\"0\" size=\"4\" />";
                  }
                } else {
                  // qty box with add to cart button
                  if (PRODUCT_LIST_PRICE_BUY_NOW == '2' && $item ['products_qty_box_status'] != 0) {
                    $lc_button = zen_draw_form('cart_quantity', zen_href_link(zcRequest::readGet('main_page'), zen_get_all_get_params(array(
                        'action'
                    )) . 'action=add_product&products_id=' . $item ['products_id']), 'post', 'enctype="multipart/form-data"') . '<input type="text" name="cart_quantity" value="' . (zen_get_buy_now_qty($item ['products_id'])) . '" maxlength="6" size="4" /><br />' . zen_draw_hidden_field('products_id', $item ['products_id']) . zen_image_submit(BUTTON_IMAGE_IN_CART, BUTTON_IN_CART_ALT) . '</form>';
                  } else {
                    $lc_button = '<a href="' . zen_href_link(zcRequest::readGet('main_page'), zen_get_all_get_params(array(
                        'action'
                    )) . 'action=buy_now&products_id=' . $item ['products_id']) . '">' . zen_image_button(BUTTON_IMAGE_BUY_NOW, BUTTON_BUY_NOW_ALT, 'class="listingBuyNowButton"') . '</a>';
                  }
                }
              }
              $the_button = $lc_button;
              $products_link = '<a href="' . zen_href_link(zen_get_info_page($item ['products_id']), 'cPath=' . ((zcRequest::readGet('manufacturers_id', 0) > 0 and zcRequest::readGet('filter_id', 0) > 0) ? zen_get_generated_category_path_rev(zcRequest::readGet('filter_id')) : zcRequest::readGet('cPath', '') != '' ? zen_get_generated_category_path_rev(zcRequest::readGet('cPath')) : zen_get_generated_category_path_rev($item ['master_categories_id'])) . '&products_id=' . $item ['products_id']) . '">' . MORE_INFO_TEXT . '</a>';
              $lc_text .= '<br />' . zen_get_buy_now_button($item ['products_id'], $the_button, $products_link) . '<br />' . zen_get_products_quantity_min_units_display($item ['products_id']);
              $lc_text .= '<br />' . (zen_get_show_product_switch($item ['products_id'], 'ALWAYS_FREE_SHIPPING_IMAGE_SWITCH') ? (zen_get_product_is_always_free_shipping($item ['products_id']) ? TEXT_PRODUCT_FREE_SHIPPING_ICON . '<br />' : '') : '');

              break;
            case 'PRODUCT_LIST_QUANTITY' :
              $lc_align = 'right';
              $lc_text = $item ['products_quantity'];
              break;
            case 'PRODUCT_LIST_WEIGHT' :
              $lc_align = 'right';
              $lc_text = $item ['products_weight'];
              break;
            case 'PRODUCT_LIST_IMAGE' :
              $lc_align = 'center';
              if ($item ['products_image'] == '' and PRODUCTS_IMAGE_NO_IMAGE_STATUS == 0) {
                $lc_text = '';
              } else {
                if (zcRequest::hasGet('manufacturers_id')) {
                  $lc_text = '<a href="' . zen_href_link(zen_get_info_page($item ['products_id']), 'cPath=' . ((zcRequest::readGet('manufacturers_id', 0) > 0 and zcRequest::readGet('filter_id', 0) > 0) ? zen_get_generated_category_path_rev(zcRequest::readGet('filter_id')) : (zcRequest::readGet('cPath', '') != '' ? zen_get_generated_category_path_rev(zcRequest::readGet('cPath')) : zen_get_generated_category_path_rev($items ['master_categories_id']))) . '&products_id=' . $item ['products_id']) . '">' . zen_image(DIR_WS_IMAGES . $item ['products_image'], $item ['products_name'], IMAGE_PRODUCT_LISTING_WIDTH, IMAGE_PRODUCT_LISTING_HEIGHT, 'class="listingProductImage"') . '</a>';
                } else {
                  $lc_text = '<a href="' . zen_href_link(zen_get_info_page($item ['products_id']), 'cPath=' . ((zcRequest::readGet('manufacturers_id', 0) > 0 and zcRequest::readGet('filter_id', 0) > 0) ? zen_get_generated_category_path_rev(zcRequest::readGet('filter_id')) : (zcRequest::readGet('cPath', '') != '' ? zen_get_generated_category_path_rev(zcRequest::readGet('cPath')) : zen_get_generated_category_path_rev($item ['master_categories_id']))) . '&products_id=' . $item ['products_id']) . '">' . zen_image(DIR_WS_IMAGES . $item ['products_image'], $item ['products_name'], IMAGE_PRODUCT_LISTING_WIDTH, IMAGE_PRODUCT_LISTING_HEIGHT, 'class="listingProductImage"') . '</a>';
                }
              }
              break;
          }

          $row [] = array(
              'value' => $lc_text,
              'col_params' => $parameters ['col_params']
          );
        }

        $listBoxContents [] = $row;
      }
    }
    $showSubmit = zen_run_normal();
    if ((($countQuantityBoxItems > 0 and $showSubmit == true and count($listBoxContents) > 0) and (PRODUCT_LISTING_MULTIPLE_ADD_TO_CART == 1 or PRODUCT_LISTING_MULTIPLE_ADD_TO_CART == 3))) {
      $showTopSubmit = true;
    } else {
      $showTopSubmit = false;
    }
    if ((($countQuantityBoxItems > 0 and $showSubmit == true and count($listBoxContents) > 0) and (PRODUCT_LISTING_MULTIPLE_ADD_TO_CART >= 2))) {
      $showBottomSubmit = true;
    } else {
      $showBottomSubmit = false;
    }
    $showForm = ($showTopSubmit || $showBottomSubmit);
    $listingbox->setTemplateVariable('showTopSubmit', $showTopSubmit);
    $listingbox->setTemplateVariable('showBottomSubmit', $showBottomSubmit);
    $listingbox->setTemplateVariable('showForm', $showForm);
    $listingbox->setTemplateVariable('headers', $header);
    $this->notify('NOTIFY_LISTING_BOX_FORMATTER_TABULAR_FORMAT_END', NULL, $listBoxContents);
    return $listBoxContents;
  }
  public function getDefaultTemplate()
  {
    global $template;
    return ($this->mainTemplate = $template->get_template_dir('tpl_listingbox_tabular_default.php', DIR_WS_TEMPLATE, $current_page_base, 'listingboxes') . '/tpl_listingbox_tabular_default.php');
  }
}
