<?php
/**
 * reviews Write
 *
 * @package page
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Sat Jul 21 16:05:31 2012 -0400 Modified in v1.5.1 $
 */
/**
 * Header code file for product reviews "write" page
 */

// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_PRODUCT_REVIEWS_WRITE');

if (!$_SESSION['customer_id']) {
  $_SESSION['navigation']->set_snapshot();
  zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
}

require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));

$product_info_query = "SELECT p.products_id, p.products_model, p.products_image,
                              p.products_price, p.products_tax_class_id, pd.products_name
                       FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                       WHERE p.products_id = :productsID
                       AND p.products_status = '1'
                       AND p.products_id = pd.products_id
                       AND pd.language_id = :languagesID";

$product_info_query = $db->bindVars($product_info_query, ':productsID', $_GET['products_id'], 'integer');
$product_info_query = $db->bindVars($product_info_query, ':languagesID', $_SESSION['languages_id'], 'integer');
$product_info = $db->Execute($product_info_query);

if (!$product_info->RecordCount()) {
  zen_redirect(zen_href_link(FILENAME_PRODUCT_REVIEWS, zen_get_all_get_params(array('action'))));
}

$customer_query = "SELECT customers_firstname, CONCAT(LEFT(customers_lastname,1),'.') AS customers_lastname, customers_email_address
                   FROM " . TABLE_CUSTOMERS . "
                   WHERE customers_id = :customersID";
$customer_query = $db->bindVars($customer_query, ':customersID', $_SESSION['customer_id'], 'integer');
$customer = $db->Execute($customer_query);

$error = false;
if (isset($_GET['action']) && ($_GET['action'] == 'process')) {
  $rating = (int)$_POST['rating'];
  $review_text = $_POST['review_text'];
  $antiSpam = isset($_POST['should_be_empty']) ? zen_db_prepare_input($_POST['should_be_empty']) : '';
  $zco_notifier->notify('NOTIFY_REVIEWS_WRITE_CAPTCHA_CHECK');

  if (strlen($review_text) < REVIEW_TEXT_MIN_LENGTH) {
    $error = true;
    $messageStack->add('review_text', JS_REVIEW_TEXT);
  }

  if (($rating < 1) || ($rating > 5)) {
    $error = true;
    $messageStack->add('review_text', JS_REVIEW_RATING);
  }

  if ($error == false) {
   if ($antiSpam != '') {
    $zco_notifier->notify('NOTIFY_SPAM_DETECTED_DURING_WRITE_REVIEW');
    $messageStack->add_session('header', (defined('ERROR_WRITE_REVIEW_SPAM_DETECTED') ? ERROR_WRITE_REVIEW_SPAM_DETECTED : 'Thank you, your post has been submitted for review.'), 'success');
   } else {

    if (REVIEWS_APPROVAL == '1') {
      $review_status = '0';
    } else {
      $review_status = '1';
    }

    $sql = "INSERT INTO " . TABLE_REVIEWS . " (products_id, customers_id, customers_name, reviews_rating, date_added, status)
            VALUES (:productsID, :customersID, :customersName, :rating, now(), " . $review_status . ")";

    $sql = $db->bindVars($sql, ':productsID', $_GET['products_id'], 'integer');
    $sql = $db->bindVars($sql, ':customersID', $_SESSION['customer_id'], 'integer');
    $sql = $db->bindVars($sql, ':customersName', $customer->fields['customers_firstname'] . ' ' . $customer->fields['customers_lastname'], 'string');
    $sql = $db->bindVars($sql, ':rating', $rating, 'string');

    $db->Execute($sql);

    $insert_id = $db->Insert_ID();

    $sql = "INSERT INTO " . TABLE_REVIEWS_DESCRIPTION . " (reviews_id, languages_id, reviews_text)
            VALUES (:insertID, :languagesID, :reviewText)";

    $sql = $db->bindVars($sql, ':insertID', $insert_id, 'integer');
    $sql = $db->bindVars($sql, ':languagesID', $_SESSION['languages_id'], 'integer');
    $sql = $db->bindVars($sql, ':reviewText', $review_text, 'string');

    $db->Execute($sql);
    // send review-notification email to admin
    if (REVIEWS_APPROVAL == '1' && SEND_EXTRA_REVIEW_NOTIFICATION_EMAILS_TO_STATUS == '1' and defined('SEND_EXTRA_REVIEW_NOTIFICATION_EMAILS_TO') and SEND_EXTRA_REVIEW_NOTIFICATION_EMAILS_TO !='') {
      $email_text  = sprintf(EMAIL_PRODUCT_REVIEW_CONTENT_INTRO, $product_info->fields['products_name']) . "\n\n" ;
      $email_text .= sprintf(EMAIL_PRODUCT_REVIEW_CONTENT_DETAILS, $review_text)."\n\n";
      $email_subject = sprintf(EMAIL_REVIEW_PENDING_SUBJECT,$product_info->fields['products_name']);
      $html_msg['EMAIL_SUBJECT'] = sprintf(EMAIL_REVIEW_PENDING_SUBJECT,$product_info->fields['products_name']);
      $html_msg['EMAIL_MESSAGE_HTML'] = str_replace('\n','',sprintf(EMAIL_PRODUCT_REVIEW_CONTENT_INTRO, $product_info->fields['products_name']));
      $html_msg['EMAIL_MESSAGE_HTML'] .= '<br />';
      $html_msg['EMAIL_MESSAGE_HTML'] .= str_replace('\n','',sprintf(EMAIL_PRODUCT_REVIEW_CONTENT_DETAILS, $review_text));
      $extra_info=email_collect_extra_info($name,$email_address, $customer->fields['customers_firstname'] . ' ' . $customer->fields['customers_lastname'] , $customer->fields['customers_email_address'] );
      $html_msg['EXTRA_INFO'] = $extra_info['HTML'];
      zen_mail('', SEND_EXTRA_REVIEW_NOTIFICATION_EMAILS_TO, $email_subject ,
      $email_text . $extra_info['TEXT'], STORE_NAME, EMAIL_FROM, $html_msg, 'reviews_extra');
    }
    // end send email
   }
    zen_redirect(zen_href_link(FILENAME_PRODUCT_REVIEWS, zen_get_all_get_params(array('action'))));
  }
}

$products_price = zen_get_products_display_price($product_info->fields['products_id']);

$products_name = $product_info->fields['products_name'];

if ($product_info->fields['products_model'] != '') {
  $products_model = '<br /><span class="smallText">[' . $product_info->fields['products_model'] . ']</span>';
} else {
  $products_model = '';
}

// set image
//  $products_image = $product_info->fields['products_image'];
if ($product_info->fields['products_image'] == '' and PRODUCTS_IMAGE_NO_IMAGE_STATUS == '1') {
  $products_image = PRODUCTS_IMAGE_NO_IMAGE;
} else {
  $products_image = $product_info->fields['products_image'];
}

$breadcrumb->add(NAVBAR_TITLE);

// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_PRODUCT_REVIEWS_WRITE');
