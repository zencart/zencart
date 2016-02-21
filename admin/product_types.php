<?php
/**
 * @package admin
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson  Modified in v1.6.0 $
 */

  require('includes/application_top.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (zen_not_null($action)) {
    switch ($action) {
      case 'layout_save':
        // demo active test
        if (zen_admin_demo()) {
          $_GET['action']= '';
          $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
          zen_redirect(zen_href_link(FILENAME_PRODUCT_TYPES, 'gID=' . $_GET['gID'] . '&cID=' . $cID));
        }
        $configuration_value = zen_db_prepare_input($_POST['configuration_value']);
        $cID = zen_db_prepare_input($_GET['cID']);

        $db->Execute("update " . TABLE_PRODUCT_TYPE_LAYOUT . "
                      set configuration_value = '" . zen_db_input($configuration_value) . "',
                          last_modified = now() where configuration_id = '" . (int)$cID . "'");
        $configuration_query = 'select configuration_key as cfgkey, configuration_value as cfgvalue
                          from ' . TABLE_PRODUCT_TYPE_LAYOUT;

        // set the WARN_BEFORE_DOWN_FOR_MAINTENANCE to false if DOWN_FOR_MAINTENANCE = true
        if ( (WARN_BEFORE_DOWN_FOR_MAINTENANCE == 'true') && (DOWN_FOR_MAINTENANCE == 'true') ) {
        $db->Execute("update " . TABLE_CONFIGURATION . "
                      set configuration_value = 'false', last_modified = '" . NOW . "'
                      where configuration_key = 'WARN_BEFORE_DOWN_FOR_MAINTENANCE'"); }

        $configuration_query = 'select configuration_key as cfgkey, configuration_value as cfgvalue
                          from ' . TABLE_CONFIGURATION;


        zen_redirect(zen_href_link(FILENAME_PRODUCT_TYPES, 'gID=' . $_GET['gID'] . '&cID=' . $cID . '&ptID=' . $_GET['ptID'] . '&action=layout'));
        break;
      case 'insert':
      case 'save':
        if (isset($_GET['ptID'])) $type_id = zen_db_prepare_input($_GET['ptID']);
        $type_name = zen_db_prepare_input($_POST['type_name']);
        $handler = zen_db_prepare_input($_POST['handler']);
        $allow_add_to_cart =  zen_db_prepare_input(($_POST['catalog_add_to_cart'] ? 'Y' : 'N'));

        $sql_data_array = array('type_name' => $type_name,
                                'type_handler' => $handler,
                                'allow_add_to_cart' => $allow_add_to_cart);

        if ($action == 'insert') {
          $insert_sql_data = array('date_added' => 'now()');

          $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

          zen_db_perform(TABLE_PRODUCT_TYPES, $sql_data_array);
          $type_id = $db->Insert_ID();
        } elseif ($action == 'save') {
          $master_type = zen_db_prepare_input($_POST['master_type']);

          $update_sql_data = array('last_modified' => 'now()',
                                   'type_master_type' => $master_type
           );

          $sql_data_array = array_merge($sql_data_array, $update_sql_data);

          zen_db_perform(TABLE_PRODUCT_TYPES, $sql_data_array, 'update', "type_id = '" . (int)$type_id . "'");
        }

        $type_image = new upload('default_image');
        $type_image->set_extensions(array('jpg','jpeg','gif','png','webp','flv','webm','ogg'));
        $type_image->set_destination(DIR_FS_CATALOG_IMAGES . $_POST['img_dir']);
        if ( $type_image->parse() &&  $type_image->save()) {
          // remove image from database if none
          if ($type_image->filename != 'none') {
            $db->Execute("update " . TABLE_PRODUCT_TYPES . "
                          set default_image = '" .  zen_db_input($_POST['img_dir'] . $type_image->filename) . "'
                          where type_id = '" . (int)$type_id . "'");
          } else {
            $db->Execute("update " . TABLE_PRODUCT_TYPES . "
                          set default_image = ''
                          where type_id = '" . (int)$type_id . "'");
          }
        }

        zen_redirect(zen_href_link(FILENAME_PRODUCT_TYPES, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'ptID=' . $type_id));
        break;
      case 'deleteconfirm':
        // demo active test
        if (zen_admin_demo()) {
          $_GET['action']= '';
          $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
          zen_redirect(zen_href_link(FILENAME_PRODUCT_TYPES, 'page=' . $_GET['page']));
        }
        $type_id = zen_db_prepare_input($_POST['ptID']);

        if (isset($_POST['delete_image']) && ($_POST['delete_image'] == 'on')) {
          $product_type = $db->Execute("select default_image
                                        from " . TABLE_PRODUCT_TYPES . "
                                        where type_id = '" . (int)$type_id . "'");

          $image_location = DIR_FS_CATALOG_IMAGES . $product_type->fields['default_image'];

          if (file_exists($image_location)) @unlink($image_location);
        }

        $db->Execute("delete from " . TABLE_PRODUCT_TYPES . "
                      where type_id = '" . (int)$type_id . "'");
//        $db->Execute("delete from " . TABLE_PRODUCT_TYPES_INFO . "
//                      where manufacturers_id = '" . (int)$manufacturers_id . "'");

        if (isset($_POST['delete_products']) && ($_POST['delete_products'] == 'on')) {
          $products = $db->Execute("select products_id
                                    from " . TABLE_PRODUCTS . "
                                    where products_type = '" . (int)$type_id . "'");

          while (!$products->EOF) {
            zen_remove_product($products->fields['products_id']);
            $products->MoveNext();
          }
        } else {
          $db->Execute("update " . TABLE_PRODUCTS . "
                        set products_type = '1'
                        where products_type = '" . (int)$type_id . "'");
        }

        zen_redirect(zen_href_link(FILENAME_PRODUCT_TYPES, 'page=' . $_GET['page']));
        break;
    }
  }
require('includes/admin_html_head.php');
?>
</head>
<body>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<?php
if ($_GET['action'] == 'layout' || $_GET['action'] == 'layout_edit') {
  $sql = "select type_name from " . TABLE_PRODUCT_TYPES . "
          where type_id = '"   . (int)$_GET['ptID'] . "'";
  $type_name = $db->Execute($sql);


?>
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE_LAYOUT .  $type_name->fields['type_name']; ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent" width="55%"><?php echo TABLE_HEADING_CONFIGURATION_TITLE; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CONFIGURATION_VALUE; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $configuration = $db->Execute("select configuration_id, configuration_title, configuration_value, configuration_key,
                                        use_function from " . TABLE_PRODUCT_TYPE_LAYOUT . "
                                        where product_type_id = '" . (int)$_GET['ptID'] . "'
                                        order by sort_order");
  while (!$configuration->EOF) {
    if (zen_not_null($configuration->fields['use_function'])) {
      $use_function = $configuration->fields['use_function'];
      if (preg_match('/->/', $use_function)) {
        $class_method = explode('->', $use_function);
        if (!is_object(${$class_method[0]})) {
          include(DIR_WS_CLASSES . $class_method[0] . '.php');
          ${$class_method[0]} = new $class_method[0]();
        }
        $cfgValue = zen_call_function($class_method[1], $configuration->fields['configuration_value'], ${$class_method[0]});
      } else {
        $cfgValue = zen_call_function($use_function, $configuration->fields['configuration_value']);
      }
    } else {
      $cfgValue = $configuration->fields['configuration_value'];
    }

    if ((!isset($_GET['cID']) || (isset($_GET['cID']) && ($_GET['cID'] == $configuration->fields['configuration_id']))) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
      $cfg_extra = $db->Execute("select configuration_key, configuration_description, date_added,
                                        last_modified, use_function, set_function
                                 from " . TABLE_PRODUCT_TYPE_LAYOUT . "
                                 where configuration_id = '" . (int)$configuration->fields['configuration_id'] . "'");
      $cInfo_array = array_merge($configuration->fields, $cfg_extra->fields);
      $cInfo = new objectInfo($cInfo_array);
    }

    if ( (isset($cInfo) && is_object($cInfo)) && ($configuration->fields['configuration_id'] == $cInfo->configuration_id) ) {
      echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link(FILENAME_PRODUCT_TYPES, 'ptID=' . $_GET['ptID'] . '&cID=' . $cInfo->configuration_id . '&action=layout_edit') . '\'">' . "\n";
    } else {
      echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link(FILENAME_PRODUCT_TYPES, 'ptID=' . $_GET['ptID'] . '&cID=' . $configuration->fields['configuration_id'] . '&action=layout_edit') . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $configuration->fields['configuration_title']; ?></td>
                <td class="dataTableContent"><?php echo zen_output_string_protected($cfgValue); ?></td>
                <td class="dataTableContent" align="right"><?php if ( (isset($cInfo) && is_object($cInfo)) && ($configuration->fields['configuration_id'] == $cInfo->configuration_id) ) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . zen_href_link(FILENAME_PRODUCT_TYPES, 'ptID=' . $_GET['ptID'] . '&cID=' . $configuration->fields['configuration_id'] . '&action=layout') . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    $configuration->MoveNext();
  }
?>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'layout_edit':
      $heading[] = array('text' => '<b>' . $cInfo->configuration_title . '</b>');

      if ($cInfo->set_function) {
        eval('$value_field = ' . $cInfo->set_function . '"' . zen_output_string_protected($cInfo->configuration_value) . '");');
      } else {
        $value_field = zen_draw_input_field('configuration_value', zen_output_string_protected($cInfo->configuration_value), 'size="60"');
      }

      $contents = array('form' => zen_draw_form('configuration', FILENAME_PRODUCT_TYPES, 'ptID=' . $_GET['ptID'] . '&cID=' . $cInfo->configuration_id . '&action=layout_save'));
      if (ADMIN_CONFIGURATION_KEY_ON == 1) {
        $contents[] = array('text' => '<strong>Key: ' . $cInfo->configuration_key . '</strong><br />');
      }
      $contents[] = array('text' => TEXT_EDIT_INTRO);
      $contents[] = array('text' => '<br><b>' . $cInfo->configuration_title . '</b><br>' . $cInfo->configuration_description . '<br>' . $value_field);
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_update.gif', IMAGE_UPDATE) . '&nbsp;<a href="' . zen_href_link(FILENAME_PRODUCT_TYPES, 'action=layout&ptID=' . $_GET['ptID'] . '&cID=' . $cInfo->configuration_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (isset($cInfo) && is_object($cInfo)) {
        $heading[] = array('text' => '<b>' . $cInfo->configuration_title . '</b>');

        if (ADMIN_CONFIGURATION_KEY_ON == 1) {
          $contents[] = array('text' => '<strong>Key: ' . $cInfo->configuration_key . '</strong><br />');
        }
        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_PRODUCT_TYPES, 'ptID=' . $_GET['ptID'] . '&cID=' . $cInfo->configuration_id . '&action=layout_edit') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a>' . '&nbsp;<a href="' . zen_href_link(FILENAME_PRODUCT_TYPES, 'ptID=' . $_GET['ptID'] . '&cID=' . $cInfo->configuration_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        $contents[] = array('text' => '<br>' . $cInfo->configuration_description);
        $contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . ' ' . zen_date_short($cInfo->date_added));
        if (zen_not_null($cInfo->last_modified)) $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . zen_date_short($cInfo->last_modified));
      }
      break;
  }

  if ( (zen_not_null($heading)) && (zen_not_null($contents)) ) {
    echo '            <td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
  }
?>
          </tr>
        </table></td>
      </tr>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<?php
} else {
?>
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCT_TYPES; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_PRODUCT_TYPES_ALLOW_ADD_TO_CART; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $product_types_query_raw = "select * from " . TABLE_PRODUCT_TYPES;
  $product_types_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $product_types_query_raw, $product_types_query_numrows);
  $product_types = $db->Execute($product_types_query_raw);
  while (!$product_types->EOF) {
    if ((!isset($_GET['ptID']) || (isset($_GET['ptID']) && ($_GET['ptID'] == $product_types->fields['type_id']))) && !isset($ptInfo) && (substr($action, 0, 3) != 'new')) {
      $product_type_products = $db->Execute("select count(*) as products_count
                                             from " . TABLE_PRODUCTS . "
                                             where products_type = '" . (int)$product_types->fields['type_id'] . "'");

      $ptInfo_array = array_merge($product_types->fields, $product_type_products->fields);

      $ptInfo = new objectInfo($ptInfo_array);
    }

    if (isset($ptInfo) && is_object($ptInfo) && ($product_types->fields['type_id'] == $ptInfo->type_id)) {
      echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link(FILENAME_PRODUCT_TYPES, 'page=' . $_GET['page'] . '&ptID=' . $product_types->fields['type_id'] . '&action=layout' ) . '\'">' . "\n";
    } else {
      echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link(FILENAME_PRODUCT_TYPES, 'page=' . $_GET['page'] . '&ptID=' . $product_types->fields['type_id']) . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $product_types->fields['type_name']; ?></td>
                <td class="dataTableContent" align="center"><?php echo $product_types->fields['allow_add_to_cart']; ?></td>
                <td class="dataTableContent" align="right"><?php if ( (isset($ptInfo) && is_object($ptInfo)) && ($product_types->fields['type_id'] == $ptInfo->type_id) ) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . zen_href_link(FILENAME_PRODUCT_TYPES, 'ptID=' . $product_types->fields['type_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;  </td>
              </tr>
<?php
    $product_types->MoveNext();
  }
?>
              <tr>
                <td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $product_types_split->display_count($product_types_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_PRODUCT_TYPES); ?></td>
                    <td class="smallText" align="right"><?php echo $product_types_split->display_links($product_types_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'new':
      $heading[] = array('text' => '<b>' . TEXT_HEADING_NEW_PRODUCT_TYPE . '</b>');

      $contents = array('form' => zen_draw_form('new_product_type', FILENAME_PRODUCT_TYPES, 'action=insert', 'post', 'enctype="multipart/form-data"'));
      $contents[] = array('text' => TEXT_NEW_INTRO);
      break;
    case 'edit':
      $heading[] = array('text' => '<b>' . TEXT_HEADING_EDIT_PRODUCT_TYPE . ' :: ' . $ptInfo->type_name . '</b>');

      $contents = array('form' => zen_draw_form('product_types', FILENAME_PRODUCT_TYPES, 'page=' . $_GET['page'] . '&ptID=' . $ptInfo->type_id . '&action=save', 'post', 'enctype="multipart/form-data"'));
      $contents[] = array('text' => TEXT_EDIT_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_PRODUCT_TYPES_NAME . '<br>' . zen_draw_input_field('type_name', $ptInfo->type_name, zen_set_field_length(TABLE_PRODUCT_TYPES, 'type_name')));
      $contents[] = array('text' => '<br />' . TEXT_PRODUCT_TYPES_IMAGE . '<br>' . zen_draw_file_field('default_image') . '<br />' . $ptInfo->default_image);
      $dir_info = zen_build_subdirectories_array(DIR_FS_CATALOG_IMAGES);
      $default_directory = substr( $ptInfo->default_image, 0,strpos( $ptInfo->default_image, '/')+1);
      $contents[] = array('text' => '<BR />' . TEXT_PRODUCTS_IMAGE_DIR . zen_draw_pull_down_menu('img_dir', $dir_info, $default_directory));
      $contents[] = array('text' => '<br />' . zen_info_image($ptInfo->default_image, $ptInfo->type_name));
      $contents[] = array('text' => '<br />' . TEXT_PRODUCT_TYPES_HANDLER . '<br>' . zen_draw_input_field('handler', $ptInfo->type_handler, zen_set_field_length(TABLE_PRODUCT_TYPES, 'type_handler')));
       $contents[] = array('text' => '<br />' . TEXT_PRODUCT_TYPES_ALLOW_ADD_CART . '<br>' . zen_draw_checkbox_field('catalog_add_to_cart', $ptInfo->allow_add_to_cart, ($ptInfo->allow_add_to_cart == 'Y' ? true : false)));
       $sql = "select type_id, type_name from " . TABLE_PRODUCT_TYPES;
       $product_type_list = $db->Execute($sql);
       while (!$product_type_list->EOF) {
         $product_type_array[] = array('text' => $product_type_list->fields['type_name'], 'id' => $product_type_list->fields['type_id']);
         $product_type_list->MoveNext();
       }
      $contents[] = array('text' => '<br />' . TEXT_MASTER_TYPE . zen_draw_pull_down_menu('master_type', $product_type_array, $ptInfo->type_master_type));

      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . zen_href_link(FILENAME_PRODUCT_TYPES, 'page=' . $_GET['page'] . '&ptID=' . $ptInfo->type_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_HEADING_DELETE_PRODUCT_TYPE . '</b>');

      $contents = array('form' => zen_draw_form('manufacturers', FILENAME_PRODUCT_TYPES, 'page=' . $_GET['page'] . '&action=deleteconfirm') . zen_draw_hidden_field('ptID', $ptInfo->type_id));
      $contents[] = array('text' => TEXT_DELETE_INTRO);
      $contents[] = array('text' => '<br><b>' . $ptInfo->type_name . '</b>');
      $contents[] = array('text' => '<br>' . zen_draw_checkbox_field('delete_image', '', true) . ' ' . TEXT_DELETE_IMAGE);

      if ($ptInfo->products_count > 0) {
        $contents[] = array('text' => '<br>' . zen_draw_checkbox_field('delete_products') . ' ' . TEXT_DELETE_PRODUCTS);
        $contents[] = array('text' => '<br>' . sprintf(TEXT_DELETE_WARNING_PRODUCTS, $ptInfo->products_count));
      }

      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . zen_href_link(FILENAME_PRODUCT_TYPES, 'page=' . $_GET['page'] . '&ptID=' . $ptInfo->type_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (isset($ptInfo) && is_object($ptInfo)) {
        $heading[] = array('text' => '<b>' . $ptInfo->type_name . '</b>');
// remove delete for now to avoid issues
//        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_PRODUCT_TYPES, 'page=' . $_GET['page'] . '&ptID=' . $ptInfo->type_id . '&action=edit') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . zen_href_link(FILENAME_PRODUCT_TYPES, 'page=' . $_GET['page'] . '&ptID=' . $ptInfo->type_id . '&action=delete') . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a> <a href="' . zen_href_link(FILENAME_PRODUCT_TYPES, 'page=' . $_GET['page'] . '&ptID=' . $ptInfo->type_id . '&action=layout') . '">' . zen_image_button('button_layout.gif', IMAGE_LAYOUT) . '</a>' );
        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_PRODUCT_TYPES, 'page=' . $_GET['page'] . '&ptID=' . $ptInfo->type_id . '&action=edit') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . zen_href_link(FILENAME_PRODUCT_TYPES, 'page=' . $_GET['page'] . '&ptID=' . $ptInfo->type_id . '&action=layout') . '">' . zen_image_button('button_layout.gif', IMAGE_LAYOUT) . '</a>' );
        $contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . ' ' . zen_date_short($ptInfo->date_added));
        if (zen_not_null($ptInfo->last_modified)) $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . zen_date_short($ptInfo->last_modified));
        $contents[] = array('text' => '<br>' . zen_info_image($ptInfo->manufacturers_image, $ptInfo->manufacturers_name));
        $contents[] = array('text' => '<br>' . TEXT_PRODUCTS . ' ' . $ptInfo->products_count);
      }
      break;
  }

  if ( (zen_not_null($heading)) && (zen_not_null($contents)) ) {
    echo '            <td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
  }
?>
          </tr>
        </table></td>
      </tr>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<?php
}
?>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>