<?php
/**
 * zcDashboardWidgetNewCustomers Class.
 *
 * @package classes
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Scott Wilson Fri Aug 17 17:42:37 2012 +0100 New in v1.5.1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * zcDashboardWidgetNewCustomers Class
 *
 * @package classes
 */
class zcDashboardWidgetNewCustomers extends zcDashboardWidgetBase
{
  public function prepareContent()
  {
    global $db;

    $customers = $db->Execute("select c.customers_id as customers_id, c.customers_firstname as customers_firstname, c.customers_lastname as customers_lastname, c.customers_email_address as customers_email_address, a.customers_info_date_account_created as customers_info_date_account_created, a.customers_info_id from " . TABLE_CUSTOMERS . " c left join " . TABLE_CUSTOMERS_INFO . " a on c.customers_id = a.customers_info_id order by a.customers_info_date_account_created DESC limit 5");
    while (!$customers->EOF) {
      $name = $customers->fields['customers_firstname'] . ' ' . $customers->fields['customers_lastname'];
      $date_created = zen_date_short($customers->fields['customers_info_date_account_created']);
      $tplVars['content'][] = array('text'=> '<a href="' . zen_href_link(FILENAME_CUSTOMERS, 'search=' . $customers->fields['customers_email_address'], 'NONSSL') . '">' . $name . '</a>', 'value'=>$date_created);
      $customers->MoveNext();
    }

    return $tplVars;
  }
}
