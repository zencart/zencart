<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @copyright Originally Programmed By: Christopher Bradley (www.wizardsandwars.com) for OsCommerce
 * @copyright Modified by Jim Keebaugh for OsCommerce
 * @copyright Adapted for Zen Cart
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2019 Jul 13 Modified in v1.5.7 $
 */

define('NAVBAR_TITLE', 'Unsubscribe');
define('HEADING_TITLE', 'Unsubscribe from our Newsletter');

define('UNSUBSCRIBE_TEXT_INFORMATION', '<br />We\'re sorry to hear that you wish to unsubscribe from our newsletter. If you have concerns about your privacy, please see our <a href="' . zen_href_link(FILENAME_PRIVACY,'','NONSSL') . '"><span class="pseudolink">privacy notice</span></a>.<br /><br />Subscribers to our newsletter are kept notified of new products, price reductions, and site news.<br /><br />If you still do not wish to receive your newsletter, please click the button below. ');
define('UNSUBSCRIBE_TEXT_NO_ADDRESS_GIVEN', '<br />We\'re sorry to hear that you wish to unsubscribe from our newsletter. If you have concerns about your privacy, please see our <a href="' . zen_href_link(FILENAME_PRIVACY,'','NONSSL') . '"><span class="pseudolink">privacy notice</span></a>.<br /><br />Subscribers to our newsletter are kept notified of new products, price reductions, and site news.<br /><br />If you still do not wish to receive your newsletter, please click the button below. You will be taken to your account-preferences page, where you may edit your subscriptions. You may be prompted to log in first.');
define('UNSUBSCRIBE_DONE_TEXT_INFORMATION', '<br />Your email address, listed below, has been removed from our Newsletter Subscription list, as per your request. <br /><br />');
define('UNSUBSCRIBE_ERROR_INFORMATION', '<br />The email address you specified was not found in our newsletter database, or has already been removed from our newsletter subscription list. <br /><br />');
?>
