<?php
/**
 * @package linkpoint_api_payment_module
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: linkpoint_review.php 18695 2011-05-04 05:24:19Z drbyte $
 */

  require('includes/application_top.php');

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  if (isset($_GET['cID'])) $_GET['cID'] = (int)$_GET['cID'];

  if (substr($_GET['search'],0,3) == '%23' or substr($_GET['search'],0,1) == '#') {
    if (substr($_GET['search'],0,3) == '%23') {
    $search = '#' . substr($_GET['search'],2);
    } else {
    $search = '#' . substr($_GET['search'],1);
    }
  }

  $error = false;
  $processed = false;

  if (zen_not_null($action)) {
    switch ($action) {
      case 'status_OFF':
        if ($_GET['current'] == CUSTOMERS_APPROVAL_AUTHORIZATION) {
          $sql = "update " . TABLE_CUSTOMERS . " set customers_authorization=0 where customers_id='" . $_GET['cID'] . "'";
        } else {
          $sql = "update " . TABLE_CUSTOMERS . " set customers_authorization='" . CUSTOMERS_APPROVAL_AUTHORIZATION . "' where customers_id='" . $_GET['cID'] . "'";
        }
        $db->Execute($sql);
        $action = '';
        zen_redirect(zen_href_link(FILENAME_CUSTOMERS, 'cID=' . $_GET['cID'] . '&page=' . $_GET['page'], 'NONSSL'));
        break;
      default:
        $customers  = "select c.customers_id, c.customers_gender, c.customers_firstname,
                                          c.customers_lastname, c.customers_dob, c.customers_email_address,
                                          c.customers_telephone, c.customers_fax,
                                          c.customers_newsletter, c.customers_default_address_id,
                                          c.customers_email_format, c.customers_group_pricing,
                                          c.customers_authorization, c.customers_referral,
                                          lp.*
                                  from " . TABLE_CUSTOMERS . " c left join " .
                                  TABLE_LINKPOINT_API . " lp on c.customers_id = lp.customer_id
                                  where lp.customer_id = c.customers_id
                                  and c.customers_id = '" . (int)$_GET['cID'] . "'" .
                                  " order by lp.customer_id, lp.id ";

        $cInfo = new objectInfo($customers->fields);
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
<?php
  if (false) {
  } else {
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr><?php echo zen_draw_form('search', FILENAME_LINKPOINT_REVIEW, '', 'get', '', true); ?>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading"><?php echo ($_GET['search'] == '' ? '<a href="' . zen_href_link(FILENAME_LINKPOINT_REVIEW, '', 'NONSSL') . '">' . zen_image_button('button_update.gif', IMAGE_UPDATE) . '</a>&nbsp;&nbsp;' : ''); ?>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', 1, HEADING_IMAGE_HEIGHT); ?></td>
            <td class="smallText" align="right">
<?php
// show reset search
    if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
      echo '<a href="' . zen_href_link(FILENAME_LINKPOINT_REVIEW, '', 'NONSSL') . '">' . zen_image_button('button_reset.gif', IMAGE_RESET) . '</a>&nbsp;&nbsp;';
    }
    echo HEADING_TITLE_SEARCH_DETAIL . ' ' . zen_draw_input_field('search') . '<br />Customers Name or use #customers_id, example: #27275';
    if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
      $keywords = zen_db_input(zen_db_prepare_input($_GET['search']));
      zen_draw_hidden_field('disp_order', $disp_order);
      echo '<br/ >' . TEXT_INFO_SEARCH_DETAIL_FILTER . $keywords . '<br />';
    }
?>
            </td>
          </form></tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
<?php
// Sort Listing
          switch ($_GET['list_order']) {
              case "id-asc":
              $disp_order = "lp.id";
              break;
              case "customers_id":
              $disp_order = "c.customers_id, lp.id";
              break;
              case "customers_id-desc":
              $disp_order = "c.customers_id DESC, lp.id";
              break;
              case "firstname":
              $disp_order = "c.customers_firstname";
              break;
              case "firstname-desc":
              $disp_order = "c.customers_firstname DESC";
              break;
              case "group-asc":
              $disp_order = "c.customers_group_pricing";
              break;
              case "group-desc":
              $disp_order = "c.customers_group_pricing DESC";
              break;
              case "lastname":
              $disp_order = "c.customers_lastname, c.customers_firstname";
              break;
              case "lastname-desc":
              $disp_order = "c.customers_lastname DESC, c.customers_firstname";
              break;
              case "company":
              $disp_order = "a.entry_company";
              break;
              case "company-desc":
              $disp_order = "a.entry_company DESC";
              break;
              case "login-asc":
              $disp_order = "ci.customers_info_date_of_last_logon";
              break;
              case "login-desc":
              $disp_order = "ci.customers_info_date_of_last_logon DESC";
              break;
              case "approval-asc":
              $disp_order = "c.customers_authorization";
              break;
              case "approval-desc":
              $disp_order = "c.customers_authorization DESC";
              break;
              default:
              $disp_order = "lp.id DESC";
              $_GET['list_order'] = "id-desc";
          }
?>
             <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent" align="left">
                  <?php echo (($_GET['list_order']=='id-asc' or $_GET['list_order']=='id-desc') ? '<span class="SortOrderHeader">' . LPID . '</span>' : LPID); ?><br>
                  <a href="<?php echo zen_href_link(basename($PHP_SELF) . '?list_order=id-asc', '', 'NONSSL'); ?>"><?php echo ($_GET['list_order']=='id-asc' ? '<span class="SortOrderHeader">Asc</span>' : '<span class="SortOrderHeaderLink">Asc</b>'); ?></a>&nbsp;
                  <a href="<?php echo zen_href_link(basename($PHP_SELF) . '?list_order=id-desc', '', 'NONSSL'); ?>"><?php echo ($_GET['list_order']=='id-desc' ? '<span class="SortOrderHeader">Desc</span>' : '<span class="SortOrderHeaderLink">Desc</b>'); ?></a>
                </td>
                <td class="dataTableHeadingContent" align="left">
                  <?php echo (($_GET['list_order']=='customers_id' or $_GET['list_order']=='customers_id-desc') ? '<span class="SortOrderHeader">' . TABLE_HEADING_ID . '</span>' : TABLE_HEADING_ID); ?><br>
                  <a href="<?php echo zen_href_link(basename($PHP_SELF) . '?list_order=customers_id', '', 'NONSSL'); ?>"><?php echo ($_GET['list_order']=='customers_id' ? '<span class="SortOrderHeader">Asc</span>' : '<span class="SortOrderHeaderLink">Asc</b>'); ?></a>&nbsp;
                  <a href="<?php echo zen_href_link(basename($PHP_SELF) . '?list_order=customers_id-desc', '', 'NONSSL'); ?>"><?php echo ($_GET['list_order']=='customers_id-desc' ? '<span class="SortOrderHeader">Desc</span>' : '<span class="SortOrderHeaderLink">Desc</b>'); ?></a>
                </td>
                <td class="dataTableHeadingContent" align="left">
                  <?php echo (($_GET['list_order']=='lastname' or $_GET['list_order']=='lastname-desc') ? '<span class="SortOrderHeader">' . TABLE_HEADING_LASTNAME . '</span>' : TABLE_HEADING_LASTNAME); ?><br>
                  <a href="<?php echo zen_href_link(basename($PHP_SELF) . '?list_order=lastname', '', 'NONSSL'); ?>"><?php echo ($_GET['list_order']=='lastname' ? '<span class="SortOrderHeader">Asc</span>' : '<span class="SortOrderHeaderLink">Asc</b>'); ?></a>&nbsp;
                  <a href="<?php echo zen_href_link(basename($PHP_SELF) . '?list_order=lastname-desc', '', 'NONSSL'); ?>"><?php echo ($_GET['list_order']=='lastname-desc' ? '<span class="SortOrderHeader">Desc</span>' : '<span class="SortOrderHeaderLink">Desc</b>'); ?></a>
                </td>
                <td class="dataTableHeadingContent" align="left">
                  <?php echo (($_GET['list_order']=='firstname' or $_GET['list_order']=='firstname-desc') ? '<span class="SortOrderHeader">' . TABLE_HEADING_FIRSTNAME . '</span>' : TABLE_HEADING_FIRSTNAME); ?><br>
                  <a href="<?php echo zen_href_link(basename($PHP_SELF) . '?list_order=firstname', '', 'NONSSL'); ?>"><?php echo ($_GET['list_order']=='firstname' ? '<span class="SortOrderHeader">Asc</span>' : '<span class="SortOrderHeaderLink">Asc</b>'); ?></a>&nbsp;
                  <a href="<?php echo zen_href_link(basename($PHP_SELF) . '?list_order=firstname-desc', '', 'NONSSL'); ?>"><?php echo ($_GET['list_order']=='firstname-desc' ? '<span class="SortOrderHeader">Desc</span>' : '<span class="SortOrderHeaderLink">Desc</span>'); ?></a>
                </td>
                <td class="dataTableHeadingContent" align="left">
                </td>
                <td class="dataTableHeadingContent" align="left">
                </td>

                <td class="dataTableHeadingContent" align="left">
                </td>

                <td class="dataTableHeadingContent" align="left">
                </td>

                <td class="dataTableHeadingContent" align="center">
                  <?php echo TABLE_HEADING_ACCOUNT_CREATED; ?>
                </td>

                <td class="dataTableHeadingContent" align="right">
                </td>
              </tr>
<?php
    $search = '';
    if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
      $keywords = zen_db_input(zen_db_prepare_input($_GET['search']));
      if (substr($keywords,0,1) == '#') {
        $search = "where lp.customer_id = c.customers_id and (c.customers_id='" . substr($keywords,1) . "')";
      } else {
        $search = "where lp.customer_id = c.customers_id and (c.customers_lastname like '%" . $keywords . "%' or c.customers_firstname like '%" . $keywords . "%' or c.customers_email_address like '%" . $keywords . "%' or c.customers_telephone rlike '" . $keywords . "' or c.customers_id='" . $keywords . "')";
      }
    } else {
      $search = "where lp.customer_id = c.customers_id";
    }
    $new_fields=', c.customers_telephone, a.entry_company, a.entry_street_address, a.entry_city, a.entry_postcode, c.customers_authorization, c.customers_referral';
