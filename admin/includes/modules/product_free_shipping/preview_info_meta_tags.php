<?php
/**
 * @package admin
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2019 May 26 Modified in v1.5.6b $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
$languages = zen_get_languages();

if (zen_not_null($_POST)) {
  $pInfo = new objectInfo($_POST);
  $metatags_title = $_POST['metatags_title'];
  $metatags_keywords = $_POST['metatags_keywords'];
  $metatags_description = $_POST['metatags_description'];
} else {
  $product = $db->Execute("SELECT pd.products_name, p.products_model,
                                  p.metatags_title_status, p.metatags_products_name_status, p.metatags_model_status,
                                  p.products_id, p.metatags_price_status, p.metatags_title_tagline_status,
                                  mtpd.metatags_title, mtpd.metatags_keywords, mtpd.metatags_description
                           FROM " . TABLE_PRODUCTS . " p,
                                " . TABLE_PRODUCTS_DESCRIPTION . " pd,
                                " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . " mtpd
                           WHERE p.products_id = " . (int)$_GET['pID'] . "
                           AND p.products_id = pd.products_id
                           AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
                           AND p.products_id = mtpd.products_id
                           AND mtpd.language_id = " . (int)$_SESSION['languages_id']);

  $pInfo = new objectInfo($product->fields);
}

$form_action = (isset($_GET['pID'])) ? 'update_product_meta_tags' : 'insert_product_meta_tags';
?>
<div class="container-fluid">
    <?php
    echo zen_draw_form($form_action, FILENAME_PRODUCT, 'cPath=' . $cPath . (isset($_GET['product_type']) ? '&product_type=' . $_GET['product_type'] : '') . (isset($_GET['pID']) ? '&pID=' . $_GET['pID'] : '') . '&action=' . $form_action . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''), 'post', 'enctype="multipart/form-data" class="form-horizontal"');

    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
      $pInfo->metatags_title = zen_db_prepare_input($metatags_title[$languages[$i]['id']]);
      $pInfo->metatags_keywords = zen_db_prepare_input($metatags_keywords[$languages[$i]['id']]);
      $pInfo->metatags_description = zen_db_prepare_input($metatags_description[$languages[$i]['id']]);
      ?>

    <table class="table table-bordered">
      <tr>
        <td class="main" valign="top">
            <?php echo zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . '<strong>' . TEXT_PRODUCTS_NAME . '</strong>' . '&nbsp;' . ($pInfo->metatags_products_name_status == '1' ? zen_get_products_name($_GET['pID'], $languages[$i]['id']) : TEXT_META_EXCLUDED); ?>
        </td>
        <td class="main" valign="top">
            <?php echo '<strong>' . TEXT_PRODUCTS_MODEL . '</strong>&nbsp;' . ($pInfo->metatags_model_status == '1' ? $pInfo->products_model : TEXT_META_EXCLUDED); ?>
        </td>
        <td class="main" valign="top">
            <?php echo '<strong>' . TEXT_PRODUCTS_PRICE_INFO . '</strong>&nbsp;' . ($pInfo->metatags_price_status == '1' ? $currencies->format($pInfo->products_price_sorter) : TEXT_META_EXCLUDED); ?>
        </td>
        <td class="main" valign="top">
            <?php echo '<strong>' . TEXT_PRODUCTS_METATAGS_TITLE_TAGLINE_STATUS . '</strong>&nbsp;' . ($pInfo->metatags_title_tagline_status == '1' ? TEXT_TITLE_PLUS_TAGLINE : TEXT_META_EXCLUDED); ?>
        </td>
      </tr>
      <tr>
        <td class="main" valign="top"><?php echo TEXT_META_TAGS_TITLE; ?>&nbsp;</td>
        <td class="main" colspan="3"><?php echo ($pInfo->metatags_title_status == '1' ? $pInfo->metatags_title : TEXT_META_EXCLUDED); ?></td>
      </tr>
      <tr>
        <td class="main" valign="top"><?php echo TEXT_META_TAGS_KEYWORDS; ?>&nbsp;</td>
        <td class="main" colspan="3"><?php echo $pInfo->metatags_keywords; ?></td>
      </tr>
      <tr>
        <td class="main" valign="top"><?php echo TEXT_META_TAGS_DESCRIPTION; ?>&nbsp;</td>
        <td class="main" colspan="3"><?php echo $pInfo->metatags_description; ?></td>
      </tr>
    </table>
    <?php
  }
  ?>
  <div class="row text-right">
    <?php
    /* Re-Post all POST'ed variables */
    foreach ($_POST as $key => $value) {
      if (!is_array($_POST[$key])) {
        echo zen_draw_hidden_field($key, htmlspecialchars(stripslashes($value), ENT_COMPAT, CHARSET, TRUE));
      }
    }

    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
      echo zen_draw_hidden_field('metatags_title[' . $languages[$i]['id'] . ']', htmlspecialchars(stripslashes($metatags_title[$languages[$i]['id']]), ENT_COMPAT, CHARSET, TRUE));
      echo zen_draw_hidden_field('metatags_keywords[' . $languages[$i]['id'] . ']', htmlspecialchars(stripslashes($metatags_keywords[$languages[$i]['id']]), ENT_COMPAT, CHARSET, TRUE));
      echo zen_draw_hidden_field('metatags_description[' . $languages[$i]['id'] . ']', htmlspecialchars(stripslashes($metatags_description[$languages[$i]['id']]), ENT_COMPAT, CHARSET, TRUE));
    }
?>
    <button type="submit" name="edit" value="edit" class="btn btn-default"><?php echo IMAGE_BACK; ?></button>
    <button type="submit" class="btn btn-primary"><?php echo (isset($_GET['pID']) ? IMAGE_UPDATE : IMAGE_INSERT); ?></button> 
    <a href="<?php echo zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, 'cPath=' . $cPath . (isset($_GET['pID']) ? '&pID=' . $_GET['pID'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')); ?>" class="btn btn-default" role="button"><?php echo IMAGE_CANCEL; ?></a>
  </div>
  <?php echo '</form>'; ?>
</div>
