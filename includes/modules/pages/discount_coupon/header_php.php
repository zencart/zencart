<?php
/**
 * discount coupon info
 *
 * @package page
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Drbyte Wed Aug 2 14:55:16 2017 -0400 Modified in v1.5.6 $
 */

  require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));
  $text_coupon_help = '';
  if (isset($_POST['lookup_discount_coupon']) and $_POST['lookup_discount_coupon'] != '') {
// lookup requested discount coupon

    $coupon = $db->Execute("select * from " . TABLE_COUPONS . " where coupon_code = '" . zen_db_input($_POST['lookup_discount_coupon']) . "' and  coupon_type != 'G'");
    $_POST['lookup_discount_coupon'] = zen_sanitize_string($_POST['lookup_discount_coupon']);
    if ($coupon->RecordCount() < 1) {
// invalid discount coupon code
      $text_coupon_help = sprintf(TEXT_COUPON_FAILED, zen_output_string_protected($_POST['lookup_discount_coupon']));
    } else {
      if ($coupon->fields['coupon_active'] == 'N') {
// inactive coupon
        $text_coupon_help = sprintf(TEXT_COUPON_INACTIVE, zen_output_string_protected($_POST['lookup_discount_coupon']));
      } else {
// valid discount coupon code
        $lookup_coupon_id = $coupon->fields['coupon_id'];

        $coupon_desc = $db->Execute("select * from " . TABLE_COUPONS_DESCRIPTION . " where coupon_id = '" . (int)$lookup_coupon_id . "' and language_id = '" . (int)$_SESSION['languages_id'] . "'");
        $text_coupon_help = TEXT_COUPON_HELP_HEADER;
        $text_coupon_help .= sprintf(TEXT_COUPON_HELP_NAME, $coupon_desc->fields['coupon_name']);
        if (zen_not_null($coupon_desc->fields['coupon_description'])) $text_coupon_help .= sprintf(TEXT_COUPON_HELP_DESC, zen_output_string_protected($coupon_desc->fields['coupon_description']));
        $coupon_amount = $coupon->fields['coupon_amount'];
        switch ($coupon->fields['coupon_type']) {
          case 'F': // amount Off
          $text_coupon_help .= sprintf(TEXT_COUPON_HELP_FIXED, $currencies->format($coupon->fields['coupon_amount']));
          break;
          case 'P': // percentage
          $text_coupon_help .= sprintf(TEXT_COUPON_HELP_FIXED, number_format($coupon->fields['coupon_amount'],2). '%');
          break;
          case 'S': // Free Shipping
          $text_coupon_help .= TEXT_COUPON_HELP_FREESHIP;
          break;
          case 'E': // percentage & Free Shipping
          $text_coupon_help .= TEXT_COUPON_HELP_FREESHIP . sprintf(TEXT_COUPON_HELP_FIXED, number_format($coupon->fields['coupon_amount'],2). '%');
          break;
          case 'O': // amount off & Free Shipping
          $text_coupon_help .= TEXT_COUPON_HELP_FREESHIP . sprintf(TEXT_COUPON_HELP_FIXED, $currencies->format($coupon->fields['coupon_amount']));
          break;
          default:
        }
        if ($coupon->fields['coupon_is_valid_for_sales'] == 0 ) $text_coupon_help .= TEXT_NO_PROD_SALES;
        if ($coupon->fields['coupon_minimum_order'] > 0 ) $text_coupon_help .= sprintf(TEXT_COUPON_HELP_MINORDER, $currencies->format($coupon->fields['coupon_minimum_order']));
        $text_coupon_help .= sprintf(TEXT_COUPON_HELP_DATE, zen_date_short($coupon->fields['coupon_start_date']),zen_date_short($coupon->fields['coupon_expire_date']));
        $text_coupon_help .= TEXT_COUPON_HELP_RESTRICT;

        if ($coupon->fields['coupon_zone_restriction'] > 0) {
          $text_coupon_help .= TEXT_COUPON_GV_RESTRICTION_ZONES;
        }

        $text_coupon_help .= TEXT_COUPON_HELP_CATEGORIES;
        $get_result=$db->Execute("select * from " . TABLE_COUPON_RESTRICT . "  where coupon_id='" . (int)$lookup_coupon_id . "' and category_id !='0'");
        $cats = array();
        if ($get_result->RecordCount() == 1 && $get_result->fields['category_id'] == '-1') {
          $cats[] = array("link" => TEXT_NO_CAT_TOP_ONLY_DENY);
        } else {
          $skip_cat_restriction = true;
          while (!$get_result->EOF) {
            if ($get_result->fields['coupon_restrict'] == 'N') {
              $restrict = TEXT_CAT_ALLOWED;
            } else {
              $restrict = TEXT_CAT_DENIED;
            }
            if ($get_result->RecordCount() >= 1 and $get_result->fields['category_id'] != '-1') {
              $result = $db->Execute("SELECT * FROM " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd WHERE c.categories_id = cd.categories_id
              and cd.language_id = '" . (int)$_SESSION['languages_id'] . "' and c.categories_id='" . $get_result->fields['category_id'] . "'");
              $cats[] = array("validity"=> ($get_result->fields['coupon_restrict'] =='N' ? 'A' : 'D'), 'name'=> $result->fields["categories_name"], 'link'=>'<a href="' . zen_href_link(FILENAME_DEFAULT, 'cPath=' . (int)$result->fields['categories_id']) . '">' . $result->fields["categories_name"] . '</a>' . $restrict);
            }
            $get_result->MoveNext();
          }
    //echo 'CAT SIZE: ' . sizeof($cats) . ' - ' . ' $restrict: ' . $restrict . '<br>';

          if ($skip_cat_restriction == false || sizeof($cats) == 0) $cats[] = array("link" => TEXT_NO_CAT_RESTRICTIONS);
        }
        sort($cats);
        $mycats = array();
        foreach($cats as $key=>$value) {
          $mycats[] = $value["link"];
        }
        $cats = '<ul id="couponCatRestrictions">' . '<li>' . implode('<li>', $mycats) . '</ul>';
        $text_coupon_help .= $cats;

        $text_coupon_help .= TEXT_COUPON_HELP_PRODUCTS;
        $get_result=$db->Execute("select * from " . TABLE_COUPON_RESTRICT . "  where coupon_id='" . (int)$lookup_coupon_id . "' and product_id !='0'");
        $prods = array();
        while (!$get_result->EOF) {
          if ($get_result->fields['coupon_restrict'] == 'N') {
            $restrict = TEXT_PROD_ALLOWED;
          } else {
            $restrict = TEXT_PROD_DENIED;
          }
          $result = $db->Execute("SELECT * FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd WHERE p.products_id = pd.products_id and
          pd.language_id = '" . (int)$_SESSION['languages_id'] . "' and p.products_id = '" . $get_result->fields['product_id'] . "'");
          $prods[] = array("validity" => ($get_result->fields['coupon_restrict'] =='N' ? 'A' : 'D'), 'name'=> $result->fields["products_name"], 'link'=> '<a href="' . zen_href_link(zen_get_info_page($result->fields['products_id']), 'cPath=' . zen_get_generated_category_path_rev($result->fields['master_categories_id']) . '&products_id=' . $result->fields['products_id']) . '">' . $result->fields['products_name'] . '</a>' . $restrict);
          $get_result->MoveNext();
        }

        if (sizeof($prods) == 0) $prods[] = array("link"=>TEXT_NO_PROD_RESTRICTIONS);

        sort($prods);
        $myprods = array();
        foreach($prods as $key=>$value) {
          $myprods[] = $value["link"];
        }
        $prods = '<ul id="couponProdRestrictions">' . '<li>' . implode('<li>', $myprods) . '</ul>';
        $text_coupon_help .= $prods . TEXT_COUPON_GV_RESTRICTION;
      } // coupon inactive
    }
  }

// include template specific file name defines
$define_page = zen_get_file_directory(DIR_WS_LANGUAGES . $_SESSION['language'] . '/html_includes/', FILENAME_DEFINE_DISCOUNT_COUPON, 'false');
$breadcrumb->add(NAVBAR_TITLE);
