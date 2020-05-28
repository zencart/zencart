<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 11 New in v1.5.7 $
 */

if (!zen_is_superuser() && !check_page(FILENAME_CUSTOMERS, '')) return;

// to disable this module for everyone, uncomment the following "return" statement so the rest of this file is ignored
// return;

$maxRows = 15;

$sql = "SELECT c.customers_id as customers_id, c.customers_firstname as customers_firstname,
                   c.customers_lastname as customers_lastname, c.customers_email_address as customers_email_address,
                   a.customers_info_date_account_created as customers_info_date_account_created, a.customers_info_id
            FROM " . TABLE_CUSTOMERS . " c
            LEFT JOIN " . TABLE_CUSTOMERS_INFO . " a ON c.customers_id = a.customers_info_id
            ORDER BY a.customers_info_date_account_created DESC";
$customers = $db->Execute($sql, (int)$maxRows, true, 1800);

?>

<div class="panel panel-default reportBox">
    <div class="panel-heading header"><?php echo BOX_ENTRY_NEW_CUSTOMERS; ?> </div>
    <table class="table table-striped table-condensed">
    <?php
        foreach ($customers as $customer) {
          $customer['customers_firstname'] = zen_output_string_protected($customer['customers_firstname']);
          $customer['customers_lastname'] = zen_output_string_protected($customer['customers_lastname']);
          ?>
        <tr>
          <td>
            <a href="<?php echo zen_href_link(FILENAME_CUSTOMERS, 'search=' . $customer['customers_email_address'] . '&origin=' . FILENAME_DEFAULT); ?>" class="contentlink">
                <?php echo $customer['customers_firstname'] . ' ' . $customer['customers_lastname']; ?>
            </a>
          </td>
          <td class="text-right"><?php echo zen_date_short($customer['customers_info_date_account_created']); ?></td>
        </tr>
    <?php
      }
    ?>
    </table>
</div>
