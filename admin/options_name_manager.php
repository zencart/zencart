<?php
/**
 * @package admin
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: mc12345678 Tue Aug 21 23:25:22 2018 -0400 Modified in v1.5.6 $
 */
require('includes/application_top.php');
$languages = zen_get_languages();

require(DIR_WS_CLASSES . 'currencies.php');
$currencies = new currencies();

// check for damaged database, caused by users indiscriminately deleting table data
$ary = array();
$chk_option_values = $db->Execute("SELECT *
                                   FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                                   WHERE products_options_values_id = " . (int)PRODUCTS_OPTIONS_VALUES_TEXT_ID);
foreach ($chk_option_values as $item) {
  $ary[] = $item['language_id'];
}
for ($i = 0, $n = sizeof($languages); $i < $n; $i ++) {
  if ((int)$languages[$i]['id'] > 0 && !in_array((int)$languages[$i]['id'], $ary)) {
    $db->Execute("INSERT INTO " . TABLE_PRODUCTS_OPTIONS_VALUES . " (products_options_values_id, language_id, products_options_values_name)
                  VALUES (" . (int)PRODUCTS_OPTIONS_VALUES_TEXT_ID . ", " . (int)$languages[$i]['id'] . ", 'TEXT')");
  }
}

$action = (isset($_GET['action']) ? $_GET['action'] : '');

// display or hide copier features
if (!isset($_SESSION['option_names_values_copier'])) {
  $_SESSION['option_names_values_copier'] = OPTION_NAMES_VALUES_GLOBAL_STATUS;
}
if (!isset($_GET['reset_option_names_values_copier'])) {
  $reset_option_names_values_copier = $_SESSION['option_names_values_copier'];
}

/*
  if (!isset($_GET['option_order_by'])) {
  $_GET['option_order_by'] = 'products_options_id';
  }
 */
if (isset($_GET['option_order_by'])) {
  $option_order_by = $_GET['option_order_by'];
} else {
  $option_order_by = 'products_options_id';
}

if (zen_not_null($action)) {
  $_SESSION['page_info'] = '';
  if (isset($_GET['option_page'])) {
    $_SESSION['page_info'] .= 'option_page=' . $_GET['option_page'] . '&';
  }
  if (isset($_GET['value_page'])) {
    $_SESSION['page_info'] .= 'value_page=' . $_GET['value_page'] . '&';
  }
  if (isset($_GET['attribute_page'])) {
    $_SESSION['page_info'] .= 'attribute_page=' . $_GET['attribute_page'] . '&';
  }
  if (zen_not_null($_SESSION['page_info'])) {
    $_SESSION['page_info'] = substr($_SESSION['page_info'], 0, -1);
  }

  switch ($action) {
    case 'set_option_names_values_copier':
      $_SESSION['option_names_values_copier'] = $_GET['reset_option_names_values_copier'];
      $action = '';
      zen_redirect(zen_href_link(FILENAME_OPTIONS_NAME_MANAGER));
      break;
    case 'add_product_options':
      //clr 030714 update to add option type to products_option.
      $products_options_id = zen_db_prepare_input($_POST['products_options_id']);
      $option_name_array = $_POST['option_name'];
      $products_options_sort_order = $_POST['products_options_sort_order'];
      $option_type = $_POST['option_type'];
      $products_options_images_per_row = $_POST['products_options_images_per_row'];
      $products_options_images_style = $_POST['products_options_images_style'];
      $products_options_rows = $_POST['products_options_rows'];

      for ($i = 0, $n = sizeof($languages); $i < $n; $i ++) {
        $option_name = zen_db_prepare_input($option_name_array[$languages[$i]['id']]);

        $db->Execute("INSERT INTO " . TABLE_PRODUCTS_OPTIONS . " (products_options_id, products_options_name, language_id, products_options_sort_order, products_options_type, products_options_images_per_row, products_options_images_style, products_options_rows)
                      VALUES ('" . (int)$products_options_id . "',
                              '" . zen_db_input($option_name) . "',
                              '" . (int)$languages[$i]['id'] . "',
                              '" . (int)$products_options_sort_order[$languages[$i]['id']] . "',
                              '" . (int)zen_db_input($option_type) . "',
                              '" . (int)zen_db_input($products_options_images_per_row) . "',
                              '" . (int)zen_db_input($products_options_images_style) . "',
                              '" . (int)(($products_options_rows <= 1 and $option_type == PRODUCTS_OPTIONS_TYPE_TEXT) ? 1 : zen_db_input($products_options_rows)) . "')");
      }

// iii 030811 added:  For TEXT and FILE option types, automatically add
// PRODUCTS_OPTIONS_VALUE_TEXT to the TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS
      switch ($option_type) {
        case PRODUCTS_OPTIONS_TYPE_TEXT:
        case PRODUCTS_OPTIONS_TYPE_FILE:
          $db->Execute("INSERT INTO " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " (products_options_values_id, products_options_id)
                        VALUES ('" . (int)PRODUCTS_OPTIONS_VALUES_TEXT_ID . "',
                                '" . (int)$products_options_id . "')");
          break;
      }

// alert if possible duplicate
      $duplicate_option = '';
      for ($i = 0, $n = sizeof($languages); $i < $n; $i ++) {
        $option_name = zen_db_prepare_input($option_name_array[$languages[$i]['id']]);

        if (!empty($option_name)) {
          $check = $db->Execute("SELECT COUNT(products_options_name) AS count
                                 FROM " . TABLE_PRODUCTS_OPTIONS . "
                                 WHERE language_id = " . (int)$languages[$i]['id'] . "
                                 AND products_options_name = '" . zen_db_input($option_name) . "'");
          if ($check->RecordCount() > 1) {
            $duplicate_option .= ' <strong>' . strtoupper(zen_get_language_name($languages[$i]['id'])) . '</strong> : ' . $option_name;
          }
        }
      }
      if (!empty($duplicate_option)) {
        $messageStack->add_session(ATTRIBUTE_POSSIBLE_OPTIONS_NAME_WARNING_DUPLICATE . ' ' . $option_id . ' - ' . $duplicate_option, 'caution');
      }

      zen_redirect(zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, $_SESSION['page_info'] . '&option_order_by=' . $option_order_by));
      break;
    case 'update_option_name':
      //clr 030714 update to add option type to products_option.
      $option_name_array = $_POST['option_name'];
      $option_type = (int)$_POST['option_type'];
      $option_id = zen_db_prepare_input($_POST['option_id']);
      $products_options_sort_order_array = $_POST['products_options_sort_order'];

      $products_options_length_array = $_POST['products_options_length'];
      $products_options_comment_array = $_POST['products_options_comment'];
      $products_options_size_array = $_POST['products_options_size'];

      $products_options_images_per_row_array = $_POST['products_options_images_per_row'];
      $products_options_images_style_array = $_POST['products_options_images_style'];
      $products_options_rows_array = $_POST['products_options_rows'];

      for ($i = 0, $n = sizeof($languages); $i < $n; $i ++) {
        $option_name = zen_db_prepare_input($option_name_array[$languages[$i]['id']]);
        $products_options_sort_order = (int)zen_db_prepare_input($products_options_sort_order_array[$languages[$i]['id']]);


        $products_options_length = zen_db_prepare_input($products_options_length_array[$languages[$i]['id']]);
        $products_options_comment = zen_db_prepare_input($products_options_comment_array[$languages[$i]['id']]);
        $products_options_size = zen_db_prepare_input($products_options_size_array[$languages[$i]['id']]);

        $products_options_images_per_row = (int)zen_db_prepare_input($products_options_images_per_row_array[$languages[$i]['id']]);
        $products_options_images_style = (int)zen_db_prepare_input($products_options_images_style_array[$languages[$i]['id']]);
        $products_options_rows = (int)zen_db_prepare_input($products_options_rows_array[$languages[$i]['id']]);

//          zen_db_query("update " . TABLE_PRODUCTS_OPTIONS . " set products_options_name = '" . zen_db_input($option_name) . "', products_options_type = '" . $option_type . "' where products_options_id = '" . (int)$option_id . "' and language_id = '" . (int)$languages[$i]['id'] . "'");

        $db->Execute("UPDATE " . TABLE_PRODUCTS_OPTIONS . "
                      SET products_options_name = '" . zen_db_input($option_name) . "',
                          products_options_type = '" . $option_type . "',
                          products_options_length = '" . zen_db_input($products_options_length) . "',
                          products_options_comment = '" . zen_db_input($products_options_comment) . "',
                          products_options_size = '" . zen_db_input($products_options_size) . "',
                          products_options_sort_order = '" . zen_db_input($products_options_sort_order) . "',
                          products_options_images_per_row = '" . zen_db_input($products_options_images_per_row) . "',
                          products_options_images_style = '" . zen_db_input($products_options_images_style) . "',
                          products_options_rows = '" . zen_db_input($products_options_rows) . "'
                      WHERE products_options_id = " . (int)$option_id . "
                      AND language_id = " . (int)$languages[$i]['id']);
      }

      switch ($option_type) {
        case PRODUCTS_OPTIONS_TYPE_TEXT:
        case PRODUCTS_OPTIONS_TYPE_FILE:
// disabled because this could cause trouble if someone changed types unintentionally and deleted all their option values.  Shops with small numbers of values per option should consider uncommenting this.
//            zen_db_query("delete from " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " where products_options_id = '" . $_POST['option_id'] . "'");
// add in a record if none exists when option type is switched
          $check_type = $db->Execute("SELECT COUNT(products_options_id) AS count
                                      FROM " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . "
                                      WHERE products_options_id = " . (int)$_POST['option_id'] . "
                                      AND products_options_values_id = 0");
          if ($check_type->fields['count'] == 0) {
            $db->Execute("INSERT INTO " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " (products_options_values_to_products_options_id, products_options_id, products_options_values_id)
                          VALUES (NULL, '" . (int)$_POST['option_id'] . "', '" . (int)PRODUCTS_OPTIONS_VALUES_TEXT_ID . "')");
          }
          break;
        default:
// if switched from file or text remove 0
          $db->Execute("DELETE FROM " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . "
                        WHERE products_options_id = " . (int)$_POST['option_id'] . "
                        AND products_options_values_id = " . (int)PRODUCTS_OPTIONS_VALUES_TEXT_ID);
          break;
      }

// alert if possible duplicate
      $duplicate_option = '';
      for ($i = 0, $n = sizeof($languages); $i < $n; $i ++) {
        $option_name = zen_db_prepare_input($option_name_array[$languages[$i]['id']]);

        $check = $db->Execute("SELECT products_options_name
                               FROM " . TABLE_PRODUCTS_OPTIONS . "
                               WHERE language_id = " . (int)$languages[$i]['id'] . "
                               AND products_options_name = '" . zen_db_input($option_name) . "'");

        if ($check->RecordCount() > 1 && !empty($option_name)) {
          $duplicate_option .= ' <strong>' . strtoupper(zen_get_language_name($languages[$i]['id'])) . '</strong> : ' . $option_name;
        }
      }
      if (!empty($duplicate_option)) {
        $messageStack->add_session(ATTRIBUTE_POSSIBLE_OPTIONS_NAME_WARNING_DUPLICATE . ' ' . $option_id . ' - ' . $duplicate_option, 'caution');
      }

      zen_redirect(zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, $_SESSION['page_info'] . '&option_order_by=' . $option_order_by));
      break;
    case 'delete_option':
      $option_id = zen_db_prepare_input($_GET['option_id']);

      $remove_option_values = $db->Execute("SELECT products_options_id, products_options_values_id
                                            FROM " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . "
                                            WHERE products_options_id = " . (int)$option_id);

      foreach ($remove_option_values as $remove_option_value) {
        $zco_notifier->notify('OPTIONS_NAME_MANAGER_DELETE_OPTION', array('option_id' => $option_id, 'options_values_id' => (int)$remove_option_value['products_options_values_id']));
        $db->Execute("DELETE FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                      WHERE products_options_values_id = " . (int)$remove_option_value['products_options_values_id'] . "
                      AND products_options_values_id != 0");
      }

      $db->Execute("DELETE FROM " . TABLE_PRODUCTS_OPTIONS . "
                    WHERE products_options_id = " . (int)$option_id);

      $db->Execute("DELETE FROM " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . "
                    WHERE products_options_id = " . (int)$option_id);

      zen_redirect(zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, $_SESSION['page_info'] . '&option_order_by=' . $option_order_by));
      break;

/////////////////////////////////////
// additional features
    case 'update_options_values':
      // get products to update with at least one option_value for selected options_name
      $update_to = (int)$_GET['update_to'];
      $update_action = $_GET['update_action'];

      switch ($update_to) {
        case (0):
          // all products
          $all_update_products = $db->Execute("SELECT DISTINCT products_id
                                               FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
                                               WHERE options_id = " . (int)$_POST['options_id']);
          break;
        case (1):
          // one product
          $product_to_update = (int)$_POST['product_to_update'];
          $all_update_products = $db->Execute("SELECT DISTINCT products_id
                                               FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
                                               WHERE options_id = " . (int)$_POST['options_id'] . "
                                               AND products_id = " . $product_to_update);
          break;
        case (2):
          // category of products
          $category_to_update = (int)$_POST['category_to_update'];
// re-write with categories
          $all_update_products = $db->Execute("SELECT DISTINCT pa.products_id
                                               FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                               LEFT JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc ON pa.products_id = ptc.products_id
                                               WHERE ptc.categories_id = " . $category_to_update . "
                                               AND pa.options_id = " . (int)$_POST['options_id'] . "
                                               AND pa.products_id = ptc.products_id");
          break;
      }

      if ($all_update_products->RecordCount() < 1) {
        $messageStack->add_session(ERROR_PRODUCTS_OPTIONS_VALUES, 'caution');
      } else {
//die('I want to update ' . $_GET['update_to'] . ' : update action: ' . $update_action . ' product: ' . $_POST['product_to_update']  . ' category: ' . $_POST['category_to_update'] . ' found records: ' . $all_update_products->RecordCount() . ' - ' . $all_update_products->fields['products_id']);

        if ($update_action == 0) {
          // action add
          foreach ($all_update_products as $all_update_product) {
            // get all option_values
            $all_options_values = $db->Execute("SELECT products_options_id, products_options_values_id
                                                FROM " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . "
                                                WHERE products_options_id = " . (int)$_POST['options_id']);
            $updated = 'false';
            foreach ($all_options_values as $all_options_value) {
              $check_all_options_values = $db->Execute("SELECT products_attributes_id
                                                        FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
                                                        WHERE products_id = " . $all_update_product['products_id'] . "
                                                        AND options_id = " . (int)$all_options_value['products_options_id'] . "
                                                        AND options_values_id = " . (int)$all_options_value['products_options_values_id']);
              if ($check_all_options_values->RecordCount() < 1) {
                // add missing options_value_id
                $updated = 'true';
                $db->Execute("INSERT INTO " . TABLE_PRODUCTS_ATTRIBUTES . " (products_id, options_id, options_values_id)
                              VALUES ('" . (int)$all_update_product['products_id'] . "', '" . (int)$all_options_value['products_options_id'] . "', '" . (int)$all_options_value['products_options_values_id'] . "')");
              } else {
                // skip it the attribute is there
              }
            }
            if ($updated == 'true') {
              zen_update_attributes_products_option_values_sort_order($all_update_product['products_id']);
            }
          }
          if ($updated == 'true') {
            $messageStack->add_session(SUCCESS_PRODUCTS_OPTIONS_VALUES, 'success');
          } else {
            $messageStack->add_session(ERROR_PRODUCTS_OPTIONS_VALUES, 'error');
          }
        } else {
          // action delete
          foreach ($all_update_products as $all_update_product) {
            // get all option_values
            $all_options_values = $db->Execute("SELECT products_options_id, products_options_values_id
                                                FROM " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . "
                                                WHERE products_options_id = " . (int)$_POST['options_id']);
            $updated = 'false';
            foreach ($all_options_values as $all_options_value) {
              $check_all_options_values = $db->Execute("SELECT products_attributes_id
                                                        FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
                                                        WHERE products_id = " . (int)$all_update_product['products_id'] . "
                                                        AND options_id = " . (int)$all_options_value['products_options_id'] . "
                                                        AND options_values_id= " . (int)$all_options_value['products_options_values_id']);
              if ($check_all_options_values->RecordCount() >= 1) {
                // delete for this product with Option Name options_value_id
// echo '<br>This should be deleted: ' . zen_get_products_name($all_options_value['products_options_id']);
// change to delete
// should add download delete
                $db->Execute("DELETE FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
                              WHERE products_id = " . (int)$all_update_product['products_id'] . "
                              AND options_id = " . (int)$_POST['options_id']);
                $zco_notifier->notify('OPTIONS_NAME_MANAGER_UPDATE_OPTIONS_VALUES_DELETE', array(
                  'products_id' => $all_update_product['products_id'],
                  'options_id' => $all_options_value['products_options_id'],
                  'options_values_id' => $all_options_value['products_options_values_id'])
                );
              } else {
                // skip this option_name does not exist
              }
            }
          }
        } // update_action
      } // no products found
      zen_redirect(zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, $_SESSION['page_info'] . '&option_order_by=' . $option_order_by));
      break;
////////////////////////////////////
// copy features
    case 'copy_options_values':
      $options_id_from = (int)$_POST['options_id_from'];
      $options_id_to = (int)$_POST['options_id_to'];

      if ($options_id_from == $options_id_to) {
        // cannot copy to self
        $messageStack->add(ERROR_OPTION_VALUES_COPIED . ' from: ' . zen_options_name($options_id_from) . ' to: ' . zen_options_name($options_id_to), 'warning');
      } else {
        // successful copy
        $start_id = $db->Execute("SELECT products_options_values_id
                                  FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                                  ORDER BY products_options_values_id DESC LIMIT 1");
        $copy_from_values = $db->Execute("SELECT pov.*
                                          FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov
                                          LEFT JOIN " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " povtpo ON pov.products_options_values_id = povtpo.products_options_values_id
                                          WHERE povtpo.products_options_id = " . (int)$options_id_from . "
                                          ORDER BY povtpo.products_options_values_id");
        if ($copy_from_values->RecordCount() > 0) {
          // successful copy
          $next_id = ($start_id->fields['products_options_values_id'] + 1);
          while (!$copy_from_values->EOF) {
            $current_id = $copy_from_values->fields['products_options_values_id'];
            $sql = "INSERT INTO " . TABLE_PRODUCTS_OPTIONS_VALUES . " (products_options_values_id, language_id, products_options_values_name, products_options_values_sort_order)
                    VALUES ('" . (int)$next_id . "', '" . (int)$copy_from_values->fields['language_id'] . "', '" . $copy_from_values->fields['products_options_values_name'] . "', '" . (int)$copy_from_values->fields['products_options_values_sort_order'] . "')";
            $db->Execute($sql);
            $copy_from_values->MoveNext();
            if ($copy_from_values->fields['products_options_values_id'] != $current_id || $copy_from_values->EOF) {
              $sql = "INSERT INTO " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " (products_options_id, products_options_values_id)
                      VALUES ('" . (int)$options_id_to . "', '" . (int)$next_id . "')";
              $db->Execute($sql);
              $next_id++;
            }
          }
          $messageStack->add(SUCCESS_OPTION_VALUES_COPIED . ' from: ' . zen_options_name($options_id_from) . ' to: ' . zen_options_name($options_id_to), 'success');
        } else {
          // warning nothing to copy
          $messageStack->add(ERROR_OPTION_VALUES_NONE . ' from: ' . zen_options_name($options_id_from) . ' to: ' . zen_options_name($options_id_to), 'warning');
        }
      }
      break;
////////////////////////////////////
  }
}

//iii 031103 added to get results from database option type query
$products_options_types_list = array();
//  $products_options_type_array = $db->Execute("select products_options_types_id, products_options_types_name from " . TABLE_PRODUCTS_OPTIONS_TYPES . " where language_id='" . $_SESSION['languages_id'] . "' order by products_options_types_id");
$products_options_type_array = $db->Execute("SELECT products_options_types_id, products_options_types_name
                                             FROM " . TABLE_PRODUCTS_OPTIONS_TYPES . "
                                             ORDER BY products_options_types_id");
foreach ($products_options_type_array as $products_options_type) {
  $products_options_types_list[$products_options_type['products_options_types_id']] = $products_options_type['products_options_types_name'];
}

$optionTypeValuesArray = [];
foreach ($products_options_types_list as $id => $text) {
  $optionTypeValuesArray[] = array(
    'id' => $id,
    'text' => $text
  );
}

//CLR 030312 add function to translate type_id to name
// Translate option_type_values to english string
//iii 031103 modified to use results of database option type query from above
function translate_type_to_name($opt_type) {
  global $products_options_types_list;
  return $products_options_types_list[$opt_type];
}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta charset="<?php echo CHARSET; ?>">
    <title><?php echo TITLE; ?></title>
    <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
    <link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
    <script src="includes/menu.js"></script>
    <script>
      function go_option() {
          if ($('#sortOrder').val() != 'none') {
              location = '<?php echo zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, 'option_page=' . (!empty($_GET['option_page']) ? $_GET['option_page'] : 1)); ?>&option_order_by=' + $('#sortOrder').val();
          }
      }
    </script>
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
  <body onLoad="init()">
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->
    <div class="container-fluid">
      <!-- body //-->
      <!-- body_text //-->
      <h1><?php echo HEADING_TITLE_OPT; ?></h1>
      <!-- options and values//-->
      <div class="row">
        <div class="col-sm-4">
          <a href="<?php echo zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER) ?>" class="btn btn-default" role="button"><?php echo IMAGE_EDIT_ATTRIBUTES; ?></a>&nbsp;
          <a href="<?php echo zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER) ?>" class="btn btn-default" role="button"><?php echo IMAGE_OPTION_VALUES; ?></a>
        </div>
        <div class="col-sm-4">
            <?php
// toggle switch for show copier features
            $option_names_values_copier_array = array(
              array('id' => '0', 'text' => TEXT_SHOW_OPTION_NAMES_VALUES_COPIER_OFF),
              array('id' => '1', 'text' => TEXT_SHOW_OPTION_NAMES_VALUES_COPIER_ON),
            );
            echo zen_draw_form('set_option_names_values_copier_form', FILENAME_OPTIONS_NAME_MANAGER, '', 'get', 'class="form-horizontal"');
            echo zen_draw_pull_down_menu('reset_option_names_values_copier', $option_names_values_copier_array, $reset_option_names_values_copier, 'onChange="this.form.submit();" class="form-control"');
            echo zen_hide_session_id();
            echo zen_draw_hidden_field('action', 'set_option_names_values_copier');
            echo '</form>';
            ?>
        </div>
        <div class="col-sm-4 text-right"><?php echo TEXT_PRODUCT_OPTIONS_INFO; ?></div>
      </div>
      <!-- options //-->
      <?php
      if ($action == 'delete_product_option') { // delete product option
        $options = $db->Execute("SELECT products_options_id, products_options_name
                                 FROM " . TABLE_PRODUCTS_OPTIONS . "
                                 WHERE products_options_id = " . (int)$_GET['option_id'] . "
                                 AND language_id = " . (int)$_SESSION['languages_id']);
        ?>
        <div class="table-responsive">
          <table class="table table-striped">
            <tr>
              <td colspan="3" class="pageHeading"><?php echo $options->fields['products_options_name']; ?></td>
            </tr>
            <?php
            $products = $db->Execute("SELECT p.products_id, pd.products_name, pov.products_options_values_name
                                      FROM " . TABLE_PRODUCTS . " p,
                                           " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov,
                                           " . TABLE_PRODUCTS_ATTRIBUTES . " pa,
                                           " . TABLE_PRODUCTS_DESCRIPTION . " pd
                                      WHERE pd.products_id = p.products_id
                                      AND pov.language_id = " . (int)$_SESSION['languages_id'] . "
                                      AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
                                      AND pa.products_id = p.products_id
                                      AND pa.options_id = " . (int)$_GET['option_id'] . "
                                      AND pov.products_options_values_id = pa.options_values_id
                                      ORDER BY pd.products_name");

            if ($products->RecordCount() > 0) {
              ?>

              <?php
// extra cancel
              if ($products->RecordCount() > 10) {
                ?>
                <tr>
                  <td colspan="3"><?php echo zen_black_line(); ?></td>
                </tr>
                <tr>
                  <td colspan="2"><?php echo '<strong>' . TEXT_OPTION_NAME . ':</strong> ' . zen_options_name((int)$_GET['option_id']) . '<br />' . TEXT_WARNING_OF_DELETE; ?></td>
                  <td class="text-right">
                    <a href="<?php echo zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&option_order_by=' . $option_order_by); ?>" class="btn btn-default" role="button"><?php echo TEXT_CANCEL; ?></a>
                  </td>
                </tr>
                <?php
              }
              ?>
              <tr class="dataTableHeadingRow">
                <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_ID; ?></th>
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCT; ?></th>
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_OPT_VALUE; ?></th>
              </tr>
              <tr>
                <td colspan="3"><?php echo zen_black_line(); ?></td>
              </tr>
              <?php
              foreach ($products as $product) {
                ?>
                <tr>
                  <td class="text-center"><?php echo $product['products_id']; ?></td>
                  <td><?php echo $product['products_name']; ?></td>
                  <td><?php echo $product['products_options_values_name']; ?></td>
                </tr>
                <?php
              }
              ?>
              <tr>
                <td colspan="3"><?php echo zen_black_line(); ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo TEXT_WARNING_OF_DELETE; ?></td>
                <td class="text-right">
                  <a href="<?php echo zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&option_order_by=' . $option_order_by); ?>" class="btn btn-default" role="button"><?php echo TEXT_CANCEL; ?></a>
                </td>
              </tr>
              <tr>
                <td colspan="3"><?php echo zen_black_line(); ?></td>
              </tr>
            </table>
            <?php
          } else {
            ?>
            <table class="table table-striped table-condensed">
              <tr>
                <td><?php echo '<strong>' . TEXT_OPTION_NAME . ':</strong> ' . zen_options_name((int)$_GET['option_id']) . '<br />' . TEXT_OK_TO_DELETE; ?></td>
              </tr>
              <tr>
                <td class="text-right">
                  <a href="<?php echo zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, 'action=delete_option&option_id=' . $_GET['option_id'] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&option_order_by=' . $option_order_by); ?>" class="btn btn-danger" role="button"><?php echo IMAGE_DELETE; ?></a>
                  <a href="<?php echo zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, (isset($_GET['order_by']) ? 'order_by=' . $_GET['order_by'] . '&' : '') . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&option_order_by=' . $option_order_by); ?>" class="btn btn-default" role="button"><?php echo TEXT_CANCEL; ?></a>
                </td>
              </tr>
            </table>
            <?php
          }
          ?>
          <?php
        } else {
          if (isset($_GET['option_order_by'])) {
            $option_order_by = $_GET['option_order_by'];
          } else {
            $option_order_by = 'products_options_id';
          }
          ?>
          <div class="row">
              <?php echo zen_draw_separator('pixel_trans.gif', '100%', '5'); ?>
          </div>
          <div class="row text-center">
            <div class="col-sm-offset-5 col-sm-2">
                <?php echo zen_draw_form('option_order_by', FILENAME_OPTIONS_NAME_MANAGER, 'option_order_by=' . $option_order_by, 'post', 'class="form-horizontal"'); ?>
              <select name="selected" onchange="go_option()" class="form-control" id="sortOrder">
                <option value="products_options_id"<?php echo ($option_order_by == 'products_options_id' ? ' selected' : ''); ?>><?php echo TEXT_OPTION_ID; ?></option>
                <option value="products_options_name"<?php echo ($option_order_by == 'products_options_name' ? ' selected' : ''); ?>><?php echo TEXT_OPTION_NAME; ?></option>
              </select>
              <?php echo '</form>'; ?>
            </div>
          </div>
          <div class="row">
              <?php
              $options_query = "SELECT *
                                FROM " . TABLE_PRODUCTS_OPTIONS . "
                                WHERE language_id = :languageId:
                                ORDER BY :optionOrderBy:";
              $options_bind_vars1 = $db->bindVars($options_query, ':languageId:', $_SESSION['languages_id'], 'integer');
              $options_bind_vars2 = $db->bindVars($options_bind_vars1, ':optionOrderBy:', $option_order_by, 'noquotestring');
              if (!isset($_GET['option_page'])) {
                $_GET['option_page'] = 1;
              }
              $prev_option_page = $_GET['option_page'] - 1;
              $next_option_page = $_GET['option_page'] + 1;

              $options = $db->Execute($options_bind_vars2);
              $num_rows = $options->RecordCount();

              $per_page = (MAX_ROW_LISTS_OPTIONS == '') ? $num_rows : (int)MAX_ROW_LISTS_OPTIONS;
              $option_page_start = ($per_page * $_GET['option_page']) - $per_page;
              if ($num_rows <= $per_page) {
                $num_pages = 1;
              } else if (($num_rows % $per_page) == 0) {
                $num_pages = ($num_rows / $per_page);
              } else {
                $num_pages = ($num_rows / $per_page) + 1;
              }
              $num_pages = (int)$num_pages;

// fix limit error on some versions
              if ($option_page_start < 0) {
                $option_page_start = 0;
              }

              $optionsLimit = $options_bind_vars2 . " LIMIT $option_page_start, $per_page";
              ?>
              <?php if ($num_pages > 1) { ?>
              <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm">
                    <?php
                    // First
                    if ($_GET['option_page'] != '1') {
                      echo '<li><a href="' . zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, 'option_page=1' . '&option_order_by=' . $option_order_by) . '" aria-label="First"  title="' . PREVNEXT_TITLE_FIRST_PAGE . '"><i class="fa fa-angle-double-left" aria-hidden="true""></i></a></li>';
                    } else {
                      echo '<li class="disabled"><a href="#" aria-label="First"><i class="fa fa-angle-double-left" aria-hidden="true""></i></a></li>';
                    }
                    // Previous
                    if ($prev_option_page) {
                      echo '<li><a href="' . zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, 'option_page=' . $prev_option_page . '&option_order_by=' . $option_order_by) . '"><i class="fa fa-angle-left" aria-hidden="true"></i></a></li>';
                    } else {
                      echo '<li class="disabled"><a href="#" aria-label="Previous"><i class="fa fa-angle-left" aria-hidden="true""></i></a></li>';
                    }

                    for ($i = 1; $i <= $num_pages; $i++) {
                      if ($i != $_GET['option_page']) {
                        echo '<li><a href="' . zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, 'option_page=' . $i . '&option_order_by=' . $option_order_by) . '">' . $i . '</a></li>';
                      } else {
                        echo '<li class="active"><a href="#">' . $i . '</a></li>';
                      }
                    }

                    // Next and Last
                    if ($_GET['option_page'] != $num_pages) {
                      echo '<li><a href="' . zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, 'option_page=' . $next_option_page . '&option_order_by=' . $option_order_by) . '"><i class="fa fa-angle-right" aria-hidden="true"></i></a></li>';
                      echo '<li><a href="' . zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, 'option_page=' . $num_pages . '&option_order_by=' . $option_order_by) . '" aria-label="Last" title="' . PREVNEXT_TITLE_LAST_PAGE . '"><i class="fa fa-angle-double-right" aria-hidden="true""></i></a></li>';
                    } else {
                      echo '<li class="disabled"><a href="#" aria-label="Next"><i class="fa fa-angle-right" aria-hidden="true""></i></a></li>';
                      echo '<li class="disabled"><a href="#" aria-label="Last"><i class="fa fa-angle-double-right" aria-hidden="true""></i></a></li>';
                    }
                    ?>
                </ul>
              </nav>
              <?php
            }
            ?>
          </div>
          <table class="table table-striped">
            <tr>
              <td colspan="7"><?php echo zen_black_line(); ?></td>
            </tr>
            <tr class="dataTableHeadingRow">
              <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_ID; ?></td>
              <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_OPT_NAME; ?></td>
              <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_OPT_TYPE; ?></td>
              <td class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_OPTION_SORT_ORDER; ?></td>
              <td class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_OPTION_VALUE_SIZE; ?></td>
              <td class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_OPTION_VALUE_MAX; ?></td>
              <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_ACTION; ?></td>
            </tr>
            <tr>
              <td colspan="7"><?php echo zen_black_line(); ?></td>
            </tr>
            <?php
            $next_id = 1;
            $options_values = $db->Execute($optionsLimit);
            foreach ($options_values as $options_value) {
              ?>
              <tr>
                  <?php
// edit option name
                  if (($action == 'update_option') && ($_GET['option_id'] == $options_value['products_options_id'])) {
                    echo zen_draw_form('option', FILENAME_OPTIONS_NAME_MANAGER, 'action=update_option_name' . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&option_order_by=' . $option_order_by, 'post', 'class="form-horizontal"');
                    $option_name_input = '';
                    $sort_order_input = '';
                    $inputs2 = '';
                    for ($i = 0, $n = sizeof($languages); $i < $n; $i ++) {
                      $option_name = $db->Execute("SELECT products_options_name, products_options_sort_order, products_options_size, products_options_length, products_options_comment, products_options_images_per_row, products_options_images_style, products_options_rows
                                                   FROM " . TABLE_PRODUCTS_OPTIONS . "
                                                   WHERE products_options_id = " . (int)$options_value['products_options_id'] . "
                                                   AND language_id = " . (int)$languages[$i]['id']);

                      $option_name_input .= zen_draw_label($languages[$i]['code'], 'option_name[' . $languages[$i]['id'] . ']', 'class="control-label"') . ': ';
                      $option_name_input .= zen_draw_input_field('option_name[' . $languages[$i]['id'] . ']', zen_output_string($option_name->fields['products_options_name']), zen_set_field_length(TABLE_PRODUCTS_OPTIONS, 'products_options_name', 40) . 'class="form-control"');
                      ($i + 1 < $n ? $option_name_input .= '<br>' : '');

                      $sort_order_input .= zen_draw_label(TEXT_SORT, 'products_options_sort_order[' . $languages[$i]['id'] . ']', 'class="control-label"');
                      $sort_order_input .= zen_draw_input_field('products_options_sort_order[' . $languages[$i]['id'] . ']', $option_name->fields['products_options_sort_order'], 'size="3" class="form-control"');
                      ($i + 1 < $n ? $sort_order_input .= '<br>' : '');

                      $inputs2 .= '<h4>' . $languages[$i]['code'] . ':</h4>';
                      $inputs2 .= '<div class="row">';
                      $inputs2 .= '<div class="col-sm-12">';
                      $inputs2 .= zen_draw_label(TEXT_OPTION_VALUE_COMMENTS, 'products_options_comment[' . $languages[$i]['id'] . ']', 'class="control-label"') . zen_draw_input_field('products_options_comment[' . $languages[$i]['id'] . ']', $option_name->fields['products_options_comment'], 'size="50" class="form-control"');
                      $inputs2 .= '</div>';
                      $inputs2 .= '</div>';
                      $inputs2 .= '<div class="row">';
                      $inputs2 .= '<div class="col-sm-4">';
                      $inputs2 .= zen_draw_label(TEXT_OPTION_VALUE_ROWS, 'products_options_rows[' . $languages[$i]['id'] . ']', 'class="control-label"') . zen_draw_input_field('products_options_rows[' . $languages[$i]['id'] . ']', $option_name->fields['products_options_rows'], 'size="3" class="form-control"');
                      $inputs2 .= '</div>';
                      $inputs2 .= '<div class="col-sm-4">';
                      $inputs2 .= zen_draw_label(TEXT_OPTION_VALUE_SIZE, 'products_options_size[' . $languages[$i]['id'] . ']', 'class="control-label"') . zen_draw_input_field('products_options_size[' . $languages[$i]['id'] . ']', $option_name->fields['products_options_size'], 'size="3" class="form-control"');
                      $inputs2 .= '</div>';
                      $inputs2 .= '<div class="col-sm-4">';
                      $inputs2 .= zen_draw_label(TEXT_OPTION_VALUE_MAX, 'products_options_length[' . $languages[$i]['id'] . ']', 'class="control-label"') . zen_draw_input_field('products_options_length[' . $languages[$i]['id'] . ']', $option_name->fields['products_options_length'], 'size="3" class="form-control"');
                      $inputs2 .= '</div>';
                      $inputs2 .= '</div>';
                      $inputs2 .= '<div class="row">';
                      $inputs2 .= '<div class="col-sm-4">';
                      $inputs2 .= zen_draw_label(TEXT_OPTION_ATTRIBUTE_IMAGES_PER_ROW, 'products_options_images_per_row[' . $languages[$i]['id'] . ']', 'class="control-label"') . zen_draw_input_field('products_options_images_per_row[' . $languages[$i]['id'] . ']', $option_name->fields['products_options_images_per_row'], 'size="3" class="form-control"');
                      $inputs2 .= '</div>';
                      $inputs2 .= '<div class="col-sm-4">';
                      $inputs2 .= zen_draw_label(TEXT_OPTION_ATTRIBUTE_IMAGES_STYLE, 'products_options_images_style[' . $languages[$i]['id'] . ']', 'class="control-label"') . zen_draw_input_field('products_options_images_style[' . $languages[$i]['id'] . ']', $option_name->fields['products_options_images_style'], 'size="3" class="form-control"');
                      $inputs2 .= '</div>';
                      $inputs2 .= '</div>';
                    }

//CLR 030212 - Add column for option type
                    ?>
                  <td class="text-center">
                      <?php echo $options_value['products_options_id']; ?>
                      <?php echo zen_draw_hidden_field('option_id', $options_value['products_options_id']); ?>
                  </td>
                  <td><?php echo $option_name_input; ?></td>
                  <td><?php echo $sort_order_input; ?></td>
                  <td><?php echo zen_draw_pull_down_menu('option_type', $optionTypeValuesArray, $options_value['products_options_type'], 'class="form-control"'); ?></td>
                  <td colspan="2">&nbsp;</td>
                  <td class="text-center">
                    <button type="submit" class="btn btn-primary"><?php echo IMAGE_UPDATE; ?></button>
                    <a href="<?php echo zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&option_order_by=' . $option_order_by); ?>" class="btn btn-default" role="button"><?php echo TEXT_CANCEL; ?></a>
                  </td>
                </tr>
                <tr>
                  <td colspan="7"><?php echo zen_draw_separator('pixel_black.gif', '100%', '2'); ?></td>
                </tr>

                <tr>
                  <td>&nbsp;</td>
                  <td colspan="6">
                    <div class="row">
                      <div class="col-sm-12"><?php echo TEXT_OPTION_ATTIBUTE_MAX_LENGTH; ?></div>
                    </div>
                    <?php echo $inputs2; ?>
                  </td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td colspan="6">
                      <?php
                      echo TEXT_OPTION_IMAGE_STYLE . '<br />' .
                      TEXT_OPTION_ATTRIBUTE_IMAGES_STYLE_0 . '<br />' .
                      TEXT_OPTION_ATTRIBUTE_IMAGES_STYLE_1 . '<br />' .
                      TEXT_OPTION_ATTRIBUTE_IMAGES_STYLE_2 . '<br />' .
                      TEXT_OPTION_ATTRIBUTE_IMAGES_STYLE_3 . '<br />' .
                      TEXT_OPTION_ATTRIBUTE_IMAGES_STYLE_4 . '<br />' .
                      TEXT_OPTION_ATTRIBUTE_IMAGES_STYLE_5 . '<br />';
                      ?>
                  </td>
                </tr>
                <tr>
                  <td colspan="7"><?php echo zen_draw_separator('pixel_black.gif', '100%', '2'); ?></td>
                </tr>
                <?php
                echo '</form>' . "\n";
              } else {
//CLR 030212 - Add column for option type
                ?>
                <tr>
                  <td class="text-center"><?php echo $options_value["products_options_id"]; ?></td>
                  <td><?php echo $options_value["products_options_name"]; ?></td>
                  <td><?php echo translate_type_to_name($options_value["products_options_type"]); ?></td>
                  <td class="text-right"><?php echo $options_value["products_options_sort_order"]; ?></td>
                  <td class="text-right"><?php echo $options_value["products_options_size"]; ?></td>
                  <td class="text-right"><?php echo $options_value["products_options_length"]; ?></td>
                  <?php
// hide buttons when editing
                  if ($action == 'update_option') {
                    ?>
                    <td>&nbsp;</td>
                    <?php
                  } else {
                    ?>
                    <td>
                      <a href="<?php echo zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, 'action=update_option&option_id=' . $options_value['products_options_id'] . '&option_order_by=' . $option_order_by . '&option_page=' . $_GET['option_page'] . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '')); ?>" class="btn btn-primary" role="button"><?php echo IMAGE_UPDATE; ?>
                      </a>&nbsp;&nbsp;
                      <a href="<?php echo zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, 'action=delete_product_option&option_id=' . $options_value['products_options_id'] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . '&option_order_by=' . $option_order_by, 'NONSSL'); ?>" class="btn btn-default" role="button"><?php echo IMAGE_DELETE; ?></a>
                    </td>
                    <?php
                  }
                  ?>
                </tr>
                <?php
              }
              ?>
              <?php
              $max_options_id_values = $db->Execute("SELECT MAX(products_options_id) + 1 AS next_id
                                                     FROM " . TABLE_PRODUCTS_OPTIONS);

              $next_id = $max_options_id_values->fields['next_id'];
            }
            ?>
            <tr>
              <td colspan="7"><?php echo zen_black_line(); ?></td>
            </tr>
            <?php
// add option name
            if ($action != 'update_option') {
              ?>
              <tr>
                  <?php
                  echo zen_draw_form('options', FILENAME_OPTIONS_NAME_MANAGER, 'action=add_product_options' . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&option_order_by=' . $option_order_by, 'post', 'class="form-horizontal"');
                  echo zen_draw_hidden_field('products_options_id', $next_id);
                  $inputs = '';
                  $inputs2 = '';
                  for ($i = 0, $n = sizeof($languages); $i < $n; $i ++) {
                    $inputs .= zen_draw_label($languages[$i]['code'], 'option_name[' . $languages[$i]['id'] . ']', 'class="control-label"');
                    $inputs .= zen_draw_input_field('option_name[' . $languages[$i]['id'] . ']', '', zen_set_field_length(TABLE_PRODUCTS_OPTIONS, 'products_options_name', 40) . 'class="form-control"');
                    ($i + 1 < $n ? $inputs .= '<br>' : '');
                    $inputs2 .= zen_draw_label(TEXT_SORT, 'products_options_sort_order[' . $languages[$i]['id'] . ']');
                    $inputs2 .= zen_draw_input_field('products_options_sort_order[' . $languages[$i]['id'] . ']', '', 'size="3" class="form-control"');
                    ($i + 1 < $n ? $inputs2 .= '<br>' : '');
                  }
//CLR 030212 - Add column for option type
                  ?>
                <td class="text-center"><?php echo $next_id; ?></td>
                <td><?php echo $inputs; ?></td>
                <td><?php echo $inputs2; ?></td>
                <td><?php echo zen_draw_pull_down_menu('option_type', $optionTypeValuesArray, '', 'class="form-control"'); ?></td>
                <td colspan="2">&nbsp;</td>
                <td>
                  <button type="submit" class="btn btn-primary"><?php echo IMAGE_INSERT; ?></button>
                </td>
                <?php
                echo '</form>';
                ?>
              </tr>
              <tr>
                <td colspan="7"><?php echo zen_black_line(); ?></td>
              </tr>
              <?php
            }
          }
          ?>
        </table>
        <!-- options eof //-->
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
            'text' => $options_value['products_options_name']);
        }
        ?>
        <?php if ($_SESSION['option_names_values_copier'] == '0') { ?>
          <div class="row pageHeading text-center">
              <?php echo TEXT_INFO_OPTION_NAMES_VALUES_COPIER_STATUS; ?>
          </div>
        <?php } else { ?>
          <div class="row pageHeading text-center"><span class="alert"><?php echo TEXT_WARNING_BACKUP; ?></span></div>
          <!-- ADD - additional features //-->
          <div style="border: 2px solid #999;">
            <table class="table table-striped">

              <!-- bof: add all option values to products with current Option Name -->
              <tr>
                <td colspan="3"><?php echo TEXT_OPTION_VALUE_ADD_ALL; ?><br><?php echo TEXT_INFO_OPTION_VALUE_ADD_ALL; ?></td>
              </tr>
              <tr>
                  <?php echo zen_draw_form('quick_jump', FILENAME_OPTIONS_NAME_MANAGER, 'action=update_options_values&update_to=0&update_action=0' . '&option_order_by=' . $option_order_by, 'post', 'class="form-horizontal"'); ?>
                <td>
                    <?php echo zen_draw_label(TEXT_SELECT_OPTION, 'options_id', 'class="control-label"'); ?>
                    <?php echo zen_draw_pull_down_menu('options_id', $optionsValuesArray, '', 'class="form-control"'); ?>
                </td>
                <td>&nbsp;</td>
                <td>
                  <button type="submit" class="btn btn-warning"><?php echo IMAGE_UPDATE; ?></button>
                </td>
                <?php echo '</form>'; ?>
              </tr>
              <tr>
                <td colspan="3"><?php echo TEXT_OPTION_VALUE_ADD_PRODUCT; ?><br><?php echo TEXT_INFO_OPTION_VALUE_ADD_PRODUCT; ?></td>
              </tr>
              <tr>
                  <?php echo zen_draw_form('quick_jump', FILENAME_OPTIONS_NAME_MANAGER, 'action=update_options_values&update_to=1&update_action=0' . '&option_order_by=' . $option_order_by, 'post', 'class="form-horizontal"'); ?>
                <td>
                    <?php echo zen_draw_label(TEXT_SELECT_OPTION, 'options_id', 'class="control-label"'); ?>
                    <?php echo zen_draw_pull_down_menu('options_id', $optionsValuesArray, '', 'class="form-control"'); ?>
                </td>
                <td>
                    <?php echo zen_draw_label(TEXT_SELECT_PRODUCT, '', 'class="control-label"'); ?>
                    <?php echo zen_draw_products_pull_down_attributes('product_to_update', 'size="5" class="form-control"'); ?>
                </td>
                <td>
                  <button type="submit" class="btn btn-warning"><?php echo IMAGE_UPDATE; ?></button>
                </td>
                <?php echo '</form>'; ?>
              </tr>
              <tr>
                <td colspan="3"><?php echo TEXT_OPTION_VALUE_ADD_CATEGORY; ?><br><?php echo TEXT_INFO_OPTION_VALUE_ADD_CATEGORY; ?></td>
              </tr>
              <tr>
                  <?php echo zen_draw_form('quick_jump', FILENAME_OPTIONS_NAME_MANAGER, 'action=update_options_values&update_to=2&update_action=0' . '&option_order_by=' . $option_order_by, 'post', 'class="form-horizontal"'); ?>
                <td>
                    <?php echo zen_draw_label(TEXT_SELECT_OPTION, 'options_id', 'class="control-label"'); ?>
                    <?php echo zen_draw_pull_down_menu('options_id', $optionsValuesArray, '', 'class="form-control"'); ?>
                </td>
                <td>
                    <?php echo zen_draw_label(TEXT_SELECT_CATEGORY, 'category_to_update', 'class="control-label"'); ?>
                    <?php echo zen_draw_products_pull_down_categories('category_to_update', 'size="5" class="form-control"', '', true, true); ?>
                </td>
                <td>
                  <button type="submit" class="btn btn-warning"><?php echo IMAGE_UPDATE; ?></button>
                </td>
                <?php echo '</form>'; ?>
              </tr>
              <tr>
                <td colspan="3"><?php echo TEXT_COMMENT_OPTION_VALUE_ADD_ALL; ?></td>
              </tr>
              <!-- eof: add all option values to products with current Option Name -->

            </table>
          </div>
          <!-- ADD - additional features eof //-->
          <div class="row">
              <?php echo zen_draw_separator('pixel_trans.gif', '100%', '5'); ?>
          </div>
          <!-- DELETE - additional features //-->

          <div style="border: 2px solid #999;">
            <table class="table table-striped">

              <!-- bof: delete all option values to products with current Option Name -->
              <tr>
                <td colspan="3"><?php echo TEXT_OPTION_VALUE_DELETE_ALL; ?><br><?php echo TEXT_INFO_OPTION_VALUE_DELETE_ALL; ?></td>
              </tr>
              <tr>
                  <?php echo zen_draw_form('quick_jump', FILENAME_OPTIONS_NAME_MANAGER, 'action=update_options_values&update_to=0&update_action=1' . '&option_order_by=' . $option_order_by, 'post', 'class="form-horizontal"'); ?>
                <td>
                    <?php echo zen_draw_label(TEXT_SELECT_OPTION, 'options_id', 'class="control-label"'); ?>
                    <?php echo zen_draw_pull_down_menu('options_id', $optionsValuesArray, '', 'class="form-control"'); ?>
                </td>
                <td>&nbsp;</td>
                <td>
                  <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> <?php echo IMAGE_DELETE; ?></button>
                </td>
                <?php echo '</form>'; ?>
              </tr>
              <tr>
                <td colspan="3"><?php echo TEXT_OPTION_VALUE_DELETE_PRODUCT; ?><br><?php echo TEXT_INFO_OPTION_VALUE_DELETE_PRODUCT; ?></td>
              </tr>
              <tr>
                  <?php echo zen_draw_form('quick_jump', FILENAME_OPTIONS_NAME_MANAGER, 'action=update_options_values&update_to=1&update_action=1' . '&option_order_by=' . $option_order_by, 'post', 'class="form-horizontal"'); ?>
                <td>
                    <?php echo zen_draw_label(TEXT_SELECT_OPTION, 'options_id', 'class="control-label"'); ?>
                    <?php echo zen_draw_pull_down_menu('options_id', $optionsValuesArray, '', 'class="form-control"'); ?>
                </td>
                <td>
                    <?php echo zen_draw_label(TEXT_SELECT_PRODUCT, 'product_to_update', 'class="control-label"'); ?>
                    <?php echo zen_draw_products_pull_down_attributes('product_to_update', 'size="5" class="form-control"'); ?>
                </td>
                <td>
                  <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> <?php echo IMAGE_DELETE; ?></button>
                </td>
                <?php echo '</form>'; ?>
              </tr>

              <tr>
                <td colspan="3"><?php echo TEXT_OPTION_VALUE_DELETE_CATEGORY; ?><br><?php echo TEXT_INFO_OPTION_VALUE_DELETE_CATEGORY; ?></td>
              </tr>
              <tr>
                  <?php echo zen_draw_form('quick_jump', FILENAME_OPTIONS_NAME_MANAGER, 'action=update_options_values&update_to=2&update_action=1' . '&option_order_by=' . $option_order_by, 'post', 'class="form-horizontal"'); ?>
                <td>
                    <?php echo zen_draw_label(TEXT_SELECT_OPTION, 'options_id', 'class="control-label"'); ?>
                    <?php echo zen_draw_pull_down_menu('options_id', $optionsValuesArray, '', 'class="form-control"'); ?>
                </td>
                <td>
                    <?php echo zen_draw_label(TEXT_SELECT_CATEGORY, 'category_to_update', 'class="control-label"'); ?>
                    <?php echo zen_draw_products_pull_down_categories('category_to_update', 'size="5" class="form-control"', '', true, true); ?>
                </td>

                <td>
                  <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> <?php echo IMAGE_DELETE; ?></button>
                </td>
                <?php echo '</form>'; ?>
              </tr>
              <tr>
                <td colspan="3"><?php echo TEXT_COMMENT_OPTION_VALUE_DELETE_ALL; ?></td>
              </tr>
              <!-- eof: delete all option values to products with current Option Name -->

            </table>
          </div>
          <!-- DELETE - additional features eof //-->
          <div class="row"><?php echo zen_draw_separator('pixel_trans.gif', '100%', '5'); ?></div>
          <!-- COPY - additional features //-->
          <div style="border: 2px solid #999;">
            <table class="table table-striped">

              <!-- bof: copy all option values to another Option Name -->
              <tr>
                <td colspan="3"><?php echo TEXT_OPTION_VALUE_COPY_ALL; ?><br><?php echo TEXT_INFO_OPTION_VALUE_COPY_ALL; ?></td>
              </tr>
              <tr>
                  <?php echo zen_draw_form('quick_jump', FILENAME_OPTIONS_NAME_MANAGER, 'action=copy_options_values' . '&option_order_by=' . $option_order_by, 'post', 'class="form-horizontal"'); ?>
                <td>
                    <?php echo zen_draw_label(TEXT_SELECT_OPTION_FROM, 'options_id_from', 'class="control-label"'); ?>
                    <?php echo zen_draw_pull_down_menu('options_id_from', $optionsValuesArray, '', 'class="form-control"'); ?>
                </td>
                <td>
                    <?php echo zen_draw_label(TEXT_SELECT_OPTION_TO, 'options_id_to', 'class="control-label"'); ?>
                    <?php echo zen_draw_pull_down_menu('options_id_to', $optionsValuesArray, '', 'class="form-control"'); ?>
                </td>
                <td>
                  <button type="submit" class="btn btn-primary"><i class="fa fa-copy"></i> <?php echo IMAGE_COPY; ?></button>
                </td>
                <?php echo '</form>'; ?>
              </tr>
              <!-- eof: copy all option values to another Option Name -->
            </table>
          </div>
        <?php } // show copier features   ?>
      </div>
    </div>
    <!-- body_text_eof //-->
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
