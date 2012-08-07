<?php
/**
 * @package admin
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: collect_info_metatags.php 19330 2011-08-07 06:32:56Z drbyte $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

    $parameters = array(
                       'products_name' => '',
                       'products_model' => '',
                       'metatags_title_status' => '',
                       'metatags_products_name_status' => '',
                       'metatags_model_status' => '',
                       'products_id' => '',
                       'metatags_price_status' => '',
                       'metatags_title_tagline_status' => '',
                       'metatags_title' => '',
                       'metatags_keywords' => '',
                       'metatags_description' => ''
                       );

    $pInfo = new objectInfo($parameters);

    if (isset($_GET['pID']) && empty($_POST)) {
// check if new meta tags or existing
    $check_meta_tags_description = $db->Execute("select products_id from " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . " where products_id='" . (int)$_GET['pID'] . "'");
    if ($check_meta_tags_description->RecordCount() <= 0) {
      $product = $db->Execute("select pd.products_name, p.products_model, p.products_price_sorter,
                                      p.metatags_title_status, p.metatags_products_name_status, p.metatags_model_status,
                                      p.products_id, p.metatags_price_status, p.metatags_title_tagline_status
                              from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                              where p.products_id = '" . (int)$_GET['pID'] . "'
                              and p.products_id = pd.products_id
                              and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'");
    } else {
      $product = $db->Execute("select pd.products_name, p.products_model, p.products_price_sorter,
                                      p.metatags_title_status, p.metatags_products_name_status, p.metatags_model_status,
                                      p.products_id, p.metatags_price_status, p.metatags_title_tagline_status,
                                      mtpd.metatags_title, mtpd.metatags_keywords, mtpd.metatags_description
                              from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . " mtpd
                              where p.products_id = '" . (int)$_GET['pID'] . "'
                              and p.products_id = pd.products_id
                              and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                              and p.products_id = mtpd.products_id
                              and mtpd.language_id = '" . (int)$_SESSION['languages_id'] . "'");
    }

      $pInfo->objectInfo($product->fields);
    } elseif (zen_not_null($_POST)) {
      $pInfo->objectInfo($_POST);
      $metatags_title = $_POST['metatags_title'];
      $metatags_keywords = $_POST['metatags_keywords'];
      $metatags_description = $_POST['metatags_description'];
    }

    $languages = zen_get_languages();

// metatags_products_name_status shows
    if (empty($pInfo->metatags_keywords) and empty($pInfo->metatags_description)) $pInfo->metatags_products_name_status = zen_get_show_product_switch($_GET['pID'], 'metatags_products_name_status');
    switch ($pInfo->metatags_products_name_status) {
      case '0': $is_metatags_products_name_status = false; $not_metatags_products_name_status = true; break;
      case '1': $is_metatags_products_name_status = true;  $not_metatags_products_name_status = false; break;
      default:  $is_metatags_products_name_status = true;  $not_metatags_products_name_status = false;
    }
// metatags_title_status shows
    if (empty($pInfo->metatags_keywords) and empty($pInfo->metatags_description)) $pInfo->metatags_title_status = zen_get_show_product_switch($_GET['pID'], 'metatags_title_status');
    switch ($pInfo->metatags_title_status) {
      case '0': $is_metatags_title_status = false; $not_metatags_title_status = true; break;
      case '1': $is_metatags_title_status = true;  $not_metatags_title_status = false; break;
      default:  $is_metatags_title_status = true;  $not_metatags_title_status = false;
    }
// metatags_model_status shows
    if (empty($pInfo->metatags_keywords) and empty($pInfo->metatags_description)) $pInfo->metatags_model_status = zen_get_show_product_switch($_GET['pID'], 'metatags_model_status');
    switch ($pInfo->metatags_model_status) {
      case '0': $is_metatags_model_status = false; $not_metatags_model_status = true; break;
      case '1': $is_metatags_model_status = true;  $not_metatags_model_status = false; break;
      default:  $is_metatags_model_status = true;  $not_metatags_model_status = false;
    }
// metatags_price_status shows
    if (empty($pInfo->metatags_keywords) and empty($pInfo->metatags_description)) $pInfo->metatags_price_status = zen_get_show_product_switch($_GET['pID'], 'metatags_price_status');
    switch ($pInfo->metatags_price_status) {
      case '0': $is_metatags_price_status = false; $not_metatags_price_status = true; break;
      case '1': $is_metatags_price_status = true;  $not_metatags_price_status = false; break;
      default:  $is_metatags_price_status = true;  $not_metatags_price_status = false;
    }
// metatags_title_tagline_status shows TITLE and TAGLINE in metatags_header.php
    if (empty($pInfo->metatags_keywords) and empty($pInfo->metatags_description)) $pInfo->metatags_title_tagline_status = zen_get_show_product_switch($_GET['pID'], 'metatags_title_tagline_status');
    switch ($pInfo->metatags_title_tagline_status) {
      case '0': $is_metatags_title_tagline_status = false; $not_metatags_title_tagline_status = true; break;
      case '1': $is_metatags_title_tagline_status = true;  $not_metatags_title_tagline_status = false; break;
      default:  $is_metatags_title_tagline_status = true;  $not_metatags_title_tagline_status = false;
    }
?>
    <?php
//  echo $type_admin_handler;
echo zen_draw_form('new_product_meta_tags', $type_admin_handler , 'cPath=' . $cPath . (isset($_GET['product_type']) ? '&product_type=' . $_GET['product_type'] : '') . (isset($_GET['pID']) ? '&pID=' . $_GET['pID'] : '') . '&action=new_product_preview_meta_tags' . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''), 'post', 'enctype="multipart/form-data"'); ?>
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo sprintf(TEXT_NEW_PRODUCT, zen_output_generated_category_path($current_category_id)); ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main" colspan="2">
          <table border="1" cellspacing="0" cellpadding="2">
            <tr>
              <td class="main" colspan="3" align="center"><?php echo TEXT_META_TAG_TITLE_INCLUDES; ?></td>
            </tr>
            <tr>
              <td class="main" align="center">
                <?php echo TEXT_PRODUCTS_METATAGS_PRODUCTS_NAME_STATUS . '<br />' . zen_draw_radio_field('metatags_products_name_status', '1', $is_metatags_products_name_status) . '&nbsp;' . TEXT_YES . '&nbsp;' . zen_draw_radio_field('metatags_products_name_status', '0', $not_metatags_products_name_status) . '&nbsp;' . TEXT_NO; ?>
              </td>
              <td class="main" align="center">
                <?php echo TEXT_PRODUCTS_METATAGS_TITLE_STATUS . '<br />' . zen_draw_radio_field('metatags_title_status', '1', $is_metatags_title_status) . '&nbsp;' . TEXT_YES . '&nbsp;' . zen_draw_radio_field('metatags_title_status', '0', $not_metatags_title_status) . '&nbsp;' . TEXT_NO; ?>
              </td>
<!-- // not used for documents
              <td class="main" align="center">
                <?php echo TEXT_PRODUCTS_METATAGS_MODEL_STATUS . '<br />' . zen_draw_radio_field('metatags_model_status', '1', $is_metatags_model_status) . '&nbsp;' . TEXT_YES . '&nbsp;' . zen_draw_radio_field('metatags_model_status', '0', $not_metatags_model_status) . '&nbsp;' . TEXT_NO; ?>
              </td>
              <td class="main" align="center">
                <?php echo TEXT_PRODUCTS_METATAGS_PRICE_STATUS . '<br />' . zen_draw_radio_field('metatags_price_status', '1', $is_metatags_price_status) . '&nbsp;' . TEXT_YES . '&nbsp;' . zen_draw_radio_field('metatags_price_status', '0', $not_metatags_price_status) . '&nbsp;' . TEXT_NO; ?>
              </td>
-->
              <td class="main" align="center">
                <?php echo TEXT_PRODUCTS_METATAGS_TITLE_TAGLINE_STATUS . '<br />' . zen_draw_radio_field('metatags_title_tagline_status', '1', $is_metatags_title_tagline_status) . '&nbsp;' . TEXT_YES . '&nbsp;' . zen_draw_radio_field('metatags_title_tagline_status', '0', $not_metatags_title_tagline_status) . '&nbsp;' . TEXT_NO; ?>
              </td>
            </tr>
          </table>
        </td>
      </tr>
<?php
    for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
      <tr>
        <td><table border="3" cellspacing="4" cellpadding="6">
          <tr>
<!-- // not used for documents
            <td class="main" colspan="2">
              <?php echo zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . '<strong>' . TEXT_DOCUMENT_NAME . '</strong>' . '&nbsp;' . zen_get_products_name($_GET['pID'], $languages[$i]['id']) . '&nbsp;&nbsp;&nbsp;<strong>' . TEXT_PRODUCTS_MODEL . '</strong>&nbsp;' . $pInfo->products_model . '&nbsp;&nbsp;&nbsp;<strong>' . TEXT_PRODUCTS_PRICE_INFO . '</strong>&nbsp;' . $currencies->format($pInfo->products_price_sorter); ?>
            </td>
-->
            <td class="main" colspan="2">
              <?php echo zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . '<strong>' . TEXT_DOCUMENT_NAME . '</strong>' . '&nbsp;' . zen_get_products_name($_GET['pID'], $languages[$i]['id']); ?>
            </td>

          </tr>
          <tr>
            <td class="main"valign="top"><?php echo TEXT_META_TAGS_TITLE; ?>&nbsp;</td>
            <td class="main">
              <?php echo zen_draw_input_field('metatags_title[' . $languages[$i]['id'] . ']', htmlspecialchars(isset($metatags_title[$languages[$i]['id']]) ? stripslashes($metatags_title[$languages[$i]['id']]) : zen_get_metatags_title($pInfo->products_id, $languages[$i]['id']), ENT_COMPAT, CHARSET, FALSE), zen_set_field_length(TABLE_META_TAGS_PRODUCTS_DESCRIPTION, 'metatags_title', '150', false)); //,'id="'.'metatags_title' . $languages[$i]['id'] . '"');?>
            </td>
          </tr>
          <tr>
            <td class="main"valign="top"><?php echo TEXT_META_TAGS_KEYWORDS; ?>&nbsp;</td>
            <td class="main">
              <?php echo zen_draw_textarea_field('metatags_keywords[' . $languages[$i]['id'] . ']', 'soft', '100%', '10', htmlspecialchars((isset($metatags_keywords[$languages[$i]['id']])) ? stripslashes($metatags_keywords[$languages[$i]['id']]) : zen_get_metatags_keywords($pInfo->products_id, $languages[$i]['id']), ENT_COMPAT, CHARSET, FALSE)); //,'id="'.'metatags_keywords' . $languages[$i]['id'] . '"'); ?>
            </td>
          </tr>
          <tr>
            <td class="main"valign="top"><?php echo TEXT_META_TAGS_DESCRIPTION; ?>&nbsp;</td>
            <td class="main">
              <?php echo zen_draw_textarea_field('metatags_description[' . $languages[$i]['id'] . ']', 'soft', '100%', '10', htmlspecialchars((isset($metatags_description[$languages[$i]['id']])) ? stripslashes($metatags_description[$languages[$i]['id']]) : zen_get_metatags_description($pInfo->products_id, $languages[$i]['id']), ENT_COMPAT, CHARSET, FALSE)); //,'id="'.'metatags_description' . $languages[$i]['id'] . '"'); ?>
            </td>
          </tr>
        </table></td>
      </tr>
<?php
    }
?>
      <tr>
        <td class="main" align="left"><?php echo TEXT_INFO_META_TAGS_USAGE; ?></td>
      </tr>
      <tr>
        <td class="main" align="right"><?php echo zen_draw_hidden_field('products_model', $pInfo->products_model) . zen_draw_hidden_field('products_price_sorter', $pInfo->products_price_sorter) . zen_image_submit('button_preview.gif', IMAGE_PREVIEW) . '&nbsp;&nbsp;<a href="' . zen_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . (isset($_GET['pID']) ? '&pID=' . $_GET['pID'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
      </tr>
    </table></form>
