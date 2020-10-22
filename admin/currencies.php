<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2020 Apr 09 Modified in v1.5.7 $
 */
require('includes/application_top.php');

require(DIR_WS_CLASSES . 'currencies.php');
$currencies = new currencies();

$action = (isset($_GET['action']) ? $_GET['action'] : '');

if (zen_not_null($action)) {
  switch ($action) {
    case 'insert':
    case 'save':
      if ($_POST['title'] == '' || $_POST['code'] == '') {
        $_GET['action'] = '';
        $messageStack->add_session(ERROR_INVALID_CURRENCY_ENTRY, 'error');
        zen_redirect(zen_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page']));
        break;
      }

      if (isset($_GET['cID'])) {
        $currency_id = zen_db_prepare_input($_GET['cID']);
      }
      $title = zen_db_prepare_input($_POST['title']);
      $code = strtoupper(zen_db_prepare_input($_POST['code']));
      $symbol_left = zen_db_prepare_input($_POST['symbol_left']);
      $symbol_right = zen_db_prepare_input($_POST['symbol_right']);
      $decimal_point = zen_db_prepare_input($_POST['decimal_point']);
      $thousands_point = zen_db_prepare_input($_POST['thousands_point']);
      $decimal_places = zen_db_prepare_input((int)$_POST['decimal_places']);
      $value = zen_db_prepare_input((float)$_POST['value']);

      // special handling for currencies which don't support decimal places
      if ($decimal_point == '0' || in_array($code, array('JPY', 'HUF', 'TWD'))) {
        $value = (int)$value;
        $decimal_places = 0;
      }

      $sql_data_array = array(
        'title' => $title,
        'code' => $code,
        'symbol_left' => $symbol_left,
        'symbol_right' => $symbol_right,
        'decimal_point' => $decimal_point,
        'thousands_point' => $thousands_point,
        'decimal_places' => $decimal_places,
        'value' => $value);

      if ($action == 'insert') {
        zen_db_perform(TABLE_CURRENCIES, $sql_data_array);
        $currency_id = zen_db_insert_id();
      } elseif ($action == 'save') {
        zen_db_perform(TABLE_CURRENCIES, $sql_data_array, 'update', "currencies_id = '" . (int)$currency_id . "'");
      }
      zen_record_admin_activity('Currency code ' . $code . ' added/updated.', 'info');

      if (isset($_POST['default']) && ($_POST['default'] == 'on')) {
        $db->Execute("UPDATE " . TABLE_CONFIGURATION . "
                      SET configuration_value = '" . zen_db_input($code) . "'
                      WHERE configuration_key = 'DEFAULT_CURRENCY'");
        zen_record_admin_activity('Default currency code changed to ' . $code, 'info');
      }

      zen_redirect(zen_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $currency_id));
      break;
    case 'deleteconfirm':
      $currencies_id = zen_db_prepare_input($_POST['cID']);

      $currency = $db->Execute("SELECT currencies_id
                                FROM " . TABLE_CURRENCIES . "
                                WHERE code = '" . zen_db_input(DEFAULT_CURRENCY) . "'");
      if ($currency->fields['currencies_id'] == $currencies_id) {
        $db->Execute("UPDATE " . TABLE_CONFIGURATION . "
                      SET configuration_value = ''
                      WHERE configuration_key = 'DEFAULT_CURRENCY'");
      }
      $db->Execute("DELETE FROM " . TABLE_CURRENCIES . "
                    WHERE currencies_id = " . (int)$currencies_id);

      zen_record_admin_activity('Deleted currency with currencies_id of ' . $currencies_id, 'notice');
      zen_redirect(zen_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page']));
      break;
    case 'update_currencies':
      zen_update_currencies();
      zen_redirect(zen_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $_GET['cID']));
      break;
    case 'delete':
      $currencies_id = zen_db_prepare_input($_GET['cID']);

      $currency = $db->Execute("SELECT code
                                FROM " . TABLE_CURRENCIES . "
                                WHERE currencies_id = " . (int)$currencies_id);

      $remove_currency = true;
      if ($currency->fields['code'] == DEFAULT_CURRENCY) {
        $remove_currency = false;
        $messageStack->add(ERROR_REMOVE_DEFAULT_CURRENCY, 'error');
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
  <body onLoad="init()">
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
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_CURRENCY_NAME; ?></th>
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_CURRENCY_CODES; ?></th>
                <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_CURRENCY_VALUE; ?></th>
                <th class="dataTableHeadingContent text-center"><?php echo TEXT_INFO_CURRENCY_LAST_UPDATED; ?></th>
                <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
              </tr>
            </thead>
            <tbody>
                <?php
                $currencies_query_raw = "SELECT currencies_id, title, code, symbol_left, symbol_right, decimal_point, thousands_point, decimal_places, last_updated, value
                                               FROM " . TABLE_CURRENCIES . "
                                               ORDER BY title";
                $currency_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $currencies_query_raw, $currency_query_numrows);
                $currencies_all = $db->Execute($currencies_query_raw);
                foreach ($currencies_all as $currency) {
                  if ((!isset($_GET['cID']) || (isset($_GET['cID']) && ($_GET['cID'] == $currency['currencies_id']))) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
                    $cInfo = new objectInfo($currency);
                  }

                  if (isset($cInfo) && is_object($cInfo) && ($currency['currencies_id'] == $cInfo->currencies_id)) {
                    echo '              <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href=\'' . zen_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->currencies_id . '&action=edit') . '\'" role="button">' . "\n";
                  } else {
                    echo '              <tr class="dataTableRow" onclick="document.location.href=\'' . zen_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $currency['currencies_id']) . '\'" role="button">' . "\n";
                  }

                  if (DEFAULT_CURRENCY == $currency['code']) {
                    echo '                <td class="dataTableContent"><b>' . $currency['title'] . ' (' . TEXT_DEFAULT . ')</b></td>' . "\n";
                  } else {
                    echo '                <td class="dataTableContent">' . $currency['title'] . '</td>' . "\n";
                  }
                  ?>
              <td class="dataTableContent"><?php echo $currency['code']; ?></td>
              <td class="dataTableContent text-right"><?php echo number_format($currency['value'], 8); ?></td>
              <td class="dataTableContent text-center"><?php echo zen_datetime_short($currency['last_updated']); ?></td>
              <td class="dataTableContent text-right"><?php
                  if (isset($cInfo) && is_object($cInfo) && ($currency['currencies_id'] == $cInfo->currencies_id)) {
                    echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif');
                  } else {
                    echo '<a href="' . zen_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $currency['currencies_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>';
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
                $heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_NEW_CURRENCY . '</h4>');

                $contents = array('form' => zen_draw_form('currencies', FILENAME_CURRENCIES, 'page=' . $_GET['page'] . (isset($cInfo) ? '&cID=' . $cInfo->currencies_id : '') . '&action=insert', 'post', 'class="form-horizontal"'));
                $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_CURRENCY_TITLE, 'title', 'class="control-label"') . zen_draw_input_field('title', '', 'class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_CURRENCY_CODE, 'code', 'class="control-label"') . zen_draw_input_field('code', '', 'class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_CURRENCY_SYMBOL_LEFT, 'symbol_left', 'class="control-label"') . zen_draw_input_field('symbol_left', '', 'class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_CURRENCY_SYMBOL_RIGHT, 'symbol_right', 'class="control-label"') . zen_draw_input_field('symbol_right', '', 'class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_CURRENCY_DECIMAL_POINT, 'decimal_point', 'class="control-label"') . zen_draw_input_field('decimal_point', '', 'class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_CURRENCY_THOUSANDS_POINT, 'thousands_point', 'class="control-label"') . zen_draw_input_field('thousands_point', '', 'class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_CURRENCY_DECIMAL_PLACES, 'decimal_places', 'class="control-label"') . zen_draw_input_field('decimal_places', '', 'class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_CURRENCY_VALUE, 'value', 'class="control-label"') . zen_draw_input_field('value', '', 'class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_checkbox_field('default') . ' ' . TEXT_INFO_SET_AS_DEFAULT);
                $contents[] = array('align' => 'center', 'text' => '<br><button type="submit" class="btn btn-primary">' . IMAGE_INSERT . '</button> <a href="' . zen_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $_GET['cID']) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              case 'edit':
                $heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_EDIT_CURRENCY . '</h4>');

                $contents = array('form' => zen_draw_form('currencies', FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->currencies_id . '&action=save', 'post', 'class="form-horizontal"'));
                $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_CURRENCY_TITLE, 'title', 'class="control-label"') . zen_draw_input_field('title', htmlspecialchars($cInfo->title, ENT_COMPAT, CHARSET, TRUE), 'class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_CURRENCY_CODE, 'code', 'class="control-label"') . zen_draw_input_field('code', htmlspecialchars($cInfo->code, ENT_COMPAT, CHARSET, TRUE), 'class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_CURRENCY_SYMBOL_LEFT, 'symbol_left', 'class="control-label"') . zen_draw_input_field('symbol_left', htmlspecialchars($cInfo->symbol_left, ENT_COMPAT, CHARSET, TRUE), 'class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_CURRENCY_SYMBOL_RIGHT, 'symbol_right', 'class="control-label"') . zen_draw_input_field('symbol_right', htmlspecialchars($cInfo->symbol_right, ENT_COMPAT, CHARSET, TRUE), 'class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_CURRENCY_DECIMAL_POINT, 'decimal_point', 'class="control-label"') . zen_draw_input_field('decimal_point', htmlspecialchars($cInfo->decimal_point, ENT_COMPAT, CHARSET, TRUE), 'class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_CURRENCY_THOUSANDS_POINT, 'thousands_point', 'class="control-label"') . zen_draw_input_field('thousands_point', htmlspecialchars($cInfo->thousands_point, ENT_COMPAT, CHARSET, TRUE), 'class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_CURRENCY_DECIMAL_PLACES, 'decimal_places', 'class="control-label"') . zen_draw_input_field('decimal_places', $cInfo->decimal_places, 'class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_CURRENCY_VALUE, 'value', 'class="control-label"') . zen_draw_input_field('value', $cInfo->value, 'class="form-control"'));
                if (DEFAULT_CURRENCY != $cInfo->code) {
                  $contents[] = array('text' => '<br>' . zen_draw_checkbox_field('default') . ' ' . TEXT_INFO_SET_AS_DEFAULT);
                }
                $contents[] = array('align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-primary">' . IMAGE_UPDATE . '</button> <a href="' . zen_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->currencies_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              case 'delete':
                $heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_DELETE_CURRENCY . '</h4>');
                $contents = array('form' => zen_draw_form('delete', FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&action=deleteconfirm') . zen_draw_hidden_field('cID', $cInfo->currencies_id));
                $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
                $contents[] = array('align' => 'text-center', 'text' => (($remove_currency) ? '<button type="submit" class="btn btn-danger">' . IMAGE_DELETE . '</button>' : '') . ' <a href="' . zen_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $_GET['cID']) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                $contents[] = array('text' => '<br><b>' . $cInfo->title . '</b>');
                break;
              default:
                if (is_object($cInfo)) {
                  $heading[] = array('text' => '<h4>' . $cInfo->title . '</h4>');

                  $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->currencies_id . '&action=edit') . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a> <a href="' . zen_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->currencies_id . '&action=delete') . '" class="btn btn-warning" role="button">' . IMAGE_DELETE . '</a>');
                  $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_TITLE . ' ' . $cInfo->title);
                  $contents[] = array('text' => TEXT_INFO_CURRENCY_CODE . ' ' . $cInfo->code);
                  $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_SYMBOL_LEFT . ' ' . $cInfo->symbol_left);
                  $contents[] = array('text' => TEXT_INFO_CURRENCY_SYMBOL_RIGHT . ' ' . $cInfo->symbol_right);
                  $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_DECIMAL_POINT . ' ' . $cInfo->decimal_point);
                  $contents[] = array('text' => TEXT_INFO_CURRENCY_THOUSANDS_POINT . ' ' . $cInfo->thousands_point);
                  $contents[] = array('text' => TEXT_INFO_CURRENCY_DECIMAL_PLACES . ' ' . $cInfo->decimal_places);
                  $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_LAST_UPDATED . ': ' . zen_datetime_short($cInfo->last_updated));
                  $contents[] = array('text' => TEXT_INFO_CURRENCY_VALUE . ' ' . number_format($cInfo->value, 8));
                  $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_EXAMPLE . '<br>' . $currencies->format('30', false, DEFAULT_CURRENCY) . ' = ' . $currencies->format('30', true, $cInfo->code));
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
            <td><?php echo $currency_split->display_count($currency_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_CURRENCIES); ?></td>
            <td class="text-right"><?php echo $currency_split->display_links($currency_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
          </tr>
          <?php
          if (empty($action)) {
            ?>
            <tr>
              <td><?php
                  if (CURRENCY_SERVER_PRIMARY) {
                    echo '<a href="' . zen_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->currencies_id . '&action=update_currencies') . '" class="btn btn-primary" role="button">' . IMAGE_UPDATE_CURRENCIES . '</a>';
                  }
                  ?></td>
              <td class="text-right"><a href="<?php echo zen_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->currencies_id . '&action=new'); ?>" class="btn btn-primary" role="button"><?php echo IMAGE_NEW_CURRENCY; ?></a></td>
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
