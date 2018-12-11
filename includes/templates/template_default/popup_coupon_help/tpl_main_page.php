<?php
/**
 * Override Template for common/tpl_main_page.php
 *
 * @package templateSystem
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Drbyte Wed Aug 2 14:55:16 2017 -0400 Modified in v1.5.6 $
 */
?>
<body id="popupCouponHelp" onload="resize();">

<?php
  $sql = "select * from " . TABLE_COUPONS . " where coupon_id = :couponID:";
  $sql = $db->bindVars($sql, ':couponID:', $_GET['cID'], 'integer');
  $coupon = $db->Execute($sql);
  $sql = "select * from " . TABLE_COUPONS_DESCRIPTION . " where coupon_id = :couponID: and language_id = :langID:";
  $sql = $db->bindVars($sql, ':couponID:', $_GET['cID'], 'integer');
  $sql = $db->bindVars($sql, ':langID:', $_SESSION['languages_id'], 'integer');
  $coupon_desc = $db->Execute($sql);

  $text_coupon_help = TEXT_COUPON_HELP_HEADER;
  $text_coupon_help .= sprintf(TEXT_COUPON_HELP_NAME, $coupon_desc->fields['coupon_name']);
  if (zen_not_null($coupon_desc->fields['coupon_description'])) $text_coupon_help .= sprintf(TEXT_COUPON_HELP_DESC, $coupon_desc->fields['coupon_description']);
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
  $text_coupon_help .= '<strong>' . TEXT_COUPON_HELP_RESTRICT . '</strong>';

  if ($coupon->fields['coupon_zone_restriction'] > 0) {
    $text_coupon_help .= '<br /><br />' . TEXT_COUPON_GV_RESTRICTION_ZONES;
  }

  $text_coupon_help .= '<br /><br />' .  TEXT_COUPON_HELP_CATEGORIES;
  $sql = "select * from " . TABLE_COUPON_RESTRICT . "  where coupon_id=:couponID: and category_id !='0'";
  $sql = $db->bindVars($sql, ':couponID:', $_GET['cID'], 'integer');
  $get_result=$db->Execute($sql);

  $cats = array();
  if ($get_result->RecordCount() == 1 && $get_result->fields['category_id'] == '-1') {
    $cats[] = array("name" => TEXT_NO_CAT_TOP_ONLY_DENY);
  } else {
    $skip_cat_restriction = true;
    while (!$get_result->EOF) {
      if ($get_result->fields['coupon_restrict'] == 'N') {
        $restrict = TEXT_ALLOWED;
      } else {
        $restrict = TEXT_DENIED;
      }
      if ($get_result->RecordCount() >= 1 and $get_result->fields['category_id'] != '-1') {
        $result = $db->Execute("SELECT * FROM " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd WHERE c.categories_id = cd.categories_id
        and cd.language_id = '" . (int)$_SESSION['languages_id'] . "' and c.categories_id='" . $get_result->fields['category_id'] . "'");
        $cats[] = array("validity"=> ($get_result->fields['coupon_restrict'] =='N' ? 'A' : 'D'), 'name'=> $result->fields["categories_name"] . $restrict, 'link'=>'<a href="' . zen_href_link(FILENAME_DEFAULT, 'cPath=' . (int)$result->fields['categories_id']) . '">' . $result->fields["categories_name"] . '</a>' . $restrict);
      }
      $get_result->MoveNext();
    }

    if ($skip_cat_restriction == false || sizeof($cats) == 0) $cats[] = array("name" => TEXT_NO_CAT_RESTRICTIONS);
  }
  sort($cats);
  $mycats = array();
  foreach($cats as $key=>$value) {
    $mycats[] = $value["name"];
  }
  $cats = '<ul id="couponCatRestrictions">' . '<li>' . implode('</li><li>', $mycats) . '</li></ul>';
  $text_coupon_help .= $cats;

  $text_coupon_help .= TEXT_COUPON_HELP_PRODUCTS;
  $sql = "select * from " . TABLE_COUPON_RESTRICT . "  where coupon_id=:couponID: and product_id !='0'";
  $sql = $db->bindVars($sql, ':couponID:', $_GET['cID'], 'integer');
  $get_result=$db->Execute($sql);
  $prods = array();
  while (!$get_result->EOF) {
    if ($get_result->fields['coupon_restrict'] == 'N') {
      $restrict = TEXT_ALLOWED;
    } else {
      $restrict = TEXT_DENIED;
    }
    $result = $db->Execute("SELECT * FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd WHERE p.products_id = pd.products_id and
    pd.language_id = '" . (int)$_SESSION['languages_id'] . "' and p.products_id = '" . $get_result->fields['product_id'] . "'");
    $prods[] = array("validity" => ($get_result->fields['coupon_restrict'] =='N' ? 'A' : 'D'), 'name'=> $result->fields["products_name"] . $restrict, 'link'=> '<a href="' . zen_href_link(zen_get_info_page($result->fields['products_id']), 'cPath=' . zen_get_generated_category_path_rev($result->fields['master_categories_id']) . '&products_id=' . $result->fields['products_id']) . '">' . $result->fields['products_name'] . '</a>' . $restrict);
    $get_result->MoveNext();
  }

  if (sizeof($prods) == 0) $prods[] = array("name"=>TEXT_NO_PROD_RESTRICTIONS);

  sort($prods);
  $myprods = array();
  foreach($prods as $key=>$value) {
    $myprods[] = $value["name"];
  }
  $prods = '<ul id="couponProdRestrictions">' . '<li>' . implode('</li><li>', $myprods) . '</li></ul>';
  $text_coupon_help .= $prods . TEXT_COUPON_GV_RESTRICTION;

  echo $text_coupon_help;

?>
<p class="smallText forward"><?php echo '<a href="javascript:window.close()">' . TEXT_CURRENT_CLOSE_WINDOW . '</a>'; ?></p>

</body>