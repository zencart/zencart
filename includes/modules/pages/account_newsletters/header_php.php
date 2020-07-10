<?php
/**
 * Header code file for the Account Newsletters page - To change customers Newsletter options
 *
 * @package page
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: mc12345678 2019 Apr 30 Modified in v1.5.6b $
 */
// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_ACCOUNT_NEWSLETTERS');

if (!zen_is_logged_in()) {
  $_SESSION['navigation']->set_snapshot();
  zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
}

require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));

$newsletter_query = "SELECT customers_newsletter
                     FROM   " . TABLE_CUSTOMERS . "
                     WHERE  customers_id = :customersID";

$newsletter_query = $db->bindVars($newsletter_query, ':customersID',$_SESSION['customer_id'], 'integer');
$newsletter = $db->Execute($newsletter_query);

if (isset($_POST['action']) && ($_POST['action'] == 'process')) {
  if (isset($_POST['newsletter_general']) && is_numeric($_POST['newsletter_general'])) {
    $newsletter_general = zen_db_prepare_input($_POST['newsletter_general']);
  } else {
    $newsletter_general = '0';
  }

  if ($newsletter_general != $newsletter->fields['customers_newsletter']) {
    $newsletter_general = (($newsletter->fields['customers_newsletter'] == '1') ? '0' : '1');

    $sql = "UPDATE " . TABLE_CUSTOMERS . "
            SET    customers_newsletter = :customersNewsletter
            WHERE  customers_id = :customersID";

    $sql = $db->bindVars($sql, ':customersID',$_SESSION['customer_id'], 'integer');
    $sql = $db->bindVars($sql, ':customersNewsletter',$newsletter_general, 'integer');
    $db->Execute($sql);
  }

  $zco_notifier->notify('NOTIFY_HEADER_ACCOUNT_NEWSLETTER_UPDATED', $newsletter_general);

  $messageStack->add_session('newsletter', SUCCESS_NEWSLETTER_UPDATED, 'success');

  zen_redirect(zen_href_link(FILENAME_ACCOUNT_NEWSLETTERS, '', 'SSL'));
}

$breadcrumb->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_ACCOUNT, '', 'SSL'));
$breadcrumb->add(NAVBAR_TITLE_2);

// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_ACCOUNT_NEWSLETTERS');

