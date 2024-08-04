<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott Wilson 2024 Jul 24 Modified in v2.1.0-alpha1 $
 */
require 'includes/application_top.php';
require DIR_WS_CLASSES . 'currencies.php';
$currencies = new currencies();
$languages = zen_get_languages();
if (!isset($_GET['action'])) {
  $_GET['action'] = '';
}
if (isset($_GET['cid'])) {
  $_GET['cid'] = (int)$_GET['cid'];
}
if (isset($_GET['reports_page'])) {
  $_GET['reports_page'] = (int)$_GET['reports_page'];
}
$active = '';
if (isset($_GET['status'])) {
  $_GET['status'] = preg_replace('/[^YNA]/', '', $_GET['status']);
  $active = $_GET['status'] != 'A' ? " AND coupon_active = '" . $_GET['status'] . "' " : '';
}
if (isset($_GET['codebase'])) {
  $_GET['codebase'] = preg_replace('/[^A-Za-z0-9\-\][\^!@#$%&*)(+=}{]/', '', $_GET['codebase']);
}
if (empty($_POST['coupon_amount'])) {
  $_POST['coupon_amount'] = '0';
}

$inSearch = '';
$delimiter = '';

if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
    $keywords = zen_db_input(zen_db_prepare_input($_GET['search']));
    $keyword_search_fields = [
        'cd.coupon_name',
        'cd.coupon_description',
        'c.coupon_code',
        'cr.referrer_domain'
    ];
    $searchWords = zen_build_keyword_where_clause($keyword_search_fields, trim($keywords), true);
    $sql = "SELECT c.coupon_id, c.coupon_active
            FROM " . TABLE_COUPONS . " c
            LEFT JOIN " . TABLE_COUPONS_DESCRIPTION . " cd ON cd.coupon_id = c.coupon_id
            LEFT JOIN " . TABLE_COUPON_REFERRERS . " cr ON cr.coupon_id = c.coupon_id
            " . $searchWords . $active;
  $search = $db->Execute($sql);
  if ($search->EOF) {
      $messageStack->add_session(ERROR_COUPON_NOT_FOUND, 'caution');
      zen_redirect(zen_href_link(FILENAME_COUPON_ADMIN));
  }
  foreach ($search as $searchResult) {
      $inSearch .= $delimiter . $searchResult['coupon_id'];
      $delimiter = ',';
  }
}

