<?php
/**
 * @package admin
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: mc12345678 2019 May 08 Modified in v1.5.6b $
 */
require('includes/application_top.php');

$action = (isset($_GET['action']) ? $_GET['action'] : '');

if (zen_not_null($action)) {
  switch ($action) {
    case 'insert':
    case 'save':
      if (isset($_GET['gID'])) {
        $group_id = zen_db_prepare_input($_GET['gID']);
      }
      $group_name = zen_db_prepare_input($_POST['group_name']);
      $group_percentage = zen_db_prepare_input((float)$_POST['group_percentage']);
      if ($group_name) {
        $sql_data_array = array(
          'group_name' => $group_name,
          'group_percentage' => $group_percentage);
        if ($action == 'insert') {
          $insert_sql_data = array('date_added' => 'now()');
          $sql_data_array = array_merge($sql_data_array, $insert_sql_data);
          zen_db_perform(TABLE_GROUP_PRICING, $sql_data_array);
          $group_id = $db->insert_ID();
        } elseif ($action == 'save') {
          $update_sql_data = array('last_modified' => 'now()');
          $sql_data_array = array_merge($sql_data_array, $update_sql_data);
          zen_db_perform(TABLE_GROUP_PRICING, $sql_data_array, 'update', "group_id = '" . (int)$group_id . "'");
        }
      }
      zen_redirect(zen_href_link(FILENAME_GROUP_PRICING, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'gID=' . $group_id));
      break;
    case 'deleteconfirm':

      $delete_cust_confirmed = (isset($_POST['delete_customers']) && $_POST['delete_customers'] == 'on') ? true : false;

      $group_id = zen_db_prepare_input($_POST['gID']);
      $customers_query = $db->Execute("SELECT customers_id
                                       FROM " . TABLE_CUSTOMERS . "
                                       WHERE customers_group_pricing = " . (int)$group_id);

      if ($customers_query->RecordCount() > 0 && $delete_cust_confirmed == true) {
        $db->Execute("DELETE FROM " . TABLE_GROUP_PRICING . "
                      WHERE group_id = " . (int)$group_id);
        $db->Execute("UPDATE " . TABLE_CUSTOMERS . "
                      SET customers_group_pricing = 0
                      WHERE customers_group_pricing = " . (int)$group_id);
      } elseif ($customers_query->RecordCount() > 0 && $delete_cust_confirmed == false) {
        $messageStack->add_session(ERROR_GROUP_PRICING_CUSTOMERS_EXIST, 'error');
      } elseif ($customers_query->RecordCount() == 0) {
        $db->Execute("DELETE FROM " . TABLE_GROUP_PRICING . "
                      WHERE group_id = " . (int)$group_id);
      }
      zen_redirect(zen_href_link(FILENAME_GROUP_PRICING, 'page=' . $_GET['page']));
      break;
  }
}

$query = $db->Execute("SELECT COUNT(*) AS count
                       FROM " . TABLE_GROUP_PRICING);
if ($query->fields['count'] > 0 && (!defined('MODULE_ORDER_TOTAL_GROUP_PRICING_STATUS') || MODULE_ORDER_TOTAL_GROUP_PRICING_STATUS != 'true')) {
  $messageStack->add(ERROR_MODULE_NOT_CONFIGURED, 'error');
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
  <body onload="init()">
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->

    <!-- body //-->
    <div class="container-fluid">
      <h1><?php echo HEADING_TITLE; ?></h1>
      <div class="row">
        <!-- body_text //-->
        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
          <table class="table table-hover">
            <thead>
              <tr class="dataTableHeadingRow">
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_GROUP_ID; ?></th>
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_GROUP_NAME; ?></th>
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_GROUP_AMOUNT; ?></th>
                <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
              </tr>
            </thead>
            <tbody>
                <?php
                $groups_query_raw = "select * from " . TABLE_GROUP_PRICING;

// Split Page
// reset page when page is unknown
                if ((empty($_GET['page']) || $_GET['page'] == '1') && !empty($_GET['gID'])) {
                  $check_page = $db->Execute($groups_query_raw);
                  $check_count = 1;
                  if ($check_page->RecordCount() > MAX_DISPLAY_SEARCH_RESULTS) {
                    foreach ($check_page as $item) {
                      if ($item['group_id'] == $_GET['gID']) {
                        break;
                      }
                      $check_count++;
                    }
                    $_GET['page'] = round((($check_count / MAX_DISPLAY_SEARCH_RESULTS) + (fmod_round($check_count, MAX_DISPLAY_SEARCH_RESULTS) != 0 ? .5 : 0)), 0);
//    zen_redirect(zen_href_link(FILENAME_CUSTOMERS, 'cID=' . $_GET['cID'] . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''), 'NONSSL'));
                  } else {
                    $_GET['page'] = 1;
                  }
                }

                $groups_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $groups_query_raw, $groups_query_numrows);
                $groups = $db->Execute($groups_query_raw);
                foreach ($groups as $group) {
                  if ((!isset($_GET['gID']) || (isset($_GET['gID']) && ($_GET['gID'] == $group['group_id']))) && !isset($gInfo) && (substr($action, 0, 3) != 'new')) {
                    $group_customers = $db->Execute("SELECT COUNT(*) AS customer_count
                                                     FROM " . TABLE_CUSTOMERS . "
                                                     WHERE customers_group_pricing = " . (int)$group['group_id']);
                    $gInfo_array = array_merge($group, $group_customers->fields);
                    $gInfo = new objectInfo($gInfo_array);
                  }

                  if (isset($gInfo) && is_object($gInfo) && ($group['group_id'] == $gInfo->group_id)) {
                    echo '              <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href=\'' . zen_href_link(FILENAME_GROUP_PRICING, 'page=' . $_GET['page'] . '&gID=' . $group['group_id'] . '&action=edit') . '\'" role="button">' . "\n";
                  } else {
                    echo '              <tr class="dataTableRow" onclick="document.location.href=\'' . zen_href_link(FILENAME_GROUP_PRICING, 'page=' . $_GET['page'] . '&gID=' . $group['group_id'] . '&action=edit') . '\'" role="button">' . "\n";
                  }
                  ?>
              <td class="dataTableContent"><?php echo $group['group_id']; ?></td>
              <td class="dataTableContent"><?php echo $group['group_name']; ?></td>
              <td class="dataTableContent"><?php echo $group['group_percentage']; ?></td>
              <td class="dataTableContent text-right">
                  <?php echo '<a href="' . zen_href_link(FILENAME_GROUP_PRICING, 'page=' . $_GET['page'] . '&gID=' . $group['group_id'] . '&action=edit') . '">' . zen_image(DIR_WS_IMAGES . 'icon_edit.gif', ICON_EDIT) . '</a>'; ?>
                  <?php echo '<a href="' . zen_href_link(FILENAME_GROUP_PRICING, 'page=' . $_GET['page'] . '&gID=' . $group['group_id'] . '&action=delete') . '">' . zen_image(DIR_WS_IMAGES . 'icon_delete.gif', ICON_DELETE) . '</a>'; ?>
                  <?php
                  if (isset($gInfo) && is_object($gInfo) && ($group['group_id'] == $gInfo->group_id)) {
                    echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', '');
                  } else {
                    echo '<a href="' . zen_href_link(FILENAME_GROUP_PRICING, zen_get_all_get_params(array('gID')) . 'gID=' . $group['group_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>';
                  }
                  ?>
              </td>
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
                $heading[] = array('text' => '<h4>' . TEXT_HEADING_NEW_PRICING_GROUP . '</h4>');

                $contents = array('form' => zen_draw_form('group_pricing', FILENAME_GROUP_PRICING, 'action=insert', 'post', 'class="form-horizontal"'));
                $contents[] = array('text' => TEXT_NEW_INTRO);
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_GROUP_PRICING_NAME, 'group_name', 'class="control-label"') . zen_draw_input_field('group_name', '', zen_set_field_length(TABLE_GROUP_PRICING, 'group_name') . ' class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_GROUP_PRICING_AMOUNT, 'group_percentage', 'class="control-label"') . zen_draw_input_field('group_percentage', '', 'class="form-control"'));
                $contents[] = array('align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-primary">' . IMAGE_SAVE . '</button> <a href="' . zen_href_link(FILENAME_GROUP_PRICING, 'page=' . $_GET['page'] . '&gID=' . $_GET['gID']) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              case 'edit':
                $heading[] = array('text' => '<h4>' . TEXT_HEADING_EDIT_PRICING_GROUP . '</h4>');

                $contents = array('form' => zen_draw_form('group_pricing', FILENAME_GROUP_PRICING, 'page=' . $_GET['page'] . '&gID=' . $gInfo->group_id . '&action=save', 'post', 'class="form-horizontal"'));
                $contents[] = array('text' => TEXT_EDIT_INTRO);
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_GROUP_PRICING_NAME, 'group_name', 'class="control-label"') . zen_draw_input_field('group_name', htmlspecialchars($gInfo->group_name, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_GROUP_PRICING, 'group_name') . ' class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_GROUP_PRICING_AMOUNT, 'group_percentage', 'class="control-label"') . zen_draw_input_field('group_percentage', $gInfo->group_percentage, 'class="form-control"'));
                $contents[] = array('align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-primary">' . IMAGE_SAVE . '</button> <a href="' . zen_href_link(FILENAME_GROUP_PRICING, 'page=' . $_GET['page'] . '&gID=' . $gInfo->group_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              case 'delete':
                $heading[] = array('text' => '<h4>' . TEXT_HEADING_DELETE_PRICING_GROUP . '</h4>');

                $contents = array('form' => zen_draw_form('group_pricing', FILENAME_GROUP_PRICING, 'page=' . $_GET['page'] . '&action=deleteconfirm') . zen_draw_hidden_field('gID', $gInfo->group_id));
                $contents[] = array('text' => TEXT_DELETE_INTRO);
                $contents[] = array('text' => '<br><b>' . $gInfo->group_name . '</b>');

                if ($gInfo->customer_count > 0) {
                  $contents[] = array('text' => '<br>' . zen_draw_checkbox_field('delete_customers') . ' ' . TEXT_DELETE_PRICING_GROUP);
                  $contents[] = array('text' => '<br>' . sprintf(TEXT_DELETE_WARNING_GROUP_MEMBERS, $gInfo->customer_count));
                }

                $contents[] = array('align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-danger">' . IMAGE_DELETE . '</button> <a href="' . zen_href_link(FILENAME_GROUP_PRICING, 'page=' . $_GET['page'] . '&gID=' . $gInfo->group_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              default:
                if (isset($gInfo) && is_object($gInfo)) {
                  $heading[] = array('text' => '<h4>' . $gInfo->group_name . '</h4>');

                  $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_GROUP_PRICING, 'page=' . $_GET['page'] . '&gID=' . $gInfo->group_id . '&action=edit') . '"class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a> <a href="' . zen_href_link(FILENAME_GROUP_PRICING, 'page=' . $_GET['page'] . '&gID=' . $gInfo->group_id . '&action=delete') . '" class="btn btn-warning" role="button">' . IMAGE_DELETE . '</a>');
                  $contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . ' ' . zen_date_short($gInfo->date_added));
                  if (zen_not_null($gInfo->last_modified))
                    $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . zen_date_short($gInfo->last_modified));
                  $contents[] = array('text' => '<br>' . TEXT_CUSTOMERS . ' ' . $gInfo->customer_count);
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
      <div class="row">
        <table class="table">
          <tr>
            <td><?php echo $groups_split->display_count($groups_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_PRICING_GROUPS); ?></td>
            <td class="text-right"><?php echo $groups_split->display_links($groups_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
          </tr>
        </table>
      </div>
      <?php
      if (empty($action)) {
        ?>
        <div class="text-right">
          <a href="<?php echo zen_href_link(FILENAME_GROUP_PRICING, 'page=' . $_GET['page'] . '&gID=' . $gInfo->group_id . '&action=new'); ?>" class="btn btn-primary" role="button"><?php echo IMAGE_INSERT; ?></a>
        </div>
        <?php
      }
      ?>
    </div>
    <!-- body_eof //-->

    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
