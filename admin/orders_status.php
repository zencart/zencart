<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Apr 27 Modified in v1.5.7 $
 */
require('includes/application_top.php');

$action = (isset($_GET['action']) ? $_GET['action'] : '');
$languages = zen_get_languages();

if (zen_not_null($action)) {
  switch ($action) {
    case 'insert':
    case 'save':
      if (isset($_GET['oID']))
        $orders_status_id = zen_db_prepare_input($_GET['oID']);

      for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
        $orders_status_name_array = $_POST['orders_status_name'];
        $language_id = $languages[$i]['id'];

        $sql_data_array = array(
            'orders_status_name' => zen_db_prepare_input($orders_status_name_array[$language_id]),
            'sort_order' => (int)$_POST['sort_order'],
        );

        if ($action == 'insert') {
          if (empty($orders_status_id)) {
            $next_id = $db->Execute("SELECT MAX(orders_status_id) AS orders_status_id
                                     FROM " . TABLE_ORDERS_STATUS);

            $orders_status_id = $next_id->fields['orders_status_id'] + 1;
          }

          $insert_sql_data = array(
            'orders_status_id' => $orders_status_id,
            'language_id' => $language_id);

          $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

          zen_db_perform(TABLE_ORDERS_STATUS, $sql_data_array);
        } elseif ($action == 'save') {
          zen_db_perform(TABLE_ORDERS_STATUS, $sql_data_array, 'update', "orders_status_id = '" . (int)$orders_status_id . "' and language_id = '" . (int)$language_id . "'");
        }
      }

      if (isset($_POST['default']) && ($_POST['default'] == 'on')) {
        $db->Execute("UPDATE " . TABLE_CONFIGURATION . "
                      SET configuration_value = " . zen_db_input($orders_status_id) . "
                      WHERE configuration_key = 'DEFAULT_ORDERS_STATUS_ID'");
      }

      zen_redirect(zen_href_link(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&oID=' . $orders_status_id));
      break;
    case 'deleteconfirm':
      $oID = zen_db_prepare_input($_POST['oID']);

      $orders_status = $db->Execute("SELECT configuration_value
                                     FROM " . TABLE_CONFIGURATION . "
                                     WHERE configuration_key = 'DEFAULT_ORDERS_STATUS_ID'");

      if ($status['configuration_value'] == $oID) {
        $db->Execute("UPDATE " . TABLE_CONFIGURATION . "
                      SET configuration_value = ''
                      WHERE configuration_key = 'DEFAULT_ORDERS_STATUS_ID'");
      }

      $db->Execute("DELETE FROM " . TABLE_ORDERS_STATUS . "
                    WHERE orders_status_id = " . zen_db_input($oID));

      zen_redirect(zen_href_link(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page']));
      break;
    case 'delete':
      $oID = zen_db_prepare_input($_GET['oID']);

      $status = $db->Execute("SELECT COUNT(*) AS count
                              FROM " . TABLE_ORDERS . "
                              WHERE orders_status = " . (int)$oID);

      $remove_status = true;
      if ($oID == DEFAULT_ORDERS_STATUS_ID) {
        $remove_status = false;
        $messageStack->add($error_message = ERROR_REMOVE_DEFAULT_ORDER_STATUS, 'error');
      } elseif ($status->fields['count'] > 0) {
        $remove_status = false;
        $messageStack->add($error_message = ERROR_STATUS_USED_IN_ORDERS, 'error');
      } else {
        $history = $db->Execute("SELECT COUNT(*) AS count
                                 FROM " . TABLE_ORDERS_STATUS_HISTORY . "
                                 WHERE orders_status_id = " . (int)$oID);

        if ($history->fields['count'] > 0) {
          $remove_status = false;
          $messageStack->add($error_message = ERROR_STATUS_USED_IN_HISTORY, 'error');
        }
      }
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
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_ORDERS_STATUS_ID; ?></th>
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_ORDERS_STATUS; ?></th>
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_SORT_ORDER; ?></th>
                <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
              </tr>
            </thead>
            <tbody>
                <?php
                $orders_status_query_raw = "SELECT *
                                            FROM " . TABLE_ORDERS_STATUS . "
                                            WHERE language_id = " . (int)$_SESSION['languages_id'] . "
                                            ORDER BY sort_order ASC, orders_status_id ASC";
                $orders_status_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $orders_status_query_raw, $orders_status_query_numrows);
                $orders_status = $db->Execute($orders_status_query_raw);
                foreach ($orders_status as $status) {
                  if ((!isset($_GET['oID']) || (isset($_GET['oID']) && ($_GET['oID'] == $status['orders_status_id']))) && !isset($oInfo) && (substr($action, 0, 3) != 'new')) {
                    $oInfo = new objectInfo($status);
                  }

                  if (isset($oInfo) && is_object($oInfo) && ($status['orders_status_id'] == $oInfo->orders_status_id)) {
                    echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href=\'' . zen_href_link(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->orders_status_id . '&action=edit') . '\'" role="button">' . "\n";
                  } else {
                    echo '                  <tr class="dataTableRow" onclick="document.location.href=\'' . zen_href_link(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&oID=' . $status['orders_status_id']) . '\'" role="button">' . "\n";
                  }
                  echo '                    <td class="dataTableContent">' . $status['orders_status_id'] . '</td>';

                  if (DEFAULT_ORDERS_STATUS_ID == $status['orders_status_id']) {
                    echo '                <td class="dataTableContent"><strong>' . $status['orders_status_name'] . ' (' . TEXT_DEFAULT . ')</strong></td>' . "\n";
                  } else {
                    echo '                <td class="dataTableContent">' . $status['orders_status_name'] . '</td>' . "\n";
                  }
                  echo '                    <td class="dataTableContent">' . $status['sort_order'] . '</td>';
                  ?>
              <td class="dataTableContent text-right"><?php
                  if (isset($oInfo) && is_object($oInfo) && ($status['orders_status_id'] == $oInfo->orders_status_id)) {
                    echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', '');
                  } else {
                    echo '<a href="' . zen_href_link(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&oID=' . $status['orders_status_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>';
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
                $heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_NEW_ORDERS_STATUS . '</h4>');

                $contents = array('form' => zen_draw_form('status', FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&action=insert', 'post', 'class="form-horizontal"'));
                $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);

                $orders_status_inputs_string = '';
                for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                  $orders_status_inputs_string .= '<br>' . zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . zen_draw_input_field('orders_status_name[' . $languages[$i]['id'] . ']', '', 'class="form-control"');
                }

                $contents[] = array('text' => '<br>' . TEXT_INFO_ORDERS_STATUS_NAME . $orders_status_inputs_string);
                $contents[] = array('text' => '<br>' . zen_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT);
                $contents[] = array('text' => '<br>' . TEXT_INFO_SORT_ORDER . '<br>&nbsp;&nbsp;&nbsp;&nbsp;' . zen_draw_input_field('sort_order', '0', 'class="form-control"'));
                $contents[] = array('align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-primary">' . IMAGE_INSERT . '</button> <a href="' . zen_href_link(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page']) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              case 'edit':
                $heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_EDIT_ORDERS_STATUS . '</h4>');

                $contents = array('form' => zen_draw_form('status', FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->orders_status_id . '&action=save', 'post', 'class="form-horizontal"'));
                $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);

                $orders_status_inputs_string = '';
                for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                  $orders_status_inputs_string .= '<br>' . zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . zen_draw_input_field('orders_status_name[' . $languages[$i]['id'] . ']', htmlspecialchars(zen_get_orders_status_name($oInfo->orders_status_id, $languages[$i]['id']), ENT_COMPAT, CHARSET, TRUE), 'class="form-control"');
                }

                $contents[] = array('text' => '<br>' . TEXT_INFO_ORDERS_STATUS_NAME . $orders_status_inputs_string);
                if (DEFAULT_ORDERS_STATUS_ID != $oInfo->orders_status_id) {
                  $contents[] = array('text' => '<br>' . zen_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT);
                }
                $contents[] = array('text' => '<br>' . TEXT_INFO_SORT_ORDER . '<br />&nbsp;&nbsp;&nbsp;&nbsp;' . zen_draw_input_field('sort_order', $oInfo->sort_order, 'class="form-control"'));
                $contents[] = array('align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-primary">' . IMAGE_UPDATE . '</button> <a href="' . zen_href_link(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->orders_status_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              case 'delete':
                $heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_DELETE_ORDERS_STATUS . '</h4>');

                $contents = array('form' => zen_draw_form('status', FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&action=deleteconfirm') . zen_draw_hidden_field('oID', $oInfo->orders_status_id));
                $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
                $contents[] = array('text' => '<br><b>' . $oInfo->orders_status_name . '</b>');
                if ($remove_status) {
                  $contents[] = array('align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-danger">' . IMAGE_DELETE . '</button> <a href="' . zen_href_link(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->orders_status_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                } elseif (!empty($error_message)) {
                    $contents[] = array('text' => '<br><b class="alert-danger">' . $error_message . '</b>');
                }
                break;
              default:
                if (isset($oInfo) && is_object($oInfo)) {
                  $heading[] = array('text' => '<h4>' . $oInfo->orders_status_name . '</h4>');

                  $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->orders_status_id . '&action=edit') . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a> <a href="' . zen_href_link(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->orders_status_id . '&action=delete') . '" class="btn btn-warning" role="button">' . IMAGE_DELETE . '</a>');

                  for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                    $contents[] = array('text' => zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . zen_get_orders_status_name($oInfo->orders_status_id, $languages[$i]['id']));
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
      </div>
      <div class="row">
        <table class="table">
          <tr>
            <td><?php echo $orders_status_split->display_count($orders_status_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_ORDERS_STATUS); ?></td>
            <td class="text-right"><?php echo $orders_status_split->display_links($orders_status_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
          </tr>
          <?php
          if (empty($action)) {
            ?>
            <tr>
              <td colspan="2" class="text-right"><a href="<?php echo zen_href_link(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&action=new'); ?>" class="btn btn-primary" role="button"><?php echo IMAGE_INSERT; ?></a></td>
            </tr>
            <?php
          }
          ?>
        </table>
      </div>
      <!-- body_text_eof //-->
    </div>
    <!-- body_eof //-->

    <!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