//    $customers_query_raw = "select c.customers_id, c.customers_lastname, c.customers_firstname, c.customers_email_address, c.customers_group_pricing, a.entry_country_id, a.entry_company, ci.customers_info_date_of_last_logon, ci.customers_info_date_account_created " . $new_fields . " from " . TABLE_CUSTOMERS . " c left join " . TABLE_CUSTOMERS_INFO . " ci on c.customers_id= ci.customers_info_id left join " . TABLE_ADDRESS_BOOK . " a on c.customers_id = a.customers_id and c.customers_default_address_id = a.address_book_id " . $search . " order by $disp_order";

        $customers_query_raw  = "select lp.*, c.*
                                  from " .
                                  TABLE_CUSTOMERS . " c, " .
                                  TABLE_LINKPOINT_API . " lp " .
                                  $search . "
                                  order by $disp_order ";

//  $testing = $db->Execute($customers_query_raw);

//die('I SEE ' . $testing->RecordCount());

// Split Page
// reset page when page is unknown
if ($_GET['page'] == '' and $_GET['cID'] != '') {
  $check_page = $db->Execute($customers_query_raw);
  $check_count=1;
  if ($check_page->RecordCount() > MAX_DISPLAY_SEARCH_RESULTS_CUSTOMER) {
    while (!$check_page->EOF) {
      if ($check_page->fields['customers_id'] == $_GET['cID']) {
        break;
      }
      $check_count++;
      $check_page->MoveNext();
    }
    $_GET['page'] = round((($check_count/MAX_DISPLAY_SEARCH_RESULTS_CUSTOMER)+(fmod($check_count,MAX_DISPLAY_SEARCH_RESULTS_CUSTOMER) !=0 ? .5 : 0)),0);
//    zen_redirect(zen_href_link(FILENAME_LINKPOINT_REVIEW, 'cID=' . $_GET['cID'] . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''), 'NONSSL'));
  } else {
    $_GET['page'] = 1;
  }
}

    $customers_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS_CUSTOMER, $customers_query_raw, $customers_query_numrows);
    $customers = $db->Execute($customers_query_raw);
    while (!$customers->EOF) {
      $info = $db->Execute("select customers_info_date_account_created as date_account_created,
                                   customers_info_date_account_last_modified as date_account_last_modified,
                                   customers_info_date_of_last_logon as date_last_logon,
                                   customers_info_number_of_logons as number_of_logons
                            from " . TABLE_CUSTOMERS_INFO . "
                            where customers_info_id = '" . $customers->fields['customers_id'] . "'");

      if ((!isset($_GET['cID']) || (isset($_GET['cID']) && ($_GET['cID'] == $customers->fields['customers_id']))) && !isset($cInfo)) {
        $country = $db->Execute("select countries_name
                                 from " . TABLE_COUNTRIES . "
                                 where countries_id = '" . (int)$customers->fields['entry_country_id'] . "'");
        if (!is_array($country->fields)) $country->fields = array();

        $customer_info = array_merge($country->fields, $info->fields);

        $cInfo_array = array_merge($customers->fields, $customer_info);
        $cInfo = new objectInfo($cInfo_array);
      }

        $group_query = $db->Execute("select group_name, group_percentage from " . TABLE_GROUP_PRICING . " where
                                     group_id = '" . $customers->fields['customers_group_pricing'] . "'");

        if ($group_query->RecordCount() < 1) {
          $group_name_entry = TEXT_NONE;
        } else {
          $group_name_entry = $group_query->fields['group_name'];
        }

/*
      if (isset($cInfo) && is_object($cInfo) && ($customers->fields['customers_id'] == $cInfo->customers_id)) {
        echo '          <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link(FILENAME_LINKPOINT_REVIEW, zen_get_all_get_params(array('cID', 'action')) . '&search=xxx' . $cInfo->customers_id, 'NONSSL') . '\'">' . "\n";
      } else {
        echo '          <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link(FILENAME_LINKPOINT_REVIEW, zen_get_all_get_params(array('cID', 'action')) . '&search=xxx' . $cInfo->customers_id, 'NONSSL') . '\'">' . "\n";
      }
*/
        echo '          <tr class="dataTableRow">' . "\n";

?>
                <td class="dataTableContent" align="right"><?php echo $customers->fields['id']; ?></td>
                <td class="dataTableContent" align="right"><?php echo '<a href="' . zen_href_link(FILENAME_LINKPOINT_REVIEW, 'search=%23' . $customers->fields['customers_id'], 'NONSSL') . '">#' . ($customers->fields['transaction_result'] != 'APPROVED' ? '<span class="alert">' . $customers->fields['customers_id'] . '</span>' : $customers->fields['customers_id']) . '</a>'; ?></td>
                <td class="dataTableContent"><?php echo '<a href="' . zen_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(array('cID', 'action', 'page', 'search', 'list_order')) . 'cID=' . $customers->fields['customer_id'], 'NONSSL') . '">' . ($customers->fields['transaction_result'] != 'APPROVED' ? '<span class="alert">' . $customers->fields['customers_lastname'] . '</span>' : $customers->fields['customers_lastname']) . '</a>'; ?></td>
                <td class="dataTableContent"><?php echo '<a href="' . zen_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(array('cID', 'action', 'page', 'search', 'list_order')) . 'cID=' . $customers->fields['customer_id'], 'NONSSL') . '">' . ($customers->fields['transaction_result'] != 'APPROVED' ? '<span class="alert">' . $customers->fields['customers_firstname'] . '</span>' : $customers->fields['customers_firstname']) . '</a>'; ?></td>
                <td class="dataTableContent"><?php echo '<a href="' . zen_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(array('cID', 'action', 'page', 'search', 'list_order')) . 'cID=' . $customers->fields['customer_id'], 'NONSSL') . '">' . ($customers->fields['transaction_result'] != 'APPROVED' ? '<span class="alert">' . $customers->fields['entry_company'] . '</span>' : $customers->fields['entry_company']) . '</a>'; ?></td>

                <td class="dataTableContent">
                  <?php echo 'Credit Card Server Time: <strong>' . ($customers->fields['transaction_response_time'] == '' ? 'Not Connected' : $customers->fields['transaction_response_time']) . '</strong>'; ?>
                </td>
                <td class="dataTableContent">
                  <?php echo 'This Server Time: <strong>' . $customers->fields['date_added'] . '</strong>'; ?>
                </td>
                <td class="dataTableContent" align="right" style="color:red;">
                  <?php echo $currencies->format($customers->fields['chargetotal']); ?>
                </td>
                <td class="dataTableContent" align="center">
                  <?php echo zen_date_short($info->fields['date_account_created']); ?>
                </td>
                <td class="dataTableContent" align="right">
                </td>
              </tr>
              <tr class="dataTableRow">
                <td class="dataTableContent" colspan="4">
                  <?php echo
                    ($customers->fields['transaction_result'] != 'APPROVED' ? '<span class="alert">' . $customers->fields['transaction_result'] . '</span>' : $customers->fields['transaction_result']) . '<br />' .
                    $customers->fields['cc_number'] . '<br />' .
                    'Expires: ' . $customers->fields['cc_expire'] . '<br />' .
                    $customers->fields['lp_trans_num'] . '<br />' .
                    $customers->fields['transaction_reference_number'] . '<br />' .
                    ($customers->fields['avs_response'] != 'YYYM' ? '<span class="alert">' . $customers->fields['avs_response'] . '</span>' : $customers->fields['avs_response']) . ' ' . ($customers->fields['r_error'] != '' ? '<span class="alert">' . $customers->fields['r_error'] . '</span>' : '') . '<br />' .
                    $customers->fields['transaction_time'];
                  ?>
                </td>
                <td class="dataTableContent" colspan="6"><?php echo str_replace(array('PREAUTH','SALE'),array('<span style="color:orange;"><strong>PREAUTH</strong></span>','<span style="color:green;"><strong>SALE</strong></span>'), $customers->fields['cust_info']) . '<br /><br />'; ?></td>
              </tr>
              <tr>
                <td colspan="10"><?php echo zen_draw_separator('pixel_black.gif', "100%", 3); ?></td>
              </tr>
<?php
      $customers->MoveNext();
    }
?>
              <tr>
                <td colspan="6"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" align="right" valign="top"><?php echo $customers_split->display_count($customers_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_CUSTOMER, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_CUSTOMERS); ?></td>
                    <td class="smallText" align="right"><?php echo $customers_split->display_links($customers_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_CUSTOMER, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], zen_get_all_get_params(array('page', 'info', 'x', 'y', 'cID'))); ?></td>
                  </tr>
<?php
    if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
?>
                  <tr>
                    <td align="right"><?php echo '<a href="' . zen_href_link(FILENAME_LINKPOINT_REVIEW, '', 'NONSSL') . '">' . zen_image_button('button_reset.gif', IMAGE_RESET) . '</a>'; ?></td>
                  </tr>
<?php
    }
?>
                </table></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
<?php
  }
?>
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
<?php require(DIR_WS_INCLUDES . 'application_bottom.php');