if ($_GET['action'] == 'send_email_to_user' && !empty($_POST['customers_email_address'])) {
  $audience_select = get_audience_sql_query($_POST['customers_email_address'], 'email');
  $mail = $db->Execute($audience_select['query_string']);
  $mail_sent_to = (!empty($_POST['email_to'])) ? $_POST['email_to'] : $audience_select['query_name'];

  $coupon_result = $db->Execute("SELECT coupon_code, coupon_start_date, coupon_expire_date, coupon_calc_base, coupon_is_valid_for_sales, coupon_product_count
                                 FROM " . TABLE_COUPONS . " c
                                 LEFT JOIN " . TABLE_COUPONS_DESCRIPTION . " cd ON cd.coupon_id = c.coupon_id
                                   AND language_id = " . (int)$_SESSION['languages_id'] . "
                                 WHERE c.coupon_id = " . (int)$_GET['cid']);

  $from = zen_db_prepare_input($_POST['from']);
  $subject = zen_db_prepare_input($_POST['subject']);
  $recip_count = 0;
  $text_coupon_help = sprintf(TEXT_COUPON_HELP_DATE, zen_date_short($coupon_result->fields['coupon_start_date']), zen_date_short($coupon_result->fields['coupon_expire_date']));
  $html_coupon_help = sprintf(HTML_COUPON_HELP_DATE, zen_date_short($coupon_result->fields['coupon_start_date']), zen_date_short($coupon_result->fields['coupon_expire_date']));

  foreach ($mail as $item) {
    $message = zen_db_prepare_input($_POST['message']);
    $message .= "\n\n" . TEXT_TO_REDEEM . "\n\n";
    $message .= TEXT_VOUCHER_IS . $coupon_result->fields['coupon_code'] . "\n\n";
    $message .= $text_coupon_help . "\n\n";
    if ($coupon_result->fields['coupon_is_valid_for_sales']) {
      $message .= TEXT_COUPON_IS_VALID_FOR_SALES_EMAIL . "\n\n";
    } else {
      $message .= TEXT_NO_COUPON_IS_VALID_FOR_SALES_EMAIL . "\n\n";
    }
    if ($coupon_result->fields['coupon_product_count']) {
      $message .= TEXT_COUPON_PRODUCT_COUNT_PER_PRODUCT . "\n\n";
    } else {
      $message .= TEXT_COUPON_PRODUCT_COUNT_PER_ORDER . "\n\n";
    }

    $message .= TEXT_REMEMBER . "\n\n";
    $message .= (!empty($coupon_result->fields['coupon_description']) ? $coupon_result->fields['coupon_description'] . "\n\n" : '');
    $message .= sprintf(TEXT_VISIT, HTTP_CATALOG_SERVER . DIR_WS_CATALOG);

    // disclaimer
    $message .= "\n-----\n" . sprintf(EMAIL_DISCLAIMER, STORE_OWNER_EMAIL_ADDRESS) . "\n\n";

    $html_msg['EMAIL_SALUTATION'] = EMAIL_SALUTATION;
    $html_msg['EMAIL_FIRST_NAME'] = $item['customers_firstname'];
    $html_msg['EMAIL_LAST_NAME'] = $item['customers_lastname'];
    $html_msg['EMAIL_MESSAGE_HTML'] = zen_db_prepare_input($_POST['message_html']);
    $html_msg['COUPON_TEXT_TO_REDEEM'] = TEXT_TO_REDEEM;
    $html_msg['COUPON_TEXT_VOUCHER_IS'] = TEXT_VOUCHER_IS;
    $html_msg['COUPON_CODE'] = $coupon_result->fields['coupon_code'] . $html_coupon_help;
    $html_msg['COUPON_DESCRIPTION'] = (!empty($coupon_result->fields['coupon_description']) ? $coupon_result->fields['coupon_description'] : '');
    $html_msg['COUPON_TEXT_REMEMBER'] = TEXT_REMEMBER;
    $html_msg['COUPON_REDEEM_STORENAME_URL'] = sprintf(TEXT_VISIT, '<a href="' . HTTP_CATALOG_SERVER . DIR_WS_CATALOG . '">' . STORE_NAME . '</a>');

//Send the emails
    zen_mail($item['customers_firstname'] . ' ' . $item['customers_lastname'], $item['customers_email_address'], $subject, $message, '', $from, $html_msg, 'coupon');
    zen_record_admin_activity('Coupon code ' . $coupon_result->fields['coupon_code'] . ' emailed to customer ' . $item['customers_email_address'], 'info');
    $zco_notifier->notify('ADMIN_COUPON_CODE_EMAILED_TO_CUSTOMER', $coupon_result->fields['coupon_code'], $item['customers_email_address']);
    $recip_count++;
    // send copy to Admin if enabled
    if (SEND_EXTRA_DISCOUNT_COUPON_ADMIN_EMAILS_TO_STATUS == '1' && SEND_EXTRA_DISCOUNT_COUPON_ADMIN_EMAILS_TO != '') {
      zen_mail('', SEND_EXTRA_DISCOUNT_COUPON_ADMIN_EMAILS_TO, SEND_EXTRA_DISCOUNT_COUPON_ADMIN_EMAILS_TO_SUBJECT . ' ' . $subject, $message, '', $from, $html_msg, 'coupon_extra');
    }
  }
  zen_redirect(zen_href_link(FILENAME_COUPON_ADMIN, 'mail_sent_to=' . urlencode($mail_sent_to) . '&recip_count=' . $recip_count));
}

if ($_GET['action'] == 'preview_email' && empty($_POST['customers_email_address'])) {
  $_GET['action'] = 'email';
  $messageStack->add(ERROR_NO_CUSTOMER_SELECTED, 'error');
}

if (!empty($_GET['mail_sent_to'])) {
  $messageStack->add(sprintf(NOTICE_EMAIL_SENT_TO, $_GET['mail_sent_to'] . '(' . $_GET['recip_count'] . ')'), 'success');
  $_GET['mail_sent_to'] = '';
}

if (empty($_GET['cid']) && $_GET['action'] === 'voucheredit') {
    $_GET['action'] = '';
}

switch ($_GET['action']) {
  case 'set_editor':
    // Reset will be done by init_html_editor.php. Now we simply redirect to refresh page properly.
    $action = '';
    zen_redirect(zen_href_link(FILENAME_COUPON_ADMIN));
    break;

  case 'confirmdelete':
    // do not allow change if set to welcome coupon
    if ($_GET['cid'] == NEW_SIGNUP_DISCOUNT_COUPON) {
      $messageStack->add_session(ERROR_DISCOUNT_COUPON_WELCOME, 'caution');
      zen_redirect(zen_href_link(FILENAME_COUPON_ADMIN, 'cid=' . $_GET['cid'] . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')));
    }

    Coupon::disable((int)$_GET['cid']);
    $messageStack->add_session(SUCCESS_COUPON_DISABLED, 'success');
    zen_redirect(zen_href_link(FILENAME_COUPON_ADMIN));
    break;

  case 'confirmreactivate':
    Coupon::enable((int)$_GET['cid']);
    $messageStack->add_session(SUCCESS_COUPON_REACTIVATE, 'success');
    zen_redirect(zen_href_link(FILENAME_COUPON_ADMIN, 'cid=' . $_GET['cid']));
    break;

  case 'confirmdeleteduplicate':
      $deleted = Coupon::deleteDuplicates($_POST['coupon_delete_duplicate_code']);
      if (isset($deleted['welcome_coupon'])) {
          $messageStack->add_session(ERROR_DISCOUNT_COUPON_WELCOME, 'caution');
      }
      foreach ($deleted['deleted'] ?? [] as $duplicate) {
          $messageStack->add_session(TEXT_DISCOUNT_COUPON_DEACTIVATED . $duplicate, 'caution');
      }
    zen_redirect(zen_href_link(FILENAME_COUPON_ADMIN));
    break;

  case 'confirmcopyduplicate':
// base code - create duplicate codes from base code
    $zc_discount_coupons_create = (int)$_POST['coupon_copy_to_count'];
    if ($zc_discount_coupons_create < 1) {
      $messageStack->add_session(WARNING_COUPON_DUPLICATE . $_POST['coupon_copy_to_dup_name'] . ' - x' . $_POST['coupon_copy_to_count'], 'caution');
    } else {
        $status = Coupon::make_duplicates((int)$_GET['cid'], $_POST['coupon_copy_to_dup_name'], $zc_discount_coupons_create);
        if ($status === true) {
            $messageStack->add_session(SUCCESS_COUPON_DUPLICATE . $_POST['coupon_copy_to_dup_name'] . ' - x' . $_POST['coupon_copy_to_count'], 'success');
        } else {
          // cannot create code
          $messageStack->add_session(WARNING_COUPON_DUPLICATE_FAILED . $_POST['coupon_copy_to_dup_name'] . ' - x' . $_POST['coupon_copy_to_count'], 'caution');
        }
    }
    zen_redirect(zen_href_link(FILENAME_COUPON_ADMIN, 'cid=' . $_GET['cid'] . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')));
    break;

  case 'confirmcopy':
    $coupon_copy_to = trim($_POST['coupon_copy_to']);
    $result = Coupon::clone((int)$_GET['cid'], $coupon_copy_to);
    if ($result === false) {
      $messageStack->add_session(ERROR_DISCOUNT_COUPON_DUPLICATE . $coupon_copy_to, 'caution');
      zen_redirect(zen_href_link(FILENAME_COUPON_ADMIN, 'cid=' . $_GET['cid'] . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')));
    } else {
        // use the new coupon id as the page to go to on success
        $_GET['cid'] = (string)$result;
    }
    $messageStack->add_session(SUCCESS_COUPON_DUPLICATE . $coupon_copy_to, 'success');
    zen_redirect(zen_href_link(FILENAME_COUPON_ADMIN, 'action=voucheredit' . '&cid=' . $_GET['cid'] . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '')));
    break;

  case 'update':
    $update_errors = 0;
    $_POST['coupon_code'] = trim($_POST['coupon_code']);
    for ($i = 0, $n = count($languages); $i < $n; $i++) {
      $language_id = $languages[$i]['id'];
      $_POST['coupon_name'][$language_id] = trim($_POST['coupon_name'][$language_id]);
      if (!$_POST['coupon_name'][$language_id]) {
        $update_errors = 1;
        $messageStack->add(ERROR_NO_COUPON_NAME . $languages[$i]['name'], 'error');
      }
      $_POST['coupon_desc'][$language_id] = trim($_POST['coupon_desc'][$language_id]);
    }
    $_POST['coupon_amount'] = trim($_POST['coupon_amount']);
    $is_pct = (substr($_POST['coupon_amount'], -1) == '%');
    $_POST['coupon_amount'] = (float)preg_replace('/[^0-9.]/', '', $_POST['coupon_amount']);
    if ($is_pct) {
      $_POST['coupon_amount'] .= "%";
    }
    if (!$_POST['coupon_name']) {
      $update_errors = 1;
      $messageStack->add(ERROR_NO_COUPON_NAME, 'error');
    }
    if ((!$_POST['coupon_amount']) && (empty($_POST['coupon_free_ship']))) {
      $update_errors = 1;
      $messageStack->add(ERROR_NO_COUPON_AMOUNT, 'error');
    }
    // no Discount Coupon code when editing
    if ($_GET['oldaction'] != 'new' && !$_POST['coupon_code']) {
      $update_errors = 1;
      $messageStack->add(ERROR_NO_COUPON_CODE, 'error');
    }
    if (!$_POST['coupon_code']) {
      $coupon_code = Coupon::generateRandomCouponCode();
    }
    if ($_POST['coupon_code']) {
      $coupon_code = $_POST['coupon_code'];
    }
    $sql = "SELECT coupon_id, coupon_code
            FROM " . TABLE_COUPONS . "
            WHERE coupon_code = :couponCode:";
    $sql = $db->bindVars($sql, ':couponCode:', $coupon_code, 'string');
    $query1 = $db->Execute($sql);
    if ($query1->RecordCount() > 0 && $_POST['coupon_code'] && $_GET['oldaction'] != 'voucheredit') {
      $update_errors = 1;
      $messageStack->add(ERROR_COUPON_EXISTS . ' - ' . $_POST['coupon_code'], 'error');
    }
    if ($update_errors == 0 && $query1->RecordCount() > 0 && $query1->fields['coupon_id'] != $_GET['cid']) {
      $update_errors = 1;
      $messageStack->add(ERROR_COUPON_EXISTS . ' - ' . $_POST['coupon_code'], 'error');
    }

    if ($update_errors != 0) {
      if ($_GET['oldaction'] != 'new' && $query1->RecordCount() > 0 && $query1->fields['coupon_id'] != $_GET['cid']) {
        $_GET['action'] = 'voucheredit';
      } else {
        $_GET['action'] = 'new';
      }
    } else {
      $_GET['action'] = 'update_preview';
    }
    break;

  case 'update_confirm':
    $coupon_type = 'F'; // amount off
    if ($_POST['coupon_free_ship']) {
      $coupon_type = 'S'; // free shipping
    }
    if (substr($_POST['coupon_amount'], -1) == '%') {
      $coupon_type = 'P'; // percentage off
    }
    if ($_POST['coupon_amount'] > 0 && $_POST['coupon_free_ship']) {
      $coupon_type = 'O';  // amount off and free shipping
    }
    if (substr($_POST['coupon_amount'], -1) == '%' && $_POST['coupon_free_ship']) {
      $coupon_type = 'E'; // percentage off and free shipping
    }
    $_POST['coupon_amount'] = preg_replace('/[^0-9.]/', '', $_POST['coupon_amount']);
    $sql_data_array = [
      'coupon_code' => zen_db_prepare_input($_POST['coupon_code']),
      'coupon_amount' => zen_db_prepare_input($_POST['coupon_amount']),
      'coupon_product_count' => (int)$_POST['coupon_product_count'],
      'coupon_type' => zen_db_prepare_input($coupon_type),
      'uses_per_coupon' => (int)$_POST['coupon_uses_coupon'],
      'uses_per_user' => (int)$_POST['coupon_uses_user'],
      'coupon_minimum_order' => (float)$_POST['coupon_min_order'],
      'restrict_to_products' => zen_db_prepare_input($_POST['coupon_products']),
      'restrict_to_categories' => zen_db_prepare_input($_POST['coupon_categories']),
      'coupon_start_date' => $_POST['coupon_startdate'],
      'coupon_expire_date' => $_POST['coupon_finishdate'],
      'date_created' => 'now()',
      'date_modified' => 'now()',
      'coupon_zone_restriction' => $_POST['coupon_zone_restriction'],
      'coupon_calc_base' => (int)$_POST['coupon_calc_base'],
      'coupon_order_limit' => (int)$_POST['coupon_order_limit'],
      'coupon_is_valid_for_sales' => (int)$_POST['coupon_is_valid_for_sales'],
      'coupon_active' => 'Y',
    ];

    for ($i = 0, $n = count($languages); $i < $n; $i++) {
      $language_id = $languages[$i]['id'];
      $sql_data_marray[$i] = [
        'coupon_name' => zen_db_prepare_input($_POST['coupon_name'][$language_id]),
        'coupon_description' => zen_db_prepare_input($_POST['coupon_desc'][$language_id])
      ];
    }
    if ($_GET['oldaction'] == 'voucheredit') {
      zen_db_perform(TABLE_COUPONS, $sql_data_array, 'update', "coupon_id = " . (int)$_GET['cid']);
      for ($i = 0, $n = count($languages); $i < $n; $i++) {
        $language_id = $languages[$i]['id'];
        $sql_data_desc_array = [
          'coupon_name' => zen_db_prepare_input($_POST['coupon_name'][$language_id]),
          'coupon_description' => zen_db_prepare_input($_POST['coupon_desc'][$language_id]),
        ];
        zen_db_perform(TABLE_COUPONS_DESCRIPTION, $sql_data_desc_array, 'update', "coupon_id = " . (int)$_GET['cid'] . " and language_id = " . (int)$languages[$i]['id']);
      }
      // referrers
        $trimmed_referrers = array_map(static fn($referrer) => trim($referrer), explode(',', $_POST['coupon_referrer'] ?? []));
        $results = $db->Execute("SELECT *
                                 FROM " . TABLE_COUPON_REFERRERS . "
                                 WHERE coupon_id = " . (int)$_GET['cid']);
        $previous_referrers = [];
        foreach ($results as $result) {
            $previous_referrers[] = $result['referrer_domain'];
        }
        foreach ($trimmed_referrers as $referrer) {
            // add new domains
            if (empty(CouponValidation::referrer_already_assigned($referrer))) {
                $sql_data_array = [
                    'referrer_domain' => $referrer,
                    'coupon_id' => (int)$_GET['cid'],
                ];
                zen_db_perform(TABLE_COUPON_REFERRERS, $sql_data_array);
            }
        }
        foreach ($previous_referrers as $referrer) {
            // delete removed domains
            if (!in_array($referrer, $trimmed_referrers)) {
                $sql = "DELETE FROM " . TABLE_COUPON_REFERRERS . " WHERE referrer_domain = :domain";
                $sql = $db->bindVars($sql, ':domain', $referrer, 'string');
                $db->Execute($sql);
            }
        }
    } else {
      zen_db_perform(TABLE_COUPONS, $sql_data_array);
      $cid = $db->insert_ID();
      $_GET['cid'] = $cid;

      for ($i = 0, $n = count($languages); $i < $n; $i++) {
        $language_id = $languages[$i]['id'];
        $sql_data_marray[$i]['coupon_id'] = (int)$cid;
        $sql_data_marray[$i]['language_id'] = (int)$language_id;
        zen_db_perform(TABLE_COUPONS_DESCRIPTION, $sql_data_marray[$i]);
      }
      // referrers
        $trimmed_referrers = array_map(static fn($referrer) => trim($referrer), explode(',', $_POST['coupon_referrer'] ?? []));
        foreach ($trimmed_referrers as $referrer) {
            if (empty(CouponValidation::referrer_already_assigned($referrer))) {
                $sql_data_array = [
                    'referrer_domain' => $referrer,
                    'coupon_id' => (int)$_GET['cid'],
                ];
                zen_db_perform(TABLE_COUPON_REFERRERS, $sql_data_array);
            }
        }
    }
    zen_redirect(zen_href_link(FILENAME_COUPON_ADMIN, 'cid=' . $_GET['cid'] . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')));
}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
    <?php
    if (!empty($editor_handler)) {
      include $editor_handler;
    }
    ?>
  </head>
  <body>
    <!-- header //-->
    <?php require DIR_WS_INCLUDES . 'header.php'; ?>
    <!-- header_eof //-->
    <!-- body //-->
    <div class="container-fluid">
      <h1 class="pageHeading"><?php echo HEADING_TITLE; ?></h1>
      <!-- body_text //-->
      <div class="row">
        <?php
        switch ($_GET['action']) {
          case 'voucherreport':
            ?>
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
              <table class="table table-striped table-hover">
                <thead>
                  <tr class="dataTableHeadingRow">
                    <th class="dataTableHeadingContent"><?php echo CUSTOMER_ID; ?></th>
                    <th class="dataTableHeadingContent text-center"><?php echo CUSTOMER_NAME; ?></th>
                    <th class="dataTableHeadingContent text-center"><?php echo IP_ADDRESS; ?></th>
                    <th class="dataTableHeadingContent text-center"><?php echo REDEEM_DATE; ?></th>
                    <th class="dataTableHeadingContent text-right"><?php echo REDEEM_ORDER_ID; ?></th>
                    <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $cc_query_raw = "SELECT *
                                   FROM " . TABLE_COUPON_REDEEM_TRACK . "
                                   WHERE coupon_id = " . (int)$_GET['cid'];
                  $cc_split = new splitPageResults($_GET['reports_page'], MAX_DISPLAY_SEARCH_RESULTS_DISCOUNT_COUPONS_REPORTS, $cc_query_raw, $cc_query_numrows);
                  $cc_list = $db->Execute($cc_query_raw);
                  if ($cc_list->EOF && empty($cInfo)) {
                    $cInfo = new objectInfo($cc_list->fields);
                  }
                  foreach ($cc_list as $item) {
                    if ((empty($_GET['uid']) || ($_GET['uid'] == $item['unique_id'])) && empty($cInfo)) {
                      $cInfo = new objectInfo($item);
                    }
                    if ((isset($cInfo)) && ($item['unique_id'] == $cInfo->unique_id)) {
                      ?>
                      <tr class="dataTableRowSelected" onclick="document.location.href = '<?php echo zen_href_link(FILENAME_COUPON_ADMIN, zen_get_all_get_params(array('cid', 'action', 'uid')) . 'cid=' . $cInfo->coupon_id . '&action=voucherreport&uid=' . $cInfo->unique_id); ?>'">
                      <?php } else { ?>
                      <tr class="dataTableRow" onclick="document.location.href = '<?php echo zen_href_link(FILENAME_COUPON_ADMIN, zen_get_all_get_params(array('cid', 'action', 'uid')) . 'cid=' . $item['coupon_id'] . '&action=voucherreport&uid=' . $item['unique_id']); ?>'">
                        <?php
                      }
                      $customer = $db->Execute("SELECT customers_firstname, customers_lastname
                                                FROM " . TABLE_CUSTOMERS . "
                                                WHERE customers_id = " . (int)$item['customer_id']);
                      ?>
                      <td class="dataTableContent"><?php echo $item['customer_id']; ?></td>
                      <td class="dataTableContent text-center"><?php echo $customer->fields['customers_firstname'] . ' ' . $customer->fields['customers_lastname']; ?></td>
                      <td class="dataTableContent text-center"><?php echo $item['redeem_ip']; ?></td>
                      <td class="dataTableContent text-center"><?php echo zen_date_short($item['redeem_date']); ?></td>
                      <td class="dataTableContent text-right"><?php echo $item['order_id']; ?></td>
                      <td class="dataTableContent text-right">
                        <?php
                        if ((isset($cInfo)) && ($item['unique_id'] == $cInfo->unique_id)) {
                          echo zen_icon('caret-right', '', '2x', true);
                        } else {
                          echo '<a href="' . zen_href_link(FILENAME_COUPON_ADMIN, 'reports_page=' . $_GET['reports_page'] . '&cid=' . $item['coupon_id']) . '">' . zen_icon('circle-info', '', '2x', true, false) . '</a>';
                        }
                        ?>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
              <table class="table">
                <tr>
                  <td><?php echo $cc_split->display_count($cc_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_DISCOUNT_COUPONS_REPORTS, $_GET['reports_page'], TEXT_DISPLAY_NUMBER_OF_COUPONS); ?></td>
                  <td class="text-right"><?php echo $cc_split->display_links($cc_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_DISCOUNT_COUPONS_REPORTS, MAX_DISPLAY_PAGE_LINKS, $_GET['reports_page'], 'action=voucherreport&cid=' . $cInfo->coupon_id, 'reports_page'); ?></td>
                </tr>
                <tr>
                  <td class="text-right" colspan="2"><a href="<?php echo zen_href_link(FILENAME_COUPON_ADMIN, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'cid=' . (!empty($cInfo->coupon_id) ? $cInfo->coupon_id : $_GET['cid']) . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '')); ?>" class="btn btn-default" role="button"><?php echo IMAGE_BACK; ?></a></td>
                </tr>
              </table>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">
              <?php
              $heading = [];
              $contents = [];
              $coupon_desc = $db->Execute("SELECT coupon_name
                                           FROM " . TABLE_COUPONS_DESCRIPTION . "
                                           WHERE coupon_id = " . (int)$_GET['cid'] . "
                                           AND language_id = " . (int)$_SESSION['languages_id']);
              $count_customers = $db->Execute("SELECT * FROM " . TABLE_COUPON_REDEEM_TRACK . "
                                               WHERE coupon_id = " . (int)$_GET['cid'] . "
                                               AND customer_id = " . (int)$cInfo->customer_id);

              $heading[] = array('text' => '<h4>[' . $_GET['cid'] . ']' . COUPON_NAME . ' ' . $coupon_desc->fields['coupon_name'] . '</h4>');
              $contents[] = array('text' => '<b>' . TEXT_REDEMPTIONS . '</b>');
              $contents[] = array('text' => TEXT_REDEMPTIONS_TOTAL . ' = ' . $cc_query_numrows);
              $contents[] = array('text' => TEXT_REDEMPTIONS_CUSTOMER . ' = ' . $count_customers->RecordCount());

              $box = new box();
              echo $box->infoBox($heading, $contents);
              ?>
            </div>
            <?php
            break;
// base code - create report on matching basecode
          case 'voucherreportduplicates':
            ?>
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
              <table class="table table-hover table-striped">
                <thead>
                  <tr class="dataTableHeadingRow">
                    <th class="dataTableHeadingContent"><?php echo CUSTOMER_ID; ?></th>
                    <th class="dataTableHeadingContent text-center"><?php echo CUSTOMER_NAME; ?></th>
                    <th class="dataTableHeadingContent text-center"><?php echo IP_ADDRESS; ?></th>
                    <th class="dataTableHeadingContent"><?php echo COUPON_CODE . ' - ' . $_GET['codebase']; ?></th>
                    <th class="dataTableHeadingContent text-center"><?php echo REDEEM_DATE; ?></th>
                    <th class="dataTableHeadingContent text-right"><?php echo REDEEM_ORDER_ID; ?></th>
                    <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $cc_previous_cid = $_GET['cid'];
                  $cc_query_raw = "SELECT crt.*, c.coupon_code
                                   FROM " . TABLE_COUPON_REDEEM_TRACK . " crt,
                                        " . TABLE_COUPONS . " c
                                   WHERE crt.coupon_id IN (SELECT coupon_id
                                                           FROM " . TABLE_COUPONS . "
                                                           WHERE coupon_code LIKE '" . $_GET['codebase'] . "%'" . ")
                                   AND crt.coupon_id = c.coupon_id";
                  $cc_split = new splitPageResults($_GET['reports_page'], MAX_DISPLAY_SEARCH_RESULTS_DISCOUNT_COUPONS_REPORTS, $cc_query_raw, $cc_query_numrows);
                  $cc_list = $db->Execute($cc_query_raw);

                  foreach ($cc_list as $item) {
                    if (empty($_GET['uid']) || ($_GET['uid'] === $item['unique_id'] && !isset($cInfo))) {
                        $cInfo = new objectInfo($item);
                    }
                    if ((isset($cInfo)) && ($item['unique_id'] == $cInfo->unique_id)) {
                      ?>
                      <tr class="dataTableRowSelected" onclick="document.location.href = '<?php echo zen_href_link(FILENAME_COUPON_ADMIN, zen_get_all_get_params(array('cid', 'action', 'uid')) . 'cid=' . $cInfo->coupon_id . '&action=voucherreport&uid=' . $cInfo->unique_id); ?>'">
                      <?php } else { ?>
                      <tr class="dataTableRow" onclick="document.location.href = '<?php echo zen_href_link(FILENAME_COUPON_ADMIN, zen_get_all_get_params(array('cid', 'action', 'uid')) . 'cid=' . $item['coupon_id'] . '&action=voucherreport&uid=' . $item['unique_id']); ?>'">
                        <?php
                      }
                      $customer = $db->Execute("SELECT customers_firstname, customers_lastname
                                                FROM " . TABLE_CUSTOMERS . "
                                                WHERE customers_id = " . (int)$item['customer_id']);
                      ?>
                      <td class="dataTableContent"><?php echo $item['customer_id']; ?></td>
                      <td class="dataTableContent text-center"><?php echo $customer->fields['customers_firstname'] . ' ' . $customer->fields['customers_lastname']; ?></td>
                      <td class="dataTableContent text-center"><?php echo $item['redeem_ip']; ?></td>
                      <td class="dataTableContent"><?php echo $item['coupon_code']; ?></td>
                      <td class="dataTableContent text-center"><?php echo zen_date_short($item['redeem_date']); ?></td>
                      <td class="dataTableContent text-right"><?php echo $item['order_id']; ?></td>
                      <td class="dataTableContent text-right">
                        <?php
                        if ((isset($cInfo)) && ($item['unique_id'] == $cInfo->unique_id)) {
                          echo '<i class="fa-solid fa-caret-right fa-fw fa-2x align-middle"></i>';
                        } else {
                          echo '<a href="' . zen_href_link(FILENAME_COUPON_ADMIN, 'reports_page=' . $_GET['reports_page'] . '&cid=' . $item['coupon_id']) . '"><i class="fa-solid fa-circle-info fa-fw fa-2x align-middle"></i></a>';
                        }
                        ?>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
              <table class="table">
                <tr>
                  <td><?php echo $cc_split->display_count($cc_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_DISCOUNT_COUPONS_REPORTS, $_GET['reports_page'], TEXT_DISPLAY_NUMBER_OF_COUPONS); ?></td>
                  <td class="text-right"><?php echo $cc_split->display_links($cc_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_DISCOUNT_COUPONS_REPORTS, MAX_DISPLAY_PAGE_LINKS, $_GET['reports_page'], 'action=voucherreport&cid=' . $cInfo->coupon_id, 'reports_page'); ?></td>
                </tr>
                <tr>
                  <td class="text-right" colspan="2"><a href="<?php echo zen_href_link(FILENAME_COUPON_ADMIN, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'cid=' . $cc_previous_cid . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '')); ?>" class="btn btn-default" role="button"><?php echo IMAGE_BACK; ?></a></td>
                </tr>
              </table>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">
              <?php
              $heading = [];
              $contents = [];
              $coupon_desc = $db->Execute("SELECT coupon_name
                                           FROM " . TABLE_COUPONS_DESCRIPTION . "
                                           WHERE coupon_id = " . (int)$_GET['cid'] . "
                                           AND language_id = " . (int)$_SESSION['languages_id']);
              $count_customers = $db->Execute("SELECT * FROM " . TABLE_COUPON_REDEEM_TRACK . "
                                               WHERE coupon_id = " . (int)$_GET['cid'] . "
                                               AND customer_id = " . (int)$cInfo->customer_id);

              $heading[] = array('text' => '<h4>[' . $_GET['cid'] . ']' . COUPON_NAME . ' ' . $coupon_desc->fields['coupon_name'] . '</h4>');
              $contents[] = array('text' => '<b>' . TEXT_REDEMPTIONS . '</b>');
              $contents[] = array('text' => TEXT_REDEMPTIONS_TOTAL . ' = ' . $cc_query_numrows);
              $contents[] = array('text' => TEXT_REDEMPTIONS_CUSTOMER . ' = ' . $count_customers->RecordCount());

              $box = new box();
              echo $box->infoBox($heading, $contents);
              ?>
            </div>
            <?php
            break;

          case 'preview_email':
            $coupon_result = $db->Execute("SELECT coupon_code
                                           FROM " . TABLE_COUPONS . "
                                           WHERE coupon_id = " . (int)$_GET['cid']);

            $audience_select = get_audience_sql_query($_POST['customers_email_address']);
            $mail_sent_to = $audience_select['query_name'];
            echo zen_draw_form('mail', FILENAME_COUPON_ADMIN, 'action=send_email_to_user&cid=' . $_GET['cid']);
            ?>
            <table class="table">
              <tr>
                <td class="text-right col-sm-3"><b><?php echo TEXT_CUSTOMER; ?></b></td>
                <td><?php echo $mail_sent_to; ?></td>
              </tr>
              <tr>
                <td class="text-right"><b><?php echo TEXT_COUPON; ?></b></td>
                <td><?php echo zen_db_prepare_input($_POST['coupon_name']); ?></td>
              </tr>
              <tr>
                <td class="text-right"><b><?php echo TEXT_FROM; ?></b></td>
                <td><?php echo htmlspecialchars(stripslashes($_POST['from']), ENT_COMPAT, CHARSET, TRUE); ?></td>
              </tr>
              <tr>
                <td class="text-right"><b><?php echo TEXT_SUBJECT; ?></b></td>
                <td><?php echo htmlspecialchars(stripslashes($_POST['subject']), ENT_COMPAT, CHARSET, TRUE); ?></td>
              </tr>
              <?php if (EMAIL_USE_HTML == 'true') { ?>
                <tr>
                  <td class="text-right"><hr><b><?php echo TEXT_RICH_TEXT_MESSAGE; ?></b></td>
                  <td><?php echo stripslashes($_POST['message_html']); ?></td>
                </tr>
              <?php } ?>
              <tr>
                <td class="text-right"><b><?php echo TEXT_MESSAGE; ?></b></td>
                <td class="tt"><?php echo nl2br(htmlspecialchars(stripslashes($_POST['message']), ENT_COMPAT, CHARSET, TRUE)); ?></td>
              </tr>
              <tr>
                <td>
                  <?php
                  /* Re-Post all POST'ed variables */
                  foreach ($_POST as $key => $value) {
                    if (!is_array($_POST[$key])) {
                      echo zen_draw_hidden_field($key, htmlspecialchars(stripslashes($value), ENT_COMPAT, CHARSET, TRUE));
                    }
                  }
                  ?>
                </td>
                <td>
                  <button type="submit" class="btn btn-primary"><?php echo IMAGE_SEND_EMAIL; ?></button>&nbsp;<a href="<?php echo zen_href_link(FILENAME_COUPON_ADMIN, 'cid=' . $_GET['cid'] . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')); ?>" class="btn btn-default" role="button"><?php echo TEXT_CANCEL; ?></a>
                </td>
              </tr>
            </table>
            <?php echo '</form>'; ?>
            <?php
            break;
          case 'email':
            $coupon_result = $db->Execute("SELECT c.coupon_code, cd.coupon_name
                                           FROM " . TABLE_COUPONS . " c
                                           LEFT JOIN " . TABLE_COUPONS_DESCRIPTION . " cd ON cd.coupon_id = c.coupon_id
                                             AND cd.language_id = " . (int)$_SESSION['languages_id'] . "
                                           WHERE c.coupon_id = " . (int)$_GET['cid']);
            echo zen_draw_form('mail', FILENAME_COUPON_ADMIN, 'action=preview_email&cid=' . (int)$_GET['cid'], 'post', 'class="form-horizontal"');
            ?>
            <div class="form-group">
              <?php echo zen_draw_label(TEXT_COUPON, 'coupon_name', 'class="control-label col-sm-3"'); ?>
              <div class="col-sm-9 col-md-6"><?php echo zen_draw_input_field('coupon_name', $coupon_result->fields['coupon_name'], 'class="form-control" id="coupon_name" readonly'); ?></div>
            </div>
            <?php $customers = get_audiences_list('email'); ?>
            <div class="form-group">
              <?php echo zen_draw_label(TEXT_CUSTOMER, 'customers_email_address', 'class="control-label col-sm-3"'); ?>
              <div class="col-sm-9 col-md-6"><?php echo zen_draw_pull_down_menu('customers_email_address', $customers, (isset($_GET['customer']) ? $_GET['customer'] : ''), 'class="form-control" id="customers_email_address"', true); ?></div>
            </div>
            <div class="form-group">
              <?php echo zen_draw_label(TEXT_FROM, 'from', 'class="control-label col-sm-3"'); ?>
              <div class="col-sm-9 col-md-6"><?php echo zen_draw_input_field('from', EMAIL_FROM, 'size="50" class="form-control" id="from"'); ?></div>
            </div>
            <?php
            /*
              <div class="form-group">
              <?php echo zen_draw_label(TEXT_RESTRICT, 'customers_restrict', 'class="control-label col-sm-3"'); ?>
              <div class="col-sm-9 col-md-6"><?php echo zen_draw_checkbox_field('customers_restrict', $customers_restrict, 'class="form-control" id="customers_restrict"');?></div>
              </div>
             */
            ?>
            <div class="form-group">
              <?php echo zen_draw_label(TEXT_SUBJECT, 'subject', 'class="control-label col-sm-3"'); ?>
              <div class="col-sm-9 col-md-6">
                <?php echo zen_draw_input_field('subject', '', 'size="50" class="form-control" id="subject"', true); ?>
              </div>
            </div>
            <?php if (EMAIL_USE_HTML == 'true') { ?>
              <div class="form-group">
                <?php echo zen_draw_label(TEXT_RICH_TEXT_MESSAGE, 'message_html', 'class="control-label col-sm-3"'); ?>
                <div class="col-sm-9 col-md-6"><?php echo zen_draw_textarea_field('message_html', 'soft', '', '25', htmlspecialchars(empty($_POST['message_html']) ? TEXT_COUPON_ANNOUNCE : stripslashes($_POST['message_html']), ENT_COMPAT, CHARSET, TRUE), 'id="message_html" class="editorHook form-control" id="message_html"'); ?></div>
              </div>
            <?php } ?>
            <div class="form-group">
              <?php echo zen_draw_label(TEXT_MESSAGE, 'message', 'class="control-label col-sm-3"'); ?>
              <div class="col-sm-9 col-md-6"><?php echo zen_draw_textarea_field('message', 'soft', '60', '15', htmlspecialchars(strip_tags((!isset($_POST['message_html']) || $_POST['message_html'] == '') ? TEXT_COUPON_ANNOUNCE : stripslashes($_POST['message_html'])), ENT_COMPAT, CHARSET, TRUE), 'class="noEditor form-control" id="message"'); ?></div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-3 col-sm-9 col-md-6">
                <button type="submit" class="btn btn-primary"><?php echo IMAGE_PREVIEW; ?></button>&nbsp;<a href="<?php echo zen_href_link(FILENAME_COUPON_ADMIN, 'cid=' . $_GET['cid'] . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')); ?>" class="btn btn-default" role="button"><?php echo TEXT_CANCEL; ?></a>
              </div>
            </div>
            <?php echo '</form>'; ?>
            <?php
            break;
          case 'update_preview':
            $invalid_message = null; // Control whether we allow submitting the new values
            echo zen_draw_form('coupon', FILENAME_COUPON_ADMIN, 'action=update_confirm&oldaction=' . $_GET['oldaction'] . '&cid=' . $_GET['cid'] . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''));
            ?>
            <table class="table">
              <tr>
                <td class="main col-sm-3"><?php echo COUPON_ZONE_RESTRICTION; ?></td>
                <td class="main"><?php echo zen_get_geo_zone_name($_POST['coupon_zone_restriction']); ?>
              </tr>
              <tr>
                <td class="main"><?php echo COUPON_ORDER_LIMIT; ?></td>
                <td><?php echo zen_output_string_protected($_POST['coupon_order_limit']); ?></td>
              </tr>
              <?php
              for ($i = 0, $n = count($languages); $i < $n; $i++) {
                $language_id = $languages[$i]['id'];
                ?>
                <tr>
                  <td><?php echo COUPON_NAME; ?></td>
                  <td><?php echo zen_db_prepare_input($_POST['coupon_name'][$language_id]); ?></td>
                </tr>
                <?php
              }
              ?>
              <?php
              for ($i = 0, $n = count($languages); $i < $n; $i++) {
                $language_id = $languages[$i]['id'];
                ?>
                <tr>
                  <td><?php echo COUPON_DESC; ?></td>
                  <td><?php echo zen_db_prepare_input($_POST['coupon_desc'][$language_id]); ?></td>
                </tr>
                <?php
              }
              ?>
              <tr>
                <td><?php echo COUPON_AMOUNT; ?></td>
                <td><?php echo zen_db_prepare_input($_POST['coupon_amount']) . ' ' . ((int)$_POST['coupon_product_count'] == 0 ? TEXT_COUPON_PRODUCT_COUNT_PER_ORDER : TEXT_COUPON_PRODUCT_COUNT_PER_PRODUCT); ?></td>
              </tr>
              <tr>
                <td><?php echo COUPON_MIN_ORDER; ?></td>
                <td><?php echo zen_db_prepare_input($_POST['coupon_min_order']); ?></td>
              </tr>
              <tr>
                <td class="main"><?php echo COUPON_TOTAL; ?></td>
                <td>
                  <?php echo ((int)$_POST['coupon_calc_base'] == 0 ? TEXT_COUPON_TOTAL_PRODUCTS . TEXT_COUPON_TOTAL_PRODUCTS_BASED : TEXT_COUPON_TOTAL_ORDER . TEXT_COUPON_TOTAL_ORDER_BASED); ?>
                </td>
              </tr>
              <tr>
                <td><?php echo COUPON_FREE_SHIP; ?></td>
                <td><?php echo (!empty($_POST['coupon_free_ship']) ? TEXT_FREE_SHIPPING : TEXT_NO_FREE_SHIPPING); ?></td>
              </tr>
              <tr>
                <td><?php echo COUPON_IS_VALID_FOR_SALES; ?></td>
                <td><?php echo (!empty($_POST['coupon_is_valid_for_sales']) ? TEXT_COUPON_IS_VALID_FOR_SALES : TEXT_NO_COUPON_IS_VALID_FOR_SALES); ?></td>
              </tr>
              <tr>
                <td><?php echo COUPON_CODE; ?></td>
                <td><?php echo $coupon_code; ?></td>
              </tr>
              <tr>
                <td><?php echo COUPON_USES_COUPON; ?></td>
                <td><?php echo $_POST['coupon_uses_coupon']; ?></td>
              </tr>
              <tr>
                <td><?php echo COUPON_USES_USER; ?></td>
                <td><?php echo $_POST['coupon_uses_user']; ?></td>
              </tr>
              <tr>
                <td><?php echo COUPON_REFERRER; ?></td>
                <td><?php
                    // Referrers inputs may be empty, sanitise here
                    $trimmed_referrers = array_map(static fn($referrer) => trim($referrer), $_POST['coupon_referrer'] ?? []);
                    $referrers = array_filter($trimmed_referrers, fn($referrer) => !empty(trim($referrer)));
                    // Validate that none clash with existing coupons, excluding ourself
                    foreach ($referrers as $referrer) {
                        $already_assigned = CouponValidation::referrer_already_assigned($referrer, $_GET['cid']);
                        if (!empty($already_assigned)) {
                            $invalid_message = sprintf(
                                COUPON_REFERRER_EXISTS,
                                $already_assigned['coupon_code'],
                                $already_assigned['coupon_id'],
                                $referrer
                            );
                            break;
                        }
                    }
                    $referrers = implode(',', $referrers);
                    echo $referrers ?: 'none'; ?></td>
              </tr>
              <tr>
                <td><?php echo COUPON_STARTDATE; ?></td>
                <?php $start_date = date(DATE_FORMAT, mktime(0, 0, 0, (int)$_POST['coupon_startdate_month'], (int)$_POST['coupon_startdate_day'], (int)$_POST['coupon_startdate_year'])); ?>
                <td><?php echo $start_date; ?></td>
              </tr>
              <tr>
                <td><?php echo COUPON_FINISHDATE; ?></td>
                <?php $finish_date = date(DATE_FORMAT, mktime(0, 0, 0, (int)$_POST['coupon_finishdate_month'], (int)$_POST['coupon_finishdate_day'], (int)$_POST['coupon_finishdate_year'])); ?>
                <td><?php echo $finish_date; ?></td>
              </tr>
              <?php
              for ($i = 0, $n = count($languages); $i < $n; $i++) {
                $language_id = $languages[$i]['id'];
                echo zen_draw_hidden_field('coupon_name[' . $languages[$i]['id'] . ']', stripslashes($_POST['coupon_name'][$language_id]));
                echo zen_draw_hidden_field('coupon_desc[' . $languages[$i]['id'] . ']', stripslashes($_POST['coupon_desc'][$language_id]));
              }
              echo zen_draw_hidden_field('coupon_amount', $_POST['coupon_amount']);
              echo zen_draw_hidden_field('coupon_product_count', (int)$_POST['coupon_product_count']);
              echo zen_draw_hidden_field('coupon_min_order', $_POST['coupon_min_order']);
              echo zen_draw_hidden_field('coupon_free_ship', (!empty($_POST['coupon_free_ship']) ? $_POST['coupon_free_ship'] : ''));
              $c_code = !empty($_POST['coupon_code']) ? $_POST['coupon_code'] : $coupon_code;
              echo zen_draw_hidden_field('coupon_code', stripslashes($c_code));
              echo zen_draw_hidden_field('coupon_uses_coupon', $_POST['coupon_uses_coupon']);
              echo zen_draw_hidden_field('coupon_uses_user', $_POST['coupon_uses_user']);
              echo zen_draw_hidden_field('coupon_referrer', $referrers);
              echo zen_draw_hidden_field('coupon_products', (!empty($_POST['coupon_products']) ? $_POST['coupon_products'] : ''));
              echo zen_draw_hidden_field('coupon_categories', (!empty($_POST['coupon_categories']) ? $_POST['coupon_categories'] : ''));
              echo zen_draw_hidden_field('coupon_startdate', date('Y-m-d', mktime(0, 0, 0, (int)$_POST['coupon_startdate_month'], (int)$_POST['coupon_startdate_day'], (int)$_POST['coupon_startdate_year'])));
              echo zen_draw_hidden_field('coupon_finishdate', date('Y-m-d', mktime(0, 0, 0, (int)$_POST['coupon_finishdate_month'], (int)$_POST['coupon_finishdate_day'], (int)$_POST['coupon_finishdate_year'])));
              echo zen_draw_hidden_field('coupon_zone_restriction', $_POST['coupon_zone_restriction']);
              echo zen_draw_hidden_field('coupon_order_limit', $_POST['coupon_order_limit']);
              echo zen_draw_hidden_field('coupon_calc_base', (int)$_POST['coupon_calc_base']);
              echo zen_draw_hidden_field('coupon_is_valid_for_sales', (int)$_POST['coupon_is_valid_for_sales']);
              ?>
              <tr>
                <td class="text-right" colspan=2>
                  <?php
                  if (!empty($invalid_message)) {
                    echo "<span class='errorText'>" . $invalid_message . '</span>';
                  }
                  ?>
                  <button type="submit" class="btn btn-primary" <?php echo empty($invalid_message) ? '' : 'disabled' ?>><?php echo COUPON_BUTTON_CONFIRM; ?></button>&nbsp;<a href="<?php echo zen_href_link(FILENAME_COUPON_ADMIN, 'cid=' . $_GET['cid'] . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')); ?>" class="btn btn-default" role="button"><?php echo TEXT_CANCEL; ?></a>
                </td>
                <td></td>
              </tr>
            </table>
            <?php echo '</form>'; ?>
            <?php
            break;
          case 'voucheredit':
            for ($i = 0, $n = count($languages); $i < $n; $i++) {
              $language_id = $languages[$i]['id'];
              $coupon = $db->Execute("SELECT coupon_name,coupon_description
                                      FROM " . TABLE_COUPONS_DESCRIPTION . "
                                      WHERE coupon_id = " . (int)$_GET['cid'] . "
                                      AND language_id = " . (int)$language_id);

              $coupon_name[$language_id] = $coupon->fields['coupon_name'];
              $coupon_desc[$language_id] = $coupon->fields['coupon_description'];
            }

            $coupon = $db->Execute("SELECT *
                                    FROM " . TABLE_COUPONS . "
                                    WHERE coupon_id = " . (int)$_GET['cid']);

            $coupon_amount = $coupon->fields['coupon_amount'];
            if ($coupon->fields['coupon_type'] == 'P' || $coupon->fields['coupon_type'] == 'E') {
              $coupon_amount .= '%';
            }
            // free shipping on free shipping only 'S' or percentage off and free shipping 'E' or amount off and free shipping 'O'
            $coupon_free_ship = ($coupon->fields['coupon_type'] == 'S' || $coupon->fields['coupon_type'] == 'O' || $coupon->fields['coupon_type'] == 'E' ? true : false);
            $coupon_min_order = $coupon->fields['coupon_minimum_order'];
            $coupon_code = $coupon->fields['coupon_code'];
            $coupon_uses_coupon = $coupon->fields['uses_per_coupon'];
            $coupon_uses_user = $coupon->fields['uses_per_user'];
            $coupon_startdate = $coupon->fields['coupon_start_date'];
            $coupon_finishdate = $coupon->fields['coupon_expire_date'];
            $coupon_zone_restriction = $coupon->fields['coupon_zone_restriction'];
            $coupon_calc_base = $coupon->fields['coupon_calc_base'];
            $coupon_order_limit = $coupon->fields['coupon_order_limit'];
            $coupon_is_valid_for_sales = $coupon->fields['coupon_is_valid_for_sales'];
            $coupon_product_count = $coupon->fields['coupon_product_count'];

            $results = $db->Execute("SELECT *
                                    FROM " . TABLE_COUPON_REFERRERS . "
                                    WHERE coupon_id = " . (int)$_GET['cid']);
            $coupon_referrer = '';
            foreach ($results as $result) {
                $coupon_referrer .= $result['referrer_domain'] . ',';
            }
            $coupon_referrer = trim($coupon_referrer, ',');

            case 'new':
// set some defaults
            if ($_GET['action'] != 'voucheredit' && empty($coupon_uses_user)) {
              $coupon_uses_user = 1;
            }
            if ($_GET['action'] != 'voucheredit' && empty($coupon_is_valid_for_sales)) {
              $coupon_is_valid_for_sales = 1;
            }
            echo zen_draw_form('coupon', FILENAME_COUPON_ADMIN, 'action=update&oldaction=' . $_GET['action'] . '&cid=' . $_GET['cid'] . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''), 'post', 'class="form-horizontal"');
            ?>
            <div class="form-group">
          <?php echo zen_draw_label(COUPON_NAME, 'coupon_name[' . $languages[0]['id'] . ']', 'class="control-label col-sm-3"'); ?>
              <div class="col-sm-9 col-md-6">
                <?php
                for ($i = 0, $n = count($languages); $i < $n; $i++) {
                  $language_id = $languages[$i]['id'];
                  ?>
                  <div class="input-group">
                    <span class="input-group-addon">
                      <?php echo zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']); ?>
                    </span>
                    <?php echo zen_draw_input_field('coupon_name[' . $languages[$i]['id'] . ']', (!empty($coupon_name[$language_id]) ? htmlspecialchars(stripslashes($coupon_name[$language_id]), ENT_COMPAT, CHARSET, TRUE) : ''), zen_set_field_length(TABLE_COUPONS_DESCRIPTION, 'coupon_name') . ' id="coupon_name[' . $languages[$i]['id'] . ']" class="form-control"'); ?>
                    <?php if ($i == 0) { ?>
                      <span class="input-group-addon">
                        <i class="fa-solid fa-circle-info fa-lg" data-toggle="tooltip" title="<?php echo COUPON_NAME_HELP; ?>"></i>
                      </span>
                    <?php } ?>
                  </div>
                  <br>
                <?php } ?>
              </div>
            </div>
            <div class="form-group">
            <?php echo zen_draw_label(COUPON_DESC, 'coupon_desc[' . $languages[0]['id'] . ']', 'class="control-label col-sm-3"'); ?>
              <div class="col-sm-9 col-md-6">
                <?php
                for ($i = 0, $n = count($languages); $i < $n; $i++) {
                  $language_id = $languages[$i]['id'];
                  ?>
                  <div class="input-group">
                    <span class="input-group-addon">
                      <?php echo zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']); ?>
                    </span>
                    <?php echo zen_draw_textarea_field('coupon_desc[' . $languages[$i]['id'] . ']', 'hard', '24', '8', (!empty($coupon_desc[$language_id]) ? htmlspecialchars(stripslashes($coupon_desc[$language_id]), ENT_COMPAT, CHARSET, TRUE) : ''), 'id="coupon_desc[' . $languages[$i]['id'] . ']" class="editorHook form-control"'); ?>
                    <?php if ($i == 0) { ?>
                      <span class="input-group-addon">
                        <i class="fa-solid fa-circle-info fa-lg" data-toggle="tooltip" title="<?php echo COUPON_DESC_HELP; ?>"></i>
                      </span>
                    <?php } ?>
                  </div>
                  <br>
                <?php } ?>
              </div>
            </div>
            <div class="form-group">
              <?php echo zen_draw_label(COUPON_AMOUNT, 'coupon_amount', 'class="control-label col-sm-3"'); ?>
              <div class="col-sm-9 col-md-6">
                <div class="input-group">
                  <?php echo zen_draw_input_field('coupon_amount', (!empty($coupon_amount) ? $coupon_amount : 0), 'class="form-control" id="coupon_amount"'); ?>
                  <span class="input-group-addon">
                    <i class="fa-solid fa-circle-info fa-lg" data-toggle="tooltip" title="<?php echo COUPON_AMOUNT_HELP; ?>"></i>
                  </span>
                </div>
              </div>
              <div class="col-sm-offset-3 col-sm-9 col-md-6">
                <label class="radio-inline"><?php echo zen_draw_radio_field('coupon_product_count', '0', (empty($coupon_product_count))) . TEXT_COUPON_PRODUCT_COUNT_PER_ORDER; ?></label>
                <label class="radio-inline"><?php echo zen_draw_radio_field('coupon_product_count', '1', (!empty($coupon_product_count))) . TEXT_COUPON_PRODUCT_COUNT_PER_PRODUCT; ?></label>
              </div>
            </div>
            <div class="form-group">
              <?php echo zen_draw_label(COUPON_MIN_ORDER, 'coupon_min_order', 'class="control-label col-sm-3"'); ?>
              <div class="col-sm-9 col-md-6">
                <div class="input-group">
                  <?php echo zen_draw_input_field('coupon_min_order', (!empty($coupon_min_order) ? $coupon_min_order : 0), 'class="form-control" id="coupon_min_order"'); ?>
                  <span class="input-group-addon">
                    <i class="fa-solid fa-circle-info fa-lg" data-toggle="tooltip" title="<?php echo COUPON_MIN_ORDER_HELP; ?>"></i>
                  </span>
                </div>
              </div>
            </div>
            <div class="form-group">
              <p class="control-label col-sm-3"><?php echo COUPON_TOTAL; ?></p>
              <div class="col-sm-9 col-md-6">
                <div class="radio">
                  <label><?php echo zen_draw_radio_field('coupon_calc_base', '0', (empty($coupon_calc_base))) . TEXT_COUPON_TOTAL_PRODUCTS . TEXT_COUPON_TOTAL_PRODUCTS_BASED; ?></label>
                  <label><?php echo zen_draw_radio_field('coupon_calc_base', '1', (!empty($coupon_calc_base))) . TEXT_COUPON_TOTAL_ORDER . TEXT_COUPON_TOTAL_ORDER_BASED; ?></label>
                  &nbsp;<i class="fa-solid fa-circle-info fa-lg" data-toggle="tooltip" title="<?php echo COUPON_TOTAL_HELP; ?>"></i>
                </div>
              </div>
            </div>
            <div class="form-group">
              <?php echo zen_draw_label(COUPON_FREE_SHIP, 'coupon_free_ship', 'class="control-label col-sm-3"'); ?>
              <div class="col-sm-9 col-md-6">
                <label><?php echo zen_draw_checkbox_field('coupon_free_ship', '', (!empty($coupon_free_ship)), '', 'id="coupon_free_ship"'); ?></label>
                <i class="fa-solid fa-circle-info fa-lg" data-toggle="tooltip" title="<?php echo COUPON_FREE_SHIP_HELP; ?>"></i>
              </div>
            </div>
            <div class="form-group">
              <p class="control-label col-sm-3"><?php echo COUPON_IS_VALID_FOR_SALES; ?></p>
              <div class="col-sm-9 col-md-6">
                <div class="radio">
                  <label><?php echo zen_draw_radio_field('coupon_is_valid_for_sales', '1', (!empty($coupon_is_valid_for_sales))) . TEXT_COUPON_IS_VALID_FOR_SALES; ?></label>
                  <label><?php echo zen_draw_radio_field('coupon_is_valid_for_sales', '0', (empty($coupon_is_valid_for_sales))) . TEXT_NO_COUPON_IS_VALID_FOR_SALES; ?></label>
                  &nbsp;<i class="fa-solid fa-circle-info fa-lg" data-toggle="tooltip" title="<?php echo COUPON_SALE_HELP; ?>"></i>
                </div>
              </div>
            </div>
            <div class="form-group">
              <?php echo zen_draw_label(COUPON_CODE, 'coupon_code', 'class="control-label col-sm-3"'); ?>
              <div class="col-sm-9 col-md-6">
                <div class="input-group"><?php echo zen_draw_input_field('coupon_code', (!empty($coupon_code) ? htmlspecialchars($coupon_code, ENT_COMPAT, CHARSET, TRUE) : ''), 'class="form-control" id="coupon_code"'); ?>
                  <span class="input-group-addon">
                    <i class="fa-solid fa-circle-info fa-lg" data-toggle="tooltip" title="<?php echo COUPON_CODE_HELP; ?>"></i>
                  </span>
                </div>
              </div>
            </div>
            <div class="form-group">
              <?php echo zen_draw_label(COUPON_USES_COUPON, 'coupon_uses_coupon', 'class="control-label col-sm-3"'); ?>
              <div class="col-sm-9 col-md-6">
                <div class="input-group"><?php echo zen_draw_input_field('coupon_uses_coupon', (!empty($coupon_uses_coupon) && $coupon_uses_coupon >= 1 ? $coupon_uses_coupon : ''), 'class="form-control" id="coupon_uses_coupon"'); ?>
                  <span class="input-group-addon">
                    <i class="fa-solid fa-circle-info fa-lg" data-toggle="tooltip" title="<?php echo COUPON_USES_COUPON_HELP; ?>"></i>
                  </span>
                </div>
              </div>
            </div>
            <div class="form-group">
              <?php echo zen_draw_label(COUPON_USES_USER, 'coupon_uses_user', 'class="control-label col-sm-3"'); ?>
              <div class="col-sm-9 col-md-6">
                <div class="input-group"><?php echo zen_draw_input_field('coupon_uses_user', (!empty($coupon_uses_user) && $coupon_uses_user >= 1 ? $coupon_uses_user : ''), 'class="form-control" id="coupon_uses_user"'); ?>
                  <span class="input-group-addon">
                    <i class="fa-solid fa-circle-info fa-lg" data-toggle="tooltip" title="<?php echo COUPON_USES_USER_HELP; ?>"></i>
                  </span>
                </div>
              </div>
            </div>
            <div class="form-group">
              <?php echo zen_draw_label(COUPON_REFERRER . '&nbsp;<i class="fa-solid fa-circle-info fa-lg" data-toggle="tooltip" title="' . COUPON_REFERRER_HELP . '"></i>', 'coupon_referrer', 'class="control-label col-sm-3"'); ?>
              <div class="input-group col-sm-9 col-md-6" data-list="referrers">
              <?php
              // Render an input box for each referrer value. These are collected on POST and recombined
              $referrers = explode(',', $coupon_referrer ?? '');
              foreach ($referrers as $idx => $referrer) {
                $btn_cls = $idx === 0 ? 'btn-success' : 'btn-danger';
                $btn_label = $idx === 0 ? 'fa-plus' : 'fa-times';

              // NOTE: any changes to the following data-list-entry div/button HTML needs to be updated in the coupon_admin.js javascript as well:
              ?>
              <div class="col-sm-12" data-list-entry>
                <div class="input-group"><?php echo zen_draw_input_field('coupon_referrer[]', $referrer, 'class="form-control"'); ?>
                <div class="input-group-btn">
                  <button type="button" class="btn <?php echo $btn_cls ?>">
                    <i class="fa-solid <?php echo $btn_label ?>"></i>
                  </button>
                </div>
                </div>
              </div>
              <?php } ?>
              </div>
            </div>
            <?php
            if (empty($coupon_startdate)) {
              $coupon_startdate = preg_split("/[-]/", date('Y-m-d'));
            } else {
              $coupon_startdate = preg_split("/[-]/", $coupon_startdate);
            }
            if (empty($coupon_finishdate)) {
              $coupon_finishdate = preg_split("/[-]/", date('Y-m-d'));
              $coupon_finishdate[0] = $coupon_finishdate[0] + 1;
            } else {
              $coupon_finishdate = preg_split("/[-]/", $coupon_finishdate);
            }
            ?>
            <div class="form-group">
              <p class="control-label col-sm-3"><?php echo COUPON_STARTDATE; ?></p>
              <div class="col-sm-9 col-md-6">
                <div class="input-group"><?php echo zen_draw_date_selector('coupon_startdate', mktime(0, 0, 0, (int)$coupon_startdate[1], (int)$coupon_startdate[2], (int)$coupon_startdate[0])); ?>
                  <span class="input-group-addon">
                    <i class="fa-solid fa-circle-info fa-lg" data-toggle="tooltip" title="<?php echo COUPON_STARTDATE_HELP; ?>"></i>
                  </span>
                </div>
              </div>
            </div>
            <div class="form-group">
              <p class="control-label col-sm-3"><?php echo COUPON_FINISHDATE; ?></p>
              <div class="col-sm-9 col-md-6">
                <div class="input-group"><?php echo zen_draw_date_selector('coupon_finishdate', mktime(0, 0, 0, (int)$coupon_finishdate[1], (int)$coupon_finishdate[2], (int)$coupon_finishdate[0])); ?>
                  <span class="input-group-addon">
                    <i class="fa-solid fa-circle-info fa-lg" data-toggle="tooltip" title="<?php echo COUPON_FINISHDATE_HELP; ?>"></i>
                  </span>
                </div>
              </div>
            </div>
            <div class="form-group">
              <?php echo zen_draw_label(COUPON_ZONE_RESTRICTION, 'coupon_zone_restriction', 'class="control-label col-sm-3"'); ?>
              <div class="col-sm-9 col-md-6">
                <div class="input-group"><?php echo zen_geo_zones_pull_down_coupon('name="coupon_zone_restriction" class="form-control" id="coupon_zone_restriction"', (!empty($coupon_zone_restriction) ? $coupon_zone_restriction : 0)); ?>
                  <span class="input-group-addon">
                    <i class="fa-solid fa-circle-info fa-lg" data-toggle="tooltip" title="<?php echo TEXT_COUPON_ZONE_RESTRICTION; ?>"></i>
                  </span>
                </div>
              </div>
            </div>
            <div class="form-group">
              <?php echo zen_draw_label(COUPON_ORDER_LIMIT, 'coupon_order_limit', 'class="control-label col-sm-3"'); ?>
              <div class="col-sm-9 col-md-6">
                <div class="input-group"><?php echo zen_draw_input_field('coupon_order_limit', (!empty($coupon_order_limit) && $coupon_order_limit >= 1 ? $coupon_order_limit : ''), 'class="form-control" id="coupon_order_limit"'); ?>
                  <span class="input-group-addon">
                    <i class="fa-solid fa-circle-info fa-lg" data-toggle="tooltip" title="<?php echo COUPON_ORDER_LIMIT_HELP; ?>"></i>
                  </span>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-3 col-sm-9 col-md-6"><button type="submit" class="btn btn-primary"><?php echo COUPON_BUTTON_PREVIEW; ?></button>&nbsp;<a href="<?php echo zen_href_link(FILENAME_COUPON_ADMIN, 'cid=' . $_GET['cid'] . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')); ?>" class="btn btn-default" role="button"><?php echo IMAGE_CANCEL; ?></a>
              </div>
            </div>
            <?php echo '</form>'; ?>
            <?php
            break;
          default:
            ?>
            <div class="col-sm-4">
              <?php
              $status_array = [
                [
                  'id' => 'Y',
                  'text' => TEXT_COUPON_ACTIVE
                ],
                [
                  'id' => 'N',
                  'text' => TEXT_COUPON_INACTIVE
                ],
                [
                  'id' => 'A',
                  'text' => TEXT_COUPON_ALL
                ]
              ];

              $status = (isset($_GET['status']) ? substr(zen_db_prepare_input($_GET['status']), 0) : 'Y');

              echo zen_draw_form('statusForm', FILENAME_COUPON_ADMIN, '', 'get', 'class="form-horizontal"');
              ?>
              <div class="form-group">
                <?php echo zen_draw_label(HEADING_TITLE_STATUS, 'status', 'class="control-label col-sm-3"'); ?>
                <div class="col-sm-9">
                  <?php echo zen_draw_pull_down_menu('status', $status_array, $status, 'onChange="this.form.submit();" class="form-control" id="status"'); ?>
                </div>
              </div>
              <?php
              echo zen_hide_session_id();
              echo '</form>';
              ?>
            </div>
            <div class="col-sm-4">
              <?php echo zen_draw_form('set_editor_form', FILENAME_COUPON_ADMIN, '', 'get', 'class="form-horizontal"'); ?>
              <div class="form-group">
                <?php echo zen_draw_label(TEXT_EDITOR_INFO, 'reset_editor', 'class="control-label col-sm-3"'); ?>
                <div class="col-sm-9">
                  <?php echo zen_draw_pull_down_menu('reset_editor', $editors_pulldown, $current_editor_key, 'onChange="this.form.submit();" class="form-control" id="reset_editor"'); ?>
                </div>
              </div>
              <?php
              echo zen_hide_session_id();
              echo zen_draw_hidden_field('action', 'set_editor');
              echo '</form>';
              ?>
            </div>
            <div class="col-sm-4 text-right">
            <?php require DIR_WS_MODULES . 'search_box.php'; ?>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
              <table class="table table-hover table-striped">
                <thead>
                  <tr class="dataTableHeadingRow">
                    <th class="dataTableHeadingContent"><?php echo COUPON_NAME; ?></th>
                    <th class="dataTableHeadingContent text-center"><?php echo COUPON_AMOUNT; ?></th>
                    <th class="dataTableHeadingContent text-center"><?php echo COUPON_CODE; ?></th>
                    <th class="dataTableHeadingContent text-center"><?php echo COUPON_ACTIVE; ?></th>
                    <th class="dataTableHeadingContent text-center"><?php echo COUPON_START_DATE; ?></th>
                    <th class="dataTableHeadingContent text-center"><?php echo COUPON_EXPIRE_DATE; ?></th>
                    <th class="dataTableHeadingContent text-center"><?php echo COUPON_RESTRICTIONS; ?></th>
                    <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $mysqlSearch = '';
                  $mysqlActive = '';
                  if ($status !== 'A') {
                      $mysqlActive = " AND coupon_active = '" . zen_db_input($status) . "'";
                  }
                  if (!empty($inSearch)) {
                      $mysqlSearch = " AND coupon_id in ($inSearch) ";
                      $mysqlActive = '';
                  }
                  if (isset($_GET['cid']) && empty($inSearch)) {
                      $cc_query_raw = "SELECT *
                                     FROM " . TABLE_COUPONS . "
                                     WHERE coupon_id = " . (int)$_GET['cid'];
                      $cc_query = $db->Execute($cc_query_raw, 1);
                      $cInfo = new objectInfo($cc_query->fields);
                  }
                  $cc_query_raw = "SELECT *
                                     FROM " . TABLE_COUPONS . "
                                     WHERE coupon_type != 'G'" . $mysqlSearch . $mysqlActive;
                  $maxDisplaySearchResults = ((defined('MAX_DISPLAY_SEARCH_RESULTS_DISCOUNT_COUPONS') && (int)MAX_DISPLAY_SEARCH_RESULTS_DISCOUNT_COUPONS > 0) ? (int)MAX_DISPLAY_SEARCH_RESULTS_DISCOUNT_COUPONS : 20);

                  $cc_split = new splitPageResults($_GET['page'], $maxDisplaySearchResults, $cc_query_raw, $cc_query_numrows);
                  $cc_list = $db->Execute($cc_query_raw);

                  if ($cc_list->EOF && (empty($_GET['cid']) || ($_GET['cid'] == $cc_list->fields['coupon_id'])) && empty($cInfo)) {
                    $cInfo = new objectInfo($cc_list->fields);
                  }
                  foreach ($cc_list as $item) {
                    if ((empty($_GET['cid']) || ($_GET['cid'] == $item['coupon_id'])) && empty($cInfo)) {
                      $cInfo = new objectInfo($item);
                    }
                    if (isset($cInfo)) {
					    $coupon_referrer = '';
                        $sql = "SELECT referrer_domain
                                FROM " . TABLE_COUPON_REFERRERS . "
                                WHERE coupon_id = " . (int)$cInfo->coupon_id ?? 0;
                        $results = $db->Execute($sql);
                        foreach ($results as $result) {
                            $coupon_referrer .= $result['referrer_domain'] . ',';
                        }
                        $cInfo->referrer = trim($coupon_referrer, ',');
					}

                    if ((isset($cInfo)) && ($item['coupon_id'] == $cInfo->coupon_id)) {
                      ?>
                      <tr class="dataTableRowSelected" onclick="document.location.href = '<?php echo zen_href_link(FILENAME_COUPON_ADMIN, zen_get_all_get_params(array('cid', 'action')) . 'cid=' . $cInfo->coupon_id . '&action=voucheredit'); ?>'">
                      <?php } else { ?>
                      <tr class="dataTableRow" onclick="document.location.href = '<?php echo zen_href_link(FILENAME_COUPON_ADMIN, zen_get_all_get_params(array('cid', 'action')) . 'cid=' . $item['coupon_id']); ?>'">
                        <?php
                      }
                      $coupon_desc = $db->Execute("SELECT coupon_name
                                                   FROM " . TABLE_COUPONS_DESCRIPTION . "
                                                   WHERE coupon_id = " . (int)$item['coupon_id'] . "
                                                   AND language_id = " . (int)$_SESSION['languages_id']);

                      $coupon_restrictions = $db->Execute("SELECT *
                                                           FROM " . TABLE_COUPON_RESTRICT . "
                                                           WHERE coupon_id = " . (int)$item['coupon_id'], 1);
                      ?>
                      <td class="dataTableContent"><?php echo $coupon_desc->fields['coupon_name']; ?></td>
                      <td class="dataTableContent text-center">
                        <?php
                        switch ($item['coupon_type']) {
                          case ('S'): // free shipping
                            echo TEXT_FREE_SHIPPING;
                            break;
                          case ('P'): // percentage off
                            echo $item['coupon_amount'] . '%';
                            break;
                          case ('F'): // amount off
                            echo $currencies->format($item['coupon_amount']);
                            break;
                          case ('E'): // percentage off and free shipping
                            echo $item['coupon_amount'] . '%' . '<br>' . TEXT_FREE_SHIPPING;
                            break;
                          case ('O'): // amount off and free shipping
                            echo $currencies->format($item['coupon_amount']) . '<br>' . TEXT_FREE_SHIPPING;
                            break;
                          default:
                            echo '***';
                            break;
                        }
                        ?>
                      </td>
                      <td class="dataTableContent text-center"><?php echo $item['coupon_code']; ?></td>
                      <td class="dataTableContent text-center"><?php echo $item['coupon_active']; ?></td>
                      <td class="dataTableContent<?php echo (strtotime($item['coupon_start_date']) > time() ? ' coupon-future' : ''); ?> text-center"><?php echo zen_date_short($item['coupon_start_date']); ?></td>
                      <td class="dataTableContent<?php echo (strtotime($item['coupon_expire_date']) < time() ? ' coupon-expired' : ''); ?> text-center"><?php echo zen_date_short($item['coupon_expire_date']); ?></td>
                      <td class="dataTableContent text-center"><?php echo ($coupon_restrictions->RecordCount() > 0 ? '<a href="' . zen_href_link(FILENAME_COUPON_RESTRICT, 'cid=' . (int)$item['coupon_id'] . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '">' . 'Y' . '</a>' : 'N'); ?></td>
                      <td class="dataTableContent text-right">
                        <?php
                        if ((isset($cInfo)) && ($item['coupon_id'] == $cInfo->coupon_id)) {
                          echo zen_icon('caret-right', '', '2x', true);
                        } else {
                          echo '<a href="' . zen_href_link(FILENAME_COUPON_ADMIN, zen_get_all_get_params(['cid',]) . 'cid=' . $item['coupon_id']) . '">' .
                            zen_icon('circle-info', '', '2x', true, false) .
                          '</a>';
                        }
                        ?>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
              <table class="table">
                <tr>
                  <td><?php echo $cc_split->display_count($cc_query_numrows, $maxDisplaySearchResults, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_COUPONS); ?></td>
                  <td class="text-right"><?php echo $cc_split->display_links($cc_query_numrows, $maxDisplaySearchResults, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], (isset($_GET['status']) ? 'status=' . $_GET['status'] : '')); ?></td>
                </tr>
                <tr>
                  <td class="text-right" colspan="2"><a id="couponInsert" href="<?php echo zen_href_link(FILENAME_COUPON_ADMIN, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'cid=' . (int)$cInfo->coupon_id . '&action=new'); ?>" class="btn btn-primary" role="button"><?php echo IMAGE_INSERT; ?></a></td>
                </tr>
              </table>
            </div>
            <?php
            $heading = [];
            $contents = [];

            switch ($_GET['action']) {
              case 'release':
                break;
              case 'voucherdelete':
                $heading[] = array('text' => '<h4>[' . $cInfo->coupon_id . ']  ' . $cInfo->coupon_code . ($cInfo->coupon_id == '' ? ' - (' . (!empty($_GET['cid']) ? $_GET['cid'] : 0) . ')' : '') . '</h4>');
                $contents[] = array('text' => TEXT_CONFIRM_DELETE);
                $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_COUPON_ADMIN, 'action=confirmdelete&cid=' . $_GET['cid'] . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '" class="btn btn-danger" role="button">' . TEXT_DISCOUNT_COUPON_CONFIRM_DELETE . '</a>&nbsp;<a href="' . zen_href_link(FILENAME_COUPON_ADMIN, 'cid=' . $cInfo->coupon_id . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'
                );
                break;
              case 'voucherreactivate':
                $heading[] = array('text' => '<h4>[' . $cInfo->coupon_id . ']  ' . $cInfo->coupon_code . ($cInfo->coupon_id == '' ? ' - (' . (!empty($_GET['cid']) ? $_GET['cid'] : 0) . ')' : '') . '</h4>');
                $contents[] = array('text' => TEXT_CONFIRM_REACTIVATE);
                $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_COUPON_ADMIN, 'action=confirmreactivate&cid=' . $_GET['cid'] . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '" class="btn btn-primary" role="button">' . TEXT_DISCOUNT_COUPON_CONFIRM_RESTORE . '</a>&nbsp;<a href="' . zen_href_link(FILENAME_COUPON_ADMIN, 'cid=' . $cInfo->coupon_id . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'
                );
                break;
              case 'vouchercopy':
                $heading[] = array('text' => '<h4>[' . $cInfo->coupon_id . ']  ' . $cInfo->coupon_code . ($cInfo->coupon_id == '' ? ' - (' . (!empty($_GET['cid']) ? $_GET['cid'] : 0) . ')' : '') . '</h4>');
                $contents = array('form' => zen_draw_form('new_coupon', FILENAME_COUPON_ADMIN, 'action=confirmcopy' . '&cid=' . $_GET['cid'] . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''), 'post', 'enctype="multipart/form-data" class="form-horizontal"'));
                $contents[] = array('text' => zen_draw_label(TEXT_COUPON_NEW, 'coupon_copy_to', 'class="control-label"') . zen_draw_input_field('coupon_copy_to', '', 'class="form-control" id="coupon_copy_to"'));
                $contents[] = array('text' => TEXT_CONFIRM_COPY);
                $contents[] = array('align' => 'text-center', 'text' => '<button type="submit" class="btn btn-primary">' . IMAGE_SAVE . '</button>&nbsp;<a href="' . zen_href_link(FILENAME_COUPON_ADMIN, 'cid=' . $cInfo->coupon_id . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              case 'voucherduplicate':
                $heading[] = array('text' => '<h4>[' . $cInfo->coupon_id . ']  ' . $cInfo->coupon_code . ($cInfo->coupon_id == '' ? ' - (' . (!empty($_GET['cid']) ? $_GET['cid'] : 0) . ')' : '') . '</h4>');
                $contents = array('form' => zen_draw_form('duplicate_coupon', FILENAME_COUPON_ADMIN, 'action=confirmcopyduplicate' . '&cid=' . $_GET['cid'] . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''), 'post', 'enctype="multipart/form-data" class="form-horizontal"'));
                $contents[] = array('text' => TEXT_COUPON_COPY_INFO);
                $contents[] = array('text' => zen_draw_label(TEXT_COUPON_COPY_DUPLICATE, 'coupon_copy_to_dup_name', 'class="control-label"') . zen_draw_input_field('coupon_copy_to_dup_name', $cInfo->coupon_code, 'class="form-control" id="coupon_copy_to_dup_name"'));
                $contents[] = array('text' => zen_draw_label(TEXT_COUPON_COPY_DUPLICATE_CNT, 'coupon_copy_to_count', 'class="control-label"') . zen_draw_input_field('coupon_copy_to_count', '', 'class="form-control" id="coupon_copy_to_count"'));
                $contents[] = array('text' => TEXT_CONFIRM_COPY);
                $contents[] = array('align' => 'text-center', 'text' => '<button type="submit" class="btn btn-primary">' . IMAGE_SAVE . '</button>&nbsp;<a href="' . zen_href_link(FILENAME_COUPON_ADMIN, 'cid=' . $cInfo->coupon_id . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              case 'voucherduplicatedelete':
                $chk_duplicate_delete = $db->Execute("SELECT *
                                                      FROM " . TABLE_COUPONS . "
                                                      WHERE coupon_id = " . (int)$_GET['cid']);
                $heading[] = array('text' => '<h4>[' . $cInfo->coupon_id . ']  ' . $cInfo->coupon_code . ($cInfo->coupon_id == '' ? ' - (' . (!empty($_GET['cid']) ? $_GET['cid'] : 0) . ')' : '') . '</h4>');
                $contents = array('form' => zen_draw_form('duplicate_coupon_delete', FILENAME_COUPON_ADMIN, 'action=confirmdeleteduplicate' . '&cid=' . $_GET['cid'] . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''), 'post', 'enctype="multipart/form-data" class="form-horizontal"'));

                $contents[] = array('text' => sprintf(TEXT_CONFIRM_DELETE_DUPLICATE, $chk_duplicate_delete->fields['coupon_code'], $chk_duplicate_delete->fields['coupon_code']));
                $contents[] = array('text' => zen_draw_label(TEXT_COUPON_DELETE_DUPLICATE, 'coupon_delete_duplicate_code', 'class="control-label"') . zen_draw_input_field('coupon_delete_duplicate_code', $chk_duplicate_delete->fields['coupon_code'], 'class="form-control" id="coupon_copy_to_count"'));
                $contents[] = array('align' => 'text-center', 'text' => '<button type="submit" class="btn btn-danger">' . TEXT_DISCOUNT_COUPON_CONFIRM_DELETE . '</button>&nbsp;<a href="' . zen_href_link(FILENAME_COUPON_ADMIN, 'cid=' . $cInfo->coupon_id . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              default:
                if ($cc_list->RecordCount() > 0) {
                  $heading[] = array('text' => '<h4>[' . $cInfo->coupon_id . ']  ' . $cInfo->coupon_code . ($cInfo->coupon_id == '' ? ' - (' . (!empty($_GET['cid']) ? $_GET['cid'] : 0) . ')' : '') . '</h4>');
                } else {
                  $heading[] = array('text' => '<h4>' . ERROR_NO_COUPONS . '</h4>');
                }
                $amount = $cInfo->coupon_amount;
                if ($cInfo->coupon_type == 'P' || $cInfo->coupon_type == 'E') {
                  $amount .= '%';
                } else {
                  $amount = $currencies->format($amount);
                }
                if ($cInfo->coupon_type == 'S' || $cInfo->coupon_type == 'E' || $cInfo->coupon_type == 'O') {
                  $amount .= ' ' . TEXT_FREE_SHIPPING;
                }
                $prod_details = TEXT_NONE;
                $product_query = $db->Execute("SELECT *
                                               FROM " . TABLE_COUPON_RESTRICT . "
                                               WHERE coupon_id = " . (int)$cInfo->coupon_id . "
                                               AND product_id != 0");
                if ($product_query->RecordCount() > 0) {
                  $prod_details = TEXT_SEE_RESTRICT;
                }
                $cat_details = TEXT_NONE;
                $category_query = $db->Execute("SELECT *
                                                FROM " . TABLE_COUPON_RESTRICT . "
                                                WHERE coupon_id = " . (int)$cInfo->coupon_id . "
                                                AND category_id != 0");
                if ($category_query->RecordCount() > 0) {
                  $cat_details = TEXT_SEE_RESTRICT;
                }
                $coupon_name = $db->Execute("SELECT cd.coupon_name, c.coupon_type
                                             FROM " . TABLE_COUPONS_DESCRIPTION . " cd
                                             LEFT JOIN " . TABLE_COUPONS . " c ON c.coupon_id = cd.coupon_id
                                               AND cd.language_id = " . (int)$_SESSION['languages_id'] . "
                                             WHERE cd.coupon_id = " . (int)$cInfo->coupon_id);

                $uses_coupon = $cInfo->uses_per_coupon;
                $uses_user = $cInfo->uses_per_user;
                $coupon_order_limit = $cInfo->coupon_order_limit;
                $coupon_is_valid_for_sales = $cInfo->coupon_is_valid_for_sales;
                if ($uses_coupon == 0 || $uses_coupon == '') {
                  $uses_coupon = TEXT_UNLIMITED;
                }
                if ($uses_user == 0 || $uses_user == '') {
                  $uses_user = TEXT_UNLIMITED;
                }
                if ($cInfo->coupon_id != '') {
                  $contents[] = array('text' => COUPON_NAME . ':&nbsp;' . $coupon_name->fields['coupon_name']);
                  $contents[] = array('text' => COUPON_AMOUNT . ':&nbsp;' . $amount . ' ' . ($cInfo->coupon_product_count == 0 ? TEXT_COUPON_PRODUCT_COUNT_PER_ORDER : TEXT_COUPON_PRODUCT_COUNT_PER_PRODUCT) . '<br>' . ($coupon_name->fields['coupon_type'] == 'E' || $coupon_name->fields['coupon_type'] == '0' ? TEXT_FREE_SHIPPING : ''));
                  $contents[] = array('text' => COUPON_STARTDATE . ':&nbsp;' . zen_date_short($cInfo->coupon_start_date));
                  $contents[] = array('text' => COUPON_FINISHDATE . ':&nbsp;' . zen_date_short($cInfo->coupon_expire_date));
                  $contents[] = array('text' => COUPON_USES_COUPON . ':&nbsp;' . $uses_coupon);
                  $contents[] = array('text' => COUPON_USES_USER . ':&nbsp;' . $uses_user);
                  $contents[] = array('text' => COUPON_REFERRER . ':&nbsp;' . ($cInfo->referrer ?? 'none'));
                  $contents[] = array('text' => COUPON_PRODUCTS . ':&nbsp;' . $prod_details);
                  $contents[] = array('text' => COUPON_CATEGORIES . ':&nbsp;' . $cat_details);
                  $contents[] = array('text' => COUPON_MIN_ORDER . ':&nbsp;' . $currencies->format($cInfo->coupon_minimum_order));
                  $contents[] = array('text' => COUPON_TOTAL . ':&nbsp;' . ($cInfo->coupon_calc_base == 0 ? TEXT_COUPON_TOTAL_PRODUCTS : TEXT_COUPON_TOTAL_ORDER));
                  $contents[] = array('text' => DATE_CREATED . ':&nbsp;' . zen_date_short($cInfo->date_created));
                  $contents[] = array('text' => DATE_MODIFIED . ':&nbsp;' . zen_date_short($cInfo->date_modified));
                  $contents[] = array('text' => COUPON_ZONE_RESTRICTION . ':&nbsp;' . zen_get_geo_zone_name($cInfo->coupon_zone_restriction));
                  $contents[] = array('text' => COUPON_ORDER_LIMIT . ':&nbsp;' . ($coupon_order_limit > 0 ? $coupon_order_limit : TEXT_UNLIMITED));
                  $contents[] = array('text' => COUPON_IS_VALID_FOR_SALES . ':&nbsp;' . ($coupon_is_valid_for_sales == 1 ? TEXT_COUPON_IS_VALID_FOR_SALES : TEXT_NO_COUPON_IS_VALID_FOR_SALES));
                  $contents[] = array('align' => 'text-center', 'text' => ($cInfo->coupon_active != 'N' ? '<a href="' . zen_href_link(FILENAME_COUPON_ADMIN, 'action=email&cid=' . $cInfo->coupon_id) . '" class="btn btn-primary" role="button">' . TEXT_DISCOUNT_COUPON_EMAIL . '</a>' : '') . '&nbsp;<a href="' . zen_href_link(FILENAME_COUPON_ADMIN, 'action=voucheredit&cid=' . $cInfo->coupon_id . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '" class="btn btn-primary" role="button">' . TEXT_DISCOUNT_COUPON_EDIT . '</a>' . ($cInfo->coupon_active != 'N' ? '&nbsp;<a href="' . zen_href_link(FILENAME_COUPON_ADMIN, 'action=voucherdelete&cid=' . $cInfo->coupon_id . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '" class="btn btn-warning" role="button">' . TEXT_DISCOUNT_COUPON_DELETE . '</a>' : '&nbsp;<a href="' . zen_href_link(FILENAME_COUPON_ADMIN, 'action=voucherreactivate&cid=' . $cInfo->coupon_id . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '" class="btn btn-primary" role="button">' . TEXT_DISCOUNT_COUPON_RESTORE . '</a>'));
                  $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_COUPON_RESTRICT, 'cid=' . $cInfo->coupon_id . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '" class="btn btn-primary" role="button">' . TEXT_DISCOUNT_COUPON_RESTRICT . '</a>&nbsp;<a href="' . zen_href_link(FILENAME_COUPON_ADMIN, 'action=voucherreport&cid=' . $cInfo->coupon_id . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (!empty($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '" class="btn btn-primary" role="button">' . TEXT_DISCOUNT_COUPON_REPORT . '</a>&nbsp;<a href="' . zen_href_link(FILENAME_COUPON_ADMIN, 'action=vouchercopy&cid=' . $cInfo->coupon_id) . '" class="btn btn-primary" role="button">' . TEXT_DISCOUNT_COUPON_COPY . '</a>');
                  $contents[] = array('align' => 'text-center', 'text' => zen_draw_separator('pixel_black.gif', '100%', '2') . '<br><br>' . sprintf(TEXT_INFO_DUPLICATE_MANAGEMENT, $cInfo->coupon_code));
                  $contents[] = array('align' => 'text-center', 'text' => ($cInfo->coupon_active != 'N' ? '<a href="' . zen_href_link(FILENAME_COUPON_ADMIN, 'action=voucherduplicate&cid=' . $cInfo->coupon_id . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '" class="btn btn-primary" role="button">' . TEXT_DISCOUNT_COUPON_COPY_MULTIPLE . '</a>' : '') . ($cInfo->coupon_active != 'N' ? '&nbsp;<a href="' . zen_href_link(FILENAME_COUPON_ADMIN, 'action=voucherduplicatedelete&cid=' . $cInfo->coupon_id . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '" class="btn btn-warning" role="button">' . TEXT_DISCOUNT_COUPON_DELETE_MULTIPLE . '</a>' : ''));
                  $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_COUPON_ADMIN, 'action=voucherreportduplicates&cid=' . $cInfo->coupon_id . '&codebase=' . $cInfo->coupon_code . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '" class="btn btn-primary" role="button">' . TEXT_DISCOUNT_COUPON_REPORT_MULTIPLE . '</a>&nbsp;<a href="' . zen_href_link(FILENAME_COUPON_ADMIN_EXPORT, 'cid=' . $cInfo->coupon_id . '&codebase=' . $cInfo->coupon_code . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '" class="btn btn-primary" role="button">' . TEXT_DISCOUNT_COUPON_DOWNLOAD . '</a>');
                }
                break;
            }
            ?>
            <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">
              <?php
              $box = new box();
              echo $box->infoBox($heading, $contents);
              ?>
            </div>
        <?php } ?>
      </div>
      <!-- body_text_eof //-->
    </div>
    <!-- body_eof //-->
    <!-- footer //-->
    <?php require DIR_WS_INCLUDES . 'footer.php'; ?>
    <!-- footer_eof //-->

  </body>
</html>
<?php
require DIR_WS_INCLUDES . 'application_bottom.php';
