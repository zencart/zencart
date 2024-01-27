<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: neekfenwick 2023 Dec 09 Modified in v2.0.0-alpha1 $
 */

  require 'includes/application_top.php';
  require DIR_FS_CATALOG . 'includes/extra_configures/log_files.php'; 
  require DIR_WS_CLASSES . 'currencies.php';
  $currencies = new currencies();

  $languages = zen_get_languages();

  $products_filter = (isset($_GET['products_filter']) ? $_GET['products_filter'] : 0);

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  $current_category_id = (isset($_GET['current_category_id']) ? (int)$_GET['current_category_id'] : (int)$current_category_id);

  $processing_message = '';
  $processing_action_url = '';

  zen_set_time_limit(600);
  switch($action) {

// update all products in catalog
    case ('update_all_products_attributes_sort_order'):
        $all_products_attributes= $db->Execute("SELECT p.products_id, pa.products_attributes_id FROM " .
        TABLE_PRODUCTS . " p, " .
        TABLE_PRODUCTS_ATTRIBUTES . " pa " . "
        WHERE p.products_id= pa.products_id"
        );
        $product_id_updated = '';
        while (!$all_products_attributes->EOF)
        {
          $product_id_updated .= ' - ' . $all_products_attributes->fields['products_id'] . ':' . $all_products_attributes->fields['products_attributes_id'];
          zen_update_attributes_products_option_values_sort_order($all_products_attributes->fields['products_id']);
          $all_products_attributes->MoveNext();
        }
        $messageStack->add_session(SUCCESS_PRODUCT_UPDATE_SORT_ALL, 'success');
        zen_record_admin_activity('Store Manager executed [update all products attributes sort order]', 'info');
        $action='';
        zen_redirect(zen_href_link(FILENAME_STORE_MANAGER));
      break;

    case ('update_all_products_price_sorter'):
      // reset products_price_sorter for searches etc.
        $sql = "SELECT products_id FROM " . TABLE_PRODUCTS;
        $update_prices = $db->Execute($sql);

        while (!$update_prices->EOF)
        {
          zen_update_products_price_sorter($update_prices->fields['products_id']);
          $update_prices->MoveNext();
        }
        $messageStack->add_session(SUCCESS_PRODUCT_UPDATE_PRODUCTS_PRICE_SORTER, 'success');
        zen_record_admin_activity('Store Manager executed [update all products price sorter]', 'info');
        $action='';
        zen_redirect(zen_href_link(FILENAME_STORE_MANAGER));
    break;

//    case ('update_all_products_viewed'):
//    // reset products_viewed to 0
////        $sql = "UPDATE " . TABLE_PRODUCTS_DESCRIPTION . " SET products_viewed = 0";
////        $db->Execute($sql);
//        $sql = "TRUNCATE TABLE " . TABLE_COUNT_PRODUCT_VIEWS;
//        $db->Execute($sql);
//
//        $messageStack->add_session(SUCCESS_PRODUCT_UPDATE_PRODUCTS_VIEWED, 'success');
//        zen_record_admin_activity('Store Manager executed [update all products viewed]', 'info');
//        $action='';
//        zen_redirect(zen_href_link(FILENAME_STORE_MANAGER));
//    break;

    case ('update_all_products_ordered'):
      // reset products_ordered to 0
        $sql = "UPDATE " . TABLE_PRODUCTS . " SET products_ordered= '0'";
        $db->Execute($sql);

        $messageStack->add_session(SUCCESS_PRODUCT_UPDATE_PRODUCTS_ORDERED, 'success');
        zen_record_admin_activity('Store Manager executed [update all products ordered]', 'info');
        $action='';
        zen_redirect(zen_href_link(FILENAME_STORE_MANAGER));
    break;

    case ('update_counter'):
    if ($_POST['new_counter'] == '' || strval($_POST['new_counter']) != strval((int)$_POST['new_counter'])) break;
    $sql = "UPDATE " . TABLE_COUNTER . " SET counter= '" . (int)$_POST['new_counter'] . "'";
    $db->Execute($sql);

    $messageStack->add_session(SUCCESS_UPDATE_COUNTER . (int)$_POST['new_counter'], 'success');
    zen_record_admin_activity('Store Manager executed [update counter], set to ' . (int)$_POST['new_counter'], 'info');
    $action='';
    zen_redirect(zen_href_link(FILENAME_STORE_MANAGER));
    break;

    case ('optimize_db_start'):
      $processing_message = TEXT_INFO_OPTIMIZING_DATABASE_TABLES;
      $processing_action_url = zen_href_link(FILENAME_STORE_MANAGER, 'action=optimize_db_do');
    break;
    case ('optimize_db_do'):
    // clean out unused space in database
        $sql = "SHOW TABLE STATUS FROM `" . DB_DATABASE ."`";
        $tables = $db->Execute($sql);
        while(!$tables->EOF) {
          // skip tables not matching prefixes
          if (DB_PREFIX != '' && substr($tables->fields['Name'], 0, strlen(DB_PREFIX)) != DB_PREFIX) {
            $tables->MoveNext();
            continue;
          }
          zen_set_time_limit(600);
          $db->Execute("OPTIMIZE TABLE `" . $tables->fields['Name'] . "`");
          $i++;
          if ($i/7 == (int)($i/7)) sleep(2);
          $tables->MoveNext();
        }
        $messageStack->add_session(SUCCESS_DB_OPTIMIZE . ' ' . $i, 'success');
        zen_record_admin_activity('Store Manager executed [optimize database tables]', 'info');
        $action='';
        zen_redirect(zen_href_link(FILENAME_STORE_MANAGER));
    break;

// clean out old DEBUG logfiles
    case 'clean_debug_files':
      foreach(array(DIR_FS_LOGS, DIR_FS_SQL_CACHE, DIR_FS_CATALOG . 'includes/modules/payment/paypal/logs') as $purgeFolder) {
        $purgeFolder = rtrim($purgeFolder, '/');
        $dir = dir($purgeFolder);
        while ($file = $dir->read()) {
          if ( ($file != '.') && ($file != '..') && substr($file, 0, 1) != '.') {
            if (preg_match('/^(' . implode('|', $log_filename_prefix_patterns) . ').*\.log$/i', $file)) {
              if (is_writeable($purgeFolder . '/' . $file)) {
                zen_remove($purgeFolder . '/' . $file);
              }
            }
          }
        }
        $dir->close();
        unset($dir);
      }
      $messageStack->add_session(SUCCESS_CLEAN_DEBUG_FILES, 'success');
      zen_record_admin_activity('Store Manager executed [clean debug/log files]', 'info');
      zen_redirect(zen_href_link(FILENAME_STORE_MANAGER));
    break;

    case ('update_all_master_categories_id'):
    // reset products master categories ID
        zen_reset_all_products_master_categories_id();
        $messageStack->add_session(SUCCESS_UPDATE_ALL_MASTER_CATEGORIES_ID, 'success');
        zen_record_admin_activity('Store Manager executed [update all master categories id]', 'info');
        $action='';
        zen_redirect(zen_href_link(FILENAME_STORE_MANAGER));
    break;

    case ('update_orders_id'):
      global $db;
      $new_orders_id = zen_db_prepare_input((int)$_POST['new_orders_id']);
      $result = $db->Execute("SELECT MAX(orders_id) AS maxorder FROM " . TABLE_ORDERS);
      $max_order1 = $result->fields['maxorder'];
      $result = $db->Execute("SELECT MAX(orders_id) AS maxorder FROM " . TABLE_ORDERS_PRODUCTS);
      $max_order2 = $result->fields['maxorder'];
      if ($new_orders_id <= $max_order1 || $new_orders_id <= $max_order2)
      {
        $new_orders_id = max($max_order1, $max_order2) + 1;
        $messageStack->add_session(sprintf(TEXT_MSG_NEXT_ORDER_MAX, $new_orders_id), 'caution');
      } elseif ($new_orders_id > 2000000000) {
        $messageStack->add_session(TEXT_MSG_NEXT_ORDER_TOO_LARGE, 'error');
      } else {
        $db->Execute("ALTER TABLE " . TABLE_ORDERS . " AUTO_INCREMENT = " . $new_orders_id);
        $messageStack->add_session(sprintf(TEXT_MSG_NEXT_ORDER, $new_orders_id), 'success');
        zen_record_admin_activity('Store Manager executed [update next order id], set to ' . $new_orders_id, 'info');
      }
      zen_redirect(zen_href_link(FILENAME_STORE_MANAGER));
    break;

    } // eof: action

