<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
// $Id: gv_redeem.php 4155 2006-08-16 17:14:52Z ajeh $
//   * @version $Id: Integrated COWOA v2.2 - 2007 - 2012
//

define('NAVBAR_TITLE', 'Redeem ' . TEXT_GV_NAME);
define('HEADING_TITLE', 'Redeem ' . TEXT_GV_NAME);
define('TEXT_INFORMATION', 'For more information regarding ' . TEXT_GV_NAME . ', please see our <a href="' . zen_href_link(FILENAME_GV_FAQ, '', 'NONSSL').'">' . GV_FAQ . '.</a>');
define('TEXT_INVALID_GV', 'The ' . TEXT_GV_NAME . ' number %s may be invalid or has already been redeemed. To contact the shop owner please use the Contact Page');
define('TEXT_VALID_GV', 'Congratulations, you have redeemed a ' . TEXT_GV_NAME . ' worth %s.');

define('ERROR_GV_CREATE_ACCOUNT', 'To redeem a Gift Voucher you must create an account.');
define('ERROR_GV_COWOA', 'To redeem a Gift Voucher you must create an account.  You may not enter a Gift Voucher once you have begun checking out without an account. If you would like to use a Gift Voucher, you may <a href="' . zen_href_link(FILENAME_LOGOFF, '', 'SSL', false) . '">click here</a> to end your session, empty your cart, and begin again.');
// eof