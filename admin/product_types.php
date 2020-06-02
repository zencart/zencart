<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2020 Apr 12 Modified in v1.5.7 $
 */
require('includes/application_top.php');

$action = (isset($_GET['action']) ? $_GET['action'] : '');
if (!isset($_GET['cID'])) $_GET['cID'] = '';
if (!isset($_GET['gID'])) $_GET['gID'] = '';

if (zen_not_null($action)) {
  switch ($action) {
    case 'layout_save':
      $configuration_value = zen_db_prepare_input($_POST['configuration_value']);
      $cID = zen_db_prepare_input($_GET['cID']);

      $db->Execute("UPDATE " . TABLE_PRODUCT_TYPE_LAYOUT . "
                    SET configuration_value = '" . zen_db_input($configuration_value) . "',
                        last_modified = now()
                    WHERE configuration_id = " . (int)$cID);
      $configuration_query = "SELECT configuration_key AS cfgkey, configuration_value AS cfgvalue
                              FROM " . TABLE_PRODUCT_TYPE_LAYOUT;

      zen_redirect(zen_href_link(FILENAME_PRODUCT_TYPES, 'gID=' . $_GET['gID'] . '&cID=' . $cID . '&ptID=' . $_GET['ptID'] . '&action=layout'));
      break;
    case 'insert':
    case 'save':
      if (!isset($_POST['type_name'])) {
        break;
      }
      if (isset($_GET['ptID'])) $type_id = zen_db_prepare_input($_GET['ptID']);
      $type_name = zen_db_prepare_input($_POST['type_name']);
      $handler = zen_db_prepare_input($_POST['handler']);
      $allow_add_to_cart = zen_db_prepare_input(($_POST['catalog_add_to_cart'] ? 'Y' : 'N'));

      $sql_data_array = array(
        'type_name' => $type_name,
        'type_handler' => $handler,
        'allow_add_to_cart' => $allow_add_to_cart);

      if ($action == 'insert') {
        $insert_sql_data = array('date_added' => 'now()');

        $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

        zen_db_perform(TABLE_PRODUCT_TYPES, $sql_data_array);
        $type_id = $db->Insert_ID();
      } elseif ($action == 'save') {
        $master_type = zen_db_prepare_input($_POST['master_type']);

        $update_sql_data = array(
          'last_modified' => 'now()',
          'type_master_type' => $master_type
        );

        $sql_data_array = array_merge($sql_data_array, $update_sql_data);

        zen_db_perform(TABLE_PRODUCT_TYPES, $sql_data_array, 'update', "type_id = " . (int)$type_id);
      }

      $type_image = new upload('default_image');
      $type_image->set_extensions(array('jpg', 'jpeg', 'gif', 'png', 'webp', 'flv', 'webm', 'ogg'));
      $type_image->set_destination(DIR_FS_CATALOG_IMAGES . $_POST['img_dir']);
      if ($type_image->parse() && $type_image->save()) {
        // remove image from database if none
        if ($type_image->filename != 'none') {
          $db->Execute("UPDATE " . TABLE_PRODUCT_TYPES . "
                        SET default_image = '" . zen_db_input($_POST['img_dir'] . $type_image->filename) . "'
                        WHERE type_id = " . (int)$type_id);
        } else {
          $db->Execute("UPDATE " . TABLE_PRODUCT_TYPES . "
                        SET default_image = ''
                        WHERE type_id = " . (int)$type_id);
        }
      }

      zen_redirect(zen_href_link(FILENAME_PRODUCT_TYPES, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'ptID=' . $type_id));
      break;
    case 'deleteconfirm':
      $type_id = zen_db_prepare_input($_POST['ptID']);

      if (isset($_POST['delete_image']) && ($_POST['delete_image'] == 'on')) {
        $product_type = $db->Execute("SELECT default_image
                                      FROM " . TABLE_PRODUCT_TYPES . "
                                      WHERE type_id = " . (int)$type_id);

        $image_location = DIR_FS_CATALOG_IMAGES . $product_type->fields['default_image'];

        if (file_exists($image_location))
          @unlink($image_location);
      }

      $db->Execute("DELETE FROM " . TABLE_PRODUCT_TYPES . "
                    WHERE type_id = " . (int)$type_id);
//        $db->Execute("delete from " . TABLE_PRODUCT_TYPES_INFO . "
//                      where manufacturers_id = '" . (int)$manufacturers_id . "'");

      if (isset($_POST['delete_products']) && ($_POST['delete_products'] == 'on')) {
        $products = $db->Execute("SELECT products_id
                                  FROM " . TABLE_PRODUCTS . "
                                  WHERE products_type = " . (int)$type_id);

        foreach ($products as $product) {
          zen_remove_product($product['products_id']);
        }
      } else {
        $db->Execute("UPDATE " . TABLE_PRODUCTS . "
                      SET products_type = 1
                      WHERE products_type = " . (int)$type_id);
      }

      zen_redirect(zen_href_link(FILENAME_PRODUCT_TYPES, 'page=' . $_GET['page']));
      break;
  }
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
    <script src="includes/general.js"></script>
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
      <?php
      if (isset($_GET['action']) && ($_GET['action'] == 'layout' || $_GET['action'] == 'layout_edit')) {
        $sql = "SELECT type_name
                FROM " . TABLE_PRODUCT_TYPES . "
                WHERE type_id = " . (int)$_GET['ptID'];
        $type_name = $db->Execute($sql);
        ?>
        <h1><?php echo HEADING_TITLE_LAYOUT . $type_name->fields['type_name']; ?></h1>

        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
            <!-- body_text //-->
            <table class="table table-hover">
              <thead>
                <tr class="dataTableHeadingRow">
                  <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_CONFIGURATION_TITLE; ?></th>
                  <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_CONFIGURATION_VALUE; ?></th>
                  <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $configuration = $db->Execute("SELECT configuration_id, configuration_title, configuration_value, configuration_key, use_function
                                               FROM " . TABLE_PRODUCT_TYPE_LAYOUT . "
                                               WHERE product_type_id = " . (int)$_GET['ptID'] . "
                                               ORDER BY sort_order");
                foreach ($configuration as $item) {
                  if (zen_not_null($item['use_function'])) {
                    $use_function = $item['use_function'];
                    if (preg_match('/->/', $use_function)) {
                      $class_method = explode('->', $use_function);
                      if (!is_object(${$class_method[0]})) {
                        include(DIR_WS_CLASSES . $class_method[0] . '.php');
                        ${$class_method[0]} = new $class_method[0]();
                      }
                      $cfgValue = zen_call_function($class_method[1], $item['configuration_value'], ${$class_method[0]});
                    } else {
                      $cfgValue = zen_call_function($use_function, $item['configuration_value']);
                    }
                  } else {
                    $cfgValue = $item['configuration_value'];
                  }

                  if ((!isset($_GET['cID']) || (isset($_GET['cID']) && ($_GET['cID'] == $item['configuration_id']))) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
                    $cfg_extra = $db->Execute("SELECT configuration_key, configuration_description, date_added, last_modified, use_function, set_function
                                                         FROM " . TABLE_PRODUCT_TYPE_LAYOUT . "
                                                         WHERE configuration_id = " . (int)$item['configuration_id']);
                    $cInfo_array = array_merge($item, $cfg_extra->fields);
                    $cInfo = new objectInfo($cInfo_array);
                  }

                  if ((isset($cInfo) && is_object($cInfo)) && ($item['configuration_id'] == $cInfo->configuration_id)) {
                    echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href=\'' . zen_href_link(FILENAME_PRODUCT_TYPES, 'ptID=' . $_GET['ptID'] . '&cID=' . $cInfo->configuration_id . '&action=layout_edit') . '\'" role="button">' . "\n";
                  } else {
                    echo '                  <tr class="dataTableRow" onclick="document.location.href=\'' . zen_href_link(FILENAME_PRODUCT_TYPES, 'ptID=' . $_GET['ptID'] . '&cID=' . $item['configuration_id'] . '&action=layout_edit') . '\'" role="button">' . "\n";
                  }
                  ?>
                <td class="dataTableContent"><?php echo $item['configuration_title']; ?></td>
                <td class="dataTableContent"><?php echo htmlspecialchars($cfgValue, ENT_COMPAT, CHARSET, TRUE); ?></td>
                <td class="dataTableContent text-right"><?php
                  if ((isset($cInfo) && is_object($cInfo)) && ($item['configuration_id'] == $cInfo->configuration_id)) {
                    echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', '');
                  } else {
                    echo '<a href="' . zen_href_link(FILENAME_PRODUCT_TYPES, 'ptID=' . $_GET['ptID'] . '&cID=' . $item['configuration_id'] . '&action=layout') . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>';
                  }
                  ?>&nbsp;</td>
                </tr>
                <?php
              }
              ?>
              </tbody>
            </table>
          </div>
          <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">
            <?php
            $heading = array();
            $contents = array();

            switch ($action) {
              case 'layout_edit':
                $heading[] = array('text' => '<h4>' . $cInfo->configuration_title . '</h4>');

                if ($cInfo->set_function) {
                  eval('$value_field = ' . $cInfo->set_function . '"' . htmlspecialchars($cInfo->configuration_value, ENT_COMPAT, CHARSET, TRUE) . '");');
                } else {
                  $value_field = zen_draw_input_field('configuration_value', htmlspecialchars($cInfo->configuration_value, ENT_COMPAT, CHARSET, TRUE), 'size="60"');
                }

                $contents = array('form' => zen_draw_form('configuration', FILENAME_PRODUCT_TYPES, 'ptID=' . $_GET['ptID'] . '&cID=' . $cInfo->configuration_id . '&action=layout_save'));
                if (ADMIN_CONFIGURATION_KEY_ON == 1) {
                  $contents[] = array('text' => '<strong>Key: ' . $cInfo->configuration_key . '</strong><br>');
                }
                $contents[] = array('text' => TEXT_EDIT_INTRO);
                $contents[] = array('text' => '<br><b>' . $cInfo->configuration_title . '</b><br>' . $cInfo->configuration_description . '<br>' . $value_field);
                $contents[] = array('align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-primary">' . IMAGE_UPDATE . '</button>&nbsp;<a href="' . zen_href_link(FILENAME_PRODUCT_TYPES, 'action=layout&ptID=' . $_GET['ptID'] . '&cID=' . $cInfo->configuration_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              default:
                if (isset($cInfo) && is_object($cInfo)) {
                  $heading[] = array('text' => '<h4>' . $cInfo->configuration_title . '</h4>');

                  if (ADMIN_CONFIGURATION_KEY_ON == 1) {
                    $contents[] = array('text' => '<strong>Key: ' . $cInfo->configuration_key . '</strong><br>');
                  }
                  $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_PRODUCT_TYPES, 'ptID=' . $_GET['ptID'] . '&cID=' . $cInfo->configuration_id . '&action=layout_edit') . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a> <a href="' . zen_href_link(FILENAME_PRODUCT_TYPES, 'ptID=' . $_GET['ptID'] . '&cID=' . $cInfo->configuration_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                  $contents[] = array('text' => '<br>' . $cInfo->configuration_description);
                  $contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . ' ' . zen_date_short($cInfo->date_added));
                  if (zen_not_null($cInfo->last_modified)) {
                    $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . zen_date_short($cInfo->last_modified));
                  }
                }
                break;
            }

            if ((zen_not_null($heading)) && (zen_not_null($contents))) {
              $box = new box;
              echo $box->infoBox($heading, $contents);
            }
            ?>
          </div>
          <!-- body_text_eof //-->
        </div>
        <?php
      } else {
        ?>
        <h1><?php echo HEADING_TITLE; ?></h1>
        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
            <!-- body_text //-->

            <table class="table table-hover">
              <thead>
                <tr class="dataTableHeadingRow">
                  <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCT_TYPES; ?></th>
                  <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_PRODUCT_TYPES_ALLOW_ADD_TO_CART; ?></th>
                  <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $product_types_query_raw = "SELECT * FROM " . TABLE_PRODUCT_TYPES;
                $product_types_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $product_types_query_raw, $product_types_query_numrows);
                $product_types = $db->Execute($product_types_query_raw);
                foreach ($product_types as $product_type) {
                  if ((!isset($_GET['ptID']) || (isset($_GET['ptID']) && ($_GET['ptID'] == $product_type['type_id']))) && !isset($ptInfo) && (substr($action, 0, 3) != 'new')) {
                    $product_type_products = $db->Execute("SELECT COUNT(*) AS products_count
                                                           FROM " . TABLE_PRODUCTS . "
                                                           WHERE products_type = " . (int)$product_type['type_id']);

                    $ptInfo_array = array_merge($product_type, $product_type_products->fields);

                    $ptInfo = new objectInfo($ptInfo_array);
                  }

                  if (isset($ptInfo) && is_object($ptInfo) && ($product_type['type_id'] == $ptInfo->type_id)) {
                    echo '              <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href=\'' . zen_href_link(FILENAME_PRODUCT_TYPES, 'page=' . $_GET['page'] . '&ptID=' . $product_type['type_id'] . '&action=layout') . '\'" role="button">' . "\n";
                  } else {
                    echo '              <tr class="dataTableRow" onclick="document.location.href=\'' . zen_href_link(FILENAME_PRODUCT_TYPES, 'page=' . $_GET['page'] . '&ptID=' . $product_type['type_id']) . '\'" role="button">' . "\n";
                  }
                  ?>
                <td class="dataTableContent"><?php echo $product_type['type_name']; ?></td>
                <td class="dataTableContent" align="center"><?php echo $product_type['allow_add_to_cart']; ?></td>
                <td class="dataTableContent" align="right"><?php
                  if ((isset($ptInfo) && is_object($ptInfo)) && ($product_type['type_id'] == $ptInfo->type_id)) {
                    echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', '');
                  } else {
                    echo '<a href="' . zen_href_link(FILENAME_PRODUCT_TYPES, 'ptID=' . $product_type['type_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>';
                  }
                  ?>&nbsp;</td>
                </tr>
                <?php
              }
              ?>
              </tbody>
            </table>
          </div>
          <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">
            <?php
            $heading = array();
            $contents = array();

            switch ($action) {
              case 'new':
                $heading[] = array('text' => '<h4>' . TEXT_HEADING_NEW_PRODUCT_TYPE . '</h4>');

                $contents = array('form' => zen_draw_form('new_product_type', FILENAME_PRODUCT_TYPES, 'action=insert', 'post', 'enctype="multipart/form-data"'));
                $contents[] = array('text' => TEXT_NEW_INTRO);
                break;
              case 'edit':
                $heading[] = array('text' => '<h4>' . TEXT_HEADING_EDIT_PRODUCT_TYPE . ' :: ' . $ptInfo->type_name . '</h4>');

                $contents = array('form' => zen_draw_form('product_types', FILENAME_PRODUCT_TYPES, 'page=' . $_GET['page'] . '&ptID=' . $ptInfo->type_id . '&action=save', 'post', 'enctype="multipart/form-data" class="form-horizontal"'));
                $contents[] = array('text' => TEXT_EDIT_INTRO);
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_PRODUCT_TYPES_NAME, 'type_name', 'class="control-label"') . zen_draw_input_field('type_name', $ptInfo->type_name, zen_set_field_length(TABLE_PRODUCT_TYPES, 'type_name') . ' class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_PRODUCT_TYPES_IMAGE, 'default_image', 'class="control-label"') . zen_draw_file_field('default_image') . '<br />' . $ptInfo->default_image);
                $dir_info = zen_build_subdirectories_array(DIR_FS_CATALOG_IMAGES);
                $default_directory = substr($ptInfo->default_image, 0, strpos($ptInfo->default_image, '/') + 1);
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_PRODUCTS_IMAGE_DIR, 'img_dir' ,'class="control-label"') . zen_draw_pull_down_menu('img_dir', $dir_info, $default_directory, 'class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_info_image($ptInfo->default_image, $ptInfo->type_name));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_PRODUCT_TYPES_HANDLER, 'handler', 'class="control-label"') . zen_draw_input_field('handler', $ptInfo->type_handler, zen_set_field_length(TABLE_PRODUCT_TYPES, 'type_handler') . ' class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_PRODUCT_TYPES_ALLOW_ADD_CART, 'catalog_add_to_cart', 'class="control-label"') . zen_draw_checkbox_field('catalog_add_to_cart', $ptInfo->allow_add_to_cart, ($ptInfo->allow_add_to_cart == 'Y' ? true : false), 'class="form-control"'));
                $sql = "SELECT type_id, type_name FROM " . TABLE_PRODUCT_TYPES;
                $product_type_list = $db->Execute($sql);
                foreach ($product_type_list as $item) {
                  $product_type_array[] = array(
                    'text' => $item['type_name'],
                    'id' => $item['type_id']);
                }
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_MASTER_TYPE, 'master_type', 'class="control-label"') . zen_draw_pull_down_menu('master_type', $product_type_array, $ptInfo->type_master_type, 'class="form-control"'));

                $contents[] = array('align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-primary">' . IMAGE_SAVE . '</button> <a href="' . zen_href_link(FILENAME_PRODUCT_TYPES, 'page=' . $_GET['page'] . '&ptID=' . $ptInfo->type_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
/*              case 'delete':
                $heading[] = array('text' => '<h4>' . TEXT_HEADING_DELETE_PRODUCT_TYPE . '</h4>');

                $contents = array('form' => zen_draw_form('manufacturers', FILENAME_PRODUCT_TYPES, 'page=' . $_GET['page'] . '&action=deleteconfirm') . zen_draw_hidden_field('ptID', $ptInfo->type_id));
                $contents[] = array('text' => TEXT_DELETE_INTRO);
                $contents[] = array('text' => '<br><b>' . $ptInfo->type_name . '</b>');
                $contents[] = array('text' => '<br>' . zen_draw_checkbox_field('delete_image', '', true) . ' ' . TEXT_DELETE_IMAGE);

                if ($ptInfo->products_count > 0) {
                  $contents[] = array('text' => '<br>' . zen_draw_checkbox_field('delete_products') . ' ' . TEXT_DELETE_PRODUCTS);
                  $contents[] = array('text' => '<br>' . sprintf(TEXT_DELETE_WARNING_PRODUCTS, $ptInfo->products_count));
                }

                $contents[] = array('align' => 'text-center', 'text' => '<br>' . zen_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . zen_href_link(FILENAME_PRODUCT_TYPES, 'page=' . $_GET['page'] . '&ptID=' . $ptInfo->type_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
                break;*/
              default:
                if (isset($ptInfo) && is_object($ptInfo)) {
                  $heading[] = array('text' => '<h4>' . $ptInfo->type_name . '</h4>');
// remove delete for now to avoid issues
//        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_PRODUCT_TYPES, 'page=' . $_GET['page'] . '&ptID=' . $ptInfo->type_id . '&action=edit') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . zen_href_link(FILENAME_PRODUCT_TYPES, 'page=' . $_GET['page'] . '&ptID=' . $ptInfo->type_id . '&action=delete') . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a> <a href="' . zen_href_link(FILENAME_PRODUCT_TYPES, 'page=' . $_GET['page'] . '&ptID=' . $ptInfo->type_id . '&action=layout') . '">' . zen_image_button('button_layout.gif', IMAGE_LAYOUT) . '</a>' );
                  $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_PRODUCT_TYPES, 'page=' . $_GET['page'] . '&ptID=' . $ptInfo->type_id . '&action=edit') . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a> <a href="' . zen_href_link(FILENAME_PRODUCT_TYPES, 'page=' . $_GET['page'] . '&ptID=' . $ptInfo->type_id . '&action=layout') . '" class="btn btn-default" role="button">' . IMAGE_LAYOUT . '</a>');
                  $contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . ' ' . zen_date_short($ptInfo->date_added));
                  if (zen_not_null($ptInfo->last_modified)) {
                    $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . zen_date_short($ptInfo->last_modified));
                  }
                  $contents[] = array('text' => '<br>' . zen_info_image($ptInfo->manufacturers_image, $ptInfo->manufacturers_name));
                  $contents[] = array('text' => '<br>' . TEXT_PRODUCTS . ' ' . $ptInfo->products_count);
                }
                break;
            }

            if ((zen_not_null($heading)) && (zen_not_null($contents))) {
              $box = new box;
              echo $box->infoBox($heading, $contents);
            }
            ?>
            <!-- body_text_eof //-->
          </div>
        </div>
        <div class="row">
          <div class="col-cm-6"><?php echo $product_types_split->display_count($product_types_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_PRODUCT_TYPES); ?></div>
          <div class="col-sm-6 text-right"><?php echo $product_types_split->display_links($product_types_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></div>
        </div>
        <?php
      }
      ?>
      <!-- body_eof //-->
    </div>
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
