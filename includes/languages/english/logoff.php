<?php
/**
 * @package languageDefines
 * @copyright Copyright 2003-2007 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: logoff.php 6992 2007-09-13 02:54:24Z ajeh $
 * @version $Id: logoff.php 6992 2007-09-13 02:54:24Z ajeh $ Integrated COWOA v2.2 - 2007 - 2012
 */

define('HEADING_TITLE', 'Log Off');
define('NAVBAR_TITLE', 'Log Off');
define('TEXT_MAIN', 'You have been logged out. It is now safe to leave the computer.<br /><br />If you had items in your cart, they have been saved. The items inside it will be restored when you <a href="' . zen_href_link(FILENAME_LOGIN, '', 'SSL') . '"><span class="pseudolink">log back into your account</span></a>.<br />');
// eof