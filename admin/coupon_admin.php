<?php
/**
 * @package admin
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ajeh  Modified in v1.6.0 $
 */
  require('includes/application_top.php');
  $currencies = new currencies();

  if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
    $sql = "SELECT coupon_id, coupon_active from " . TABLE_COUPONS . " WHERE coupon_code = :couponCode:";
    $sql = $db->bindVars($sql, ':couponCode:', $_GET['search'], 'string');
    $search_query = $db->Execute($sql);
    if (!$search_query->EOF) {
      $_GET['cid'] = $search_query->fields['coupon_id'];
      $_GET['status'] = $search_query->fields['coupon_active'];
      $messageStack->add_session(SUCCESS_COUPON_FOUND . ($_GET['status'] == 'N' ? ' - ' . TEXT_COUPON_INACTIVE : ''), 'success');
      zen_redirect(zen_href_link(FILENAME_COUPON_ADMIN, 'cid=' . $_GET['cid'] . '&status=' . $_GET['status']));
    } else {
      $messageStack->add_session(ERROR_COUPON_NOT_FOUND, 'caution');
      zen_redirect(zen_href_link(FILENAME_COUPON_ADMIN));
    }
  }

  if (isset($_GET['cid'])) $_GET['cid'] = (int)$_GET['cid'];
  if (isset($_GET['status'])) $_GET['status'] = preg_replace('/[^YNA]/','',$_GET['status']);
  if (isset($_GET['codebase'])) $_GET['codebase'] = preg_replace('/[^A-Za-z0-9\-\][\^!@#$%&*)(+=}{]/', '', $_GET['codebase']);
  if (($_GET['action'] == 'send_email_to_user') && ($_POST['customers_email_address']) && (!$_POST['back_x'])) {
    $audience_select = get_audience_sql_query($_POST['customers_email_address'], 'email');
    $mail = $db->Execute($audience_select['query_string']);
    $mail_sent_to = $audience_select['query_name'];
    if ($_POST['email_to']) {
      $mail_sent_to = $_POST['email_to'];
    }

    $coupon_result = $db->Execute("select coupon_code, coupon_start_date, coupon_expire_date, coupon_total, coupon_is_valid_for_sales
                                   from " . TABLE_COUPONS . "
                                   where coupon_id = '" . $_GET['cid'] . "'");

    $coupon_name = $db->Execute("select coupon_name, coupon_description
                                 from " . TABLE_COUPONS_DESCRIPTION . "
                                 where coupon_id = '" . $_GET['cid'] . "'
                                 and language_id = '" . (int)$_SESSION['languages_id'] . "'");

    // demo active test
    if (zen_admin_demo()) {
      $_GET['action']= '';
      $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
      zen_redirect(zen_href_link(FILENAME_COUPON_ADMIN, 'mail_sent_to=' . urlencode($mail_sent_to)));
    }
    $from = zen_db_prepare_input($_POST['from']);
    $subject = zen_db_prepare_input($_POST['subject']);
    $recip_count=0;
    $text_coupon_help = sprintf(TEXT_COUPON_HELP_DATE, zen_date_short($coupon_result->fields['coupon_start_date']), zen_date_short($coupon_result->fields['coupon_expire_date']));
    $html_coupon_help = sprintf(HTML_COUPON_HELP_DATE, zen_date_short($coupon_result->fields['coupon_start_date']), zen_date_short($coupon_result->fields['coupon_expire_date']));

    while (!$mail->EOF) {
      $message = zen_db_prepare_input($_POST['message']);
      $message .= "\n\n" . TEXT_TO_REDEEM . "\n\n";
      $message .= TEXT_VOUCHER_IS . $coupon_result->fields['coupon_code'] . "\n\n";
      $message .= $text_coupon_help . "\n\n";
      if ($coupon_result->fields['coupon_is_valid_for_sales']) {
        $message .= TEXT_COUPON_IS_VALID_FOR_SALES . "\n\n";
      } else {
        $message .= TEXT_NO_COUPON_IS_VALID_FOR_SALES . "\n\n";
      }
      $message .= TEXT_REMEMBER . "\n\n";
      $message .=(!empty($coupon_name->fields['coupon_description']) ? $coupon_name->fields['coupon_description'] . "\n\n" : '');
      $message .= sprintf(TEXT_VISIT, HTTP_CATALOG_SERVER . DIR_WS_CATALOG);

      // disclaimer
      $message .= "\n-----\n" . sprintf(EMAIL_DISCLAIMER, STORE_OWNER_EMAIL_ADDRESS) . "\n\n";

      $html_msg['EMAIL_FIRST_NAME'] = $mail->fields['customers_firstname'];
      $html_msg['EMAIL_LAST_NAME']  = $mail->fields['customers_lastname'];
      $html_msg['EMAIL_MESSAGE_HTML'] = zen_db_prepare_input($_POST['message_html']);
      $html_msg['COUPON_TEXT_TO_REDEEM'] = TEXT_TO_REDEEM;
      $html_msg['COUPON_TEXT_VOUCHER_IS'] = TEXT_VOUCHER_IS;
      $html_msg['COUPON_CODE'] = $coupon_result->fields['coupon_code'] . $html_coupon_help;
      $html_msg['COUPON_DESCRIPTION'] =(!empty($coupon_name->fields['coupon_description']) ? $coupon_name->fields['coupon_description'] : '');
      $html_msg['COUPON_TEXT_REMEMBER']  = TEXT_REMEMBER;
      $html_msg['COUPON_REDEEM_STORENAME_URL'] = sprintf(TEXT_VISIT ,'<a href="'.HTTP_CATALOG_SERVER . DIR_WS_CATALOG.'">'.STORE_NAME.'</a>');

//Send the emails
      zen_mail($mail->fields['customers_firstname'] . ' ' . $mail->fields['customers_lastname'], $mail->fields['customers_email_address'], $subject , $message, '',$from, $html_msg, 'coupon');
      zen_record_admin_activity('Coupon code ' . $coupon_result->fields['coupon-code'] . ' emailed to customer ' . $mail->fields['customers_email_address'], 'info');
      $recip_count++;
      // send copy to Admin if enabled
      if (SEND_EXTRA_DISCOUNT_COUPON_ADMIN_EMAILS_TO_STATUS== '1' and SEND_EXTRA_DISCOUNT_COUPON_ADMIN_EMAILS_TO != '') {
        zen_mail('', SEND_EXTRA_DISCOUNT_COUPON_ADMIN_EMAILS_TO, SEND_EXTRA_DISCOUNT_COUPON_ADMIN_EMAILS_TO_SUBJECT . ' ' . $subject , $message, '',$from, $html_msg, 'coupon_extra');
      }
      $mail->MoveNext();
    }
    zen_redirect(zen_href_link(FILENAME_COUPON_ADMIN, 'mail_sent_to=' . urlencode($mail_sent_to) . '&recip_count='. $recip_count ));
  }

  if ( ($_GET['action'] == 'preview_email') && (!$_POST['customers_email_address']) ) {
    $_GET['action'] = 'email';
    $messageStack->add(ERROR_NO_CUSTOMER_SELECTED, 'error');
  }

  if ($_GET['mail_sent_to']) {
    $messageStack->add(sprintf(NOTICE_EMAIL_SENT_TO, $_GET['mail_sent_to']. '(' . $_GET['recip_count'] . ')'), 'success');
    $_GET['mail_sent_to'] = '';
  }

  switch ($_GET['action']) {
      case 'set_editor':
        // Reset will be done by init_html_editor.php. Now we simply redirect to refresh page properly.
        $action='';
        zen_redirect(zen_href_link(FILENAME_COUPON_ADMIN));
        break;
    case 'confirmdelete':
      // demo active test
      if (zen_admin_demo()) {
        $_GET['action']= '';
        $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
        zen_redirect(zen_href_link(FILENAME_COUPON_ADMIN));
      }

// do not allow change if set to welcome coupon
      if ($_GET['cid'] == NEW_SIGNUP_DISCOUNT_COUPON) {
        $messageStack->add_session(ERROR_DISCOUNT_COUPON_WELCOME, 'caution');
        zen_redirect(zen_href_link(FILENAME_COUPON_ADMIN, 'cid=' . $_GET['cid'] . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')));
      }

      $db->Execute("update " . TABLE_COUPONS . "
                    set coupon_active = 'N'
                    where coupon_id='".$_GET['cid']."'");
      $messageStack->add_session(SUCCESS_COUPON_DISABLED, 'success');
      zen_redirect(zen_href_link(FILENAME_COUPON_ADMIN));
      break;

    case 'confirmreactivate':
      // demo active test
      if (zen_admin_demo()) {
        $_GET['action']= '';
        $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
        zen_redirect(zen_href_link(FILENAME_COUPON_ADMIN));
      }

      $db->Execute("update " . TABLE_COUPONS . "
                    set coupon_active = 'Y'
                    where coupon_id='".$_GET['cid']."'");
      $messageStack->add_session(SUCCESS_COUPON_REACTIVATE, 'success');
      zen_redirect(zen_href_link(FILENAME_COUPON_ADMIN, 'cid=' . $_GET['cid']));
      break;

    case 'confirmdeleteduplicate':
// base code - confirm base code for duplicate codes
// do not allow change if matches welcome coupon
      $delete_duplicate_coupons_check = $db->Execute("SELECT * from " . TABLE_COUPONS . " WHERE coupon_code LIKE '" . $_POST['coupon_delete_duplicate_code'] . "%' and coupon_id = '" . NEW_SIGNUP_DISCOUNT_COUPON . "'");
      if (!$delete_duplicate_coupons_check->EOF) {
        $messageStack->add_session(ERROR_DISCOUNT_COUPON_WELCOME, 'caution');
      }
      $delete_duplicate_coupons = $db->Execute("SELECT * from " . TABLE_COUPONS . " WHERE coupon_code LIKE '" . $_POST['coupon_delete_duplicate_code'] . "%' and coupon_active = 'Y' and coupon_id !='" . NEW_SIGNUP_DISCOUNT_COUPON . "' and coupon_type != 'G'");
      while (!$delete_duplicate_coupons->EOF) {
//        echo 'Delete: ' . $delete_duplicate_coupons->fields['coupon_code'] . '<br>';
        $messageStack->add_session('Delete: ' . $delete_duplicate_coupons->fields['coupon_code'], 'caution');
        $db->Execute("UPDATE " . TABLE_COUPONS . " SET coupon_active = 'N' WHERE coupon_code = '" . $delete_duplicate_coupons->fields['coupon_code'] . "' and coupon_type != 'G'");
        $delete_duplicate_coupons->MoveNext();
      }
      zen_redirect(zen_href_link(FILENAME_COUPON_ADMIN));
      break;

    case 'confirmcopyduplicate':
// base code - create duplicate codes from base code
/*
      echo 'Build copies from coupon cid: ' . $_GET['cid'] . '<br>';
      echo 'Base name coupon_copy_to_dup_name: ' . $_POST['coupon_copy_to_dup_name'] . '<br>';
      echo 'Number to make coupon_copy_to_count: ' . $_POST['coupon_copy_to_count'] . '<br>';
      echo 'Build Copy Duplicates' . '<br>';
*/
      $zc_discount_coupons_create = (int)$_POST['coupon_copy_to_count'];
      if ($zc_discount_coupons_create < 1) {
        $messageStack->add_session(WARNING_COUPON_DUPLICATE . $_POST['coupon_copy_to_dup_name'] . ' - ' . $_POST['coupon_copy_to_count'], 'caution');
      } else {
        $check_new_coupon = $db->Execute("SELECT * from " . TABLE_COUPONS . " WHERE coupon_id = '" . $_GET['cid'] . "'");
        for ($i=1; $i<=$zc_discount_coupons_create; $i++) {
          $old_code_length = strlen($_POST['coupon_copy_to_dup_name']);
          $minimum_extra_chars = 7;
          $delta_calculation = SECURITY_CODE_LENGTH - ($old_code_length + $minimum_extra_chars);
          $new_code_length = ($delta_calculation > 0) ? $minimum_extra_chars + $delta_calculation : $minimum_extra_chars;
          $new_code = create_coupon_code($_POST['coupon_copy_to_dup_name'], $new_code_length, $_POST['coupon_copy_to_dup_name']);
          if ($new_code != '') {
          // make new coupon
            $sql_data_array = array('coupon_code' => zen_db_prepare_input($new_code),
                                    'coupon_amount' => zen_db_prepare_input($check_new_coupon->fields['coupon_amount']),
                                    'coupon_type' => zen_db_prepare_input($check_new_coupon->fields['coupon_type']),
                                    'uses_per_coupon' => zen_db_prepare_input((int)$check_new_coupon->fields['uses_per_coupon']),
                                    'uses_per_user' => zen_db_prepare_input((int)$check_new_coupon->fields['uses_per_user']),
                                    'coupon_minimum_order' => zen_db_prepare_input((float)$check_new_coupon->fields['coupon_minimum_order']),
                                    'restrict_to_products' => zen_db_prepare_input($check_new_coupon->fields['restrict_to_products']),
                                    'restrict_to_categories' => zen_db_prepare_input($check_new_coupon->fields['restrict_to_categories']),
                                    'coupon_start_date' => $check_new_coupon->fields['coupon_start_date'],
                                    'coupon_expire_date' => $check_new_coupon->fields['coupon_expire_date'],
                                    'date_created' => 'now()',
                                    'date_modified' => 'now()',
                                    'coupon_zone_restriction' => $check_new_coupon->fields['coupon_zone_restriction'],
                                    'coupon_total' => $check_new_coupon->fields['coupon_total'],
                                    'coupon_order_limit' => $check_new_coupon->fields['coupon_order_limit'],
                                    'coupon_is_valid_for_sales' => $check_new_coupon->fields['coupon_is_valid_for_sales'],
                                    'coupon_active' => 'Y'
                                    );
            zen_db_perform(TABLE_COUPONS, $sql_data_array);
            $insert_id = $db->Insert_ID();
            $cid = $insert_id;

          // make new description
            $sql = "SELECT * from " . TABLE_COUPONS_DESCRIPTION . " where coupon_id='" . $_GET['cid'] . "'";
            $new_coupon_descriptions = $db->Execute($sql);

            while (!$new_coupon_descriptions->EOF) {
              $sql_mdata_array = array('coupon_id' => zen_db_prepare_input($cid),
                                      'language_id' => zen_db_prepare_input($new_coupon_descriptions->fields['language_id']),
                                      'coupon_name' => zen_db_prepare_input($new_coupon_descriptions->fields['coupon_name']),
                                      'coupon_description' => zen_db_prepare_input($new_coupon_descriptions->fields['coupon_description'])
                                      );
              zen_db_perform(TABLE_COUPONS_DESCRIPTION, $sql_mdata_array);
              $new_coupon_descriptions->MoveNext();
            }

          // add restrictions
            $sql = "SELECT * from " . TABLE_COUPON_RESTRICT . " where coupon_id='" . $_GET['cid'] . "'";
            $copy_coupon_restrictions = $db->Execute($sql);

            while (!$copy_coupon_restrictions->EOF) {
              $sql_rdata_array = array('coupon_id' => zen_db_prepare_input($cid),
                                       'product_id' => zen_db_prepare_input($copy_coupon_restrictions->fields['product_id']),
                                       'category_id' => zen_db_prepare_input($copy_coupon_restrictions->fields['category_id']),
                                       'coupon_restrict' => zen_db_prepare_input($copy_coupon_restrictions->fields['coupon_restrict'])
                                       );
              zen_db_perform(TABLE_COUPON_RESTRICT, $sql_rdata_array);
              $copy_coupon_restrictions->MoveNext();
            }
            $success= true;
          } else {
            // cannot create code
            $messageStack->add_session(WARNING_COUPON_DUPLICATE_FAILED . $_POST['coupon_copy_to_dup_name'] . ' - ' . $_POST['coupon_copy_to_count'], 'caution');
            $success= false;
            break;
          }
        } // eof for
        if ($success) {
          $messageStack->add_session(SUCCESS_COUPON_DUPLICATE . $_POST['coupon_copy_to_dup_name'] . ' - ' . $_POST['coupon_copy_to_count'], 'success');
        }
      }
      zen_redirect(zen_href_link(FILENAME_COUPON_ADMIN, 'cid=' . $_GET['cid'] . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')));
      break;
    case 'confirmcopy':
      $coupon_copy_to = trim($_POST['coupon_copy_to']);

      // check if new coupon code exists
      $sql = "SELECT * from " . TABLE_COUPONS . " where coupon_code = :coupon_copy:";
      $sql = $db->bindVars($sql, ':coupon_copy:', $coupon_copy_to, 'string');
      $check_new_coupon = $db->Execute($sql);
      if ($check_new_coupon->RecordCount() > 0) {
        $messageStack->add_session(ERROR_DISCOUNT_COUPON_DUPLICATE . $coupon_copy_to, 'caution');
        zen_redirect(zen_href_link(FILENAME_COUPON_ADMIN, 'cid=' . $_GET['cid'] . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')));
      }

      $sql = "SELECT * from " . TABLE_COUPONS . " where coupon_id='" . $_GET['cid'] . "'";
      $check_new_coupon = $db->Execute($sql);

      // create duplicate coupon
        $sql_data_array = array('coupon_code' => zen_db_prepare_input($coupon_copy_to),
                                'coupon_amount' => zen_db_prepare_input($check_new_coupon->fields['coupon_amount']),
                                'coupon_type' => zen_db_prepare_input($check_new_coupon->fields['coupon_type']),
                                'uses_per_coupon' => zen_db_prepare_input((int)$check_new_coupon->fields['uses_per_coupon']),
                                'uses_per_user' => zen_db_prepare_input((int)$check_new_coupon->fields['uses_per_user']),
                                'coupon_minimum_order' => zen_db_prepare_input((float)$check_new_coupon->fields['coupon_minimum_order']),
                                'restrict_to_products' => zen_db_prepare_input($check_new_coupon->fields['restrict_to_products']),
                                'restrict_to_categories' => zen_db_prepare_input($check_new_coupon->fields['restrict_to_categories']),
                                'coupon_start_date' => $check_new_coupon->fields['coupon_start_date'],
                                'coupon_expire_date' => $check_new_coupon->fields['coupon_expire_date'],
                                'date_created' => 'now()',
                                'date_modified' => 'now()',
                                'coupon_zone_restriction' => $check_new_coupon->fields['coupon_zone_restriction'],
                                'coupon_total' => $check_new_coupon->fields['coupon_total'],
                                'coupon_order_limit' => $check_new_coupon->fields['coupon_order_limit'],
                                'coupon_is_valid_for_sales' => $check_new_coupon->fields['coupon_is_valid_for_sales'],
                                'coupon_active' => 'Y'
                                );

          zen_db_perform(TABLE_COUPONS, $sql_data_array);
          $insert_id = $db->Insert_ID();
          $cid = $insert_id;
//          $_GET['cid'] = $cid;

          // create duplicate coupon description
          $sql = "SELECT * from " . TABLE_COUPONS_DESCRIPTION . " where coupon_id='" . $_GET['cid'] . "'";
          $new_coupon_descriptions = $db->Execute($sql);

          while (!$new_coupon_descriptions->EOF) {
            $sql_mdata_array = array('coupon_id' => zen_db_prepare_input($cid),
                                    'language_id' => zen_db_prepare_input($new_coupon_descriptions->fields['language_id']),
                                    'coupon_name' => zen_db_prepare_input('COPY: ' . $new_coupon_descriptions->fields['coupon_name']),
                                    'coupon_description' => zen_db_prepare_input($new_coupon_descriptions->fields['coupon_description'])
                                    );
            zen_db_perform(TABLE_COUPONS_DESCRIPTION, $sql_mdata_array);
            $new_coupon_descriptions->MoveNext();
          }

      // copy restrictions
      $sql = "SELECT * from " . TABLE_COUPON_RESTRICT . " where coupon_id='" . $_GET['cid'] . "'";
      $copy_coupon_restrictions = $db->Execute($sql);

      while (!$copy_coupon_restrictions->EOF) {
        $sql_rdata_array = array('coupon_id' => zen_db_prepare_input($cid),
                                 'product_id' => zen_db_prepare_input($copy_coupon_restrictions->fields['product_id']),
                                 'category_id' => zen_db_prepare_input($copy_coupon_restrictions->fields['category_id']),
                                 'coupon_restrict' => zen_db_prepare_input($copy_coupon_restrictions->fields['coupon_restrict'])
                                 );
        zen_db_perform(TABLE_COUPON_RESTRICT, $sql_rdata_array);
        $copy_coupon_restrictions->MoveNext();
      }

      $_GET['cid'] = $cid;
      $messageStack->add_session(SUCCESS_COUPON_DUPLICATE . $coupon_copy_to, 'success');
      zen_redirect(zen_href_link(FILENAME_COUPON_ADMIN, 'action=voucheredit' . '&cid=' . $_GET['cid'] . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '')));
      break;
    case 'update':
      $update_errors = 0;
      // get all HTTP_POST_VARS and validate
      $_POST['coupon_code'] = trim($_POST['coupon_code']);
        $languages = zen_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          $language_id = $languages[$i]['id'];
          $_POST['coupon_name'][$language_id] = trim($_POST['coupon_name'][$language_id]);
          if (!$_POST['coupon_name'][$language_id]) {
            $update_errors = 1;
            $messageStack->add(ERROR_NO_COUPON_NAME . $languages[$i]['name'], 'error');
          }
          $_POST['coupon_desc'][$language_id] = trim($_POST['coupon_desc'][$language_id]);
        }
      $_POST['coupon_amount'] = trim($_POST['coupon_amount']);
      $_POST['coupon_amount'] = preg_replace('/[^0-9.%]/', '', $_POST['coupon_amount']);
      if (!$_POST['coupon_name']) {
        $update_errors = 1;
        $messageStack->add(ERROR_NO_COUPON_NAME, 'error');
      }
      if ((!$_POST['coupon_amount']) && (!$_POST['coupon_free_ship'])) {
        $update_errors = 1;
        $messageStack->add(ERROR_NO_COUPON_AMOUNT, 'error');
      }
      // no Discount Coupon code when editing
      if ($_GET['oldaction'] != 'new' && !$_POST['coupon_code']) {
        $update_errors = 1;
        $messageStack->add(ERROR_NO_COUPON_CODE, 'error');
      }
      if (!$_POST['coupon_code']) {
        $coupon_code = create_coupon_code();
      }
      if ($_POST['coupon_code']) $coupon_code = $_POST['coupon_code'];
      $sql = "select coupon_id, coupon_code
                              from " . TABLE_COUPONS . "
                              where coupon_code = :couponCode:";
      $sql = $db->bindVars($sql, ':couponCode:', $coupon_code, 'string');
      $query1 = $db->Execute($sql);
      if ($query1->RecordCount()>0 && $_POST['coupon_code'] && $_GET['oldaction'] != 'voucheredit')  {
        $update_errors = 1;
        $messageStack->add(ERROR_COUPON_EXISTS . ' - ' . $_POST['coupon_code'], 'error');
      }
      if ($update_errors == 0 && $query1->RecordCount()>0 &&  $query1->fields['coupon_id'] != $_GET['cid'])  {
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
      if ( ($_POST['back_x']) || ($_POST['back_y']) ) {
        $_GET['action'] = 'new';
      } else {
        $coupon_type = 'F'; // amount off
        if ($_POST['coupon_free_ship']) $coupon_type = 'S'; // free shipping
        if (substr($_POST['coupon_amount'], -1) == '%') $coupon_type = 'P'; // percentage off
        if ($_POST['coupon_amount'] > 0 && $_POST['coupon_free_ship']) $coupon_type = 'O';  // amount off and free shipping
        if (substr($_POST['coupon_amount'], -1) == '%' && $_POST['coupon_free_ship']) $coupon_type = 'E'; // percentage off and free shipping
        $_POST['coupon_amount'] = preg_replace('/[^0-9.]/', '', $_POST['coupon_amount']);
        $sql_data_array = array('coupon_code' => zen_db_prepare_input($_POST['coupon_code']),
                                'coupon_amount' => zen_db_prepare_input($_POST['coupon_amount']),
                                'coupon_type' => zen_db_prepare_input($coupon_type),
                                'uses_per_coupon' => zen_db_prepare_input((int)$_POST['coupon_uses_coupon']),
                                'uses_per_user' => zen_db_prepare_input((int)$_POST['coupon_uses_user']),
                                'coupon_minimum_order' => zen_db_prepare_input((float)$_POST['coupon_min_order']),
                                'restrict_to_products' => zen_db_prepare_input($_POST['coupon_products']),
                                'restrict_to_categories' => zen_db_prepare_input($_POST['coupon_categories']),
                                'coupon_start_date' => $_POST['coupon_startdate'],
                                'coupon_expire_date' => $_POST['coupon_finishdate'],
                                'date_created' => 'now()',
                                'date_modified' => 'now()',
                                'coupon_zone_restriction' => $_POST['coupon_zone_restriction'],
                                'coupon_total' => (int)$_POST['coupon_total'],
                                'coupon_order_limit' => zen_db_prepare_input((int)$_POST['coupon_order_limit']),
                                'coupon_is_valid_for_sales' => (int)$_POST['coupon_is_valid_for_sales'],
                                'coupon_active' => 'Y'
                                );

        $languages = zen_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          $language_id = $languages[$i]['id'];
          $sql_data_marray[$i] = array('coupon_name' => zen_db_prepare_input($_POST['coupon_name'][$language_id]),
                                       'coupon_description' => zen_db_prepare_input($_POST['coupon_desc'][$language_id])
                                       );
        }
        if ($_GET['oldaction']=='voucheredit') {
          zen_db_perform(TABLE_COUPONS, $sql_data_array, 'update', "coupon_id='" . $_GET['cid']."'");
          for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $language_id = $languages[$i]['id'];
            $sql_data_desc_array = array('coupon_name' => zen_db_prepare_input($_POST['coupon_name'][$language_id]),
                                         'coupon_description' => zen_db_prepare_input($_POST['coupon_desc'][$language_id])
                                         );
            zen_db_perform(TABLE_COUPONS_DESCRIPTION, $sql_data_desc_array, 'update', "coupon_id = '" . $_GET['cid'] . "' and language_id = '" . (int)$languages[$i]['id'] . "'");
          }
        } else {
          zen_db_perform(TABLE_COUPONS, $sql_data_array);
          $insert_id = $db->Insert_ID();
          $cid = $insert_id;
          $_GET['cid'] = $cid;

          for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $language_id = $languages[$i]['id'];
            $sql_data_marray[$i]['coupon_id'] = (int)$insert_id;
            $sql_data_marray[$i]['language_id'] = (int)$language_id;
            zen_db_perform(TABLE_COUPONS_DESCRIPTION, $sql_data_marray[$i]);
          }
        }
      }
      zen_redirect(zen_href_link(FILENAME_COUPON_ADMIN, 'cid=' . $_GET['cid'] . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')));
  }
require('includes/admin_html_head.php');
?>
<script type="text/javascript">
var form = "";
var submitted = false;
var error = false;
var error_message = "";

function check_select(field_name, field_default, message) {
  if (form.elements[field_name] && (form.elements[field_name].type != "hidden")) {
    var field_value = form.elements[field_name].value;

    if (field_value == field_default) {
      error_message = error_message + "* " + message + "\n";
      error = true;
    }
  }
}
function check_message(msg) {
  if (form.elements['message'] && form.elements['message_html']) {
    var field_value1 = form.elements['message'].value;
    var field_value2 = form.elements['message_html'].value;

    if ((field_value1 == '' || field_value1.length < 3) && (field_value2 == '' || field_value2.length < 3)) {
      error_message = error_message + "* " + msg + "\n";
      error = true;
    }
  }
}
function check_input(field_name, field_size, message) {
  if (form.elements[field_name] && (form.elements[field_name].type != "hidden")) {
    var field_value = form.elements[field_name].value;

    if (field_value == '' || field_value.length < field_size) {
      error_message = error_message + "* " + message + "\n";
      error = true;
    }
  }
}

function check_form(form_name) {
  if (submitted == true) {
    alert("<?php echo JS_ERROR_SUBMITTED; ?>");
    return false;
  }
  error = false;
  form = form_name;
  error_message = "<?php echo JS_ERROR; ?>";

  check_select('customers_email_address', '', "<?php echo ERROR_NO_CUSTOMER_SELECTED; ?>");
  check_message("<?php echo ENTRY_NOTHING_TO_SEND; ?>");
  check_input('subject','',"<?php echo ERROR_NO_SUBJECT; ?>");

  if (error == true) {
    alert(error_message);
    return false;
  } else {
    submitted = true;
    return true;
  }
}
</script>
<?php if ($editor_handler != '') include ($editor_handler); ?>
</head>
<body>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<!-- body_text //-->
<?php
  switch ($_GET['action']) {
  case 'voucherreport':
?>
      <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="0">
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
                <td class="dataTableHeadingContent"><?php echo CUSTOMER_ID; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo CUSTOMER_NAME; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo IP_ADDRESS; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo REDEEM_DATE; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo REDEEM_ORDER_ID; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
    $cc_query_raw = "select * from " . TABLE_COUPON_REDEEM_TRACK . " where coupon_id = '" . $_GET['cid'] . "'";
    $cc_split = new splitPageResults($_GET['reports_page'], MAX_DISPLAY_SEARCH_RESULTS_DISCOUNT_COUPONS_REPORTS, $cc_query_raw, $cc_query_numrows);
    $cc_list = $db->Execute($cc_query_raw);
    while (!$cc_list->EOF) {
      if (((!$_GET['uid']) || (@$_GET['uid'] == $cc_list->fields['unique_id'])) && (!$cInfo)) {
        $cInfo = new objectInfo($cc_list->fields);
      }
      if ( (is_object($cInfo)) && ($cc_list->fields['unique_id'] == $cInfo->unique_id) ) {
        echo '          <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'hand\'" onclick="document.location.href=\'' . zen_href_link(FILENAME_COUPON_ADMIN, zen_get_all_get_params(array('cid', 'action', 'uid')) . 'cid=' . $cInfo->coupon_id . '&action=voucherreport&uid=' . $cinfo->unique_id) . '\'">' . "\n";
      } else {
        echo '          <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'hand\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . zen_href_link(FILENAME_COUPON_ADMIN, zen_get_all_get_params(array('cid', 'action', 'uid')) . 'cid=' . $cc_list->fields['coupon_id'] . '&action=voucherreport&uid=' . $cc_list->fields['unique_id']) . '\'">' . "\n";
      }
      $customer = $db->Execute("select customers_firstname, customers_lastname
                                from " . TABLE_CUSTOMERS . "
                                where customers_id = '" . $cc_list->fields['customer_id'] . "'");

?>
                <td class="dataTableContent"><?php echo $cc_list->fields['customer_id']; ?></td>
                <td class="dataTableContent" align="center"><?php echo $customer->fields['customers_firstname'] . ' ' . $customer->fields['customers_lastname']; ?></td>
                <td class="dataTableContent" align="center"><?php echo $cc_list->fields['redeem_ip']; ?></td>
                <td class="dataTableContent" align="center"><?php echo zen_date_short($cc_list->fields['redeem_date']); ?></td>
                <td class="dataTableContent" align="right"><?php echo $cc_list->fields['order_id']; ?></td>
                <td class="dataTableContent" align="right"><?php if ( (is_object($cInfo)) && ($cc_list->fields['unique_id'] == $cInfo->unique_id) ) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . zen_href_link(FILENAME_COUPON_ADMIN, 'reports_page=' . $_GET['reports_page'] . '&cid=' . $cc_list->fields['coupon_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
      $cc_list->MoveNext();
    }
?>
          <tr>
            <td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td class="smallText">&nbsp;<?php echo $cc_split->display_count($cc_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_DISCOUNT_COUPONS_REPORTS, $_GET['reports_page'], TEXT_DISPLAY_NUMBER_OF_COUPONS); ?>&nbsp;</td>
                <td align="right" class="smallText">&nbsp;<?php echo $cc_split->display_links($cc_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_DISCOUNT_COUPONS_REPORTS, MAX_DISPLAY_PAGE_LINKS, $_GET['reports_page'], 'action=voucherreport&cid=' . $cInfo->coupon_id, 'reports_page'); ?>&nbsp;</td>
              </tr>

              <tr>
                <td align="right" colspan="2" class="smallText"><?php echo '<a href="' . zen_href_link(FILENAME_COUPON_ADMIN, 'page=' . $_GET['page'] . '&cid=' . (!empty($cInfo->coupon_id) ? $cInfo->coupon_id : $_GET['cid']) . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '')) . '">' . zen_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></td>
              </tr>
            </table></td>
          </tr>
             </table></td>
<?php
      $heading = array();
      $contents = array();
      $coupon_desc = $db->Execute("select coupon_name
                                   from " . TABLE_COUPONS_DESCRIPTION . "
                                   where coupon_id = '" . $_GET['cid'] . "'
                                   and language_id = '" . (int)$_SESSION['languages_id'] . "'");
      $count_customers = $db->Execute("select * from " . TABLE_COUPON_REDEEM_TRACK . "
                                       where coupon_id = '" . $_GET['cid'] . "'
                                       and customer_id = '" . (int)$cInfo->customer_id . "'");

      $heading[] = array('text' => '<b>[' . $_GET['cid'] . ']' . COUPON_NAME . ' ' . $coupon_desc->fields['coupon_name'] . '</b>');
      $contents[] = array('text' => '<b>' . TEXT_REDEMPTIONS . '</b>');
//      $contents[] = array('text' => TEXT_REDEMPTIONS_TOTAL . '=' . $cc_list->RecordCount());
      $contents[] = array('text' => TEXT_REDEMPTIONS_TOTAL . '=' . $cc_query_numrows);
      $contents[] = array('text' => TEXT_REDEMPTIONS_CUSTOMER . '=' . $count_customers->RecordCount());
      $contents[] = array('text' => '');
?>
    <td width="25%" valign="top">
<?php
      $box = new box;
      echo $box->infoBox($heading, $contents);
      echo '            </td>' . "\n";
?>
<?php
    break;


// base code - create report on matching basecode
  case 'voucherreportduplicates':
?>
      <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="0">
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
                <td class="dataTableHeadingContent"><?php echo CUSTOMER_ID; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo CUSTOMER_NAME; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo IP_ADDRESS; ?></td>
                <td class="dataTableHeadingContent" align="left"><?php echo COUPON_CODE . ' - ' . $_GET['codebase']; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo REDEEM_DATE; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo REDEEM_ORDER_ID; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
    $cc_previous_cid = $_GET['cid'];
    $cc_query_raw = "select crt.*, c.coupon_code from " . TABLE_COUPON_REDEEM_TRACK . " crt, " . TABLE_COUPONS . " c where crt.coupon_id IN (select coupon_id from " . TABLE_COUPONS . " WHERE coupon_code LIKE '" . $_GET['codebase'] . "%'" . ") and crt.coupon_id = c.coupon_id";
    $cc_split = new splitPageResults($_GET['reports_page'], MAX_DISPLAY_SEARCH_RESULTS_DISCOUNT_COUPONS_REPORTS, $cc_query_raw, $cc_query_numrows);
    $cc_list = $db->Execute($cc_query_raw);

    while (!$cc_list->EOF) {
      if (((!$_GET['uid']) || (@$_GET['uid'] == $cc_list->fields['unique_id'])) && (!$cInfo)) {
        $cInfo = new objectInfo($cc_list->fields);
      }
      if ( (is_object($cInfo)) && ($cc_list->fields['unique_id'] == $cInfo->unique_id) ) {
        echo '          <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'hand\'" onclick="document.location.href=\'' . zen_href_link(FILENAME_COUPON_ADMIN, zen_get_all_get_params(array('cid', 'action', 'uid')) . 'cid=' . $cInfo->coupon_id . '&action=voucherreport&uid=' . $cinfo->unique_id) . '\'">' . "\n";
      } else {
        echo '          <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'hand\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . zen_href_link(FILENAME_COUPON_ADMIN, zen_get_all_get_params(array('cid', 'action', 'uid')) . 'cid=' . $cc_list->fields['coupon_id'] . '&action=voucherreport&uid=' . $cc_list->fields['unique_id']) . '\'">' . "\n";
      }
      $customer = $db->Execute("select customers_firstname, customers_lastname
                                from " . TABLE_CUSTOMERS . "
                                where customers_id = '" . $cc_list->fields['customer_id'] . "'");

?>
                <td class="dataTableContent"><?php echo $cc_list->fields['customer_id']; ?></td>
                <td class="dataTableContent" align="center"><?php echo $customer->fields['customers_firstname'] . ' ' . $customer->fields['customers_lastname']; ?></td>
                <td class="dataTableContent" align="center"><?php echo $cc_list->fields['redeem_ip']; ?></td>
                <td class="dataTableContent" align="left"><?php echo $cc_list->fields['coupon_code']; ?></td>
                <td class="dataTableContent" align="center"><?php echo zen_date_short($cc_list->fields['redeem_date']); ?></td>
                <td class="dataTableContent" align="right"><?php echo $cc_list->fields['order_id']; ?></td>
                <td class="dataTableContent" align="right"><?php if ( (is_object($cInfo)) && ($cc_list->fields['unique_id'] == $cInfo->unique_id) ) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . zen_href_link(FILENAME_COUPON_ADMIN, 'reports_page=' . $_GET['reports_page'] . '&cid=' . $cc_list->fields['coupon_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
      $cc_list->MoveNext();
    }
?>
          <tr>
            <td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td class="smallText">&nbsp;<?php echo $cc_split->display_count($cc_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_DISCOUNT_COUPONS_REPORTS, $_GET['reports_page'], TEXT_DISPLAY_NUMBER_OF_COUPONS); ?>&nbsp;</td>
                <td align="right" class="smallText">&nbsp;<?php echo $cc_split->display_links($cc_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_DISCOUNT_COUPONS_REPORTS, MAX_DISPLAY_PAGE_LINKS, $_GET['reports_page'], 'action=voucherreport&cid=' . $cInfo->coupon_id, 'reports_page'); ?>&nbsp;</td>
              </tr>

              <tr>
                <td align="right" colspan="2" class="smallText"><?php echo '<a href="' . zen_href_link(FILENAME_COUPON_ADMIN, 'page=' . $_GET['page'] . '&cid=' . $cc_previous_cid . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '')) . '">' . zen_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></td>
              </tr>
            </table></td>
          </tr>
             </table></td>
<?php
      $heading = array();
      $contents = array();
      $coupon_desc = $db->Execute("select coupon_name
                                   from " . TABLE_COUPONS_DESCRIPTION . "
                                   where coupon_id = '" . $_GET['cid'] . "'
                                   and language_id = '" . (int)$_SESSION['languages_id'] . "'");
      $count_customers = $db->Execute("select * from " . TABLE_COUPON_REDEEM_TRACK . "
                                       where coupon_id = '" . $_GET['cid'] . "'
                                       and customer_id = '" . (int)$cInfo->customer_id . "'");

      $heading[] = array('text' => '<b>[' . $_GET['cid'] . ']' . COUPON_NAME . ' ' . $coupon_desc->fields['coupon_name'] . '</b>');
      $contents[] = array('text' => '<b>' . TEXT_REDEMPTIONS . '</b>');
//      $contents[] = array('text' => TEXT_REDEMPTIONS_TOTAL . '=' . $cc_list->RecordCount());
      $contents[] = array('text' => TEXT_REDEMPTIONS_TOTAL . '=' . $cc_query_numrows);
      $contents[] = array('text' => TEXT_REDEMPTIONS_CUSTOMER . '=' . $count_customers->RecordCount());
      $contents[] = array('text' => '');
?>
    <td width="25%" valign="top">
<?php
      $box = new box;
      echo $box->infoBox($heading, $contents);
      echo '            </td>' . "\n";
?>
<?php
    break;

  case 'preview_email':
    $coupon_result = $db->Execute("select coupon_code
                                   from " .TABLE_COUPONS . "
                                   where coupon_id = '" . $_GET['cid'] . "'");

    $coupon_name = $db->Execute("select coupon_name
                                 from " . TABLE_COUPONS_DESCRIPTION . "
                                 where coupon_id = '" . $_GET['cid'] . "'
                                 and language_id = '" . (int)$_SESSION['languages_id'] . "'");

    $audience_select = get_audience_sql_query($_POST['customers_email_address']);
    $mail_sent_to = $audience_select['query_name'];

?>
      <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr><?php echo zen_draw_form('mail', FILENAME_COUPON_ADMIN, 'action=send_email_to_user&cid=' . $_GET['cid']); ?>
            <td><table border="0" width="100%" cellpadding="0" cellspacing="2">
              <tr>
                <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td class="smallText"><b><?php echo TEXT_CUSTOMER; ?></b><br /><?php echo $mail_sent_to; ?></td>
              </tr>
              <tr>
                <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td class="smallText"><b><?php echo TEXT_COUPON; ?></b><br /><?php echo $coupon_name->fields['coupon_name']; ?></td>
              </tr>
              <tr>
                <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td class="smallText"><b><?php echo TEXT_FROM; ?></b><br /><?php echo htmlspecialchars(stripslashes($_POST['from']), ENT_COMPAT, CHARSET, TRUE); ?></td>
              </tr>
              <tr>
                <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td class="smallText"><b><?php echo TEXT_SUBJECT; ?></b><br /><?php echo htmlspecialchars(stripslashes($_POST['subject']), ENT_COMPAT, CHARSET, TRUE); ?></td>
              </tr>
              <tr>
                <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td><hr /><b><?php echo TEXT_RICH_TEXT_MESSAGE; ?></b><br /><?php echo stripslashes($_POST['message_html']); ?></td>
              </tr>
              <tr>
                <td ><hr /><b><?php echo TEXT_MESSAGE; ?></b><br /><tt><?php echo nl2br(htmlspecialchars(stripslashes($_POST['message']), ENT_COMPAT, CHARSET, TRUE)); ?></tt><hr /></td>
              </tr>
              <tr>
                <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td>
<?php
/* Re-Post all POST'ed variables */
    reset($_POST);
    while (list($key, $value) = each($_POST)) {
      if (!is_array($_POST[$key])) {
        echo zen_draw_hidden_field($key, htmlspecialchars(stripslashes($value), ENT_COMPAT, CHARSET, TRUE));
      }
    }
?>
                <table border="0" width="100%" cellpadding="0" cellspacing="2">
                  <tr>
                    <td><?php ?>&nbsp;</td>
                    <td align="right"><?php echo '<a href="' . zen_href_link(FILENAME_COUPON_ADMIN,  'cid=' . $_GET['cid'] . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a> ' . zen_image_submit('button_send_mail.gif', IMAGE_SEND_EMAIL); ?></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
          </form></tr>
<?php
    break;
  case 'email':
    $coupon_result = $db->Execute("select coupon_code
                                   from " . TABLE_COUPONS . "
                                   where coupon_id = '" . $_GET['cid'] . "'");
    $coupon_name = $db->Execute("select coupon_name
                                 from " . TABLE_COUPONS_DESCRIPTION . "
                                 where coupon_id = '" . $_GET['cid'] . "'
                                 and language_id = '" . (int)$_SESSION['languages_id'] . "'");
?>
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
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr><?php echo zen_draw_form('mail', FILENAME_COUPON_ADMIN, 'action=preview_email&cid='. $_GET['cid'],'post', 'onsubmit="return check_form(mail);"'); ?>
            <td><table border="0" width="100%" cellpadding="0" cellspacing="2">
              <tr>
                <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td class="main"><?php echo TEXT_COUPON; ?>&nbsp;&nbsp;</td>
                <td><?php echo $coupon_name->fields['coupon_name']; ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
<?php
    $customers = get_audiences_list('email');
?>
              <tr>
                <td class="main"><?php echo TEXT_CUSTOMER; ?>&nbsp;&nbsp;</td>
                <td><?php echo zen_draw_pull_down_menu('customers_email_address', $customers, $_GET['customer']);?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td class="main"><?php echo TEXT_FROM; ?>&nbsp;&nbsp;</td>
                <td><?php echo zen_draw_input_field('from', EMAIL_FROM, 'size="50"'); ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
<?php
/*
              <tr>
                <td class="main"><?php echo TEXT_RESTRICT; ?>&nbsp;&nbsp;</td>
                <td><?php echo zen_draw_checkbox_field('customers_restrict', $customers_restrict);?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
*/
?>
              <tr>
                <td class="main"><?php echo TEXT_SUBJECT; ?>&nbsp;&nbsp;</td>
                <td><?php echo zen_draw_input_field('subject', '', 'size="50"'); ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
<?php   if (EMAIL_USE_HTML == 'true') { ?>
              <tr>
                <td valign="top" class="main"><?php echo TEXT_RICH_TEXT_MESSAGE; ?></td>
                <td><?php echo zen_draw_textarea_field('message_html', 'soft', '100%', '25', htmlspecialchars(($_POST['message_html']=='') ? TEXT_COUPON_ANNOUNCE : stripslashes($_POST['message_html']), ENT_COMPAT, CHARSET, TRUE), 'id="message_html" class="editorHook"'); ?></td>
              </tr>
<?php } ?>
              <tr>
                <td valign="top" class="main"><?php echo TEXT_MESSAGE; ?>&nbsp;&nbsp;</td>
                <td><?php echo zen_draw_textarea_field('message', 'soft', '60', '15', htmlspecialchars(strip_tags(($_POST['message_html']=='') ? TEXT_COUPON_ANNOUNCE : stripslashes($_POST['message_html'])), ENT_COMPAT, CHARSET, TRUE), 'class="noEditor"'); ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td colspan="2" align="right"><?php echo '<a href="' . zen_href_link(FILENAME_COUPON_ADMIN, 'cid=' . $_GET['cid'] . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a> ' .  zen_image_submit('button_send_mail.gif', IMAGE_SEND_EMAIL); ?></td>
              </tr>
            </table></td>
          </form></tr>
  </table></td>
<?php
    break;
  case 'update_preview':
?>
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
      <td>
<?php echo zen_draw_form('coupon', FILENAME_COUPON_ADMIN, 'action=update_confirm&oldaction=' . $_GET['oldaction'] . '&cid=' . $_GET['cid'] . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')); ?>
      <table border="0" width="100%" cellspacing="0" cellpadding="6">
        <tr>
          <td align="left" class="main"><?php echo COUPON_ZONE_RESTRICTION; ?></td>
          <td align="left" class="main"><?php echo zen_get_geo_zone_name($_POST['coupon_zone_restriction']); ?>
        </tr>
        <tr>
          <td align="left" class="main"><?php echo COUPON_ORDER_LIMIT; ?></td>
          <td align="left"><?php echo zen_db_prepare_input($_POST['coupon_order_limit']); ?></td>
        </tr>

<?php
        $languages = zen_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $language_id = $languages[$i]['id'];
?>
      <tr>
        <td align="left"><?php echo COUPON_NAME; ?></td>
        <td align="left"><?php echo zen_db_prepare_input($_POST['coupon_name'][$language_id]); ?></td>
      </tr>
<?php
}
?>
<?php
        $languages = zen_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $language_id = $languages[$i]['id'];
?>
      <tr>
        <td align="left"><?php echo COUPON_DESC; ?></td>
        <td align="left"><?php echo zen_db_prepare_input($_POST['coupon_desc'][$language_id]); ?></td>
      </tr>
<?php
}
?>
      <tr>
        <td align="left"><?php echo COUPON_AMOUNT; ?></td>
        <td align="left"><?php echo zen_db_prepare_input($_POST['coupon_amount']); ?></td>
      </tr>

      <tr>
        <td align="left"><?php echo COUPON_MIN_ORDER; ?></td>
        <td align="left"><?php echo zen_db_prepare_input($_POST['coupon_min_order']); ?></td>
      </tr>
      <tr>
        <td align="left" class="main"><?php echo COUPON_TOTAL; ?></td>
        <td align="left">
          <?php echo ($_POST['coupon_total'] == 0 ? TEXT_COUPON_TOTAL_PRODUCTS . TEXT_COUPON_TOTAL_PRODUCTS_BASED : TEXT_COUPON_TOTAL_ORDER . TEXT_COUPON_TOTAL_ORDER_BASED); ?>
        </td>
      </tr>

      <tr>
        <td align="left"><?php echo COUPON_FREE_SHIP; ?></td>
<?php
    if ($_POST['coupon_free_ship']) {
?>
        <td align="left"><?php echo TEXT_FREE_SHIPPING; ?></td>
<?php
    } else {
?>
        <td align="left"><?php echo TEXT_NO_FREE_SHIPPING; ?></td>
<?php
    }
?>
      </tr>
      <tr>
        <td align="left"><?php echo COUPON_IS_VALID_FOR_SALES; ?></td>
<?php
    if ($_POST['coupon_is_valid_for_sales']) {
?>
        <td align="left"><?php echo TEXT_COUPON_IS_VALID_FOR_SALES; ?></td>
<?php
    } else {
?>
        <td align="left"><?php echo TEXT_NO_COUPON_IS_VALID_FOR_SALES; ?></td>
<?php
    }
?>
      </tr>
      <tr>
        <td align="left"><?php echo COUPON_CODE; ?></td>
<?php
    if ($_POST['coupon_code']) {
      $c_code = $_POST['coupon_code'];
    } else {
      $c_code = $coupon_code;
    }
?>
        <td align="left"><?php echo $coupon_code; ?></td>
      </tr>

      <tr>
        <td align="left"><?php echo COUPON_USES_COUPON; ?></td>
        <td align="left"><?php echo $_POST['coupon_uses_coupon']; ?></td>
      </tr>

      <tr>
        <td align="left"><?php echo COUPON_USES_USER; ?></td>
        <td align="left"><?php echo $_POST['coupon_uses_user']; ?></td>
      </tr>

      <tr>
        <td align="left"><?php echo COUPON_STARTDATE; ?></td>
<?php
    $start_date = date(DATE_FORMAT, mktime(0, 0, 0, $_POST['coupon_startdate_month'],$_POST['coupon_startdate_day'] ,$_POST['coupon_startdate_year'] ));
?>
        <td align="left"><?php echo $start_date; ?></td>
      </tr>

      <tr>
        <td align="left"><?php echo COUPON_FINISHDATE; ?></td>
<?php
    $finish_date = date(DATE_FORMAT, mktime(0, 0, 0, $_POST['coupon_finishdate_month'],$_POST['coupon_finishdate_day'] ,$_POST['coupon_finishdate_year'] ));
?>
        <td align="left"><?php echo $finish_date; ?></td>
      </tr>
<?php
        $languages = zen_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          $language_id = $languages[$i]['id'];
          echo zen_draw_hidden_field('coupon_name[' . $languages[$i]['id'] . ']', stripslashes($_POST['coupon_name'][$language_id]));
          echo zen_draw_hidden_field('coupon_desc[' . $languages[$i]['id'] . ']', stripslashes($_POST['coupon_desc'][$language_id]));
        }
        echo zen_draw_hidden_field('coupon_amount', $_POST['coupon_amount']);
        echo zen_draw_hidden_field('coupon_min_order', $_POST['coupon_min_order']);
        echo zen_draw_hidden_field('coupon_free_ship', $_POST['coupon_free_ship']);
        echo zen_draw_hidden_field('coupon_code', stripslashes($c_code));
        echo zen_draw_hidden_field('coupon_uses_coupon', $_POST['coupon_uses_coupon']);
        echo zen_draw_hidden_field('coupon_uses_user', $_POST['coupon_uses_user']);
        echo zen_draw_hidden_field('coupon_products', $_POST['coupon_products']);
        echo zen_draw_hidden_field('coupon_categories', $_POST['coupon_categories']);
        echo zen_draw_hidden_field('coupon_startdate', date('Y-m-d', mktime(0, 0, 0, $_POST['coupon_startdate_month'],$_POST['coupon_startdate_day'] ,$_POST['coupon_startdate_year'] )));
        echo zen_draw_hidden_field('coupon_finishdate', date('Y-m-d', mktime(0, 0, 0, $_POST['coupon_finishdate_month'],$_POST['coupon_finishdate_day'] ,$_POST['coupon_finishdate_year'] )));
        echo zen_draw_hidden_field('coupon_zone_restriction', $_POST['coupon_zone_restriction']);
        echo zen_draw_hidden_field('coupon_order_limit', $_POST['coupon_order_limit']);
        echo zen_draw_hidden_field('coupon_total', $_POST['coupon_total']);
        echo zen_draw_hidden_field('coupon_is_valid_for_sales', $_POST['coupon_is_valid_for_sales']);
?>
     <tr>
        <td align="left"><?php echo zen_image_submit('button_confirm.gif',COUPON_BUTTON_CONFIRM, (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')); ?></td>
        <td align="left"><?php echo zen_image_submit('button_cancel.gif',COUPON_BUTTON_CANCEL, 'name=back'); ?></td>
      </td>
      </tr>

      </td></table></form>
      </tr>

      </table></td>
<?php

    break;
  case 'voucheredit':
    $languages = zen_get_languages();
    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
      $language_id = $languages[$i]['id'];
      $coupon = $db->Execute("select coupon_name,coupon_description
                              from " . TABLE_COUPONS_DESCRIPTION . "
                              where coupon_id = '" .  $_GET['cid'] . "'
                              and language_id = '" . (int)$language_id . "'");

      $_POST['coupon_name'][$language_id] = $coupon->fields['coupon_name'];
      $_POST['coupon_desc'][$language_id] = $coupon->fields['coupon_description'];
    }

    $coupon = $db->Execute("select coupon_code, coupon_amount, coupon_type, coupon_minimum_order,
                                   coupon_start_date, coupon_expire_date, uses_per_coupon,
                                   uses_per_user, restrict_to_products, restrict_to_categories, coupon_zone_restriction, coupon_total, coupon_order_limit,
                                   coupon_is_valid_for_sales
                            from " . TABLE_COUPONS . "
                            where coupon_id = '" . $_GET['cid'] . "'");

    $_POST['coupon_amount'] = $coupon->fields['coupon_amount'];
    if ($coupon->fields['coupon_type'] == 'P' || $coupon->fields['coupon_type'] == 'E') {
      $_POST['coupon_amount'] .= '%';
    }
    // free shipping on free shipping only 'S' or percentage off and free shipping 'E' or amount off and free shipping 'O'
    if ($coupon->fields['coupon_type'] == 'S' || $coupon->fields['coupon_type'] == 'O' || $coupon->fields['coupon_type'] == 'E') {
      $_POST['coupon_free_ship'] = true;
    } else {
      $_POST['coupon_free_ship'] = false;
    }
    $_POST['coupon_min_order'] = $coupon->fields['coupon_minimum_order'];
    $_POST['coupon_code'] = $coupon->fields['coupon_code'];
    $_POST['coupon_uses_coupon'] = $coupon->fields['uses_per_coupon'];
    $_POST['coupon_uses_user'] = $coupon->fields['uses_per_user'];
    $_POST['coupon_startdate'] = $coupon->fields['coupon_start_date'];
    $_POST['coupon_finishdate'] = $coupon->fields['coupon_expire_date'];
    $_POST['coupon_zone_restriction'] = $coupon->fields['coupon_zone_restriction'];
    $_POST['coupon_total'] = $coupon->fields['coupon_total'];
    $_POST['coupon_order_limit'] = $coupon->fields['coupon_order_limit'];
    $_POST['coupon_is_valid_for_sales'] = $coupon->fields['coupon_is_valid_for_sales'];

  case 'new':
// set some defaults
    if ($_GET['action'] != 'voucheredit' and $_POST['coupon_uses_user'] == '') $_POST['coupon_uses_user'] = 1;
    if ($_GET['action'] != 'voucheredit' and $_POST['coupon_is_valid_for_sales'] == '') $_POST['coupon_is_valid_for_sales'] = 1;
?>
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
      <td>
<?php
    echo zen_draw_form('coupon', FILENAME_COUPON_ADMIN, 'action=update&oldaction=' . $_GET['action'] . '&cid=' . $_GET['cid'] . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''));
?>
      <table border="0" width="100%" cellspacing="0" cellpadding="6">
<?php
        $languages = zen_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
        $language_id = $languages[$i]['id'];
?>
      <tr>
        <td align="left" class="main"><?php if ($i==0) echo COUPON_NAME; ?></td>
        <td align="left"><?php echo zen_draw_input_field('coupon_name[' . $languages[$i]['id'] . ']', htmlspecialchars(stripslashes($_POST['coupon_name'][$language_id]), ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_COUPONS_DESCRIPTION, 'coupon_name')) . '&nbsp;' . zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']); ?></td>
        <td align="left" class="main" width="40%"><?php if ($i==0) echo COUPON_NAME_HELP; ?></td>
      </tr>
<?php
}
?>
<?php
        $languages = zen_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
        $language_id = $languages[$i]['id'];
?>

      <tr>
        <td align="left" valign="top" class="main"><?php if ($i==0) echo COUPON_DESC; ?></td>
        <td align="left" valign="top"><?php echo zen_draw_textarea_field('coupon_desc[' . $languages[$i]['id'] . ']','physical','24','8', htmlspecialchars(stripslashes($_POST['coupon_desc'][$language_id]), ENT_COMPAT, CHARSET, TRUE), 'class="editorHook"'); ?>
            <?php echo '&nbsp;' . zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']); ?></td>
        <td align="left" valign="top" class="main"><?php if ($i==0) echo COUPON_DESC_HELP; ?></td>
      </tr>
<?php
}
?>
      <tr>
        <td align="left" class="main"><?php echo COUPON_AMOUNT; ?></td>
        <td align="left"><?php echo zen_draw_input_field('coupon_amount', $_POST['coupon_amount']); ?></td>
        <td align="left" class="main"><?php echo COUPON_AMOUNT_HELP; ?></td>
      </tr>
      <tr>
        <td align="left" class="main"><?php echo COUPON_MIN_ORDER; ?></td>
        <td align="left"><?php echo zen_draw_input_field('coupon_min_order', $_POST['coupon_min_order']); ?></td>
        <td align="left" class="main"><?php echo COUPON_MIN_ORDER_HELP; ?></td>
      </tr>
      <tr>
        <td align="left" valign="top" class="main"><?php echo COUPON_TOTAL; ?></td>
        <td align="left" valign="top">
          <?php echo zen_draw_radio_field('coupon_total', '0', ($_POST['coupon_total'] == 0)) . '&nbsp;' . TEXT_COUPON_TOTAL_PRODUCTS . TEXT_COUPON_TOTAL_PRODUCTS_BASED; ?><br />
          <?php echo zen_draw_radio_field('coupon_total', '1', ($_POST['coupon_total'] ==1)) . '&nbsp;' . TEXT_COUPON_TOTAL_ORDER . TEXT_COUPON_TOTAL_ORDER_BASED; ?><br />
        </td>
        <td align="left" valign="top" class="main"><?php echo COUPON_TOTAL_HELP; ?></td>
      </tr>
      <tr>
        <td align="left" class="main"><?php echo COUPON_FREE_SHIP; ?></td>
        <td align="left"><input type="checkbox" name="coupon_free_ship" <?php if ($_POST['coupon_free_ship']) echo 'CHECKED'; ?>></td>
        <td align="left" class="main"><?php echo COUPON_FREE_SHIP_HELP; ?></td>
      </tr>

      <tr>
        <td align="left" valign="top" class="main"><?php echo COUPON_IS_VALID_FOR_SALES; ?></td>
        <td align="left" valign="top">
          <?php echo zen_draw_radio_field('coupon_is_valid_for_sales', '1', ($_POST['coupon_is_valid_for_sales'] == 1)) . '&nbsp;' . TEXT_COUPON_IS_VALID_FOR_SALES; ?><br />
          <?php echo zen_draw_radio_field('coupon_is_valid_for_sales', '0', ($_POST['coupon_is_valid_for_sales'] == 0)) . '&nbsp;' . TEXT_NO_COUPON_IS_VALID_FOR_SALES; ?><br />
        </td>
        <td align="left" valign="top" class="main"><?php echo COUPON_TOTAL_HELP; ?></td>
      </tr>

      <tr>
        <td align="left" class="main"><?php echo COUPON_CODE; ?></td>
        <td align="left"><?php echo zen_draw_input_field('coupon_code', htmlspecialchars($_POST['coupon_code'], ENT_COMPAT, CHARSET, TRUE)); ?></td>
        <td align="left" class="main"><?php echo COUPON_CODE_HELP; ?></td>
      </tr>
      <tr>
        <td align="left" class="main"><?php echo COUPON_USES_COUPON; ?></td>
        <td align="left"><?php echo zen_draw_input_field('coupon_uses_coupon', ($_POST['coupon_uses_coupon'] >= 1 ? $_POST['coupon_uses_coupon'] : '')); ?></td>
        <td align="left" class="main"><?php echo COUPON_USES_COUPON_HELP; ?></td>
      </tr>
      <tr>
        <td align="left" class="main"><?php echo COUPON_USES_USER; ?></td>
        <td align="left"><?php echo zen_draw_input_field('coupon_uses_user', ($_POST['coupon_uses_user'] >= 1 ? $_POST['coupon_uses_user'] : '')); ?></td>
        <td align="left" class="main"><?php echo COUPON_USES_USER_HELP; ?></td>
      </tr>
      <tr>
<?php
    if (!$_POST['coupon_startdate']) {
      $coupon_startdate = preg_split("/[-]/", date('Y-m-d'));
    } else {
      $coupon_startdate = preg_split("/[-]/", $_POST['coupon_startdate']);
    }
    if (!$_POST['coupon_finishdate']) {
      $coupon_finishdate = preg_split("/[-]/", date('Y-m-d'));
      $coupon_finishdate[0] = $coupon_finishdate[0] + 1;
    } else {
      $coupon_finishdate = preg_split("/[-]/", $_POST['coupon_finishdate']);
    }
?>
        <td align="left" class="main"><?php echo COUPON_STARTDATE; ?></td>
        <td align="left"><?php echo zen_draw_date_selector('coupon_startdate', mktime(0,0,0, $coupon_startdate[1], $coupon_startdate[2], $coupon_startdate[0])); ?></td>
        <td align="left" class="main"><?php echo COUPON_STARTDATE_HELP; ?></td>
      </tr>
      <tr>
        <td align="left" class="main"><?php echo COUPON_FINISHDATE; ?></td>
        <td align="left"><?php echo zen_draw_date_selector('coupon_finishdate', mktime(0,0,0, $coupon_finishdate[1], $coupon_finishdate[2], $coupon_finishdate[0])); ?></td>
        <td align="left" class="main"><?php echo COUPON_FINISHDATE_HELP; ?></td>
      </tr>
      <tr>
        <td align="left" class="main"><?php echo COUPON_ZONE_RESTRICTION; ?></td>
        <td align="left" class="main"><?php echo zen_geo_zones_pull_down_coupon('name="coupon_zone_restriction" style="font-size:10px"', $_POST['coupon_zone_restriction']); ?>
        <td align="left" class="main"><?php echo TEXT_COUPON_ZONE_RESTRICTION; ?></td>
      </tr>
      <tr>
        <td align="left" class="main"><?php echo COUPON_ORDER_LIMIT; ?></td>
        <td align="left"><?php echo zen_draw_input_field('coupon_order_limit', ($_POST['coupon_order_limit'] >= 1 ? $_POST['coupon_order_limit'] : '')); ?></td>
        <td align="left" class="main"><?php echo COUPON_ORDER_LIMIT_HELP; ?></td>
      </tr>
      <tr>
        <td align="left"><?php echo zen_image_submit('button_preview.gif',COUPON_BUTTON_PREVIEW); ?></td>
        <td align="left">&nbsp;&nbsp;<a href="<?php echo zen_href_link(FILENAME_COUPON_ADMIN, 'cid=' . $_GET['cid'] . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')); ?>"><?php echo zen_image_button('button_cancel.gif', IMAGE_CANCEL); ?></a>
      </td>
      </tr>
      </td></table></form>
      </tr>

      </table></td>
<?php
    break;
  default:
?>
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="main"><?php echo zen_draw_form('status', FILENAME_COUPON_ADMIN, '', 'get'); ?>
<?php
    $status_array[] = array('id' => 'Y', 'text' => TEXT_COUPON_ACTIVE);
    $status_array[] = array('id' => 'N', 'text' => TEXT_COUPON_INACTIVE);
    $status_array[] = array('id' => 'A', 'text' => TEXT_COUPON_ALL);

    if (isset($_GET['status'])) {
      $status = zen_db_prepare_input($_GET['status']);
    } else {
      $status = 'Y';
    }
    echo zen_hide_session_id();
    $status = $status[0];
    echo HEADING_TITLE_STATUS . ' ' . zen_draw_pull_down_menu('status', $status_array, $status, 'onChange="this.form.submit();"');
?>
              </form>
           </td>
            <td class="main">
<?php
// toggle switch for editor
        echo TEXT_EDITOR_INFO . zen_draw_form('set_editor_form', FILENAME_COUPON_ADMIN, '', 'get') . '&nbsp;&nbsp;' . zen_draw_pull_down_menu('reset_editor', $editors_pulldown, $current_editor_key, 'onChange="this.form.submit();"') .
        zen_hide_session_id() .
        zen_draw_hidden_field('action', 'set_editor') .
        '</form>';
?>
</td>
            <td class="main" align="right">
<?php
// search for Discount Coupon
            echo zen_draw_form('search', FILENAME_COUPON_ADMIN, '', 'get', '', true);
            echo HEADING_TITLE_SEARCH_DETAIL . ' ' . zen_draw_input_field('search') . zen_hide_session_id();
            echo '</form>';
?>
</td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo COUPON_NAME; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo COUPON_AMOUNT; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo COUPON_CODE; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo COUPON_ACTIVE; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo COUPON_START_DATE; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo COUPON_EXPIRE_DATE; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo COUPON_RESTRICTIONS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
    if ($status != 'A') {
      $cc_query_raw = "select * from " . TABLE_COUPONS ." where coupon_active='" . zen_db_input($status) . "' and coupon_type != 'G'";
    } else {
      $cc_query_raw = "select * from " . TABLE_COUPONS . " where coupon_type != 'G'";
    }
    $maxDisplaySearchResults = (defined('MAX_DISPLAY_SEARCH_RESULTS_DISCOUNT_COUPONS') && (int)MAX_DISPLAY_SEARCH_RESULTS_DISCOUNT_COUPONS > 0) ? (int)MAX_DISPLAY_SEARCH_RESULTS_DISCOUNT_COUPONS : 20;

    $cc_split = new splitPageResults($_GET['page'], $maxDisplaySearchResults, $cc_query_raw, $cc_query_numrows);
    $cc_list = $db->Execute($cc_query_raw);
    while (!$cc_list->EOF) {
      if (((!$_GET['cid']) || (@$_GET['cid'] == $cc_list->fields['coupon_id'])) && (!$cInfo)) {
        $cInfo = new objectInfo($cc_list->fields);
      }
      if ( (is_object($cInfo)) && ($cc_list->fields['coupon_id'] == $cInfo->coupon_id) ) {
        echo '          <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'hand\'" onclick="document.location.href=\'' . zen_href_link(FILENAME_COUPON_ADMIN, zen_get_all_get_params(array('cid', 'action')) . 'cid=' . $cInfo->coupon_id . '&action=voucheredit') . '\'">' . "\n";
      } else {
        echo '          <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'hand\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . zen_href_link(FILENAME_COUPON_ADMIN, zen_get_all_get_params(array('cid', 'action')) . 'cid=' . $cc_list->fields['coupon_id']) . '\'">' . "\n";
      }
      $coupon_desc = $db->Execute("select coupon_name
                                   from " . TABLE_COUPONS_DESCRIPTION . "
                                   where coupon_id = '" . (int)$cc_list->fields['coupon_id'] . "'
                                   and language_id = '" . (int)$_SESSION['languages_id'] . "'");

      $coupon_restrictions = $db->Execute("select * from " . TABLE_COUPON_RESTRICT . " where coupon_id = '" . (int)$cc_list->fields['coupon_id'] . "' limit 1");

?>
                <td class="dataTableContent"><?php echo $coupon_desc->fields['coupon_name']; ?></td>
                <td class="dataTableContent" align="center">
<?php
      switch ($cc_list->fields['coupon_type']) {
        case ('S'): // free shipping
          echo TEXT_FREE_SHIPPING;
          break;
        case ('P'): // percentage off
          echo $cc_list->fields['coupon_amount'] . '%';
          break;
        case ('F'): // amount off
          echo $currencies->format($cc_list->fields['coupon_amount']);
          break;
        case ('E'): // percentage off and free shipping
          echo $cc_list->fields['coupon_amount'] . '%' . '<br />' . TEXT_FREE_SHIPPING;
          break;
        case ('O'): // amount off and free shipping
          echo $currencies->format($cc_list->fields['coupon_amount']) . '<br />' . TEXT_FREE_SHIPPING;
          break;
        default:
          echo '***';
          break;
      }
?>
            &nbsp;</td>
                <td class="dataTableContent" align="center"><?php echo $cc_list->fields['coupon_code']; ?></td>
                <td class="dataTableContent" align="center"><?php echo $cc_list->fields['coupon_active']; ?></td>
                <td class="dataTableContent<?php if (strtotime($cc_list->fields['coupon_start_date']) > time()) echo ' coupon-future'; ?>" align="center"><?php echo zen_date_short($cc_list->fields['coupon_start_date']); ?></td>
                <td class="dataTableContent<?php if (strtotime($cc_list->fields['coupon_expire_date']) < time()) echo ' coupon-expired'; ?>" align="center"><?php echo zen_date_short($cc_list->fields['coupon_expire_date']); ?></td>
                <td class="dataTableContent" align="center"><?php echo ($coupon_restrictions->RecordCount() > 0 ? '<a href="'.zen_href_link(FILENAME_COUPON_RESTRICT,'cid='.(int)$cc_list->fields['coupon_id'] . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''),'NONSSL').'">' . 'Y' . '</a>' : 'N'); ?></td>
                <td class="dataTableContent" align="right"><?php if ( (is_object($cInfo)) && ($cc_list->fields['coupon_id'] == $cInfo->coupon_id) ) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . zen_href_link(FILENAME_COUPON_ADMIN, 'page=' . $_GET['page'] . '&cid=' . $cc_list->fields['coupon_id'] . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '')) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
      $cc_list->MoveNext();
    }
?>
          <tr>
            <td colspan="7"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td class="smallText">&nbsp;<?php echo $cc_split->display_count($cc_query_numrows, $maxDisplaySearchResults, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_COUPONS); ?>&nbsp;</td>
                <td align="right" class="smallText">&nbsp;<?php echo $cc_split->display_links($cc_query_numrows, $maxDisplaySearchResults, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], (isset($_GET['status']) ? '&status=' . $_GET['status'] : '')); ?>&nbsp;</td>
              </tr>

              <tr>
                <td align="right" colspan="2" class="smallText"><?php echo '<a name="couponInsert" href="' . zen_href_link(FILENAME_COUPON_ADMIN, 'page=' . $_GET['page'] . '&cid=' . $cInfo->coupon_id . '&action=new') . '">' . zen_image_button('button_insert.gif', IMAGE_INSERT) . '</a>'; ?></td>
              </tr>
            </table></td>
          </tr>
        </table></td>

<?php

    $heading = array();
    $contents = array();

    switch ($_GET['action']) {
    case 'release':
      break;
    case 'voucherreport':
      $heading[] = array('text' => '<b>' . TEXT_HEADING_COUPON_REPORT . '</b>');
      $contents[] = array('text' => TEXT_NEW_INTRO);
      break;
// base code - header for report for basecode
    case 'voucherreportduplicates':
      $heading[] = array('text' => '<b>' . TEXT_HEADING_COUPON_REPORT . '</b>');
      $contents[] = array('text' => TEXT_NEW_INTRO);
      break;
    case 'new':
      $heading[] = array('text' => '<b>' . TEXT_HEADING_NEW_COUPON . '</b>');
      $contents[] = array('text' => TEXT_NEW_INTRO);
      $contents[] = array('text' => '<br />' . COUPON_NAME . '<br />' . zen_draw_input_field('name'));
      $contents[] = array('text' => '<br />' . COUPON_AMOUNT . '<br />' . zen_draw_input_field('voucher_amount'));
      $contents[] = array('text' => '<br />' . COUPON_CODE . '<br />' . zen_draw_input_field('voucher_code'));
      $contents[] = array('text' => '<br />' . COUPON_USES_COUPON . '<br />' . zen_draw_input_field('voucher_number_of'));
      break;
    default:
      $heading[] = array('text'=>'['.$cInfo->coupon_id.']  '.$cInfo->coupon_code . ($cInfo->coupon_id == '' ? ' - (' . $_GET['cid'] . ')' : ''));
      $amount = $cInfo->coupon_amount;
      if ($cInfo->coupon_type == 'P' || $cInfo->coupon_type == 'E') {
        $amount .= '%';
      } else {
        $amount = $currencies->format($amount);
      }
      if ($cInfo->coupon_type == 'S'|| $cInfo->coupon_type == 'E' || $cInfo->coupon_type == 'O' ) {
        $amount .= ' ' . TEXT_FREE_SHIPPING;
      }
      if ($_GET['action'] == 'voucherdelete' or $_GET['action'] == 'vouchercopy' or $_GET['action'] == 'voucherduplicate' or $_GET['action'] == 'voucherduplicatedelete' or $_GET['action'] == 'voucherreactivate') {
        if ($_GET['action'] == 'voucherdelete') {
          $contents[] = array('text'=> TEXT_CONFIRM_DELETE . '</br></br>' .
                  '<a href="'.zen_href_link(FILENAME_COUPON_ADMIN,'action=confirmdelete&cid='.$_GET['cid'] . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''),'NONSSL').'">'.zen_image_button('button_confirm.gif', TEXT_DISCOUNT_COUPON_CONFIRM_DELETE).'</a>' .
                  '<a href="'.zen_href_link(FILENAME_COUPON_ADMIN,'cid='.$cInfo->coupon_id . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''),'NONSSL').'">'.zen_image_button('button_cancel.gif','Cancel').'</a>'
                  );
        }

        if ($_GET['action'] == 'voucherreactivate') {
          $contents[] = array('text'=> TEXT_CONFIRM_REACTIVATE . '</br></br>' .
                  '<a href="'.zen_href_link(FILENAME_COUPON_ADMIN,'action=confirmreactivate&cid='.$_GET['cid'] . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''),'NONSSL').'">'.zen_image_button('button_confirm.gif', TEXT_DISCOUNT_COUPON_CONFIRM_RESTORE).'</a>' .
                  '<a href="'.zen_href_link(FILENAME_COUPON_ADMIN,'cid='.$cInfo->coupon_id . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''),'NONSSL').'">'.zen_image_button('button_cancel.gif','Cancel').'</a>'
                  );
        }

        if ($_GET['action'] == 'vouchercopy') {
          $contents = array('form' => zen_draw_form('new_coupon', FILENAME_COUPON_ADMIN, 'action=confirmcopy' . '&cid='.$_GET['cid'] . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''), 'post', 'enctype="multipart/form-data"'));

          $contents[] = array('text' => '<br>' . TEXT_COUPON_NEW . '&nbsp;' . zen_draw_input_field('coupon_copy_to', '', 6, 6));

          $contents[] = array('text'=> TEXT_CONFIRM_COPY . '</br>');
          $contents[] = array('text'=> zen_image_submit('button_save.gif', IMAGE_SAVE));
          $contents[] = array('text'=>
                  '<a href="'.zen_href_link(FILENAME_COUPON_ADMIN,'cid='.$cInfo->coupon_id . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''),'NONSSL').'">'.zen_image_button('button_cancel.gif','Cancel').'</a>'
                  );
        }
// base code - create multiple duplicate coupons from basecode
        if ($_GET['action'] == 'voucherduplicate') {
          $contents = array('form' => zen_draw_form('duplicate_coupon', FILENAME_COUPON_ADMIN, 'action=confirmcopyduplicate' . '&cid='.$_GET['cid'] . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''), 'post', 'enctype="multipart/form-data"'));

          $contents[] = array('text' => '<br>' . TEXT_COUPON_COPY_INFO . '<br>');
          $contents[] = array('text' => '<br>' . TEXT_COUPON_COPY_DUPLICATE . '&nbsp;' . zen_draw_input_field('coupon_copy_to_dup_name', $cInfo->coupon_code, 6, 6));
          $contents[] = array('text' => '<br>' . TEXT_COUPON_COPY_DUPLICATE_CNT . '&nbsp;' . zen_draw_input_field('coupon_copy_to_count', '', 6, 6));

          $contents[] = array('text'=> TEXT_CONFIRM_COPY . '</br>');
          $contents[] = array('text'=> zen_image_submit('button_save.gif', IMAGE_SAVE));
          $contents[] = array('text'=>
                  '<a href="'.zen_href_link(FILENAME_COUPON_ADMIN,'cid='.$cInfo->coupon_id . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''),'NONSSL').'">'.zen_image_button('button_cancel.gif','Cancel').'</a>'
                  );
        }
// base code - delete multiple duplicate coupons from basecode
        if ($_GET['action'] == 'voucherduplicatedelete') {
          $chk_duplicate_delete = $db->Execute("SELECT * from " . TABLE_COUPONS . " WHERE coupon_id = '" . $_GET['cid'] . "'");
          $contents = array('form' => zen_draw_form('duplicate_coupon_delete', FILENAME_COUPON_ADMIN, 'action=confirmdeleteduplicate' . '&cid='.$_GET['cid'] . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''), 'post', 'enctype="multipart/form-data"'));

          $contents[] = array('text'=> sprintf(TEXT_CONFIRM_DELETE_DUPLICATE, $chk_duplicate_delete->fields['coupon_code'], $chk_duplicate_delete->fields['coupon_code']) . '</br>');
          $contents[] = array('text' => '<br>' . TEXT_COUPON_DELETE_DUPLICATE . '&nbsp;' . zen_draw_input_field('coupon_delete_duplicate_code', $chk_duplicate_delete->fields['coupon_code'], 6, 6));
          $contents[] = array('text'=> zen_image_submit('button_confirm.gif', 'Confirm Delete of Multiple Discount Coupons'));
          $contents[] = array('text'=>
                  '<a href="'.zen_href_link(FILENAME_COUPON_ADMIN,'cid='.$cInfo->coupon_id . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''),'NONSSL').'">'.zen_image_button('button_cancel.gif','Cancel').'</a>'
                  );

        }

      } else {
        $prod_details = TEXT_NONE;
//bof 12-6ke
$product_query = $db->Execute("select * from " . TABLE_COUPON_RESTRICT . " where coupon_id = '" . $cInfo->coupon_id . "' and product_id != '0'");        if ($product_query->RecordCount() > 0) $prod_details = TEXT_SEE_RESTRICT;
//eof 12-6ke
        $cat_details = TEXT_NONE;
//bof 12-6ke
$category_query = $db->Execute("select * from " . TABLE_COUPON_RESTRICT . " where coupon_id = '" . $cInfo->coupon_id . "' and category_id != '0'");        if ($category_query->RecordCount() > 0) $cat_details = TEXT_SEE_RESTRICT;
//eof 12-6ke
        $coupon_name = $db->Execute("select coupon_name
                                     from " . TABLE_COUPONS_DESCRIPTION . "
                                     where coupon_id = '" . $cInfo->coupon_id . "'
                                     and language_id = '" . (int)$_SESSION['languages_id'] . "'");
        $uses_coupon = $cInfo->uses_per_coupon;
        $uses_user = $cInfo->uses_per_user;
        $coupon_order_limit = $cInfo->coupon_order_limit;
        $coupon_is_valid_for_sales = $cInfo->coupon_is_valid_for_sales;
        if ($uses_coupon == 0 || $uses_coupon == '') $uses_coupon = TEXT_UNLIMITED;
        if ($uses_user == 0 || $uses_user == '') $uses_user = TEXT_UNLIMITED;
        if ($cInfo->coupon_id != '') $contents[] = array('text'=>COUPON_NAME . '&nbsp;::&nbsp; ' . $coupon_name->fields['coupon_name'] . '<br />' .
                     COUPON_AMOUNT . '&nbsp;::&nbsp; ' . $amount . '<br />' .
                     ($coupon_name->fields['coupon_type'] == 'E' || $coupon_name->fields['coupon_type'] == '0' ? TEXT_FREE_SHIPPING . '<br />' : '') .
                     COUPON_STARTDATE . '&nbsp;::&nbsp; ' . zen_date_short($cInfo->coupon_start_date) . '<br />' .
                     COUPON_FINISHDATE . '&nbsp;::&nbsp; ' . zen_date_short($cInfo->coupon_expire_date) . '<br />' .
                     COUPON_USES_COUPON . '&nbsp;::&nbsp; ' . $uses_coupon . '<br />' .
                     COUPON_USES_USER . '&nbsp;::&nbsp; ' . $uses_user . '<br />' .
                     COUPON_PRODUCTS . '&nbsp;::&nbsp; ' . $prod_details . '<br />' .
                     COUPON_CATEGORIES . '&nbsp;::&nbsp; ' . $cat_details . '<br />' .
                     COUPON_MIN_ORDER . '&nbsp;::&nbsp; ' . $currencies->format($cInfo->coupon_minimum_order) . '<br />' .
                     COUPON_TOTAL . '&nbsp;::&nbsp; ' . ($cInfo->coupon_total == 0 ? TEXT_COUPON_TOTAL_PRODUCTS : TEXT_COUPON_TOTAL_ORDER) . '<br />' .
                     DATE_CREATED . '&nbsp;::&nbsp; ' . zen_date_short($cInfo->date_created) . '<br />' .
                     DATE_MODIFIED . '&nbsp;::&nbsp; ' . zen_date_short($cInfo->date_modified) . '<br /><br />' .
                     COUPON_ZONE_RESTRICTION . '&nbsp;::&nbsp; ' . zen_get_geo_zone_name($cInfo->coupon_zone_restriction) . '<br /><br />' .
                     COUPON_ORDER_LIMIT . '&nbsp;::&nbsp; ' . ($coupon_order_limit > 0 ? $coupon_order_limit : TEXT_UNLIMITED) . '<br /><br />' .
                     COUPON_IS_VALID_FOR_SALES . '&nbsp;::&nbsp; ' . ($coupon_is_valid_for_sales == 1 ? TEXT_COUPON_IS_VALID_FOR_SALES : TEXT_NO_COUPON_IS_VALID_FOR_SALES) . '<br /><br />' .
                     '<center>' .($cInfo->coupon_active != 'N' ? '<a href="'.zen_href_link(FILENAME_COUPON_ADMIN,'action=email&cid='.$cInfo->coupon_id,'NONSSL').'">'.zen_image_button('button_email.gif', TEXT_DISCOUNT_COUPON_EMAIL).'</a>' : '') .
                     '<a href="'.zen_href_link(FILENAME_COUPON_ADMIN,'action=voucheredit&cid='.$cInfo->coupon_id . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''),'NONSSL').'">'.zen_image_button('button_edit.gif', TEXT_DISCOUNT_COUPON_EDIT) .'</a>' .
                     ($cInfo->coupon_active != 'N' ? '<a href="'.zen_href_link(FILENAME_COUPON_ADMIN,'action=voucherdelete&cid='.$cInfo->coupon_id . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''),'NONSSL').'">'.zen_image_button('button_delete.gif', TEXT_DISCOUNT_COUPON_DELETE).'</a>' : '<a href="'.zen_href_link(FILENAME_COUPON_ADMIN,'action=voucherreactivate&cid='.$cInfo->coupon_id . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''),'NONSSL').'">'.zen_image_button('button_restore.gif', TEXT_DISCOUNT_COUPON_RESTORE).'</a>') .
                     '<br /><a href="'.zen_href_link(FILENAME_COUPON_RESTRICT,'cid='.$cInfo->coupon_id  . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''),'NONSSL').'">'.zen_image_button('button_restrict.gif', TEXT_DISCOUNT_COUPON_RESTRICT).'</a>' .
                     '<a href="'.zen_href_link(FILENAME_COUPON_ADMIN,'action=voucherreport&cid='.$cInfo->coupon_id . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''),'NONSSL').'">'.zen_image_button('button_report.gif', TEXT_DISCOUNT_COUPON_REPORT) . '</a>' .
                     '<a href="'.zen_href_link(FILENAME_COUPON_ADMIN,'action=vouchercopy&cid='.$cInfo->coupon_id,'NONSSL').'">'.zen_image_button('button_copy.gif', TEXT_DISCOUNT_COUPON_COPY) . '</a>' .
                   '<br /><br />' . zen_draw_separator('pixel_black.gif', '100%', '2') . '<br><br>' . sprintf(TEXT_INFO_DUPLICATE_MANAGEMENT, $cInfo->coupon_code) . '<br>' .
                     ($cInfo->coupon_active != 'N' ? '<br><a href="'.zen_href_link(FILENAME_COUPON_ADMIN,'action=voucherduplicate&cid='.$cInfo->coupon_id . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''),'NONSSL').'">'.zen_image_button('button_copy_to.gif', TEXT_DISCOUNT_COUPON_COPY_MULTIPLE) . '</a>' : '') .
                     ($cInfo->coupon_active != 'N' ? '&nbsp;&nbsp;<a href="'.zen_href_link(FILENAME_COUPON_ADMIN,'action=voucherduplicatedelete&cid='.$cInfo->coupon_id . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''),'NONSSL').'">'.zen_image_button('button_remove.gif', TEXT_DISCOUNT_COUPON_DELETE_MULTIPLE) . '</a>' : '').
                     '<br /><a href="'.zen_href_link(FILENAME_COUPON_ADMIN,'action=voucherreportduplicates&cid='.$cInfo->coupon_id . '&codebase=' . $cInfo->coupon_code . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''),'NONSSL').'">'.zen_image_button('button_report.gif', TEXT_DISCOUNT_COUPON_REPORT_MULTIPLE) . '</a>' .
                     '&nbsp;&nbsp;<a href="'.zen_href_link(FILENAME_COUPON_ADMIN_EXPORT, 'cid=' . $cInfo->coupon_id . '&codebase=' . $cInfo->coupon_code . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''),'NONSSL') .'">'.zen_image_button('button_download_now.gif', TEXT_DISCOUNT_COUPON_DOWNLOAD) . '</a>' .
                     '</center>'
                     );

        }
        break;
      }
?>
    <td width="25%" valign="top">
<?php
      $box = new box;
      echo $box->infoBox($heading, $contents);
    echo '            </td>' . "\n";
    }
?>
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
