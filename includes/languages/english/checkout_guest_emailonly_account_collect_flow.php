<?php
/**
 * @package languageDefines
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: New in V1.6.0 $
 */
define('NAVBAR_TITLE_1', 'Checkout');
define('NAVBAR_TITLE_2', 'Address Details');

define('TEXT_ORIGIN_LOGIN', 'If you already have an account with us, you can <a href="%s"><u>Login here</u></a>.');
define('TITLE_CONTINUE_CHECKOUT_PROCEDURE', 'Continue to next step');
define('TEXT_CONTINUE_CHECKOUT_PROCEDURE', '- review your order.');

define('TABLE_HEADING_CONDITIONS', '<span class="termsconditions">Terms and Conditions</span>');
define('TEXT_CONDITIONS_DESCRIPTION', '<span class="termsdescription">Please acknowledge the terms and conditions bound to this order by ticking the following box. The terms and conditions can be read <a href="' . zen_href_link(FILENAME_CONDITIONS, '', 'SSL') . '"><span class="pseudolink">here</span></a>.');
define('TEXT_CONDITIONS_CONFIRM', '<span class="termsiagree">I have read and agreed to the terms and conditions bound to this order.</span>');
