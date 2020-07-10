<?php
/**
 * admin subtemplate for Paypal Website Payments Standard payment method
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @copyright Portions Copyright 2004 DevosC.com
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 16 Modified in v1.5.7 $
 */

// strip slashes in case they were added to handle apostrophes:
  foreach ($ipn->fields as $key=>$value){
    $ipn->fields[$key] = stripslashes($value);
  }

// display all paypal status fields (in admin Orders page):
          $output = '<table>'."\n";
          $output .= '<tr style="background-color : #cccccc; border: 1px solid black;">'."\n";

          $output .= '<td valign="top"><table>'."\n";

          $output .= '<tr><td class="main">'."\n";
          $output .= MODULE_PAYMENT_PAYPAL_ENTRY_FIRST_NAME."\n";
          $output .= '</td><td class="main">'."\n";
          $output .= $ipn->fields['first_name']."\n";
          $output .= '</td></tr>'."\n";

          $output .= '<tr><td class="main">'."\n";
          $output .= MODULE_PAYMENT_PAYPAL_ENTRY_LAST_NAME."\n";
          $output .= '</td><td class="main">'."\n";
          $output .= $ipn->fields['last_name']."\n";
          $output .= '</td></tr>'."\n";

          $output .= '<tr><td class="main">'."\n";
          $output .= MODULE_PAYMENT_PAYPAL_ENTRY_BUSINESS_NAME."\n";
          $output .= '</td><td class="main">'."\n";
          $output .= $ipn->fields['payer_business_name']."\n";
          $output .= '</td></tr>'."\n";

          $output .= '<tr><td class="main">'."\n";
          $output .= MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_NAME."\n";
          $output .= '</td><td class="main">'."\n";
          $output .= $ipn->fields['address_name']."\n";
          $output .= '</td></tr>'."\n";
          $output .= '<tr><td class="main">'."\n";
          $output .= MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_STREET."\n";
          $output .= '</td><td class="main">'."\n";
          $output .= $ipn->fields['address_street']."\n";
          $output .= '</td></tr>'."\n";
          $output .= '<tr><td class="main">'."\n";
          $output .= MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_CITY."\n";
          $output .= '</td><td class="main">'."\n";
          $output .= $ipn->fields['address_city']."\n";
          $output .= '</td></tr>'."\n";
          $output .= '<tr><td class="main">'."\n";
          $output .= MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_STATE."\n";
          $output .= '</td><td class="main">'."\n";
          $output .= $ipn->fields['address_state']."\n";
          $output .= '</td></tr>'."\n";
          $output .= '<tr><td class="main">'."\n";
          $output .= MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_COUNTRY."\n";
          $output .= '</td><td class="main">'."\n";
          $output .= $ipn->fields['address_country']."\n";
          $output .= '</td></tr>'."\n";

          $output .= '</table></td>'."\n";

          $output .= '<td valign="top"><table>'."\n";

          $output .= '<tr><td class="main">'."\n";
          $output .= MODULE_PAYMENT_PAYPAL_ENTRY_EMAIL_ADDRESS."\n";
          $output .= '</td><td class="main">'."\n";
          $output .= $ipn->fields['payer_email']."\n";
          $output .= '</td></tr>'."\n";

          $output .= '<tr><td class="main">'."\n";
          $output .= MODULE_PAYMENT_PAYPAL_ENTRY_EBAY_ID."\n";
          $output .= '</td><td class="main">'."\n";
          $output .= $ipn->fields['ebay_address_id']."\n";
          $output .= '</td></tr>'."\n";

          $output .= '<tr><td class="main">'."\n";
          $output .= MODULE_PAYMENT_PAYPAL_ENTRY_PAYER_ID."\n";
          $output .= '</td><td class="main">'."\n";
          $output .= $ipn->fields['payer_id']."\n";
          $output .= '</td></tr>'."\n";

          $output .= '<tr><td class="main">'."\n";
          $output .= MODULE_PAYMENT_PAYPAL_ENTRY_PAYER_STATUS."\n";
          $output .= '</td><td class="main">'."\n";
          $output .= $ipn->fields['payer_status']."\n";
          $output .= '</td></tr>'."\n";

          $output .= '<tr><td class="main">'."\n";
          $output .= MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_STATUS."\n";
          $output .= '</td><td class="main">'."\n";
          $output .= $ipn->fields['address_status']."\n";
          $output .= '</td></tr>'."\n";

          $output .= '<tr><td class="main">'."\n";
          $output .= MODULE_PAYMENT_PAYPAL_ENTRY_TXN_TYPE."\n";
          $output .= '</td><td class="main">'."\n";
          $output .= $ipn->fields['txn_type']."\n";
          $output .= '</td></tr>'."\n";

          $output .= '<tr><td class="main">'."\n";
          $output .= MODULE_PAYMENT_PAYPAL_ENTRY_TXN_ID."\n";
          $output .= '</td><td class="main">'."\n";
          $output .= '<a href="https://www.paypal.com/us/cgi-bin/webscr?cmd=_view-a-trans&id=' . $ipn->fields['txn_id'] . '" rel="noopener" target="_blank">' . $ipn->fields['txn_id'] . '</a>' ."\n";
          $output .= '</td></tr>'."\n";

          $output .= '<tr><td class="main">'."\n";
          $output .= MODULE_PAYMENT_PAYPAL_ENTRY_PARENT_TXN_ID."\n";
          $output .= '</td><td class="main">'."\n";
          $output .= $ipn->fields['parent_txn_id']."\n";
          $output .= '</td></tr>'."\n";

          $output .= '</table></td>'."\n";

          $output .= '<td valign="top"><table>'."\n";

          $output .= '<tr><td class="main">'."\n";
          $output .= MODULE_PAYMENT_PAYPAL_ENTRY_PAYMENT_TYPE."\n";
          $output .= '</td><td class="main">'."\n";
          $output .= $ipn->fields['payment_type']."\n";
          $output .= '</td></tr>'."\n";

          $output .= '<tr><td class="main">'."\n";
          $output .= MODULE_PAYMENT_PAYPAL_ENTRY_PAYMENT_STATUS."\n";
          $output .= '</td><td class="main">'."\n";
          $output .= $ipn->fields['payment_status']."\n";
          $output .= '</td></tr>'."\n";

          $output .= '<tr><td class="main">'."\n";
          $output .= MODULE_PAYMENT_PAYPAL_ENTRY_PENDING_REASON."\n";
          $output .= '</td><td class="main">'."\n";
          $output .= $ipn->fields['pending_reason']."\n";
          $output .= '</td></tr>'."\n";

          $output .= '<tr><td class="main">'."\n";
          $output .= MODULE_PAYMENT_PAYPAL_ENTRY_INVOICE."\n";
          $output .= '</td><td class="main">'."\n";
          $output .= $ipn->fields['invoice']."\n";
          $output .= '</td></tr>'."\n";

          $output .= '<tr><td class="main">'."\n";
          $output .= MODULE_PAYMENT_PAYPAL_ENTRY_PAYMENT_DATE."\n";
          $output .= '</td><td class="main">'."\n";
          $output .= zen_datetime_short($ipn->fields['payment_date'])."\n";
          $output .= '</td></tr>'."\n";

          $output .= '</table></td>'."\n";

          $output .= '<td valign="top"><table>'."\n";

          $output .= '<tr><td class="main">'."\n";
          $output .= MODULE_PAYMENT_PAYPAL_ENTRY_CURRENCY."\n";
          $output .= '</td><td class="main">'."\n";
          $output .= $ipn->fields['mc_currency']."\n";
          $output .= '</td></tr>'."\n";

          $output .= '<tr><td class="main">'."\n";
          $output .= MODULE_PAYMENT_PAYPAL_ENTRY_GROSS_AMOUNT."\n";
          $output .= '</td><td class="main">'."\n";
          $output .= $ipn->fields['mc_gross']."\n";
          $output .= '</td></tr>'."\n";

          $output .= '<tr><td class="main">'."\n";
          $output .= MODULE_PAYMENT_PAYPAL_ENTRY_PAYMENT_FEE."\n";
          $output .= '</td><td class="main">'."\n";
          $output .= $ipn->fields['mc_fee']."\n";
          $output .= '</td></tr>'."\n";

          $output .= '<tr><td class="main">'."\n";
          $output .= MODULE_PAYMENT_PAYPAL_ENTRY_EXCHANGE_RATE."\n";
          $output .= '</td><td class="main">'."\n";
          $output .= $ipn->fields['exchange_rate']."\n";
          $output .= '</td></tr>'."\n";

          $output .= '<tr><td class="main">'."\n";
          $output .= MODULE_PAYMENT_PAYPAL_ENTRY_CART_ITEMS."\n";
          $output .= '</td><td class="main">'."\n";
          $output .= $ipn->fields['num_cart_items']."\n";
          $output .= '</td></tr>'."\n";

          $output .= '</table></td>'."\n";

          $output .= '</tr>'."\n";
        if ($ipn->fields['memo'] != '') {
          $output .= '<tr style="background-color : #cccccc; border-style : dotted;">'."\n";
          $output .= '<td valign="top" colspan="4" ><table>'."\n";
          $output .= '<tr><td valign="top" class="main">'."\n";
          $output .= MODULE_PAYMENT_PAYPAL_ENTRY_COMMENTS."\n";
          $output .= '</td><td valign="top" class="main">'."\n";
          $output .= nl2br($ipn->fields['memo'])."\n";
          $output .= '</td></tr>'."\n";
          $output .= '</table></td>'."\n";
        }
          $output .= '</tr>'."\n";
          $output .='</table>'."\n";