?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
<head>
      <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
<?php if ($processing_message != '' && $processing_action_url != '') echo '<meta http-equiv="refresh" content="2;URL=' . $processing_action_url . '">'; ?>

</head>
<body>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table>
  <tr>
<!-- body_text //-->
    <td><table>
      <tr>
        <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
        <td class="pageHeading"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
      </tr>

<?php
if ($processing_message != '') {
?>
  <tr><td><?php echo $processing_message; ?></td></tr>
  <tr><td class="text-center"><?php echo zen_icon('loading', '', '2x'); ?></td></tr>
<?php
} else {
?>

<!-- bof: update all option values sort orders -->
      <tr>
        <td colspan="2"><table>
          <tr>
            <td class="main"><?php echo TEXT_INFO_ATTRIBUTES_FEATURES_UPDATES; ?></td>
            <td class="main"><?php echo zen_draw_form('update_all_products_attributes_sort_order', FILENAME_STORE_MANAGER, 'action=update_all_products_attributes_sort_order')?>
                <button type="submit" class="btn btn-default btn-sm"><?php echo IMAGE_UPDATE; ?></button>
                <?php echo '</form>'; ?></td>
          </tr>
        </table></td>
      </tr>
<!-- eof: update all option values sort orders -->

<!-- bof: update all products price sorter -->
      <tr>
        <td colspan="2"><table>
          <tr>
            <td class="main"><?php echo TEXT_INFO_PRODUCTS_PRICE_SORTER_UPDATE; ?></td>
            <td class="main"><?php echo zen_draw_form('update_all_products_price_sorter', FILENAME_STORE_MANAGER, 'action=update_all_products_price_sorter')?>
                <button type="submit" class="btn btn-default btn-sm"><?php echo IMAGE_UPDATE; ?></button>
                <?php echo '</form>'; ?></td>
          </tr>
        </table></td>
      </tr>
<!-- eof: update all products price sorter -->

<!-- bof: reset all counter to 0 -->
    <tr>
        <td colspan="2">
            <form name="update_counter" action="<?php echo zen_href_link(FILENAME_STORE_MANAGER, 'action=update_counter', 'NONSSL'); ?>" method="post">
                <?php echo zen_draw_hidden_field('securityToken', $_SESSION['securityToken']); ?>
                <table>
                    <tr>
                        <td class="main"><?php echo TEXT_INFO_COUNTER_UPDATE; ?></td>
                        <td class="main"><?php echo zen_draw_input_field('new_counter'); ?></td>
                        <td class="main"><button type="submit" class="btn btn-default btn-sm"><?php echo IMAGE_UPDATE; ?></button></td>
                    </tr>
                </table>
                <?php echo '</form>'; ?>
        </td>
    </tr>
    <!-- eof: reset all counter to 0 -->

<?php /*
<!-- bof: reset all products_viewed to 0 -->
      <tr>
        <td colspan="2"><br><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main text-left align-top"><?php echo TEXT_INFO_PRODUCTS_VIEWED_UPDATE; ?></td>
            <td class="main text-right align-middle"><?php echo zen_draw_form('update_all_products_viewed', FILENAME_STORE_MANAGER, 'action=update_all_products_viewed')?><input class="btn btn-default btn-sm" type="submit" value="<?php echo IMAGE_RESET; ?>"></form></td>
          </tr>
        </table></td>
      </tr>
<!-- eof: reset all products_viewed to 0 -->
*/
?>

<!-- bof: reset all products_ordered to 0 -->
      <tr>
        <td colspan="2"><table>
          <tr>
            <td class="main"><?php echo TEXT_INFO_PRODUCTS_ORDERED_UPDATE; ?></td>
            <td class="main"><?php echo zen_draw_form('update_all_products_ordered', FILENAME_STORE_MANAGER, 'action=update_all_products_ordered')?>
                <input class="btn btn-default btn-sm" type="submit" value="<?php echo IMAGE_RESET; ?>">
                <?php echo '</form>'; ?>
            </td>
          </tr>
        </table></td>
      </tr>
<!-- eof: reset all products_ordered to 0 -->

<!-- bof: reset all master_categories_id -->
      <tr>
        <td colspan="2"><table>
          <tr>
            <td class="main"><?php echo TEXT_INFO_MASTER_CATEGORIES_ID_UPDATE; ?></td>
            <td class="main"><?php echo zen_draw_form('update_all_master_categories_id', FILENAME_STORE_MANAGER, 'action=update_all_master_categories_id')?>
                <input class="btn btn-default btn-sm" type="submit" value="<?php echo IMAGE_RESET; ?>">
                <?php echo '</form>'; ?>
            </td>
          </tr>
        </table></td>
      </tr>
<!-- eof: reset all master_categories_id -->

<!-- bof: reset next order to new order number -->
    <tr>
        <td colspan="2">
            <form name="update_orders" action="<?php echo zen_href_link(FILENAME_STORE_MANAGER, 'action=update_orders_id', 'NONSSL'); ?>" method="post">
                <?php echo zen_draw_hidden_field('securityToken', $_SESSION['securityToken']); ?>
                <table>
                    <tr>
                        <td class="main"><?php echo TEXT_INFO_SET_NEXT_ORDER_NUMBER; ?><br>
                            <?php echo TEXT_NEW_ORDERS_ID . '&nbsp;' . zen_draw_input_field('new_orders_id', (isset($new_orders_id) ? $new_orders_id : '')); ?>
                            <button type="submit" class="btn btn-default btn-sm"><?php echo IMAGE_UPDATE; ?></button>
                        </td>
                    </tr>
                </table>
                <?php echo '</form>'; ?>
        </td>
    </tr>
    <!-- eof: reset next order to new order number -->

<!-- bof: database table-optimize -->
      <tr>
        <td colspan="2"><table>
          <tr>
            <td class="main"><?php echo TEXT_INFO_DATABASE_OPTIMIZE; ?></td>
            <td class="main"><?php echo zen_draw_form('optimize_db_start', FILENAME_STORE_MANAGER, 'action=optimize_db_start')?>
                <input class="btn btn-default btn-sm" type="submit" value="<?php echo IMAGE_RESET; ?>">
            <?php echo '</form>'; ?>
            </td>
          </tr>
        </table></td>
      </tr>
<!-- eof: database table-optimize -->

<!-- bof: clean_debug_files -->
      <tr>
        <td colspan="2"><table>
          <tr>
            <td class="main"><?php echo TEXT_INFO_PURGE_DEBUG_LOG_FILES; ?></td>
            <td class="main"><?php echo zen_draw_form('clean_debug_files', FILENAME_STORE_MANAGER, 'action=clean_debug_files', 'post'); ?>
                <input class="btn btn-default btn-sm" type="submit" value="<?php echo IMAGE_CONFIRM; ?>">
                <?php echo '</form>'; ?>
          </tr>
        </table></td>
      </tr>
<!-- eof: clean_debug_files -->

<?php
} // eof configure
?>
      <tr>
        <td colspan="2"><?php echo '<br>' . zen_draw_separator('pixel_black.gif', '100%', '2'); ?></td>
      </tr>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
