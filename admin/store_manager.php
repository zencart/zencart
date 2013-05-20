<?php
/**
 * @package admin
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Tue Aug 28 16:48:39 2012 -0400 Modified in v1.5.1 $
 */

  require('includes/application_top.php');

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  $languages = zen_get_languages();

  $products_filter = (isset($_GET['products_filter']) ? $_GET['products_filter'] : $products_filter);

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  $current_category_id = (isset($_GET['current_category_id']) ? (int)$_GET['current_category_id'] : (int)$current_category_id);

  $processing_message = '';
  $processing_action_url = '';

  zen_set_time_limit(600);
  switch($action) {

// update all products in catalog
    case ('update_all_products_attributes_sort_order'):
      if (isset($_POST['confirm']) && $_POST['confirm'] == 'yes')
      {
        $all_products_attributes= $db->Execute("select p.products_id, pa.products_attributes_id from " .
        TABLE_PRODUCTS . " p, " .
        TABLE_PRODUCTS_ATTRIBUTES . " pa " . "
        where p.products_id= pa.products_id"
        );
        while (!$all_products_attributes->EOF)
        {
          $count++;
          $product_id_updated .= ' - ' . $all_products_attributes->fields['products_id'] . ':' . $all_products_attributes->fields['products_attributes_id'];
          zen_update_attributes_products_option_values_sort_order($all_products_attributes->fields['products_id']);
          $all_products_attributes->MoveNext();
        }
        $messageStack->add_session(SUCCESS_PRODUCT_UPDATE_SORT_ALL, 'success');
        $action='';
        zen_redirect(zen_href_link(FILENAME_STORE_MANAGER));
      }
      break;

    case ('update_all_products_price_sorter'):
      if (isset($_POST['confirm']) && $_POST['confirm'] == 'yes')
      {
      // reset products_price_sorter for searches etc.
        $sql = "select products_id from " . TABLE_PRODUCTS;
        $update_prices = $db->Execute($sql);

        while (!$update_prices->EOF)
        {
          zen_update_products_price_sorter($update_prices->fields['products_id']);
          $update_prices->MoveNext();
        }
        $messageStack->add_session(SUCCESS_PRODUCT_UPDATE_PRODUCTS_PRICE_SORTER, 'success');
        $action='';
        zen_redirect(zen_href_link(FILENAME_STORE_MANAGER));
      }
    break;

    case ('update_all_products_viewed'):
    // reset products_viewed to 0
      if (isset($_POST['confirm']) && $_POST['confirm'] == 'yes')
      {
        $sql = "update " . TABLE_PRODUCTS_DESCRIPTION . " set products_viewed= '0'";
        $update_viewed = $db->Execute($sql);

        $messageStack->add_session(SUCCESS_PRODUCT_UPDATE_PRODUCTS_VIEWED, 'success');
        $action='';
        zen_redirect(zen_href_link(FILENAME_STORE_MANAGER));
      }
    break;

    case ('update_all_products_ordered'):
      if (isset($_POST['confirm']) && $_POST['confirm'] == 'yes')
      {
      // reset products_ordered to 0
        $sql = "update " . TABLE_PRODUCTS . " set products_ordered= '0'";
        $update_viewed = $db->Execute($sql);

        $messageStack->add_session(SUCCESS_PRODUCT_UPDATE_PRODUCTS_ORDERED, 'success');
        $action='';
        zen_redirect(zen_href_link(FILENAME_STORE_MANAGER));
      }
    break;

    case ('update_counter'):
    if ($_POST['new_counter'] == '' || strval($_POST['new_counter']) != strval((int)$_POST['new_counter'])) break;
    $sql = "update " . TABLE_COUNTER . " set counter= '" . (int)$_POST['new_counter'] . "'";
    $update_counter = $db->Execute($sql);

    $messageStack->add_session(SUCCESS_UPDATE_COUNTER . (int)$_POST['new_counter'], 'success');
    $action='';
    zen_redirect(zen_href_link(FILENAME_STORE_MANAGER));
    break;

    case ('optimize_db_start'):
      $processing_message = TEXT_INFO_OPTIMIZING_DATABASE_TABLES;
      $processing_action_url = zen_href_link(FILENAME_STORE_MANAGER, 'action=optimize_db_do');
    break;
    case ('optimize_db_do'):
    // clean out unused space in database
      if (isset($_POST['confirm']) && $_POST['confirm'] == 'yes')
      {
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
        $action='';
        zen_redirect(zen_href_link(FILENAME_STORE_MANAGER));
      }
    break;

// clean out old DEBUG logfiles
    case 'clean_debug_files':
      foreach(array(DIR_FS_LOGS, DIR_FS_SQL_CACHE, DIR_FS_CATALOG . '/includes/modules/payment/paypal/logs') as $purgeFolder) {
        $purgeFolder = rtrim($purgeFolder, '/');
        $dir = dir($purgeFolder);
        while ($file = $dir->read()) {
          if ( ($file != '.') && ($file != '..') && substr($file, 0, 1) != '.') {
            if (preg_match('/^(myDEBUG-|AIM_Debug_|SIM_Debug_|FirstData_Debug_|Linkpoint_Debug_|Paypal|paypal|ipn_|zcInstall|SHIP_|PAYMENT_|.*debug).*\.log$/i', $file)) {
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
      zen_redirect(zen_href_link(FILENAME_STORE_MANAGER));
    break;

    case ('update_all_master_categories_id'):
    // reset products master categories ID
      if (isset($_POST['confirm']) && $_POST['confirm'] == 'yes')
      {

        $sql = "select products_id from " . TABLE_PRODUCTS;
        $check_products = $db->Execute($sql);
        while (!$check_products->EOF) {

          $sql = "select products_id, categories_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id='" . $check_products->fields['products_id'] . "'";
          $check_category = $db->Execute($sql);

          $sql = "update " . TABLE_PRODUCTS . " set master_categories_id='" . $check_category->fields['categories_id'] . "' where products_id='" . $check_products->fields['products_id'] . "'";
          $update_viewed = $db->Execute($sql);

          $check_products->MoveNext();
        }

        $messageStack->add_session(SUCCESS_UPDATE_ALL_MASTER_CATEGORIES_ID, 'success');
        $action='';
        zen_redirect(zen_href_link(FILENAME_STORE_MANAGER));
      }
    break;

    case ('update_orders_id'):
      global $db;
      $new_orders_id = zen_db_prepare_input((int)$_POST['new_orders_id']);
      $result = $db->Execute("select max(orders_id) as maxorder from " . TABLE_ORDERS);
      $max_order1 = $result->fields['maxorder'];
      $result = $db->Execute("select max(orders_id) as maxorder from " . TABLE_ORDERS_PRODUCTS);
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
      }
      zen_redirect(zen_href_link(FILENAME_STORE_MANAGER));
    break;

    } // eof: action

require('includes/admin_html_head.php');
?>
</head>
<body>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
        <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
      </tr>

<?php
if ($processing_message != '') {
?>
  <tr><td><?php echo $processing_message; ?></td></tr>
  <tr><td align="center"><?php echo zen_image(DIR_WS_IMAGES . 'loadingsmall.gif'); ?></td></tr>
<?php
} else {
?>

<!-- bof: update all option values sort orders -->
      <tr>
        <td colspan="2"><br /><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main" align="left" valign="top"><?php echo TEXT_INFO_ATTRIBUTES_FEATURES_UPDATES; ?></td>
            <td class="main" align="right" valign="middle"><?php echo zen_draw_form('update_all_products_attributes_sort_order', FILENAME_STORE_MANAGER, 'action=update_all_products_attributes_sort_order')?><input type="hidden" name="confirm" value="yes" /><?php echo zen_image_submit('button_update.gif', IMAGE_UPDATE); ?></form></td>
          </tr>
        </table></td>
      </tr>
<!-- eof: update all option values sort orders -->

<!-- bof: update all products price sorter -->
      <tr>
        <td colspan="2"><br /><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main" align="left" valign="top"><?php echo TEXT_INFO_PRODUCTS_PRICE_SORTER_UPDATE; ?></td>
            <td class="main" align="right" valign="middle"><?php echo zen_draw_form('update_all_products_price_sorter', FILENAME_STORE_MANAGER, 'action=update_all_products_price_sorter')?><input type="hidden" name="confirm" value="yes" /><?php echo zen_image_submit('button_update.gif', IMAGE_UPDATE); ?></form></td>
          </tr>
        </table></td>
      </tr>
<!-- eof: update all products price sorter -->

<!-- bof: reset all counter to 0 -->
      <tr>
        <td colspan="2"><br /><table border="0" cellspacing="0" cellpadding="2">
          <tr><form name = "update_counter" action="<?php echo zen_href_link(FILENAME_STORE_MANAGER, 'action=update_counter', 'NONSSL'); ?>" method="post">
          <?php echo zen_draw_hidden_field('securityToken', $_SESSION['securityToken']); ?>
            <td class="main" align="left" valign="top"><?php echo TEXT_INFO_COUNTER_UPDATE; ?></td>
            <td class="main" align="left" valign="bottom"><?php echo zen_draw_input_field('new_counter'); ?></td>
            <td class="main" align="right" valign="middle"><?php echo zen_image_submit('button_reset.gif', IMAGE_RESET); ?></td>
          </form></tr>
        </table></td>
      </tr>
<!-- eof: reset all counter to 0 -->

<!-- bof: reset all products_viewed to 0 -->
      <tr>
        <td colspan="2"><br /><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main" align="left" valign="top"><?php echo TEXT_INFO_PRODUCTS_VIEWED_UPDATE; ?></td>
            <td class="main" align="right" valign="middle"><?php echo zen_draw_form('update_all_products_viewed', FILENAME_STORE_MANAGER, 'action=update_all_products_viewed')?><input type="hidden" name="confirm" value="yes" /><?php echo zen_image_submit('button_reset.gif', IMAGE_UPDATE); ?></form></td>
          </tr>
        </table></td>
      </tr>
<!-- eof: reset all products_viewed to 0 -->

<!-- bof: reset all products_ordered to 0 -->
      <tr>
        <td colspan="2"><br /><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main" align="left" valign="top"><?php echo TEXT_INFO_PRODUCTS_ORDERED_UPDATE; ?></td>
            <td class="main" align="right" valign="middle"><?php echo zen_draw_form('update_all_products_ordered', FILENAME_STORE_MANAGER, 'action=update_all_products_ordered')?><input type="hidden" name="confirm" value="yes" /><?php echo zen_image_submit('button_reset.gif', IMAGE_UPDATE); ?></form></td>
          </tr>
        </table></td>
      </tr>
<!-- eof: reset all products_ordered to 0 -->

<!-- bof: reset all master_categories_id -->
      <tr>
        <td colspan="2"><br /><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main" align="left" valign="top"><?php echo TEXT_INFO_MASTER_CATEGORIES_ID_UPDATE; ?></td>
            <td class="main" align="right" valign="middle"><?php echo zen_draw_form('update_all_master_categories_id', FILENAME_STORE_MANAGER, 'action=update_all_master_categories_id')?><input type="hidden" name="confirm" value="yes" /><?php echo zen_image_submit('button_reset.gif', IMAGE_UPDATE); ?></form></td>
          </tr>
        </table></td>
      </tr>
<!-- eof: reset all master_categories_id -->

<!-- bof: reset next order to new order number -->
      <tr>
        <td colspan="2"><br /><table border="0" cellspacing="0" cellpadding="2">
          <tr><form name="update_orders" action="<?php echo zen_href_link(FILENAME_STORE_MANAGER, 'action=update_orders_id', 'NONSSL'); ?>" method="post">
          <?php echo zen_draw_hidden_field('securityToken', $_SESSION['securityToken']); ?>
            <td class="main" align="left" valign="top"><?php echo TEXT_INFO_SET_NEXT_ORDER_NUMBER; ?>
            <br />
            <?php echo TEXT_NEW_ORDERS_ID . '&nbsp;' . zen_draw_input_field('new_orders_id', $nextOrderNumber); ?>
            <?php echo zen_image_submit('button_reset.gif', IMAGE_RESET); ?></td>
            </form>
          </tr>
        </table>
        </td>
      </tr>
<!-- eof: reset next order to new order number -->

<!-- bof: database table-optimize -->
      <tr>
        <td colspan="2"><br /><br /><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main" align="left" valign="top"><?php echo TEXT_INFO_DATABASE_OPTIMIZE; ?></td>
            <td class="main" align="right" valign="middle"><?php echo zen_draw_form('optimize_db_start', FILENAME_STORE_MANAGER, 'action=optimize_db_start')?><input type="hidden" name="confirm" value="yes" /><?php echo zen_image_submit('button_reset.gif', IMAGE_UPDATE); ?></form></td>
          </tr>
        </table></td>
      </tr>
<!-- eof: database table-optimize -->

<!-- bof: clean_debug_files -->
      <tr>
        <td colspan="2"><br /><br /><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main" align="left" valign="top"><?php echo TEXT_INFO_PURGE_DEBUG_LOG_FILES; ?></td>
            <td class="main" align="right" valign="middle"><?php echo zen_draw_form('clean_debug_files', FILENAME_STORE_MANAGER, 'action=clean_debug_files', 'post') . zen_image_submit('button_confirm.gif', IMAGE_UPDATE) . '</form>'; ?>
          </tr>
        </table></td>
      </tr>
<!-- eof: clean_debug_files -->

<?php
} // eof configure
?>
      <tr>
        <td colspan="2"><?php echo '<br />' . zen_draw_separator('pixel_black.gif', '100%', '2'); ?></td>
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