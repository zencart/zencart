<?php
/**
 * product_listing module for v1.5.7/1.5.8
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Dec 26  Modified in v1.5.8 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

$row = 0;
$col = 0;
$list_box_contents = [];
$title = '';
$show_top_submit_button = false;
$show_bottom_submit_button = false;
$error_categories = false;

$show_submit = zen_run_normal();

$columns_per_row = defined('PRODUCT_LISTING_COLUMNS_PER_ROW') ? (int)PRODUCT_LISTING_COLUMNS_PER_ROW : 1;
$product_listing_layout_style = $columns_per_row > 1 ? 'columns' : 'rows';
if (empty($columns_per_row)) $product_listing_layout_style = 'fluid';
if ($columns_per_row === 'fluid') $product_listing_layout_style = 'fluid';

$max_results = (int)MAX_DISPLAY_PRODUCTS_LISTING;
if ($product_listing_layout_style == 'columns' && $columns_per_row > 1) {
    $max_results = ($columns_per_row * (int)($max_results/$columns_per_row));
}
if ($max_results < 1) $max_results = 1;

$listing_split = new splitPageResults($listing_sql, $max_results, 'p.products_id', 'page');
$zco_notifier->notify('NOTIFY_MODULE_PRODUCT_LISTING_RESULTCOUNT', $listing_split->number_of_rows);

// counter for how many items on the page can use add-to-cart, so we can decide what kinds of submit-buttons to offer in the template
$how_many = 0;

// Begin Row Headings
if ($product_listing_layout_style == 'rows') {
    $list_box_contents[0] = array('params' => 'class="productListing-rowheading"');

    $zc_col_count_description = 0;
    for ($col = 0, $n = count($column_list); $col < $n; $col++) {
        $lc_align = '';
        $lc_text = '';
        switch ($column_list[$col]) {
            case 'PRODUCT_LIST_MODEL':
                $lc_text = TABLE_HEADING_MODEL;
                $lc_align = '';
                $zc_col_count_description++;
                break;
            case 'PRODUCT_LIST_NAME':
                $lc_text = TABLE_HEADING_PRODUCTS;
                $lc_align = '';
                $zc_col_count_description++;
                break;
            case 'PRODUCT_LIST_MANUFACTURER':
                $lc_text = TABLE_HEADING_MANUFACTURER;
                $lc_align = '';
                $zc_col_count_description++;
                break;
            case 'PRODUCT_LIST_PRICE':
                $lc_text = TABLE_HEADING_PRICE;
                $lc_align = 'right' . (PRODUCTS_LIST_PRICE_WIDTH > 0 ? '" width="' . PRODUCTS_LIST_PRICE_WIDTH : '');
                $zc_col_count_description++;
                break;
            case 'PRODUCT_LIST_QUANTITY':
                $lc_text = TABLE_HEADING_QUANTITY;
                $lc_align = 'right';
                $zc_col_count_description++;
                break;
            case 'PRODUCT_LIST_WEIGHT':
                $lc_text = TABLE_HEADING_WEIGHT;
                $lc_align = 'right';
                $zc_col_count_description++;
                break;
            case 'PRODUCT_LIST_IMAGE':
                $lc_text = '&nbsp;';
//                $lc_text = TABLE_HEADING_IMAGE;   //-Uncomment this line if you want the "Products Image" header title
                $lc_align = 'center';
                $zc_col_count_description++;
                break;
        }

        // Add clickable "sort" links to column headings
        if ($column_list[$col] != 'PRODUCT_LIST_IMAGE') {
            $lc_text = zen_create_sort_heading(isset($_GET['sort']) ? $_GET['sort'] : '', $col+1, $lc_text);
        }


        $list_box_contents[0][$col] = [
            'align' => $lc_align,
            'params' => 'class="productListing-heading"',
            'text' => $lc_text,
        ];
    }
}


// Build row/cell content

$num_products_count = $listing_split->number_of_rows;

$rows = 0;
$column = 0;
$extra_row = 0;

if ($num_products_count > 0) {
  if ($product_listing_layout_style == 'columns') {
    if ($num_products_count < $columns_per_row || $columns_per_row == 0 ) {
      $col_width = floor(100/$num_products_count) - 0.5;
    } else {
      $col_width = floor(100/$columns_per_row) - 0.5;
    }
  }
  $listing = $db->Execute($listing_split->sql_query);
  foreach ($listing as $record) {
    if ($product_listing_layout_style == 'rows') {
        $rows++;
        // handle even/odd striping if not set already with CSS
        $list_box_contents[$rows] = ['params' => 'class="productListing-' . ((($rows - $extra_row) % 2 == 0) ? 'even' : 'odd') . '"'];
        $cur_row = count($list_box_contents) - 1;
    }

    // set css classes for "row" wrapper, to allow for fluid grouping of cells based on viewport
    if ($product_listing_layout_style == 'fluid') {
        $grid_cards_classes = 'row-cols-1 row-cols-md-2 row-cols-lg-2 row-cols-xl-3';
        $grid_classes_matrix = [
            '10' => 'row-cols-1 row-cols-md-2 row-cols-lg-4 row-cols-xl-5',
            '8' => 'row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4',
            '6' => 'row-cols-1 row-cols-md-2 row-cols-lg-2 row-cols-xl-3',
        ];
        if (isset($center_column_width)) {
            foreach($grid_classes_matrix as $width => $classes) {
                if ($center_column_width >= $width) {
                    $grid_cards_classes = $classes;
                    break;
                }
            }
        }
        $list_box_contents[$rows]['params'] = 'class="row ' . $grid_cards_classes . ' text-center"';
    }

    $product_contents = [];

    $linkCpath = $record['master_categories_id'];
    if (!empty($_GET['cPath'])) $linkCpath = $_GET['cPath'];
    if (!empty($_GET['manufacturers_id']) && !empty($_GET['filter_id'])) $linkCpath = $_GET['filter_id'];

    for ($col=0, $n=count($column_list); $col<$n; $col++) {
      $lc_align = '';
      $lc_text = '';
      switch ($column_list[$col]) {
        case 'PRODUCT_LIST_MODEL':
        $lc_align = 'center';
        if ($product_listing_layout_style == 'rows') $lc_align = '';
        $lc_text = '';
        //if ($product_listing_layout_style == 'columns') $lc_text .= '<label>' . TABLE_HEADING_MODEL . '</label>';
        $lc_text .= $record['products_model'];
        break;
        case 'PRODUCT_LIST_NAME':
        $lc_align = 'center';
        if ($product_listing_layout_style == 'rows') $lc_align = '';
        $lc_text = '<h3 class="itemTitle">
            <a href="' . zen_href_link(zen_get_info_page($record['products_id']), 'cPath=' . zen_get_generated_category_path_rev($linkCpath) . '&products_id=' . $record['products_id']) . '">' . $record['products_name'] . '</a>
            </h3>';
        if ((int)PRODUCT_LIST_DESCRIPTION > 0) {
            $lc_text .= '
            <div class="listingDescription">' . zen_trunc_string(zen_clean_html(stripslashes(zen_get_products_description($record['products_id'], $_SESSION['languages_id']))), PRODUCT_LIST_DESCRIPTION) . '</div>';
        }
        break;
        case 'PRODUCT_LIST_MANUFACTURER':
        $lc_align = 'center';
        if ($product_listing_layout_style == 'rows') $lc_align = '';
        $lc_text = '';
        //if ($product_listing_layout_style == 'columns') $lc_text .= '<label>' . TABLE_HEADING_MANUFACTURER . '</label>';
            $lc_text .= '<a href="' . zen_href_link(FILENAME_DEFAULT, 'manufacturers_id=' . $record['manufacturers_id']) . '">' . $record['manufacturers_name'] . '</a>';
        break;
        case 'PRODUCT_LIST_PRICE':
        $lc_price = zen_get_products_display_price($record['products_id']) . '<br>';
        $lc_align = 'center';
        if ($product_listing_layout_style == 'rows') $lc_align = 'right';
        $lc_text = '';
        // if ($product_listing_layout_style == 'columns') $lc_text .= '<label>' . TABLE_HEADING_PRICE . '</label>';
        $lc_text .=  $lc_price;

        // more info in place of buy now
        $lc_button = '';
        if (zen_requires_attribute_selection($record['products_id']) || PRODUCT_LIST_PRICE_BUY_NOW == '0') {
          $lc_button = '<a class="list-more" href="' . zen_href_link(zen_get_info_page($record['products_id']), 'cPath=' . zen_get_generated_category_path_rev($linkCpath) . '&products_id=' . $record['products_id']) . '">' . MORE_INFO_TEXT . '</a>';
        } else {
          if (PRODUCT_LISTING_MULTIPLE_ADD_TO_CART != 0) {
            if (
                // not a hide qty box product
                $record['products_qty_box_status'] != 0 &&
                // product type can be added to cart
                zen_get_products_allow_add_to_cart($record['products_id']) != 'N'
                &&
                // product is not call for price
                $record['product_is_call'] == 0
                &&
                // product is in stock or customers may add it to cart anyway
                ($record['products_quantity'] > 0 || SHOW_PRODUCTS_SOLD_OUT_IMAGE == 0)
            ) {
              $how_many++;
            }
            // hide quantity box
            if ($record['products_qty_box_status'] == 0) {
              $lc_button = '<a href="' . zen_href_link($_GET['main_page'], zen_get_all_get_params(array('action')) . 'action=buy_now&products_id=' . $record['products_id']) . '">' . zen_image_button(BUTTON_IMAGE_BUY_NOW, BUTTON_BUY_NOW_ALT, 'class="listingBuyNowButton"') . '</a>';
            } else {
              $lc_button = '';
              $lc_button .= TEXT_PRODUCT_LISTING_MULTIPLE_ADD_TO_CART;
              $lc_button .= '<input type="text" name="products_id[' . $record['products_id'] . ']" value="0" size="4" aria-label="' . ARIA_QTY_ADD_TO_CART . '">';
            }
          } else {
// qty box with add to cart button
            if (PRODUCT_LIST_PRICE_BUY_NOW == '2' && $record['products_qty_box_status'] != 0) {
              $lc_button= zen_draw_form('cart_quantity', zen_href_link($_GET['main_page'], zen_get_all_get_params(array('action')) . 'action=add_product&products_id=' . $record['products_id']), 'post', 'enctype="multipart/form-data"') . '<input type="text" name="cart_quantity" value="' . (zen_get_buy_now_qty($record['products_id'])) . '" maxlength="6" size="4" aria-label="' . ARIA_QTY_ADD_TO_CART . '"><br>' . zen_draw_hidden_field('products_id', $record['products_id']) . zen_image_submit(BUTTON_IMAGE_IN_CART, BUTTON_IN_CART_ALT) . '</form>';
            } else {
              $lc_button = '<a href="' . zen_href_link($_GET['main_page'], zen_get_all_get_params(array('action')) . 'action=buy_now&products_id=' . $record['products_id']) . '">' . zen_image_button(BUTTON_IMAGE_BUY_NOW, BUTTON_BUY_NOW_ALT, 'class="listingBuyNowButton"') . '</a>';
            }
          }
        }

        $zco_notifier->notify('NOTIFY_MODULES_PRODUCT_LISTING_PRODUCTS_BUTTON', array(), $record, $lc_button);

        $the_button = $lc_button;
        $products_link = '<a class="" href="' . zen_href_link(zen_get_info_page($record['products_id']), 'cPath=' . zen_get_generated_category_path_rev($linkCpath) . '&products_id=' . $record['products_id']) . '">' . MORE_INFO_TEXT . '</a>';
        $lc_text .= '<br>' . zen_get_buy_now_button($record['products_id'], $the_button, $products_link) . '<br>' . zen_get_products_quantity_min_units_display($record['products_id']);
        $lc_text .= '<br>' . (zen_get_show_product_switch($record['products_id'], 'ALWAYS_FREE_SHIPPING_IMAGE_SWITCH') ? (zen_get_product_is_always_free_shipping($record['products_id']) ? TEXT_PRODUCT_FREE_SHIPPING_ICON . '<br>' : '') : '');

        break;
        case 'PRODUCT_LIST_QUANTITY':
        $lc_align = 'center';
        if ($product_listing_layout_style == 'rows') $lc_align = 'right';
        $lc_text = '';
        //if ($product_listing_layout_style == 'columns') $lc_text .= '<label>' . TABLE_HEADING_QUANTITY . '</label>';
        $lc_text .= $record['products_quantity'];
        break;
        case 'PRODUCT_LIST_WEIGHT':
        $lc_align = 'center';
        if ($product_listing_layout_style == 'rows') $lc_align = 'right';
        $lc_text = '';
        //if ($product_listing_layout_style == 'columns') $lc_text .= '<label>' . TABLE_HEADING_WEIGHT . '</label>';
        $lc_text .= $record['products_weight'];
        break;
        case 'PRODUCT_LIST_IMAGE':
        $lc_align = 'center';
        if (!empty($record['products_image']) || PRODUCTS_IMAGE_NO_IMAGE_STATUS > 0) {
          $lc_text = '<a href="' . zen_href_link(zen_get_info_page($record['products_id']), 'cPath=' . zen_get_generated_category_path_rev($linkCpath) . '&products_id=' . $record['products_id']) . '">' . zen_image(DIR_WS_IMAGES . $record['products_image'], $record['products_name'], IMAGE_PRODUCT_LISTING_WIDTH, IMAGE_PRODUCT_LISTING_HEIGHT, 'class="listingProductImage"') . '</a>';
        }
        break;
      }

      $product_contents[] = $lc_text; // (used in column mode)

      if ($product_listing_layout_style == 'rows') {
        $list_box_contents[$rows][] = [
            'align' => $lc_align,
            'params' => 'class="productListing-data"',
            'text'  => $lc_text,
        ];
//        // add description and match alternating colors
//        if (PRODUCT_LIST_DESCRIPTION > 0) {
//          $rows++;
//          if ($extra_row == 1) {
//            $list_box_description = "productListing-data-description-even";
//            $extra_row=0;
//          } else {
//            $list_box_description = "productListing-data-description-odd";
//            $extra_row=1;
//          }
//          $list_box_contents[$rows][] = array('params' => 'class="' . $list_box_description . '" colspan="' . $zc_col_count_description . '"',
//                                              'text' => zen_trunc_string(zen_clean_html(stripslashes(zen_get_products_description($record['products_id'], $_SESSION['languages_id']))), PRODUCT_LIST_DESCRIPTION));
//        }
      }
    }

    if ($product_listing_layout_style == 'columns' || $product_listing_layout_style == 'fluid') {
      $lc_text = implode('<br>', $product_contents);
      $style = '';
      if ($product_listing_layout_style == 'columns') {
          $style = ' style="width:' . $col_width . '%;"';
      }
      $list_box_contents[$rows][] = [
          'params' => 'class="centerBoxContentsProducts centeredContent back gridlayout"' . $style,
          'text'  => $lc_text,
          'wrap_with_classes' => '',
          'card_type' => $product_listing_layout_style,
      ];
      if ($product_listing_layout_style == 'columns') {
          $column ++;
          if ($column >= $columns_per_row) {
              $column = 0;
              $rows ++;
          }
      }
    }
  }
} else {

  $list_box_contents = [];
  $list_box_contents[0][] = [
      'params' => 'class="productListing-data"',
      'text' => TEXT_NO_PRODUCTS,
  ];
  $error_categories = true;
}

if (($how_many > 0 && $show_submit == true && $num_products_count > 0) && (PRODUCT_LISTING_MULTIPLE_ADD_TO_CART == 1 || PRODUCT_LISTING_MULTIPLE_ADD_TO_CART == 3) ) {
  $show_top_submit_button = true;
}
if (($how_many > 0 && $show_submit == true && $num_products_count > 0) && (PRODUCT_LISTING_MULTIPLE_ADD_TO_CART >= 2) ) {
  $show_bottom_submit_button = true;
}

$zco_notifier->notify('NOTIFY_PRODUCT_LISTING_END', $current_page_base, $list_box_contents, $listing_split, $show_top_submit_button, $show_bottom_submit_button, $show_submit, $how_many);

  if ($how_many > 0 && PRODUCT_LISTING_MULTIPLE_ADD_TO_CART != 0 && $show_submit == true && $num_products_count > 0) {
  // bof: multiple products
    echo zen_draw_form('multiple_products_cart_quantity', zen_href_link(FILENAME_DEFAULT, zen_get_all_get_params(array('action')) . 'action=multiple_products_add_product', $request_type), 'post', 'enctype="multipart/form-data"');
  }

