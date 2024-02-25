<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Feb 22 Modified in v2.0.0-beta1 $
 */
require('includes/application_top.php');

// verify option values exist
$chk_option_values = $db->Execute("SELECT DISTINCT language_id
                                   FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                                   WHERE language_id = " . (int)$_SESSION['languages_id']);
if ($chk_option_values->RecordCount() < 1) {
  $messageStack->add_session(ERROR_DEFINE_OPTION_VALUES, 'caution');
  zen_redirect(zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER));
}

require(DIR_WS_CLASSES . 'currencies.php');
$currencies = new currencies();

if (!isset($_GET['action'])) {
  $_GET['action'] = '';
}

switch ($_GET['action']) {

    //Update by Option Name
    case ('update_sort_order'):

      if (empty($_POST['language']) || $_POST['language'] === 'current_only') {
          $languages_array[0] = ['id' => $_SESSION['languages_id']];
      } else {
          $languages_array = zen_get_languages();
      }
      foreach ($languages_array as $language) {
          foreach ($_POST['options_values_new_sort_order'] as $id => $new_sort_order) {
              $db->Execute(
                  'UPDATE ' . TABLE_PRODUCTS_OPTIONS_VALUES . '
                    SET products_options_values_sort_order = ' . (int)$new_sort_order . '
                    WHERE products_options_values_id = ' . (int)$id . ' AND language_id = ' . (int)$language['id']
              );
              $messageStack->add_session(
                  sprintf(
                      SUCCESS_OPTION_VALUES_SORT_ORDER_NAME,
                      htmlentities(zen_get_option_name_language($_GET['options_id'], (int)$language['id'])),
                      $_GET['options_id'],
                      htmlentities(zen_values_name($id, (int)$language['id'])),
                      $id,
                      $new_sort_order
                  ),
                  'success'
              );
          }
      }
    $_GET['action'] = '';
    zen_redirect(zen_href_link(FILENAME_PRODUCTS_OPTIONS_VALUES));
    break;

// update by product
  case ('update_product'):
    $messageStack->add_session(SUCCESS_PRODUCT_UPDATE_SORT . $_POST['products_update_id'] . ' ' . zen_get_products_name($_POST['products_update_id'], $_SESSION['languages_id']), 'success');
    zen_update_attributes_products_option_values_sort_order($_POST['products_update_id']);
    $action = '';
    zen_redirect(zen_href_link(FILENAME_PRODUCTS_OPTIONS_VALUES));
    break;
// update by category
  case ('update_categories_attributes'):
    $all_products_attributes = $db->Execute("SELECT ptoc.products_id, pa.products_attributes_id
                                             FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " ptoc,
                                                  " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                             WHERE ptoc.categories_id = " . (int)$_POST['categories_update_id'] . "
                                             AND pa.products_id = ptoc.products_id");
    foreach ($all_products_attributes as $products_attribute) {
      zen_update_attributes_products_option_values_sort_order($products_attribute);
    }
    $messageStack->add_session(SUCCESS_CATEGORIES_UPDATE_SORT . (int)$_POST['categories_update_id'] . ' ' . zen_get_category_name($_POST['categories_update_id'], $_SESSION['languages_id']), 'success');
    $action = '';
    zen_redirect(zen_href_link(FILENAME_PRODUCTS_OPTIONS_VALUES));
    break;
// update all products in catalog
  case ('update_all_products_attributes_sort_order'):
    if (isset($_POST['confirm']) && $_POST['confirm'] == 'y') {
      $all_products_attributes = $db->Execute("SELECT p.products_id, pa.products_attributes_id
                                               FROM " . TABLE_PRODUCTS . " p,
                                                    " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                               WHERE p.products_id= pa.products_id");
      foreach ($all_products_attributes as $products_attribute) {
        zen_update_attributes_products_option_values_sort_order($products_attribute['products_id']);
      }
    }
    $messageStack->add_session(SUCCESS_PRODUCT_UPDATE_SORT_ALL, 'success');
    $action = '';
    zen_redirect(zen_href_link(FILENAME_PRODUCTS_OPTIONS_VALUES));
    break;
}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
  </head>
  <body>
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->

    <!-- body //-->
    <div class="container-fluid">

        <h1><?= HEADING_TITLE ?></h1>
        <!-- body_text //-->
        <?php
        if (empty($_GET['options_id'])) {
            ?>
            <div id="editOptionName">
                <h2><?= TEXT_UPDATE_OPTION_VALUES ?></h2>
                <?php
                echo zen_draw_form('quick_jump', FILENAME_PRODUCTS_OPTIONS_VALUES, '', 'get', 'class="form-inline"');
                echo zen_draw_label(TEXT_SELECT_OPTION, 'options_id', 'class="control-label"');
                // Get only Option Names that have Option Values
                $options_values = $db->Execute(
                    'SELECT DISTINCT po.products_options_id, po.products_options_name
                                  FROM ' . TABLE_PRODUCTS_OPTIONS . ' po INNER JOIN ' . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . ' povtpo
                                  ON po.products_options_id = povtpo.products_options_id
                                  WHERE po.language_id = ' . (int)$_SESSION['languages_id'] . '
                                  AND po.products_options_name != ""
                                  AND po.products_options_type != ' . (int)PRODUCTS_OPTIONS_TYPE_TEXT . '
                                  AND po.products_options_type != ' . (int)PRODUCTS_OPTIONS_TYPE_FILE . '
                                  ORDER BY po.products_options_name');
                $optionsValuesArray = [];
                foreach ($options_values as $options_value) {
                    $optionsValuesArray[] = [
                        'id' => $options_value['products_options_id'],
                        'text' => $options_value['products_options_name']
                    ];
                }
                echo zen_draw_pull_down_menu('options_id', $optionsValuesArray, '', 'class="form-control" id="options_id"'); ?>
                <button type="submit" class="btn btn-primary"><?= IMAGE_EDIT ?></button>
                <?php echo '</form>'; ?>
            </div>
            <hr>
            <?php
        } else {
            ?>
            <h2><?= TEXT_UPDATE_OPTION_VALUES ?></h2>
            <h3><?= TEXT_EDIT_OPTION_NAME . zen_options_name($_GET['options_id']) ?></h3>
            <div class="table-responsive">
                <?php
                echo zen_draw_form('update', FILENAME_PRODUCTS_OPTIONS_VALUES, 'action=update_sort_order&options_id=' . $_GET['options_id'], 'post', 'class="form-horizontal"'); ?>
                <table class="table-condensed">
                    <tr class="dataTableHeadingRow">
                        <th class="dataTableHeadingContent text-center"><?= TABLE_HEADING_OPTION_VALUE_ID ?></th>
                        <th class="dataTableHeadingContent"><?= TABLE_HEADING_OPTION_VALUE_NAME ?></th>
                        <th class="dataTableHeadingContent text-center"><?= TEXT_SORT_ORDER ?></th>
                    </tr>
                    <?php
                    $rows = $db->Execute(
                        'SELECT *
                                  FROM ' . TABLE_PRODUCTS_OPTIONS_VALUES . ' pov,
                                       ' . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . ' povtpo
                                  WHERE povtpo.products_options_values_id = pov.products_options_values_id
                                  AND povtpo.products_options_id = ' . (int)$_GET['options_id'] . '
                                  AND pov.language_id = ' . (int)$_SESSION['languages_id'] . '
                                  ORDER BY pov.products_options_values_sort_order, pov.products_options_values_id'
                    );
                    foreach ($rows as $row) { ?>
                        <tr>
                            <td class="dataTableContent text-center"><?= $row['products_options_values_id'] ?></td>
                            <td class="dataTableContent"><?= $row['products_options_values_name'] ?></td>
                            <td class="dataTableContent"><?= zen_draw_input_field('options_values_new_sort_order[' . $row['products_options_values_id'] . ']', $row['products_options_values_sort_order'], 'size="4" class="form-control text-right"') ?>
                            </td>
                        </tr>
                        <?php
                    } ?>
                </table>
                <div class="row">
                    <?php
                    if (count($languages_array) > 1) { ?>
                        <div class="radio"><label><?= zen_draw_radio_field('language', 'current_only') . TEXT_UPDATE_SORT_LANGUAGE_CURRENT ?></label></div>
                        <div class="radio"><label><?= zen_draw_radio_field('language', 'all', true) . TEXT_UPDATE_SORT_LANGUAGE_ALL ?></label></div> <?php
                    } ?>
                    <button type="submit" class="btn btn-warning"><?= TEXT_UPDATE_SUBMIT ?></button>
                    <a href="<?= zen_href_link(FILENAME_PRODUCTS_OPTIONS_VALUES) ?>" class="btn btn-default" role="button"><?= IMAGE_CANCEL ?></a>
                </div>
                <?='</form>' ?>
            </div>
            <?php
        }

//////////////////////////////////////////
// BOF: Update by Product, Category or All products
// only show when not updating Option Value Sort Order
      if (empty($_GET['options_id'])) {

// select from all product with attributes
        ?>
        <div class="row">
            <?php echo zen_draw_separator('pixel_trans.gif', '100%', '5'); ?>
        </div>

        <div class="row">
            <?php echo TEXT_UPDATE_SORT_ORDERS_OPTIONS; ?>
        </div>

        <div class="row">
            <?php echo zen_draw_separator('pixel_trans.gif', '100%', '5'); ?>
        </div>
        <div class="row">
            <?php echo TEXT_UPDATE_SORT_ORDERS_OPTIONS_PRODUCTS; ?>
        </div>
        <div class="row">
            <?php echo zen_draw_form('update_product_attributes', FILENAME_PRODUCTS_OPTIONS_VALUES, 'action=update_product', 'post', 'class="form-horizontal"'); ?>
            <?php echo zen_draw_hidden_field('products_update_id'); ?>
          <div class="col-sm-6"><?php echo zen_draw_pulldown_products_having_attributes('products_update_id', 'class="form-control"'); ?></div>
          <div class="col-sm-2">
            <button type="submit" class="btn btn-warning"><?php echo IMAGE_UPDATE; ?></button>
          </div>
          <?php echo'</form>'; ?>
        </div>

        <?php
// select from all categories with products with attributes
        ?>
        <div class="row">
            <?php echo zen_draw_separator('pixel_trans.gif', '100%', '5'); ?>
        </div>
        <div class="row">
            <?php echo TEXT_UPDATE_SORT_ORDERS_OPTIONS_CATEGORIES; ?>
        </div>
        <div class="row">
            <?php echo zen_draw_form('update_categories_attributes', FILENAME_PRODUCTS_OPTIONS_VALUES, 'action=update_categories_attributes', 'post', 'class="form-horizontal"'); ?>
            <?php echo zen_draw_hidden_field('categories_update_id'); ?>
          <div class="col-sm-3"><?php echo zen_draw_pulldown_categories_having_products_with_attributes('categories_update_id', 'class="form-control"'); ?></div>
          <div class="col-sm-2"><button type="submit" class="btn btn-warning"><?php echo IMAGE_UPDATE; ?></button></div>
          <?php echo '</form>'; ?>
        </div>

        <?php
// select the catalog and update all products with attributes
        ?>
        <div class="row">
            <?php echo zen_draw_separator('pixel_trans.gif', '100%', '5'); ?>
        </div>
        <div class="row">
          <div class="col-sm-3"><?php echo TEXT_INFO_ATTRIBUTES_FEATURES_UPDATES; ?></div>
          <div class="col-sm-2">
              <?php echo zen_draw_form('update_all_sort', FILENAME_PRODUCTS_OPTIONS_VALUES, 'action=update_all_products_attributes_sort_order', 'post', 'class="form-horizontal"') ?>
              <?php echo zen_draw_hidden_field('confirm', 'y'); ?>
            <button type="submit" class="btn btn-warning"><?php echo IMAGE_UPDATE; ?></button>
            <?php echo '</form>'; ?>
          </div>
        </div>
        <?php
      }
// EOF: Update by Product, Category or All products
//////////////////////////////////////////
      ?>
      <!-- body_text_eof //-->
    </div>
    <!-- body_eof //-->
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
