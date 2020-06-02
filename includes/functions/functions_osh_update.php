<?php
/**
 * functions_osh_update
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Apr 12 Modified in v1.5.7 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    exit('Invalid Access');
}

// -----
// A common-use (both storefront and admin) that updates an order's status-history.
//
// Inputs:
// - $order_id ................ The order for which the status record is to be created
// - $message ................. The comments associated with the history record, if non-blank.
// - $updated_by .............. If non-null, the specified value will be used for the like-named field.  Otherwise,
//                              the value will be calculated based on some defaults.
// - $orders_new_status ....... The orders_status value for the update.  If set to -1, no change in the status value was detected.
// - $notify_customer ......... Identifies whether the history record is sent via email and visible to the customer via the "account_history_info" page:
//                               0 ... No emails sent, customer can view on "account_history_info"
//                               1 ... Email sent, customer can view on "account_history_info"
//                              -1 ... No emails sent, comments and status-change hidden from customer view
//                              -2 ... Email sent only to configured admins; status-change hidden from customer view
// - $email_include_message ... Identifies whether (true) or not (false) to include the status message ($osh_additional_comments) in any email sent.
// - $email_subject ........... If specified, overrides the default email subject line.
// - $send_extra_mails_to ..... If specified, overrides the "standard" database settings SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO_STATUS and
//                              SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO.
//
// Returns:
// - $osh_id ............ A value > 0 if the record has been written (the orders_status_history_id number)
//                        -2 if no order record was found for the specified $orders_id
//                        -1 if no status change was detected (i.e. no record written).
//
function zen_update_orders_history($orders_id, $message = '', $updated_by = null, $orders_new_status = -1, $notify_customer = -1, $email_include_message = true, $email_subject = '', $send_extra_emails_to = '') 
{
    global $osh_sql, $osh_additional_comments;
    
    // -----
    // Initialize return value to indicate no change and sanitize various inputs.
    //
    $osh_id = -1;
    $orders_id = (int)$orders_id;
    $message = (string)$message;
    $email_subject = (string)$email_subject;
    $send_extra_emails_to = (string)$send_extra_emails_to;
    
    $osh_info = $GLOBALS['db']->ExecuteNoCache(
        "SELECT customers_name, customers_email_address, orders_status, date_purchased 
           FROM " . TABLE_ORDERS . " 
          WHERE orders_id = $orders_id
          LIMIT 1" 
    );
    if ($osh_info->EOF) {
        $osh_id = -2; 
    } else {
        // -----
        // Determine the message to be included in any email(s) sent.  If an observer supplies an additional
        // message, that text is appended to the message supplied on the function's call.
        //
        $message = stripslashes($message);
        $email_message = '';
        if ($email_include_message === true) {
            $email_message = $message;
            if (empty($osh_additional_comments)) {
                $osh_additional_comments = '';
            }
            $GLOBALS['zco_notifier']->notify('ZEN_UPDATE_ORDERS_HISTORY_PRE_EMAIL', array('message' => $message), $osh_additional_comments);
            if (!empty($osh_additional_comments)) {
                if (!empty($email_message)) {
                    $email_message .= "\n\n";
                }
                $email_message .= (string)$osh_additional_comments;
            }
            if (!empty($email_message)) {
                $email_message = OSH_EMAIL_TEXT_COMMENTS_UPDATE . $email_message . "\n\n";
            }
        }
        
        $orders_current_status = $osh_info->fields['orders_status'];
        $orders_new_status = (int)$orders_new_status;
        if (($orders_new_status != -1 && $orders_current_status != $orders_new_status) || !empty($email_message)) {
            if ($orders_new_status == -1) {
                $orders_new_status = $orders_current_status;
            }
            $GLOBALS['zco_notifier']->notify('ZEN_UPDATE_ORDERS_HISTORY_STATUS_VALUES', array('orders_id' => $orders_id, 'new' => $orders_new_status, 'old' => $orders_current_status));
        
            $GLOBALS['db']->Execute( 
                "UPDATE " . TABLE_ORDERS . " 
                    SET orders_status = $orders_new_status,
                        last_modified = now() 
                  WHERE orders_id = $orders_id
                  LIMIT 1" 
            );
        
            $notify_customer = ($notify_customer == 1 || $notify_customer == -1 || $notify_customer == -2) ? $notify_customer : 0;
        
            if ($notify_customer == 1 || $notify_customer == -2) {
                $new_orders_status_name = zen_get_orders_status_name($orders_new_status);
                if ($new_orders_status_name == '') {
                    $new_orders_status_name = 'N/A';
                }

                if ($orders_new_status != $orders_current_status) {
                    $status_text = OSH_EMAIL_TEXT_STATUS_UPDATED;
                    $status_value_text = sprintf(OSH_EMAIL_TEXT_STATUS_CHANGE, zen_get_orders_status_name($orders_current_status), $new_orders_status_name);
                } else {
                    $status_text = OSH_EMAIL_TEXT_STATUS_NO_CHANGE;
                    $status_value_text = sprintf(OSH_EMAIL_TEXT_STATUS_LABEL, $new_orders_status_name);
                }
                
                //send emails
                $email_text =
                    EMAIL_SALUTATION . ' ' . $osh_info->fields['customers_name'] . ', ' . "\n\n" .
                    STORE_NAME . ' ' . OSH_EMAIL_TEXT_ORDER_NUMBER . ' ' . $orders_id . "\n\n" .
                    OSH_EMAIL_TEXT_INVOICE_URL . ' ' . zen_catalog_href_link(FILENAME_CATALOG_ACCOUNT_HISTORY_INFO, "order_id=$orders_id", 'SSL') . "\n\n" .
                    OSH_EMAIL_TEXT_DATE_ORDERED . ' ' . zen_date_long($osh_info->fields['date_purchased']) . "\n\n" .
                    strip_tags($email_message) .
                    $status_text . $status_value_text .
                    OSH_EMAIL_TEXT_STATUS_PLEASE_REPLY;

                // Add in store specific order message
                $email_order_message = defined('EMAIL_ORDER_UPDATE_MESSAGE') ? constant('EMAIL_ORDER_UPDATE_MESSAGE') : '';
                $GLOBALS['zco_notifier']->notify('ZEN_UPDATE_ORDERS_HISTORY_SET_ORDER_UPDATE_MESSAGE', $orders_id, $email_order_message); 
                if (!empty($email_order_message)) {
                 $email_text .= "\n\n" . $email_order_message . "\n\n";
                }
                $html_msg['EMAIL_ORDER_UPDATE_MESSAGE'] = $email_order_message;

                $html_msg['EMAIL_SALUTATION'] = EMAIL_SALUTATION;
                $html_msg['EMAIL_CUSTOMERS_NAME']    = $osh_info->fields['customers_name'];
                $html_msg['EMAIL_TEXT_ORDER_NUMBER'] = OSH_EMAIL_TEXT_ORDER_NUMBER . ' ' . $orders_id;
                $html_msg['EMAIL_TEXT_INVOICE_URL']  = '<a href="' . zen_catalog_href_link(FILENAME_CATALOG_ACCOUNT_HISTORY_INFO, "order_id=$orders_id", 'SSL') .'">' . str_replace(':', '', OSH_EMAIL_TEXT_INVOICE_URL) . '</a>';
                $html_msg['EMAIL_TEXT_DATE_ORDERED'] = OSH_EMAIL_TEXT_DATE_ORDERED . ' ' . zen_date_long($osh_info->fields['date_purchased']);
                $html_msg['EMAIL_TEXT_STATUS_COMMENTS'] = nl2br($email_message);
                $html_msg['EMAIL_TEXT_STATUS_UPDATED'] = str_replace("\n", '', $status_text);
                $html_msg['EMAIL_TEXT_STATUS_LABEL'] = str_replace("\n", '', $status_value_text);
                $html_msg['EMAIL_TEXT_NEW_STATUS'] = $new_orders_status_name;
                $html_msg['EMAIL_TEXT_STATUS_PLEASE_REPLY'] = str_replace("\n", '', OSH_EMAIL_TEXT_STATUS_PLEASE_REPLY);
                $html_msg['EMAIL_PAYPAL_TRANSID'] = '';
                
                if (empty($email_subject)) {
                    $email_subject = OSH_EMAIL_TEXT_SUBJECT . ' #' . $orders_id;
                }

                if ($notify_customer == 1) { 
                    zen_mail($osh_info->fields['customers_name'], $osh_info->fields['customers_email_address'], $email_subject, $email_text, STORE_NAME, EMAIL_FROM, $html_msg, 'order_status');
                } 

                // PayPal Trans ID, if any
                $result = $GLOBALS['db']->Execute(
                    "SELECT txn_id, parent_txn_id 
                       FROM " . TABLE_PAYPAL . " 
                      WHERE order_id = $orders_id
                   ORDER BY last_modified DESC, date_added DESC, parent_txn_id DESC, paypal_ipn_id DESC"
                );
                if (!$result->EOF) {
                    $email_text .= "\n\n" . ' PayPal Trans ID: ' . $result->fields['txn_id'];
                    $html_msg['EMAIL_PAYPAL_TRANSID'] = $result->fields['txn_id'];
                }

                //send extra emails
                if (empty($send_extra_emails_to) && SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO_STATUS == '1') {
                    $send_extra_emails_to = (string)SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO;
                }
                if (!empty($send_extra_emails_to)) {
                    zen_mail('', $send_extra_emails_to, SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO_SUBJECT . ' ' . $email_subject, $email_text, STORE_NAME, EMAIL_FROM, $html_msg, 'order_status_extra');
                }
            }
    
            if (empty($updated_by)) {
                if (IS_ADMIN_FLAG === true && isset($_SESSION['admin_id'])) {
                    $updated_by = zen_updated_by_admin();
                } elseif (IS_ADMIN_FLAG === false && isset($_SESSION['customers_id'])) {
                    $updated_by = '';
                } else {
                    $updated_by = 'N/A';
                }
            }
    
            $osh_sql = array(
                'orders_id' => $orders_id,
                'orders_status_id' => $orders_new_status,
                'date_added' => 'now()',
                'customer_notified' => $notify_customer,
                'comments' => $message,
                'updated_by' => $updated_by
            );
                       
            $GLOBALS['zco_notifier']->notify('ZEN_UPDATE_ORDERS_HISTORY_BEFORE_INSERT', array(), $osh_sql);
    
            zen_db_perform (TABLE_ORDERS_STATUS_HISTORY, $osh_sql);
            $osh_id = $GLOBALS['db']->Insert_ID();
        }    
    }
    return $osh_id;
}
