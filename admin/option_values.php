<?php
/**
 * @package admin
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2019 Mar 22 Modified in v1.5.6b $
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
  case ('update_sort_order'):
    foreach ($_POST['options_values_new_sort_order'] as $id => $new_sort_order) {

      $db->Execute("UPDATE " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                    SET products_options_values_sort_order = " . (int)$_POST['options_values_new_sort_order'][$id] . "
                    WHERE products_options_values_id = " . (int)$id);
    }
    $messageStack->add_session(SUCCESS_OPTION_VALUES_SORT_ORDER . ' ' . zen_options_name($_GET['options_id']), 'success');
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
      $product_id_updated .= ' - ' . $products_attribute['products_id'] . ':' . $products_attribute['products_attributes_id'];
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
} // switch
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta charset="<?php echo CHARSET; ?>">
    <title><?php echo TITLE; ?></title>
    <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
    <link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
    <script type="text/javascript" src="includes/menu.js"></script>
    <script type="text/javascript" src="includes/general.js"></script>
    <script>
      function init() {
          cssjsmenu('navbar');
          if (document.getElementById) {
              var kill = document.getElementById('hoverJS');
              kill.disabled = true;
          }
      }
    </script>
  </head>
  <body onload="init()">
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->

    <!-- body //-->
    <div class="container-fluid">
      <div class="table-responsive">
        <h1><?php echo HEADING_TITLE; ?></h1>
        <!-- body_text //-->
        <?php
        if (empty($_GET['options_id'])) {
          ?>
          <?php echo zen_draw_form('quick_jump', FILENAME_PRODUCTS_OPTIONS_VALUES, '', 'get', 'class="form-horizontal"'); ?>
          <table class="table table-condensed">
            <tr class="dataTableHeadingRow">
              <td colspan="2" align="center" class="dataTableHeadingContent"><?php echo TEXT_UPDATE_OPTION_VALUES; ?></td>
            </tr>
            <tr class="dataTableHeadingRow">
              <td class="dataTableHeadingContent">
                  <?php echo zen_draw_label(TEXT_SELECT_OPTION, 'options_id', 'class="control-label"'); ?>
                  <?php
                  $options_values = $db->Execute("SELECT products_options_id, products_options_name
                                                  FROM " . TABLE_PRODUCTS_OPTIONS . "
                                                  WHERE language_id = " . (int)$_SESSION['languages_id'] . "
                                                  AND products_options_name != ''
                                                  AND products_options_type != " . (int)PRODUCTS_OPTIONS_TYPE_TEXT . "
                                                  AND products_options_type != " . (int)PRODUCTS_OPTIONS_TYPE_FILE . "
                                                  ORDER BY products_options_name");
                  $optionsValuesArray = array();
                  foreach ($options_values as $options_value) {
                    $optionsValuesArray[] = array(
                      'id' => $options_value['products_options_id'],
                      'text' => $options_value['products_options_name']
                    );
                  }
                  ?>
                  <?php echo zen_draw_pull_down_menu('options_id', $optionsValuesArray, '', 'class="form-control"'); ?>
              </td>
              <td class="dataTableHeadingContent text-center">
                <button type="submit" class="btn btn-primary"><?php echo IMAGE_EDIT; ?></button>
              </td>
            </tr>
          </table>
          <?php echo '</form>'; ?>
          <?php
        } else {
          ?>
          <?php echo zen_draw_form('update', FILENAME_PRODUCTS_OPTIONS_VALUES, 'action=update_sort_order&options_id=' . $_GET['options_id'], 'post', 'class="form-horizontal"'); ?>
          <table class="table table-condensed table-striped">
            <tr class="dataTableHeadingRow">
              <td colspan="3" class="dataTableHeadingContent" align="center">
                <?php echo TEXT_EDIT_OPTION_NAME; ?> <?php echo zen_options_name($_GET['options_id']); ?></td>
            </tr>
            <tr class="dataTableHeadingRow">
              <td class="dataTableHeadingContent">Option ID</td>
              <td class="dataTableHeadingContent">Option Value Name</td>
              <td class="dataTableHeadingContent">Sort Order</td>
            </tr>
            <?php
            $rows = $db->Execute("SELECT *
                                  FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov,
                                       " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " povtpo
                                  WHERE povtpo.products_options_values_id = pov.products_options_values_id
                                  AND povtpo.products_options_id = " . (int)$_GET['options_id'] . "
                                  AND pov.language_id = " . (int)$_SESSION['languages_id'] . "
                                  ORDER BY pov.products_options_values_sort_order, pov.products_options_values_id");

            if ($rows->RecordCount() > 0) {
              $option_values_exist = true;
              foreach ($rows as $row) {
                ?>
                <tr>
                  <td class="dataTableContent"><?php echo $row['products_options_values_id']; ?></td>
                  <td class="dataTableContent"><?php echo $row['products_options_values_name']; ?></td>
                  <td class="dataTableContent">
                    <?php echo zen_draw_input_field('options_values_new_sort_order[' . $row['products_options_values_id'] . ']', $row['products_options_values_sort_order'], 'size="4" class="form-control"'); ?>
                  </td>
                </tr>
                <?php
              }
            } else {
              $option_values_exist = false;
              ?>
              <tr>
                <td colspan="3" class="text-center dataTableContent"><?php echo TEXT_NO_OPTION_VALUE . zen_options_name($_GET['options_id']); ?></td>
              </tr>
              <?php
            }
            ?>
            <tr class="dataTableHeadingRow">
                <?php
                if ($option_values_exist == true) {
                  ?>
                <td colspan="2" class="dataTableHeadingContent text-right">
                  <button type="submit" class="btn btn-primary"><?php echo TEXT_UPDATE_SUBMIT; ?></button>
                </td>
                <?php
              }
              ?>
              <td <?php echo ($option_values_exist == true ? '' : 'colspan="3"'); ?> class="dataTableHeadingContent text-left">
                <a href="<?php echo zen_href_link(FILENAME_PRODUCTS_OPTIONS_VALUES); ?>" class="btn btn-default" role="button"><?php echo IMAGE_CANCEL; ?></a>
              </td>
            </tr>
          </table>
          <?php echo '</form>'; ?>
          <?php
        } // which table
        ?>

      </div>
      <?php
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
          <div class="col-sm-6"><?php echo zen_draw_products_pull_down_attributes('products_update_id', 'class="form-control"'); ?></div>
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
          <div class="col-sm-3"><?php echo zen_draw_products_pull_down_categories_attributes('categories_update_id', 'class="form-control"'); ?></div>
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
