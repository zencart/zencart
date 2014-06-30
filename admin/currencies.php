<?php
/**
 * @package admin
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Sat Aug 31 18:50:16 2013 -0400 Modified in v1.5.2 $
 */

  require('includes/application_top.php');

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (zen_not_null($action)) {
    switch ($action) {
      case 'insert':
      case 'save':
        if ($_POST['title'] == '' || $_POST['code'] == '' ) {
          $_GET['action']= '';
          $messageStack->add_session(ERROR_INVALID_CURRENCY_ENTRY, 'error');
          zen_redirect(zen_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page']));
          break;
        }

        if (isset($_GET['cID'])) $currency_id = zen_db_prepare_input($_GET['cID']);
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

        $sql_data_array = array('title' => $title,
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
          $db->Execute("update " . TABLE_CONFIGURATION . "
                        set configuration_value = '" . zen_db_input($code) . "'
                        where configuration_key = 'DEFAULT_CURRENCY'");
          zen_record_admin_activity('Default currency code changed to ' . $code, 'info');
        }

        zen_redirect(zen_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $currency_id));
        break;
      case 'deleteconfirm':
        // demo active test
        if (zen_admin_demo()) {
          $_GET['action']= '';
          $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
          zen_redirect(zen_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page']));
        }
        $currencies_id = zen_db_prepare_input($_POST['cID']);

        $currency = $db->Execute("select currencies_id
                                  from " . TABLE_CURRENCIES . "
                                  where code = '" . zen_db_input(DEFAULT_CURRENCY) . "'");
        if ($currency->fields['currencies_id'] == $currencies_id) {
          $db->Execute("update " . TABLE_CONFIGURATION . "
                        set configuration_value = ''
                        where configuration_key = 'DEFAULT_CURRENCY'");
        }
        $db->Execute("delete from " . TABLE_CURRENCIES . "
                      where currencies_id = '" . (int)$currencies_id . "'");

        zen_record_admin_activity('Deleted currency with currencies_id of ' . $currencies_id, 'notice');
        zen_redirect(zen_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page']));
        break;
      case 'update_currencies':
        $server_used = CURRENCY_SERVER_PRIMARY;
        zen_set_time_limit(600);
        $currency = $db->Execute("select currencies_id, code, title, decimal_places from " . TABLE_CURRENCIES);
        while (!$currency->EOF) {
          $quote_function = 'quote_' . CURRENCY_SERVER_PRIMARY . '_currency';
          $rate = $quote_function($currency->fields['code']);

          if (empty($rate) && (zen_not_null(CURRENCY_SERVER_BACKUP))) {
            // failed to get currency quote from primary server - attempting to use backup server instead
            $messageStack->add_session(sprintf(WARNING_PRIMARY_SERVER_FAILED, CURRENCY_SERVER_PRIMARY, $currency->fields['title'], $currency->fields['code']), 'warning');
            $quote_function = 'quote_' . CURRENCY_SERVER_BACKUP . '_currency';
            $rate = $quote_function($currency->fields['code']);
            $server_used = CURRENCY_SERVER_BACKUP;
          }

          /* Add currency uplift */
          if ($rate != 1 && defined('CURRENCY_UPLIFT_RATIO') && (int)CURRENCY_UPLIFT_RATIO != 0) {
            $rate = (string)((float)$rate * (float)CURRENCY_UPLIFT_RATIO);
          }

          // special handling for currencies which don't support decimal places
          if ($currency->fields['decimal_places'] == '0') {
            $rate = (int)$rate;
          }

          if (zen_not_null($rate) && $rate > 0) {
            $db->Execute("update " . TABLE_CURRENCIES . "
                          set value = '" . $rate . "', last_updated = now()
                          where currencies_id = '" . (int)$currency->fields['currencies_id'] . "'");
            $messageStack->add_session(sprintf(TEXT_INFO_CURRENCY_UPDATED, $currency->fields['title'], $currency->fields['code'], $server_used), 'success');
          } else {
            $messageStack->add_session(sprintf(ERROR_CURRENCY_INVALID, $currency->fields['title'], $currency->fields['code'], $server_used), 'error');
          }
          $currency->MoveNext();
        }
        zen_record_admin_activity('Currency exchange rates updated via the Update button in the admin console.', 'info');
        zen_redirect(zen_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $_GET['cID']));
        break;
      case 'delete':
        // demo active test
        if (zen_admin_demo()) {
          $_GET['action']= '';
          $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
          zen_redirect(zen_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $_GET['cID']));
        }
        $currencies_id = zen_db_prepare_input($_GET['cID']);

        $currency = $db->Execute("select code
                                  from " . TABLE_CURRENCIES . "
                                  where currencies_id = '" . (int)$currencies_id . "'");

        $remove_currency = true;
        if ($currency->fields['code'] == DEFAULT_CURRENCY) {
          $remove_currency = false;
          $messageStack->add(ERROR_REMOVE_DEFAULT_CURRENCY, 'error');
        }
        break;
    }
  }
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
<script language="javascript" src="includes/menu.js"></script>
<script language="javascript" src="includes/general.js"></script>
<script type="text/javascript">
  <!--
  function init()
  {
    cssjsmenu('navbar');
    if (document.getElementById)
    {
      var kill = document.getElementById('hoverJS');
      kill.disabled = true;
    }
  }
  // -->
</script>
</head>
<body onLoad="init()">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
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
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CURRENCY_NAME; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CURRENCY_CODES; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_CURRENCY_VALUE; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $currency_query_raw = "select currencies_id, title, code, symbol_left, symbol_right, decimal_point, thousands_point, decimal_places, last_updated, value from " . TABLE_CURRENCIES . " order by title";
  $currency_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $currency_query_raw, $currency_query_numrows);
  $currency = $db->Execute($currency_query_raw);
  while (!$currency->EOF) {
    if ((!isset($_GET['cID']) || (isset($_GET['cID']) && ($_GET['cID'] == $currency->fields['currencies_id']))) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
      $cInfo = new objectInfo($currency->fields);
    }

    if (isset($cInfo) && is_object($cInfo) && ($currency->fields['currencies_id'] == $cInfo->currencies_id) ) {
      echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->currencies_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $currency->fields['currencies_id']) . '\'">' . "\n";
    }

    if (DEFAULT_CURRENCY == $currency->fields['code']) {
      echo '                <td class="dataTableContent"><b>' . $currency->fields['title'] . ' (' . TEXT_DEFAULT . ')</b></td>' . "\n";
    } else {
      echo '                <td class="dataTableContent">' . $currency->fields['title'] . '</td>' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $currency->fields['code']; ?></td>
                <td class="dataTableContent" align="right"><?php echo number_format($currency->fields['value'], 8); ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($cInfo) && is_object($cInfo) && ($currency->fields['currencies_id'] == $cInfo->currencies_id) ) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . zen_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $currency->fields['currencies_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    $currency->MoveNext();
  }
?>
              <tr>
                <td colspan="4"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $currency_split->display_count($currency_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_CURRENCIES); ?></td>
                    <td class="smallText" align="right"><?php echo $currency_split->display_links($currency_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                  </tr>
<?php
  if (empty($action)) {
?>
                  <tr>
                    <td><?php if (CURRENCY_SERVER_PRIMARY) { echo '<a href="' . zen_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->currencies_id . '&action=update_currencies') . '">' . zen_image_button('button_update_currencies.gif', IMAGE_UPDATE_CURRENCIES) . '</a>'; } ?></td>
                    <td align="right"><?php echo '<a href="' . zen_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->currencies_id . '&action=new') . '">' . zen_image_button('button_new_currency.gif', IMAGE_NEW_CURRENCY) . '</a>'; ?></td>
                  </tr>
<?php
  }
?>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'new':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_CURRENCY . '</b>');

      $contents = array('form' => zen_draw_form('currencies', FILENAME_CURRENCIES, 'page=' . $_GET['page'] . (isset($cInfo) ? '&cID=' . $cInfo->currencies_id : '') . '&action=insert'));
      $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_TITLE . '<br>' . zen_draw_input_field('title'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_CODE . '<br>' . zen_draw_input_field('code'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_SYMBOL_LEFT . '<br>' . zen_draw_input_field('symbol_left'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_SYMBOL_RIGHT . '<br>' . zen_draw_input_field('symbol_right'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_DECIMAL_POINT . '<br>' . zen_draw_input_field('decimal_point'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_THOUSANDS_POINT . '<br>' . zen_draw_input_field('thousands_point'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_DECIMAL_PLACES . '<br>' . zen_draw_input_field('decimal_places'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_VALUE . '<br>' . zen_draw_input_field('value'));
      $contents[] = array('text' => '<br>' . zen_draw_checkbox_field('default') . ' ' . TEXT_INFO_SET_AS_DEFAULT);
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_insert.gif', IMAGE_INSERT) . ' <a href="' . zen_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $_GET['cID']) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'edit':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_CURRENCY . '</b>');

      $contents = array('form' => zen_draw_form('currencies', FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->currencies_id . '&action=save'));
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_TITLE . '<br>' . zen_draw_input_field('title', htmlspecialchars($cInfo->title, ENT_COMPAT, CHARSET, TRUE)));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_CODE . '<br>' . zen_draw_input_field('code', htmlspecialchars($cInfo->code, ENT_COMPAT, CHARSET, TRUE)));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_SYMBOL_LEFT . '<br>' . zen_draw_input_field('symbol_left', htmlspecialchars($cInfo->symbol_left, ENT_COMPAT, CHARSET, TRUE)));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_SYMBOL_RIGHT . '<br>' . zen_draw_input_field('symbol_right', htmlspecialchars($cInfo->symbol_right, ENT_COMPAT, CHARSET, TRUE)));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_DECIMAL_POINT . '<br>' . zen_draw_input_field('decimal_point', htmlspecialchars($cInfo->decimal_point, ENT_COMPAT, CHARSET, TRUE)));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_THOUSANDS_POINT . '<br>' . zen_draw_input_field('thousands_point', htmlspecialchars($cInfo->thousands_point, ENT_COMPAT, CHARSET, TRUE)));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_DECIMAL_PLACES . '<br>' . zen_draw_input_field('decimal_places', $cInfo->decimal_places));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_VALUE . '<br>' . zen_draw_input_field('value', $cInfo->value));
      if (DEFAULT_CURRENCY != $cInfo->code) $contents[] = array('text' => '<br>' . zen_draw_checkbox_field('default') . ' ' . TEXT_INFO_SET_AS_DEFAULT);
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_update.gif', IMAGE_UPDATE) . ' <a href="' . zen_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->currencies_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_CURRENCY . '</b>');
      $contents = array('form'=>zen_draw_form('delete', FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&action=deleteconfirm') . zen_draw_hidden_field('cID', $cInfo->currencies_id));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text'=> (($remove_currency) ? zen_image_submit('button_delete.gif', IMAGE_DELETE) : '') . ' <a href="' . zen_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $_GET['cID']) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>', 'align'=>'center');
      $contents[] = array('text' => '<br><b>' . $cInfo->title . '</b>');
      break;
    default:
      if (is_object($cInfo)) {
        $heading[] = array('text' => '<b>' . $cInfo->title . '</b>');

        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->currencies_id . '&action=edit') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . zen_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->currencies_id . '&action=delete') . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
        $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_TITLE . ' ' . $cInfo->title);
        $contents[] = array('text' => TEXT_INFO_CURRENCY_CODE . ' ' . $cInfo->code);
        $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_SYMBOL_LEFT . ' ' . $cInfo->symbol_left);
        $contents[] = array('text' => TEXT_INFO_CURRENCY_SYMBOL_RIGHT . ' ' . $cInfo->symbol_right);
        $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_DECIMAL_POINT . ' ' . $cInfo->decimal_point);
        $contents[] = array('text' => TEXT_INFO_CURRENCY_THOUSANDS_POINT . ' ' . $cInfo->thousands_point);
        $contents[] = array('text' => TEXT_INFO_CURRENCY_DECIMAL_PLACES . ' ' . $cInfo->decimal_places);
        $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_LAST_UPDATED . ' ' . zen_date_short($cInfo->last_updated));
        $contents[] = array('text' => TEXT_INFO_CURRENCY_VALUE . ' ' . number_format($cInfo->value, 8));
        $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_EXAMPLE . '<br>' . $currencies->format('30', false, DEFAULT_CURRENCY) . ' = ' . $currencies->format('30', true, $cInfo->code));
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
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
