<?php
/**
 * @package admin
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Tue Jul 31 11:39:58 2012 -0400 Modified in v1.5.1 $
 */

  require('includes/application_top.php');
  $languages = zen_get_languages();

  $currencies = new currencies();

  // check for damaged database, caused by users indiscriminately deleting table data
  $ary = array();
  $chk_option_values = $db->Execute("select * from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where products_options_values_id=" . (int)PRODUCTS_OPTIONS_VALUES_TEXT_ID);
  while (!$chk_option_values->EOF) {
    $ary[] = $chk_option_values->fields['language_id'];
    $chk_option_values->MoveNext();
  }
  for ($i=0, $n=sizeof($languages); $i<$n; $i ++) {
    if ((int)$languages[$i]['id'] > 0 && !in_array((int)$languages[$i]['id'], $ary)) {
      $db->Execute("INSERT INTO " . TABLE_PRODUCTS_OPTIONS_VALUES . " (products_options_values_id, language_id, products_options_values_name) VALUES (" . (int)PRODUCTS_OPTIONS_VALUES_TEXT_ID . ", " . (int)$languages[$i]['id'] . ", 'TEXT')");
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
    if (isset($_GET['option_page'])) $_SESSION['page_info'] .= 'option_page=' . $_GET['option_page'] . '&';
    if (isset($_GET['value_page'])) $_SESSION['page_info'] .= 'value_page=' . $_GET['value_page'] . '&';
    if (isset($_GET['attribute_page'])) $_SESSION['page_info'] .= 'attribute_page=' . $_GET['attribute_page'] . '&';
    if (zen_not_null($_SESSION['page_info'])) {
      $_SESSION['page_info'] = substr($_SESSION['page_info'], 0, -1);
    }

    switch ($action) {
      case 'set_option_names_values_copier':
        $_SESSION['option_names_values_copier'] = $_GET['reset_option_names_values_copier'];
        $action='';
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

        for ($i=0, $n=sizeof($languages); $i<$n; $i ++) {
          $option_name = zen_db_prepare_input($option_name_array[$languages[$i]['id']]);

          $db->Execute("insert into " . TABLE_PRODUCTS_OPTIONS . "
                      (products_options_id, products_options_name, language_id, products_options_sort_order, products_options_type, products_options_images_per_row, products_options_images_style, products_options_rows)
                      values ('" . (int)$products_options_id . "',
                              '" . zen_db_input($option_name) . "',
                              '" . (int)$languages[$i]['id'] . "',
                              '" . (int)$products_options_sort_order[$languages[$i]['id']] . "',
                              '" . (int)zen_db_input($option_type) . "',
                              '" . (int)zen_db_input($products_options_images_per_row) . "',
                              '" . (int)zen_db_input($products_options_images_style) . "',
                              '" . (int)(($products_options_rows <= 1 and $option_type == PRODUCTS_OPTIONS_TYPE_TEXT) ? 1 : zen_db_input($products_options_rows)) . "'
                              )");
        }

// iii 030811 added:  For TEXT and FILE option types, automatically add
// PRODUCTS_OPTIONS_VALUE_TEXT to the TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS
        switch ($option_type) {
          case PRODUCTS_OPTIONS_TYPE_TEXT:
          case PRODUCTS_OPTIONS_TYPE_FILE:
            $db->Execute("insert into " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . "
                        (products_options_values_id, products_options_id)
                        values ('" . (int)PRODUCTS_OPTIONS_VALUES_TEXT_ID .  "',
                                '" .  (int)$products_options_id .  "')");
            break;
        }

// alert if possible duplicate
        $duplicate_option= '';
        for ($i=0, $n=sizeof($languages); $i<$n; $i ++) {
          $option_name = zen_db_prepare_input($option_name_array[$languages[$i]['id']]);

          if (!empty($option_name)) {
            $check= $db->Execute("select count(products_options_name) as count
                                  from " . TABLE_PRODUCTS_OPTIONS . "
                                  where language_id= '" . (int)$languages[$i]['id'] . "'
                                  and products_options_name='" . zen_db_input($option_name) . "'");
            if ($check->fields['count'] > 1) {
              $duplicate_option .= ' <b>' . strtoupper(zen_get_language_name($languages[$i]['id'])) . '</b> : ' . $option_name;
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

        for ($i=0, $n=sizeof($languages); $i<$n; $i ++) {
          $option_name = zen_db_prepare_input($option_name_array[$languages[$i]['id']]);
          $products_options_sort_order = (int)zen_db_prepare_input($products_options_sort_order_array[$languages[$i]['id']]);


          $products_options_length = zen_db_prepare_input($products_options_length_array[$languages[$i]['id']]);
          $products_options_comment = zen_db_prepare_input($products_options_comment_array[$languages[$i]['id']]);
          $products_options_size = zen_db_prepare_input($products_options_size_array[$languages[$i]['id']]);

          $products_options_images_per_row = (int)zen_db_prepare_input($products_options_images_per_row_array[$languages[$i]['id']]);
          $products_options_images_style = (int)zen_db_prepare_input($products_options_images_style_array[$languages[$i]['id']]);
          $products_options_rows = (int)zen_db_prepare_input($products_options_rows_array[$languages[$i]['id']]);

//          zen_db_query("update " . TABLE_PRODUCTS_OPTIONS . " set products_options_name = '" . zen_db_input($option_name) . "', products_options_type = '" . $option_type . "' where products_options_id = '" . (int)$option_id . "' and language_id = '" . (int)$languages[$i]['id'] . "'");

          $db->Execute("update " . TABLE_PRODUCTS_OPTIONS . "
                        set products_options_name = '" . zen_db_input($option_name) . "', products_options_type = '" . $option_type . "', products_options_length = '" . zen_db_input($products_options_length) . "', products_options_comment = '" . zen_db_input($products_options_comment) . "', products_options_size = '" . zen_db_input($products_options_size) . "', products_options_sort_order = '" . zen_db_input($products_options_sort_order) . "', products_options_images_per_row = '" . zen_db_input($products_options_images_per_row) . "', products_options_images_style = '" . zen_db_input($products_options_images_style) . "', products_options_rows = '" . zen_db_input($products_options_rows) . "'
                        where products_options_id = '" . (int)$option_id . "'
                        and language_id = '" . (int)$languages[$i]['id'] . "'");
        }

        switch ($option_type) {
          case PRODUCTS_OPTIONS_TYPE_TEXT:
          case PRODUCTS_OPTIONS_TYPE_FILE:
// disabled because this could cause trouble if someone changed types unintentionally and deleted all their option values.  Shops with small numbers of values per option should consider uncommenting this.
//            zen_db_query("delete from " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " where products_options_id = '" . $_POST['option_id'] . "'");
// add in a record if none exists when option type is switched
            $check_type = $db->Execute("select count(products_options_id) as count from " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " where products_options_id='" . (int)$_POST['option_id'] .  "' and products_options_values_id ='0'");
            if ($check_type->fields['count'] == 0) {
              $db->Execute("insert into " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " (products_options_values_to_products_options_id, products_options_id, products_options_values_id) values (NULL, '" . (int)$_POST['option_id'] . "', '" . (int)PRODUCTS_OPTIONS_VALUES_TEXT_ID . "')");
            }
            break;
          default:
// if switched from file or text remove 0
            $db->Execute("delete from " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " where products_options_id='" . (int)$_POST['option_id'] . "' and products_options_values_id = '" . (int)PRODUCTS_OPTIONS_VALUES_TEXT_ID . "'");
            break;
        }

// alert if possible duplicate
        $duplicate_option= '';
        for ($i=0, $n=sizeof($languages); $i<$n; $i ++) {
          $option_name = zen_db_prepare_input($option_name_array[$languages[$i]['id']]);

          $check= $db->Execute("select products_options_name
                                from " . TABLE_PRODUCTS_OPTIONS . "
                                where language_id= '" . (int)$languages[$i]['id'] . "'
                                and products_options_name='" . zen_db_input($option_name) . "'");

          if ($check->RecordCount() > 1 and !empty($option_name)) {
            $duplicate_option .= ' <b>' . strtoupper(zen_get_language_name($languages[$i]['id'])) . '</b> : ' . $option_name;
          }
        }
        if (!empty($duplicate_option)) {
          $messageStack->add_session(ATTRIBUTE_POSSIBLE_OPTIONS_NAME_WARNING_DUPLICATE . ' ' . $option_id . ' - ' . $duplicate_option, 'caution');
        }

        zen_redirect(zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, $_SESSION['page_info'] . '&option_order_by=' . $option_order_by));
        break;
      case 'delete_option':
        // demo active test
        if (zen_admin_demo()) {
          $_GET['action']= '';
          $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
          zen_redirect(zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, $_SESSION['page_info'] . '&option_order_by=' . $option_order_by));
        }
        $option_id = zen_db_prepare_input($_GET['option_id']);

        $remove_option_values = $db->Execute("select products_options_id, products_options_values_id from " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " where products_options_id='" . (int)$option_id . "'");

        while (!$remove_option_values->EOF) {
          $db->Execute("delete from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where products_options_values_id='" . (int)$remove_option_values->fields['products_options_values_id'] . "' and products_options_values_id !=0");
          $remove_option_values->MoveNext();
        }

        $db->Execute("delete from " . TABLE_PRODUCTS_OPTIONS . "
                      where products_options_id = '" . (int)$option_id . "'");

        $db->Execute("delete from " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " where products_options_id = '" . (int)$option_id . "'");

        zen_redirect(zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, $_SESSION['page_info'] . '&option_order_by=' . $option_order_by));
        break;

/////////////////////////////////////
// additional features
    case 'update_options_values':
      // get products to update with at least one option_value for selected options_name
      $update_to = (int)$_GET['update_to'];
      $update_action = $_GET['update_action'];

      switch($update_to) {
        case (0):
        // all products
        $all_update_products = $db->Execute("select distinct products_id from " . TABLE_PRODUCTS_ATTRIBUTES . " where options_id='" . (int)$_POST['options_id'] . "'");
        break;
        case (1):
        // one product
        $product_to_update = (int)$_POST['product_to_update'];
        $all_update_products = $db->Execute("select distinct products_id from " . TABLE_PRODUCTS_ATTRIBUTES . " where options_id='" . (int)$_POST['options_id'] . "' and products_id='" . $product_to_update . "'");
        break;
        case (2):
        // category of products
        $category_to_update = (int)$_POST['category_to_update'];
// re-write with categories
        $all_update_products = $db->Execute("select distinct pa.products_id from " . TABLE_PRODUCTS_ATTRIBUTES . " pa left join " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc on pa.products_id = ptc.products_id where ptc.categories_id ='" . $category_to_update . "' and pa.options_id='" . (int)$_POST['options_id'] . "' and pa.products_id = ptc.products_id");
        break;
      }

      if ($all_update_products->RecordCount() < 1) {
        $messageStack->add_session(ERROR_PRODUCTS_OPTIONS_VALUES, 'caution');
      } else {
//die('I want to update ' . $_GET['update_to'] . ' : update action: ' . $update_action . ' product: ' . $_POST['product_to_update']  . ' category: ' . $_POST['category_to_update'] . ' found records: ' . $all_update_products->RecordCount() . ' - ' . $all_update_products->fields['products_id']);

        if ($update_action == 0) {
          // action add
          while (!$all_update_products->EOF) {
            // get all option_values
            $all_options_values = $db->Execute("select products_options_id, products_options_values_id from " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " where products_options_id='" . (int)$_POST['options_id'] . "'");
            $updated = 'false';
           while (!$all_options_values->EOF) {
              $check_all_options_values = $db->Execute("select products_attributes_id from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id='" . $all_update_products->fields['products_id'] . "' and options_id='" . (int)$all_options_values->fields['products_options_id'] . "' and options_values_id='" . (int)$all_options_values->fields['products_options_values_id'] . "'");
              if ($check_all_options_values->RecordCount() < 1) {
                // add missing options_value_id
                $updated = 'true';
                $db->Execute("insert into " . TABLE_PRODUCTS_ATTRIBUTES . " (products_id, options_id, options_values_id) values ('" . (int)$all_update_products->fields['products_id'] . "', '" . (int)$all_options_values->fields['products_options_id'] . "', '" . (int)$all_options_values->fields['products_options_values_id'] . "')");
              } else {
                // skip it the attribute is there
              }
              $all_options_values->MoveNext();
            }
            if ($updated == 'true') {
              zen_update_attributes_products_option_values_sort_order($all_update_products->fields['products_id']);
            }
            $all_update_products->MoveNext();
          }
          if ($updated='true') {
            $messageStack->add_session(SUCCESS_PRODUCTS_OPTIONS_VALUES, 'success');
          } else {
            $messageStack->add_session(ERROR_PRODUCTS_OPTIONS_VALUES, 'error');
          }
        } else {
          // action delete
          while (!$all_update_products->EOF) {
            // get all option_values
            $all_options_values = $db->Execute("select products_options_id, products_options_values_id from " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " where products_options_id='" . (int)$_POST['options_id'] . "'");
            $updated = 'false';
           while (!$all_options_values->EOF) {
              $check_all_options_values = $db->Execute("select products_attributes_id from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id='" . (int)$all_update_products->fields['products_id'] . "' and options_id='" . (int)$all_options_values->fields['products_options_id'] . "' and options_values_id='" . (int)$all_options_values->fields['products_options_values_id'] . "'");
              if ($check_all_options_values->RecordCount() >= 1) {
                // delete for this product with Option Name options_value_id
// echo '<br>This should be deleted: ' . zen_get_products_name($all_options_values->fields['products_options_id']);
// change to delete
// should add download delete
                $db->Execute("delete from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id='" . (int)$all_update_products->fields['products_id'] . "' and options_id='" . (int)$_POST['options_id'] . "'");
              } else {
                // skip this option_name does not exist
              }
              $all_options_values->MoveNext();
            }
            $all_update_products->MoveNext();
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
        $start_id = $db->Execute("select pov.products_options_values_id from " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov order by pov.products_options_values_id DESC LIMIT 1");
        $copy_from_values = $db->Execute("select pov.* from " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov left join " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " povtpo on pov.products_options_values_id= povtpo.products_options_values_id where povtpo.products_options_id='" . (int)$options_id_from . "' order by povtpo.products_options_values_id");
        if ($copy_from_values->RecordCount() > 0) {
          // successful copy
          $next_id = ($start_id->fields['products_options_values_id'] + 1);
          while(!$copy_from_values->EOF) {
            $current_id = $copy_from_values->fields['products_options_values_id'];
            $sql = "insert into " . TABLE_PRODUCTS_OPTIONS_VALUES . " (products_options_values_id, language_id, products_options_values_name, products_options_values_sort_order) values ('" . (int)$next_id . "', '" . (int)$copy_from_values->fields['language_id'] . "', '" . $copy_from_values->fields['products_options_values_name'] . "', '" . (int)$copy_from_values->fields['products_options_values_sort_order'] . "')";
            $db->Execute($sql);
            $copy_from_values->MoveNext();
            if ($copy_from_values->fields['products_options_values_id'] != $current_id or $copy_from_values->EOF) {
              $sql = "insert into " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " (products_options_values_to_products_options_id, products_options_id, products_options_values_id) values (0, '" . (int)$options_id_to . "', '" . (int)$next_id . "')";
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
  $products_options_type_array = $db->Execute("select products_options_types_id, products_options_types_name from " . TABLE_PRODUCTS_OPTIONS_TYPES . " order by products_options_types_id");
  while (!$products_options_type_array->EOF) {
    $products_options_types_list[$products_options_type_array->fields['products_options_types_id']] = $products_options_type_array->fields['products_options_types_name'];
    $products_options_type_array->MoveNext();
  }

//CLR 030312 add function to draw pulldown list of option types
// Draw a pulldown for Option Types
//iii 031103 modified to use results of database option type query from above
function draw_optiontype_pulldown($name, $default = '') {
  global $products_options_types_list;
  $values = array();
  foreach ($products_options_types_list as $id => $text) {
    $values[] = array('id' => $id, 'text' => $text);
  }
  return zen_draw_pull_down_menu($name, $values, $default);
}

//CLR 030312 add function to translate type_id to name
// Translate option_type_values to english string
//iii 031103 modified to use results of database option type query from above
function translate_type_to_name($opt_type) {
  global $products_options_types_list;
  return $products_options_types_list[$opt_type];
  return 'Error ' . $opt_type;
}

require('includes/admin_html_head.php');
?>
<script language="javascript"><!--
function go_option() {
  if (document.option_order_by.selected.options[document.option_order_by.selected.selectedIndex].value != "none") {
    location = "<?php echo zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, 'option_page=' . ($_GET['option_page'] ? $_GET['option_page'] : 1)); ?>&option_order_by="+document.option_order_by.selected.options[document.option_order_by.selected.selectedIndex].value;
  }
}
//--></script>
</head>
<body>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<!-- body_text //-->

    <!-- options and values//-->
  <table border="0" width="75%" cellspacing="0" cellpadding="0" align="center">
      <tr>
        <td width="100%">
          <table width="100%" border="0" cellspacing="0" cellpadding="2">
            <tr>
              <td height="40" valign="bottom">
                <a href="<?php echo  zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, '', 'NONSSL') ?>"><?php echo zen_image_button('button_edit_attribs.gif', IMAGE_EDIT_ATTRIBUTES); ?></a> &nbsp;
                <a href="<?php echo  zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, '', 'NONSSL') ?>"><?php echo zen_image_button('button_option_values.gif', IMAGE_OPTION_VALUES); ?></a>
              </td>
              <td class="main" height="40" valign="bottom">
                <?php
// toggle switch for show copier features
                  $option_names_values_copier_array = array(array('id' => '0', 'text' => TEXT_SHOW_OPTION_NAMES_VALUES_COPIER_OFF),
                                        array('id' => '1', 'text' => TEXT_SHOW_OPTION_NAMES_VALUES_COPIER_ON),
                                        );
                  echo zen_draw_form('set_option_names_values_copier_form', FILENAME_OPTIONS_NAME_MANAGER, '', 'get') . '&nbsp;&nbsp;' . zen_draw_pull_down_menu('reset_option_names_values_copier', $option_names_values_copier_array, $reset_option_names_values_copier, 'onChange="this.form.submit();"') .
                  zen_hide_session_id() .
                  zen_draw_hidden_field('action', 'set_option_names_values_copier') .
                  '</form>';
                ?>
              </td>
              <td class="main" align="right" valign="bottom"><?php echo TEXT_PRODUCT_OPTIONS_INFO; ?></td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td valign="top" width="50%">
           <table width="100%" border="0" cellspacing="0" cellpadding="2">
<!-- options //-->
<?php
  if ($action == 'delete_product_option') { // delete product option
    $options = $db->Execute("select products_options_id, products_options_name
                             from " . TABLE_PRODUCTS_OPTIONS . "
                             where products_options_id = '" . (int)$_GET['option_id'] . "'
                             and language_id = '" . (int)$_SESSION['languages_id'] . "'");

?>
              <tr>
                <td class="pageHeading">&nbsp;<?php echo $options_values->fields['products_options_name']; ?>&nbsp;</td>
              </tr>
              <tr>
                <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
    $products = $db->Execute("select p.products_id, pd.products_name, pov.products_options_values_name
                              from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov,
                                   " . TABLE_PRODUCTS_ATTRIBUTES . " pa,
                                   " . TABLE_PRODUCTS_DESCRIPTION . " pd
                              where pd.products_id = p.products_id
                              and pov.language_id = '" . (int)$_SESSION['languages_id'] . "'
                              and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                              and pa.products_id = p.products_id
                              and pa.options_id='" . (int)$_GET['option_id'] . "'
                              and pov.products_options_values_id = pa.options_values_id
                              order by pd.products_name");

    if ($products->RecordCount()>0) {
?>

<?php
// extra cancel
      if ($products->RecordCount()> 10) {
?>
                  <tr>
                    <td colspan="3"><?php echo zen_black_line(); ?></td>
                  </tr>
                  <tr>
                    <td colspan="2" class="main"><br /><?php echo '<strong>' . TEXT_OPTION_NAME . ':</strong> ' . zen_options_name((int)$_GET['option_id']) . '<br />' . TEXT_WARNING_OF_DELETE; ?></td>
                    <td align="right" colspan="3" class="main"><br /><?php echo '<a href="' . zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&option_order_by=' . $option_order_by) . '">'; ?><?php echo zen_image_button('button_cancel.gif', ' cancel '); ?></a>&nbsp;</td>
                  </tr>
<?php
      }
?>
                  <tr class="dataTableHeadingRow">
                    <td class="dataTableHeadingContent" align="center">&nbsp;<?php echo TABLE_HEADING_ID; ?>&nbsp;</td>
                    <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_PRODUCT; ?>&nbsp;</td>
                    <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_OPT_VALUE; ?>&nbsp;</td>
                  </tr>
                  <tr>
                    <td colspan="3"><?php echo zen_black_line(); ?></td>
                  </tr>
<?php
      $rows = 0;
      while (!$products->EOF) {
        $rows++;
?>
                  <tr class="<?php echo (floor($rows/2) == ($rows/2) ? 'attributes-even' : 'attributes-odd'); ?>">
                    <td align="center" class="smallText">&nbsp;<?php echo $products->fields['products_id']; ?>&nbsp;</td>
                    <td class="smallText">&nbsp;<?php echo $products->fields['products_name']; ?>&nbsp;</td>
                    <td class="smallText">&nbsp;<?php echo $products->fields['products_options_values_name']; ?>&nbsp;</td>
                  </tr>
<?php
        $products->MoveNext();
      }
?>
                  <tr>
                    <td colspan="3"><?php echo zen_black_line(); ?></td>
                  </tr>
                  <tr>
                    <td colspan="2" class="main"><br /><?php echo TEXT_WARNING_OF_DELETE; ?></td>
                    <td align="right" colspan="3" class="main"><br /><?php echo '<a href="' . zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&option_order_by=' . $option_order_by ) . '">'; ?><?php echo zen_image_button('button_cancel.gif', ' cancel '); ?></a>&nbsp;</td>
                  </tr>
                  <tr>
                    <td colspan="3"><?php echo zen_black_line(); ?></td>
                  </tr>
<?php
    } else {
?>
                  <tr>
                    <td class="main" colspan="3"><br /><?php echo '<strong>' . TEXT_OPTION_NAME . ':</strong> ' . zen_options_name((int)$_GET['option_id']) . '<br />' . TEXT_OK_TO_DELETE; ?></td>
                  </tr>
                  <tr>
                    <td class="main" align="right" colspan="3"><br /><?php echo '<a href="' . zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, 'action=delete_option&option_id=' . $_GET['option_id'] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&option_order_by=' . $option_order_by ) . '">'; ?><?php echo zen_image_button('button_delete.gif', ' delete '); ?></a>&nbsp;&nbsp;&nbsp;<?php echo '<a href="' . zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, (isset($_GET['order_by']) ? 'order_by=' . $_GET['order_by'] . '&' : '') . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&option_order_by=' . $option_order_by ) . '">'; ?><?php echo zen_image_button('button_cancel.gif', ' cancel '); ?></a>&nbsp;</td>
                  </tr>
<?php
    }
?>
                </table></td>
              </tr>
<?php
  } else {
    if (isset($_GET['option_order_by'])) {
      $option_order_by = $_GET['option_order_by'];
    } else {
      $option_order_by = 'products_options_id';
    }
?>
              <tr>
                <td colspan="2" class="pageHeading">&nbsp;<?php echo HEADING_TITLE_OPT; ?>&nbsp;</td>
                <td valign="top" align="left"><form name="option_order_by" action="<?php echo zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, 'option_order_by=' . $option_order_by, 'NONSSL'); ?>"><select name="selected" onChange="go_option()"><option value="products_options_id"<?php if ($option_order_by == 'products_options_id') { echo ' SELECTED'; } ?>><?php echo TEXT_OPTION_ID; ?></option><option value="products_options_name"<?php if ($option_order_by == 'products_options_name') { echo ' SELECTED'; } ?>><?php echo TEXT_OPTION_NAME; ?></option></select></form></td>
              </tr>
              <tr>
                <td colspan="4" class="smallText">
<?php
    $options = "select * from " . TABLE_PRODUCTS_OPTIONS . " where language_id = :languageId: order by :optionOrderBy:";
    $options = $db->bindVars($options, ':languageId:', $_SESSION['languages_id'], 'integer');
    $options = $db->bindVars($options, ':optionOrderBy:', $option_order_by, 'noquotestring');
    if (!isset($_GET['option_page'])) {
      $_GET['option_page'] = 1;
    }
    $prev_option_page = $_GET['option_page'] - 1;
    $next_option_page = $_GET['option_page'] + 1;

    $option_query = $db->Execute($options);
    $num_rows = $option_query->RecordCount();

    $per_page = (MAX_ROW_LISTS_OPTIONS == '') ? $num_rows : (int)MAX_ROW_LISTS_OPTIONS;

    $option_page_start = ($per_page * $_GET['option_page']) - $per_page;
    if ($num_rows <= $per_page) {
      $num_pages = 1;
    } else if (($num_rows % $per_page) == 0) {
      $num_pages = ($num_rows / $per_page);
    } else {
      $num_pages = ($num_rows / $per_page) + 1;
    }
    $num_pages = (int) $num_pages;

// fix limit error on some versions
    if ($option_page_start < 0) { $option_page_start = 0; }

    $options = $options . " LIMIT $option_page_start, $per_page";

    // Previous
    if ($prev_option_page)  {
      echo '<a href="' . zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, 'option_page=' . $prev_option_page . '&option_order_by=' . $option_order_by) . '"> &lt;&lt; </a> | ';
    }

    for ($i = 1; $i <= $num_pages; $i++) {
      if ($i != $_GET['option_page']) {
        echo '<a href="' . zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, 'option_page=' . $i . '&option_order_by=' . $option_order_by) . '">' . $i . '</a> | ';
      } else {
        echo '<b><font color=red>' . $i . '</font></b> | ';
      }
    }

    // Next
    if ($_GET['option_page'] != $num_pages) {
      echo '<a href="' . zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, 'option_page=' . $next_option_page . '&option_order_by=' . $option_order_by) . '"> &gt;&gt; </a>';
    }
//CLR 030212 - Add column for option type
?>
                </td>
              </tr>
              <tr>
                <td colspan="7"><?php echo zen_black_line(); ?></td>
              </tr>
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_ID; ?>&nbsp;</td>
                <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_OPT_NAME; ?>&nbsp;</td>
                <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_OPT_TYPE; ?>&nbsp;</td>
                <td class="dataTableHeadingContent" align="right">&nbsp;<?php echo TABLE_HEADING_OPTION_SORT_ORDER; ?>&nbsp;</td>
                <td class="dataTableHeadingContent" align="center">&nbsp;<?php echo TABLE_HEADING_OPTION_VALUE_SIZE; ?></td>
                <td class="dataTableHeadingContent" align="center">&nbsp;<?php echo TABLE_HEADING_OPTION_VALUE_MAX; ?></td>
                <td class="dataTableHeadingContent" align="center">&nbsp;<?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
              <tr>
                <td colspan="7"><?php echo zen_black_line(); ?></td>
              </tr>
<?php
    $next_id = 1;
    $rows = 0;
    $options_values = $db->Execute($options);
    while (!$options_values->EOF) {
      $rows++;
?>
              <tr class="<?php echo (floor($rows/2) == ($rows/2) ? 'attributes-even' : 'attributes-odd'); ?>">
<?php
// edit option name
      if (($action == 'update_option') && ($_GET['option_id'] == $options_values->fields['products_options_id'])) {
        echo '<form name="option" action="' . zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, 'action=update_option_name' . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '')  . '&option_order_by=' . $option_order_by) . '" method="post">';echo zen_draw_hidden_field('securityToken', $_SESSION['securityToken']);
        $inputs = '';
        $inputs2 = '';
        for ($i = 0, $n = sizeof($languages); $i < $n; $i ++) {
          $option_name = $db->Execute("select products_options_name, products_options_sort_order, products_options_size, products_options_length, products_options_comment, products_options_images_per_row, products_options_images_style, products_options_rows
                                       from " . TABLE_PRODUCTS_OPTIONS . "
                                       where products_options_id = '" . (int)$options_values->fields['products_options_id'] . "'
                                       and language_id = '" . (int)$languages[$i]['id'] . "'");

          $inputs .= $languages[$i]['code'] . ':&nbsp;<input type="text" name="option_name[' . $languages[$i]['id'] . ']" ' . zen_set_field_length(TABLE_PRODUCTS_OPTIONS, 'products_options_name', 40) . ' value="' . zen_output_string($option_name->fields['products_options_name']) . '">' . TEXT_SORT . '<input type="text" name="products_options_sort_order[' . $languages[$i]['id'] . ']" size="3" value="' . $option_name->fields['products_options_sort_order'] . '">&nbsp;<br />';
          $inputs2 .= $languages[$i]['code'] . ':&nbsp; ' .
                   '&nbsp;' . TEXT_OPTION_VALUE_COMMENTS . '<input type="text" name="products_options_comment[' . $languages[$i]['id'] . ']" size="50" value="' . zen_output_string($option_name->fields['products_options_comment']) . '">' .
                   '<br /><br />' . TEXT_OPTION_VALUE_ROWS . '<input type="text" name="products_options_rows[' . $languages[$i]['id'] . ']" size="3" value="' . $option_name->fields['products_options_rows'] . '">' .
                   '&nbsp;' . TEXT_OPTION_VALUE_SIZE . '<input type="text" name="products_options_size[' . $languages[$i]['id'] . ']" size="3" value="' . $option_name->fields['products_options_size'] . '">' .
                   '&nbsp;' . TEXT_OPTION_VALUE_MAX . '<input type="text" name="products_options_length[' . $languages[$i]['id'] . ']" size="3" value="' . $option_name->fields['products_options_length'] . '">' .
                   '<br /><br />' . TEXT_OPTION_ATTRIBUTE_IMAGES_PER_ROW . '<input type="text" name="products_options_images_per_row[' . $languages[$i]['id'] . ']" size="3" value="' . $option_name->fields['products_options_images_per_row'] . '">' .
                   '&nbsp;' . TEXT_OPTION_ATTRIBUTE_IMAGES_STYLE . '<input type="text" name="products_options_images_style[' . $languages[$i]['id'] . ']" size="3" value="' . $option_name->fields['products_options_images_style'] . '">' . '<br /><br />';

        }

//CLR 030212 - Add column for option type
?>
                <td height="50" align="center" class="attributeBoxContent">&nbsp;<?php echo $options_values->fields['products_options_id']; ?><input type="hidden" name="option_id" value="<?php echo $options_values->fields['products_options_id']; ?>">&nbsp;</td>
                <td class="attributeBoxContent"><?php echo $inputs; ?></td>
                <td class="attributeBoxContent"><?php echo draw_optiontype_pulldown('option_type', $options_values->fields['products_options_type']); ?></td>
                <td colspan="3" align="left" class="attributeBoxContent">&nbsp;</td>
                <td colspan="1"  align="center" class="attributeBoxContent">&nbsp;<?php echo zen_image_submit('button_update.gif', IMAGE_UPDATE); ?>&nbsp;<?php echo '<a href="' . zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&option_order_by=' . $option_order_by ) . '">'; ?><?php echo zen_image_button('button_cancel.gif', IMAGE_CANCEL); ?></a>&nbsp;</td>
              </tr>
      <tr>
        <td colspan="7"><?php echo zen_draw_separator('pixel_black.gif', '100%', '2'); ?></td>
      </tr>

              <tr class="attributeBoxContent">
                <td class="attributeBoxContent">&nbsp;</td>
                <td colspan="6" class="attributeBoxContent"><?php echo TEXT_OPTION_ATTIBUTE_MAX_LENGTH . $inputs2; ?></td>
              </tr>
              <tr class="attributeBoxContent">
                <td class="attributeBoxContent">&nbsp;</td>
                <td colspan="6" class="attributeBoxContent">
              <?php echo '<br />' .
                     TEXT_OPTION_IMAGE_STYLE . '<br />' .
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
<?php
        echo '</form>' . "\n";
      } else {
//CLR 030212 - Add column for option type
?>
                <td align="center" class="smallText">&nbsp;<?php echo $options_values->fields["products_options_id"]; ?>&nbsp;</td>
                <td class="smallText">&nbsp;<?php echo $options_values->fields["products_options_name"]; ?>&nbsp;</td>
                <td class="smallText">&nbsp;<?php echo translate_type_to_name($options_values->fields["products_options_type"]); ?>&nbsp;</td>
                <td class="smallText" align="right">&nbsp;<?php echo $options_values->fields["products_options_sort_order"]; ?>&nbsp;</td>
                <td class="smallText" align="right">&nbsp;<?php echo $options_values->fields["products_options_size"]; ?>&nbsp;</td>
                <td class="smallText" align="right">&nbsp;<?php echo $options_values->fields["products_options_length"]; ?>&nbsp;</td>
<?php
// hide buttons when editing
  if ($action== 'update_option') {
?>
            <td width='120' align="center" class="smallText">&nbsp;</td>
<?php
  } else {
?>
                <td align="center" class="smallText">&nbsp;<?php echo '<a href="' . zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, 'action=update_option&option_id=' . $options_values->fields['products_options_id'] . '&option_order_by=' . $option_order_by . '&option_page=' . $_GET['option_page'] . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') ) . '">'; ?><?php echo zen_image_button('button_edit.gif', IMAGE_UPDATE); ?></a>&nbsp;&nbsp;<?php echo '<a href="' . zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, 'action=delete_product_option&option_id=' . $options_values->fields['products_options_id'] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . '&option_order_by=' . $option_order_by, 'NONSSL') , '">'; ?><?php echo zen_image_button('button_delete.gif', IMAGE_DELETE); ?></a>&nbsp;</td>
<?php
  }
?>
<?php
      }
?>
              </tr>
<?php
      $max_options_id_values = $db->Execute("select max(products_options_id) + 1 as next_id
                                             from " . TABLE_PRODUCTS_OPTIONS);

      $next_id = $max_options_id_values->fields['next_id'];
      $options_values->MoveNext();
    }
?>
              <tr>
                <td colspan="7"><?php echo zen_black_line(); ?></td>
              </tr>
<?php
// add option name
    if ($action != 'update_option') {
?>
              <tr class="<?php echo (floor($rows/2) == ($rows/2) ? 'attributes-even' : 'attributes-odd'); ?>">
<?php
      echo '<form name="options" action="' . zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, 'action=add_product_options' . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&option_order_by=' . $option_order_by ) . '" method="post"><input type="hidden" name="products_options_id" value="' . $next_id . '">';echo zen_draw_hidden_field('securityToken', $_SESSION['securityToken']);
      $inputs = '';
      for ($i = 0, $n = sizeof($languages); $i < $n; $i ++) {
        $inputs .= $languages[$i]['code'] . ':&nbsp;<input type="text" name="option_name[' . $languages[$i]['id'] . ']" ' . zen_set_field_length(TABLE_PRODUCTS_OPTIONS, 'products_options_name', 40) . '>' . TEXT_SORT . '<input type="text" name="products_options_sort_order[' . $languages[$i]['id'] . ']" size="3">' . '&nbsp;<br />';
      }
//CLR 030212 - Add column for option type
?>
                <td align="center" class="smallText">&nbsp;<?php echo $next_id; ?>&nbsp;</td>
                <td class="smallText"><?php echo $inputs; ?></td>
                <td class="smallText"><?php echo draw_optiontype_pulldown('option_type'); ?></td>
                <td colspan="2" class="smallText">&nbsp;</td>
                <td colspan="2" align="center" class="smallText">&nbsp;<?php echo zen_image_submit('button_insert.gif', IMAGE_INSERT); ?>&nbsp;</td>
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
</td></tr></table>
<!-- options eof //-->

<?php if ($_SESSION['option_names_values_copier'] == '0') { ?>
  <table align="center" width="90%">
    <tr>
      <td><?php echo zen_draw_separator('pixel_trans.gif', '100%', '5'); ?></td>
    </tr>
    <tr>
      <td class="pageHeading" align="center"><?php echo TEXT_INFO_OPTION_NAMES_VALUES_COPIER_STATUS; ?></td>
    </tr>
    <tr>
      <td><?php echo zen_draw_separator('pixel_trans.gif', '100%', '5'); ?></td>
    </tr>
  </table>
<?php } else { ?>
  <table align="center" width="90%">
    <tr>
      <td><?php echo zen_draw_separator('pixel_trans.gif', '100%', '5'); ?></td>
    </tr>
    <tr>
      <td class="pageHeading" align="center"><span class="alert"><?php echo TEXT_WARNING_BACKUP; ?></span></td>
    </tr>
  </table>

<!-- ADD - additional features //-->
  <table border="2" width="75%" cellspacing="0" cellpadding="0" align="center">
      <tr>
        <td width="100%">
          <table width="100%" border="0" cellspacing="0" cellpadding="2">

<!-- bof: add all option values to products with current Option Name -->
            <tr>
              <td class="main"><?php echo TEXT_OPTION_VALUE_ADD_ALL; ?></td>
            </tr>
            <tr>
              <td class="main"><?php echo TEXT_INFO_OPTION_VALUE_ADD_ALL; ?></td>
            </tr>
            <tr class="dataTableHeadingRow">
              <td><table border="0" cellspacing="0" cellpadding="2">
                <tr class="dataTableHeadingRow">
                  <form name="quick_jump" method="post" action="<?php echo zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, 'action=update_options_values&update_to=0&update_action=0' . '&option_order_by=' . $option_order_by, 'NONSSL'); ?>"><?php echo zen_draw_hidden_field('securityToken', $_SESSION['securityToken']); ?>
                  <td class="dataTableHeadingContent"><?php echo TEXT_SELECT_OPTION; ?><br /><select name="options_id">
<?php
        $options_values = $db->Execute("select * from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . (int)$_SESSION['languages_id'] . "' and products_options_name !='' and products_options_type !='" . (int)PRODUCTS_OPTIONS_TYPE_TEXT . "' and products_options_type !='" . (int)PRODUCTS_OPTIONS_TYPE_FILE . "' order by products_options_name");
        while(!$options_values->EOF) {
            echo "\n" . '<option name="' . $options_values->fields['products_options_name'] . '" value="' . $options_values->fields['products_options_id'] . '">' . $options_values->fields['products_options_name'] . '</option>';
            $options_values->MoveNext();
        }
?>
                  </select>&nbsp;</td>
                  <td align="right" class="dataTableHeadingContent">&nbsp;<?php echo zen_image_submit('button_update.gif', IMAGE_UPDATE); ?>&nbsp;</td>
                  </form>
                </tr>

              </table></td>
            </tr>

            <tr>
              <td class="main"><?php echo TEXT_OPTION_VALUE_ADD_PRODUCT; ?></td>
            </tr>
            <tr>
              <td class="main"><?php echo TEXT_INFO_OPTION_VALUE_ADD_PRODUCT; ?></td>
            </tr>
            <tr class="dataTableHeadingRow">
              <td><table border="0" cellspacing="0" cellpadding="2">
                <tr class="dataTableHeadingRow">
                  <form name="quick_jump" method="post" action="<?php echo zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, 'action=update_options_values&update_to=1&update_action=0' . '&option_order_by=' . $option_order_by, 'NONSSL'); ?>"><?php echo zen_draw_hidden_field('securityToken', $_SESSION['securityToken']); ?>
                  <td class="dataTableHeadingContent"><?php echo TEXT_SELECT_OPTION; ?><br /><select name="options_id">
<?php
        $options_values = $db->Execute("select * from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . (int)$_SESSION['languages_id'] . "' and products_options_name !='' and products_options_type !='" . (int)PRODUCTS_OPTIONS_TYPE_TEXT . "' and products_options_type !='" . (int)PRODUCTS_OPTIONS_TYPE_FILE . "' order by products_options_name");
        while(!$options_values->EOF) {
            echo "\n" . '<option name="' . $options_values->fields['products_options_name'] . '" value="' . $options_values->fields['products_options_id'] . '">' . $options_values->fields['products_options_name'] . '</option>';
            $options_values->MoveNext();
        }
?>
                  </select>&nbsp;</td>
                  <td class="dataTableHeadingContent"><?php echo TEXT_SELECT_PRODUCT; ?><br /><?php echo zen_draw_products_pull_down_attributes('product_to_update', 'size="5"', '', true, $_GET['products_filter'], true); ?></td>

                  <td align="center" class="dataTableHeadingContent">&nbsp;<?php echo zen_image_submit('button_update.gif', IMAGE_UPDATE); ?>&nbsp;</td>
                  </form>
                </tr>

              </table></td>
            </tr>

            <tr>
              <td class="main"><?php echo TEXT_OPTION_VALUE_ADD_CATEGORY; ?></td>
            </tr>
            <tr>
              <td class="main"><?php echo TEXT_INFO_OPTION_VALUE_ADD_CATEGORY; ?></td>
            </tr>
            <tr class="dataTableHeadingRow">
              <td><table border="0" cellspacing="0" cellpadding="2">
                <tr class="dataTableHeadingRow">
                  <form name="quick_jump" method="post" action="<?php echo zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, 'action=update_options_values&update_to=2&update_action=0' . '&option_order_by=' . $option_order_by, 'NONSSL'); ?>"><?php echo zen_draw_hidden_field('securityToken', $_SESSION['securityToken']); ?>
                  <td class="dataTableHeadingContent"><?php echo TEXT_SELECT_OPTION; ?><br /><select name="options_id">
<?php
        $options_values = $db->Execute("select * from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . (int)$_SESSION['languages_id'] . "' and products_options_name !='' and products_options_type !='" . (int)PRODUCTS_OPTIONS_TYPE_TEXT . "' and products_options_type !='" . (int)PRODUCTS_OPTIONS_TYPE_FILE . "' order by products_options_name");
        while(!$options_values->EOF) {
            echo "\n" . '<option name="' . $options_values->fields['products_options_name'] . '" value="' . $options_values->fields['products_options_id'] . '">' . $options_values->fields['products_options_name'] . '</option>';
            $options_values->MoveNext();
        }
?>
                  </select>&nbsp;</td>
                  <td class="dataTableHeadingContent"><?php echo TEXT_SELECT_CATEGORY; ?><br /><?php echo zen_draw_products_pull_down_categories('category_to_update', 'size="5"', '', true, $_GET['products_filter'], true); ?></td>

                  <td align="lef" class="dataTableHeadingContent">&nbsp;<?php echo zen_image_submit('button_update.gif', IMAGE_UPDATE); ?>&nbsp;</td>
                  </form>
                </tr>

              </table></td>
            </tr>

            <tr>
              <td class="main"><?php echo TEXT_COMMENT_OPTION_VALUE_ADD_ALL; ?></td>
            </tr>
<!-- eof: add all option values to products with current Option Name -->

          </table>
        </td>
      </tr>
   </table>
<!-- ADD - additional features eof //-->

  <table>
    <tr>
      <td colspan="4"><?php echo zen_draw_separator('pixel_trans.gif', '100%', '5'); ?></td>
    </tr>
  </table>

<!-- DELETE - additional features //-->
  <table border="2" width="75%" cellspacing="0" cellpadding="0" align="center">
      <tr>
        <td width="100%">
          <table width="100%" border="0" cellspacing="0" cellpadding="2">

<!-- bof: delete all option values to products with current Option Name -->
            <tr>
              <td class="main"><?php echo TEXT_OPTION_VALUE_DELETE_ALL; ?></td>
            </tr>
            <tr>
              <td class="main"><?php echo TEXT_INFO_OPTION_VALUE_DELETE_ALL; ?></td>
            </tr>
            <tr class="dataTableHeadingRow">
              <td><table border="0" cellspacing="0" cellpadding="2">
                <tr class="dataTableHeadingRow">
                  <form name="quick_jump" method="post" action="<?php echo zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, 'action=update_options_values&update_to=0&update_action=1' . '&option_order_by=' . $option_order_by, 'NONSSL'); ?>"><?php echo zen_draw_hidden_field('securityToken', $_SESSION['securityToken']); ?>
                  <td class="dataTableHeadingContent"><?php echo TEXT_SELECT_OPTION; ?><br /><select name="options_id">
<?php
        $options_values = $db->Execute("select * from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . (int)$_SESSION['languages_id'] . "' and products_options_name !='' and products_options_type !='" . (int)PRODUCTS_OPTIONS_TYPE_TEXT . "' and products_options_type !='" . (int)PRODUCTS_OPTIONS_TYPE_FILE . "' order by products_options_name");
        while(!$options_values->EOF) {
            echo "\n" . '<option name="' . $options_values->fields['products_options_name'] . '" value="' . $options_values->fields['products_options_id'] . '">' . $options_values->fields['products_options_name'] . '</option>';
            $options_values->MoveNext();
        }
?>
                  </select>&nbsp;</td>
                  <td align="right" class="dataTableHeadingContent">&nbsp;<?php echo zen_image_submit('button_update.gif', IMAGE_UPDATE); ?>&nbsp;</td>
                  </form>
                </tr>

              </table></td>
            </tr>

            <tr>
              <td class="main"><?php echo TEXT_OPTION_VALUE_DELETE_PRODUCT; ?></td>
            </tr>
            <tr>
              <td class="main"><?php echo TEXT_INFO_OPTION_VALUE_DELETE_PRODUCT; ?></td>
            </tr>
            <tr class="dataTableHeadingRow">
              <td><table border="0" cellspacing="0" cellpadding="2">
                <tr class="dataTableHeadingRow">
                  <form name="quick_jump" method="post" action="<?php echo zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, 'action=update_options_values&update_to=1&update_action=1' . '&option_order_by=' . $option_order_by, 'NONSSL'); ?>"><?php echo zen_draw_hidden_field('securityToken', $_SESSION['securityToken']); ?>
                  <td class="dataTableHeadingContent"><?php echo TEXT_SELECT_OPTION; ?><br /><select name="options_id">
<?php
        $options_values = $db->Execute("select * from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . (int)$_SESSION['languages_id'] . "' and products_options_name !='' and products_options_type !='" . (int)PRODUCTS_OPTIONS_TYPE_TEXT . "' and products_options_type !='" . (int)PRODUCTS_OPTIONS_TYPE_FILE . "' order by products_options_name");
        while(!$options_values->EOF) {
            echo "\n" . '<option name="' . $options_values->fields['products_options_name'] . '" value="' . $options_values->fields['products_options_id'] . '">' . $options_values->fields['products_options_name'] . '</option>';
            $options_values->MoveNext();
        }
?>
                  </select>&nbsp;</td>
                  <td class="dataTableHeadingContent"><?php echo TEXT_SELECT_PRODUCT; ?><br /><?php echo zen_draw_products_pull_down_attributes('product_to_update', 'size="5"', '', true, $_GET['products_filter'], true); ?></td>

                  <td align="center" class="dataTableHeadingContent">&nbsp;<?php echo zen_image_submit('button_update.gif', IMAGE_UPDATE); ?>&nbsp;</td>
                  </form>
                </tr>

              </table></td>
            </tr>

            <tr>
              <td class="main"><?php echo TEXT_OPTION_VALUE_DELETE_CATEGORY; ?></td>
            </tr>
            <tr>
              <td class="main"><?php echo TEXT_INFO_OPTION_VALUE_DELETE_CATEGORY; ?></td>
            </tr>
            <tr class="dataTableHeadingRow">
              <td><table border="0" cellspacing="0" cellpadding="2">
                <tr class="dataTableHeadingRow">
                  <form name="quick_jump" method="post" action="<?php echo zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, 'action=update_options_values&update_to=2&update_action=1' . '&option_order_by=' . $option_order_by, 'NONSSL'); ?>"><?php echo zen_draw_hidden_field('securityToken', $_SESSION['securityToken']); ?>
                  <td class="dataTableHeadingContent"><?php echo TEXT_SELECT_OPTION; ?><br /><select name="options_id">
<?php
        $options_values = $db->Execute("select * from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . (int)$_SESSION['languages_id'] . "' and products_options_name !='' and products_options_type !='" . (int)PRODUCTS_OPTIONS_TYPE_TEXT . "' and products_options_type !='" . (int)PRODUCTS_OPTIONS_TYPE_FILE . "' order by products_options_name");
        while(!$options_values->EOF) {
            echo "\n" . '<option name="' . $options_values->fields['products_options_name'] . '" value="' . $options_values->fields['products_options_id'] . '">' . $options_values->fields['products_options_name'] . '</option>';
            $options_values->MoveNext();
        }
?>
                  </select>&nbsp;</td>
                  <td class="dataTableHeadingContent"><?php echo TEXT_SELECT_CATEGORY; ?><br /><?php echo zen_draw_products_pull_down_categories('category_to_update', 'size="5"', '', true, $_GET['products_filter'], true); ?></td>

                  <td align="lef" class="dataTableHeadingContent">&nbsp;<?php echo zen_image_submit('button_update.gif', IMAGE_UPDATE); ?>&nbsp;</td>
                  </form>
                </tr>

              </table></td>
            </tr>

            <tr>
              <td class="main"><?php echo TEXT_COMMENT_OPTION_VALUE_DELETE_ALL; ?></td>
            </tr>
<!-- eof: delete all option values to products with current Option Name -->

          </table>
        </td>
      </tr>
   </table>
<!-- DELETE - additional features eof //-->


  <table>
    <tr>
      <td colspan="4"><?php echo zen_draw_separator('pixel_trans.gif', '100%', '5'); ?></td>
    </tr>
  </table>


<!-- COPY - additional features //-->
  <table border="2" width="75%" cellspacing="0" cellpadding="0" align="center">
      <tr>
        <td width="100%">
          <table width="100%" border="0" cellspacing="0" cellpadding="2">

<!-- bof: copy all option values to another Option Name -->
            <tr>
              <td class="main"><?php echo TEXT_OPTION_VALUE_COPY_ALL; ?></td>
            </tr>
            <tr>
              <td class="main"><?php echo TEXT_INFO_OPTION_VALUE_COPY_ALL; ?></td>
            </tr>
            <tr class="dataTableHeadingRow">
              <td><table border="0" cellspacing="0" cellpadding="2">
                <tr class="dataTableHeadingRow">
                  <form name="quick_jump" method="post" action="<?php echo zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, 'action=copy_options_values' . '&option_order_by=' . $option_order_by, 'NONSSL'); ?>"><?php echo zen_draw_hidden_field('securityToken', $_SESSION['securityToken']); ?>
                  <td class="dataTableHeadingContent"><?php echo TEXT_SELECT_OPTION_FROM; ?><br /><select name="options_id_from">
<?php
        $options_values_from = $db->Execute("select * from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . (int)$_SESSION['languages_id'] . "' and products_options_name !='' and products_options_type !='" . (int)PRODUCTS_OPTIONS_TYPE_TEXT . "' and products_options_type !='" . (int)PRODUCTS_OPTIONS_TYPE_FILE . "' order by products_options_name");
        while(!$options_values_from->EOF) {
            echo "\n" . '<option name="' . $options_values_from->fields['products_options_name'] . '" value="' . $options_values_from->fields['products_options_id'] . '">' . $options_values_from->fields['products_options_name'] . '</option>';
            $options_values_from->MoveNext();
        }
?>
                  </select>&nbsp;</td>
                  <td class="dataTableHeadingContent" width="75">&nbsp;</td>
                  <td class="dataTableHeadingContent"><?php echo TEXT_SELECT_OPTION_TO; ?><br /><select name="options_id_to">
<?php
        $options_values_to = $db->Execute("select * from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" .(int) $_SESSION['languages_id'] . "' and products_options_name !='' and products_options_type !='" . (int)PRODUCTS_OPTIONS_TYPE_TEXT . "' and products_options_type !='" . (int)PRODUCTS_OPTIONS_TYPE_FILE . "' order by products_options_name");
        while(!$options_values_to->EOF) {
            echo "\n" . '<option name="' . $options_values_to->fields['products_options_name'] . '" value="' . $options_values_to->fields['products_options_id'] . '">' . $options_values_to->fields['products_options_name'] . '</option>';
            $options_values_to->MoveNext();
        }
?>
                  </select>&nbsp;</td>

                  <td align="right" class="dataTableHeadingContent">&nbsp;<?php echo zen_image_submit('button_update.gif', IMAGE_UPDATE); ?>&nbsp;</td>
                  </form>
                </tr>
              </table></td>
            </tr>
<!-- eof: copy all option values to another Option Name -->
          </table>
<?php } // show copier features ?>
        </td>
      </tr>
   </table>




<!-- body_text_eof //-->
<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
