<?php
/**
 * @package admin
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2019 May 25 Modified in v1.5.6b $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

$parameters = array(
  'products_name' => '',
  'products_model' => '',
  'metatags_title_status' => '1',
  'metatags_products_name_status' => '1',
  'metatags_model_status' => '1',
  'products_id' => '',
  'metatags_price_status' => '1',
  'metatags_title_tagline_status' => '1',
  'metatags_title' => '',
  'metatags_keywords' => '',
  'metatags_description' => ''
);

$pInfo = new objectInfo($parameters);

if (isset($_GET['pID']) && empty($_POST)) {
// check if new meta tags or existing
  $check_meta_tags_description = $db->Execute("SELECT products_id
                                               FROM " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . "
                                               WHERE products_id = " . (int)$_GET['pID']);
  if ($check_meta_tags_description->RecordCount() <= 0) {
      $product = $db->Execute("SELECT pd.products_name,
                                    p.metatags_title_status, p.metatags_products_name_status, p.metatags_model_status,
                                    p.products_id, p.metatags_price_status, p.metatags_title_tagline_status
                             FROM " . TABLE_PRODUCTS . " p,
                                  " . TABLE_PRODUCTS_DESCRIPTION . " pd
                             WHERE p.products_id = " . (int)$_GET['pID'] . "
                             AND p.products_id = pd.products_id
                             AND pd.language_id = " . (int)$_SESSION['languages_id']);
  } else {
      $product = $db->Execute("SELECT pd.products_name,
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
  }

  $pInfo->updateObjectInfo($product->fields);
} elseif (zen_not_null($_POST)) {
  $pInfo->updateObjectInfo($_POST);
  $metatags_title = $_POST['metatags_title'];
  $metatags_keywords = $_POST['metatags_keywords'];
  $metatags_description = $_POST['metatags_description'];
}

$languages = zen_get_languages();

// metatags_products_name_status shows
if (empty($pInfo->metatags_keywords) && empty($pInfo->metatags_description)) {
  $pInfo->metatags_products_name_status = zen_get_show_product_switch($_GET['pID'], 'metatags_products_name_status');
}
// metatags_title_status shows
if (empty($pInfo->metatags_keywords) && empty($pInfo->metatags_description)) {
  $pInfo->metatags_title_status = zen_get_show_product_switch($_GET['pID'], 'metatags_title_status');
}
// metatags_model_status shows
if (empty($pInfo->metatags_keywords) && empty($pInfo->metatags_description)) {
  $pInfo->metatags_model_status = zen_get_show_product_switch($_GET['pID'], 'metatags_model_status');
}
// metatags_price_status shows
if (empty($pInfo->metatags_keywords) && empty($pInfo->metatags_description)) {
  $pInfo->metatags_price_status = zen_get_show_product_switch($_GET['pID'], 'metatags_price_status');
}
// metatags_title_tagline_status shows TITLE and TAGLINE in metatags_header.php
if (empty($pInfo->metatags_keywords) && empty($pInfo->metatags_description)) {
  $pInfo->metatags_title_tagline_status = zen_get_show_product_switch($_GET['pID'], 'metatags_title_tagline_status');
}
?>
<div class="container-fluid">
    <?php
//  echo $type_handler;
    echo zen_draw_form('new_product_meta_tags', FILENAME_PRODUCT, 'cPath=' . $cPath . (isset($_GET['product_type']) ? '&product_type=' . $_GET['product_type'] : '') . (isset($_GET['pID']) ? '&pID=' . $_GET['pID'] : '') . '&action=new_product_preview_meta_tags' . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''), 'post', 'enctype="multipart/form-data" class="form-horizontal"');
    ?>
  <h3><?php echo sprintf(TEXT_NEW_PRODUCT, zen_output_generated_category_path($current_category_id)); ?></h3>
  <div class="form-group">
    <div class="col-sm-3 control-label"><?php echo TEXT_META_TAG_TITLE_INCLUDES; ?></div>
    <div class="col-sm-6">
      <div class="form-group">
          <?php echo zen_draw_label(TEXT_PRODUCTS_METATAGS_PRODUCTS_NAME_STATUS, 'metatags_products_name_status', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9">
          <label class="radio-inline"><?php echo zen_draw_radio_field('metatags_products_name_status', '1', ($pInfo->metatags_products_name_status == '1')) . TEXT_YES; ?></label>
          <label class="radio-inline"><?php echo zen_draw_radio_field('metatags_products_name_status', '0', ($pInfo->metatags_products_name_status == '0')) . TEXT_NO; ?></label>
        </div>
      </div>
      <div class="form-group">
          <?php echo zen_draw_label(TEXT_PRODUCTS_METATAGS_TITLE_STATUS, 'metatags_title_status', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-6">
          <label class="radio-inline"><?php echo zen_draw_radio_field('metatags_title_status', '1', ($pInfo->metatags_title_status == '1')) . TEXT_YES; ?></label>
          <label class="radio-inline"><?php echo zen_draw_radio_field('metatags_title_status', '0', ($pInfo->metatags_title_status == '0')) . TEXT_NO; ?></label>
        </div>
      </div>
<!-- // not used for documents
      <div class="form-group">
          <?php echo zen_draw_label(TEXT_PRODUCTS_METATAGS_MODEL_STATUS, 'metatags_model_status', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-6">
          <label class="radio-inline"><?php echo zen_draw_radio_field('metatags_model_status', '1', ($pInfo->metatags_model_status == '1')) . TEXT_YES; ?></label>
          <label class="radio-inline"><?php echo zen_draw_radio_field('metatags_model_status', '0', ($pInfo->metatags_model_status == '0')) . TEXT_NO; ?></label>
        </div>
      </div>
      <div class="form-group">
          <?php echo zen_draw_label(TEXT_PRODUCTS_METATAGS_PRICE_STATUS, 'metatags_price_status', 'class="col-sm-3 control-label"') ?>
        <div class="col-sm-9 col-md-6">
          <label class="radio-inline"><?php echo zen_draw_radio_field('metatags_price_status', '1', ($pInfo->metatags_price_status == '1')) . TEXT_YES; ?></label>
          <label class="radio-inline"><?php echo zen_draw_radio_field('metatags_price_status', '0', ($pInfo->metatags_price_status == '0')) . TEXT_NO; ?></label>
        </div>
      </div>
-->
      <div class="form-group">
          <?php echo zen_draw_label(TEXT_PRODUCTS_METATAGS_TITLE_TAGLINE_STATUS, 'metatags_title_tagline_status', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-6">
          <label class="radio-inline"><?php echo zen_draw_radio_field('metatags_title_tagline_status', '1', ($pInfo->metatags_title_tagline_status == '1')) . TEXT_YES; ?></label>
          <label class="radio-inline"><?php echo zen_draw_radio_field('metatags_title_tagline_status', '0', ($pInfo->metatags_title_tagline_status == '0')) . TEXT_NO; ?></label>
        </div>
      </div>
    </div>
  </div>
  <div class="form-group"><?php echo zen_draw_separator('pixel_black.gif', '100%', '3'); ?></div>
  <div class="form-group">
      <?php
      for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
        ?>
      <div class="input-group">
        <span class="input-group-addon">
            <?php echo zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']); ?>
        </span>
        <div style="border: 1px solid #ccc;">
          <div class="col-sm-12" style="padding-top: 10px;padding-bottom: 10px;">
              <strong><?php echo TEXT_DOCUMENT_NAME; ?></strong>&nbsp;<?php echo zen_get_products_name($_GET['pID'], $languages[$i]['id']); ?>
          </div>
          <div class="form-group">
              <?php echo zen_draw_label(TEXT_META_TAGS_TITLE, 'metatags_title[' . $languages[$i]['id'] . ']', 'class="col-sm-3 control-label"'); ?>
            <div class="col-sm-9 col-md-6">
                <?php echo zen_draw_input_field('metatags_title[' . $languages[$i]['id'] . ']', htmlspecialchars(isset($metatags_title[$languages[$i]['id']]) ? stripslashes($metatags_title[$languages[$i]['id']]) : zen_get_metatags_title($pInfo->products_id, $languages[$i]['id']), ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_META_TAGS_PRODUCTS_DESCRIPTION, 'metatags_title', '150', false) . 'class="form-control"'); //,'id="'.'metatags_title' . $languages[$i]['id'] . '"'); ?>
            </div>
          </div>
          <div class="form-group">
              <?php echo zen_draw_label(TEXT_META_TAGS_KEYWORDS, 'metatags_keywords[' . $languages[$i]['id'] . ']', 'class="col-sm-3 control-label"'); ?>
            <div class="col-sm-9 col-md-6">
                <?php echo zen_draw_textarea_field('metatags_keywords[' . $languages[$i]['id'] . ']', 'soft', '100%', '10', htmlspecialchars((isset($metatags_keywords[$languages[$i]['id']])) ? stripslashes($metatags_keywords[$languages[$i]['id']]) : zen_get_metatags_keywords($pInfo->products_id, $languages[$i]['id']), ENT_COMPAT, CHARSET, TRUE), 'class="noEditor form-control"'); //,'id="'.'metatags_keywords' . $languages[$i]['id'] . '"');   ?>
            </div>
          </div>
          <div class="form-group">
              <?php echo zen_draw_label(TEXT_META_TAGS_DESCRIPTION, 'metatags_description[' . $languages[$i]['id'] . ']', 'class="col-sm-3 control-label"'); ?>
            <div class="col-sm-9 col-md-6">
                <?php echo zen_draw_textarea_field('metatags_description[' . $languages[$i]['id'] . ']', 'soft', '100%', '10', htmlspecialchars((isset($metatags_description[$languages[$i]['id']])) ? stripslashes($metatags_description[$languages[$i]['id']]) : zen_get_metatags_description($pInfo->products_id, $languages[$i]['id']), ENT_COMPAT, CHARSET, TRUE), 'class="noEditor form-control"'); //,'id="'.'metatags_description' . $languages[$i]['id'] . '"');   ?>
            </div>
          </div>
        </div>
      </div>
      <br>
      <?php
    }
    ?>
  </div>
  <div class="form-group"><?php echo TEXT_INFO_META_TAGS_USAGE; ?></div>
  <div class="form-group text-right">
      <?php echo zen_draw_hidden_field('products_model', $pInfo->products_model); ?>
      <?php echo zen_draw_hidden_field('products_price_sorter', $pInfo->products_price_sorter); ?>
    <button type="submit" class="btn btn-primary"><?php echo IMAGE_PREVIEW; ?></button> <a href="<?php echo zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, 'cPath=' . $cPath . (isset($_GET['pID']) ? '&pID=' . $_GET['pID'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')); ?>" class="btn btn-default" role="button"><?php echo IMAGE_CANCEL; ?></a>
  </div>
  <?php echo '</form>'; ?>
</div>
