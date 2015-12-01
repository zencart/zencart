<?php
/**
 * Header code file for the Account Newsletters page - To change customers Newsletter options
 *
 * @package page
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: header_php.php 3162 2006-03-11 01:39:16Z drbyte $
 */
if (!$_SESSION['customer_id']) {
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

  $messageStack->add_session('account', SUCCESS_NEWSLETTER_UPDATED, 'success');

  zen_redirect(zen_href_link(FILENAME_ACCOUNT, '', 'SSL'));
}

$breadcrumb->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_ACCOUNT, '', 'SSL'));
$breadcrumb->add(NAVBAR_TITLE_2);
?>
