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
// $Id: gv_faq.php 4155 2006-08-16 17:14:52Z ajeh $
//

define('NAVBAR_TITLE', TEXT_GV_NAME . ' FAQ');
define('HEADING_TITLE', TEXT_GV_NAME . ' FAQ');

define('TEXT_INFORMATION', '<a name="Top"></a>
  <a href="'.zen_href_link(FILENAME_GV_FAQ,'faq_item=1','NONSSL').'">Purchasing ' . TEXT_GV_NAMES . '</a><br />
  <a href="'.zen_href_link(FILENAME_GV_FAQ,'faq_item=2','NONSSL').'">How to send ' . TEXT_GV_NAMES . '</a><br />
  <a href="'.zen_href_link(FILENAME_GV_FAQ,'faq_item=3','NONSSL').'">Buying with ' . TEXT_GV_NAMES . '</a><br />
  <a href="'.zen_href_link(FILENAME_GV_FAQ,'faq_item=4','NONSSL').'">Redeeming ' . TEXT_GV_NAMES . '</a><br />
  <a href="'.zen_href_link(FILENAME_GV_FAQ,'faq_item=5','NONSSL').'">When problems occur</a><br />
');
switch ($_GET['faq_item']) {
  case '1':
define('SUB_HEADING_TITLE','Purchasing ' . TEXT_GV_NAMES);
define('SUB_HEADING_TEXT', TEXT_GV_NAMES . ' are purchased just like any other item in our store. You can
  pay for them using the stores standard payment method(s).
  Once purchased the value of the ' . TEXT_GV_NAME . ' will be added to your own personal
   ' . TEXT_GV_NAME . ' Account. If you have funds in your ' . TEXT_GV_NAME . ' Account, you will
  notice that the amount now shows in the Shopping Cart box, and also provides a
  link to a page where you can send the ' . TEXT_GV_NAME . ' to some one via email.');
  break;
  case '2':
define('SUB_HEADING_TITLE','How to Send ' . TEXT_GV_NAMES);
define('SUB_HEADING_TEXT','To send a ' . TEXT_GV_NAME . ' you need to go to our Send ' . TEXT_GV_NAME . ' Page. You can
  find the link to this page in the Shopping Cart Box in the right hand column of each page.
  When you send a ' . TEXT_GV_NAME . ', you need to specify the following.
  The name of the person you are sending the ' . TEXT_GV_NAME . ' to.
  The email address of the person you are sending the ' . TEXT_GV_NAME . ' to.
  The amount you want to send. (Note you don\'t have to send the full amount that
  is in your ' . TEXT_GV_NAME . ' Account.)
  A short message which will appear in the email.
  Please ensure that you have entered all of the information correctly, although
  you will be given the opportunity to change this as much as you want before
  the email is actually sent.');
  break;
  case '3':
  define('SUB_HEADING_TITLE','Buying with ' . TEXT_GV_NAMES);
  define('SUB_HEADING_TEXT','If you have funds in your ' . TEXT_GV_NAME . ' Account, you can use those funds to
  purchase other items in our store. At the checkout stage, an extra box will
  appear. Enter the amount to apply from the funds in your ' . TEXT_GV_NAME . ' Account.
  Please note, you will still have to select another payment method if there
  is not enough in your ' . TEXT_GV_NAME . ' Account to cover the cost of your purchase.
  If you have more funds in your ' . TEXT_GV_NAME . ' Account than the total cost of
  your purchase the balance will be left in your ' . TEXT_GV_NAME . ' Account for the
  future.');
  break;
  case '4':
  define('SUB_HEADING_TITLE','Redeeming ' . TEXT_GV_NAMES);
  define('SUB_HEADING_TEXT','If you receive a ' . TEXT_GV_NAME . ' by email it will contain details of who sent
  you the ' . TEXT_GV_NAME . ', along with possibly a short message from them. The email
  will also contain the ' . TEXT_GV_NAME . ' ' . TEXT_GV_REDEEM . '. It is probably a good idea to print
  out this email for future reference. You can now redeem the ' . TEXT_GV_NAME . ' in
  two ways.<br /><br />
  1. By clicking on the link contained within the email for this express purpose.
  This will take you to the store\'s Redeem ' . TEXT_GV_NAME . ' page. You will then be requested
  to create an account, before the ' . TEXT_GV_NAME . ' is validated and placed in your
   ' . TEXT_GV_NAME . ' Account ready for you to spend it on whatever you want.<br /><br />
  2. During the checkout process, on the same page that you select a payment method
there will be a box to enter a ' . TEXT_GV_REDEEM . '. Enter the ' . TEXT_GV_REDEEM . ' here, and
click the Redeem button. The code will be
validated and the amount added to your ' . TEXT_GV_NAME . ' Account. You can then use the amount to purchase any item from our store');
  break;
  case '5':
  define('SUB_HEADING_TITLE','When problems occur.');
  define('SUB_HEADING_TEXT','For any queries regarding the ' . TEXT_GV_NAME . ' System, please contact the store
  by email at '. STORE_OWNER_EMAIL_ADDRESS . '. Please make sure you give
  as much information as possible in the email. ');
  break;
  default:
  define('SUB_HEADING_TITLE','');
  define('SUB_HEADING_TEXT','Please choose from one of the questions above.');

  }

  define('TEXT_GV_REDEEM_INFO', 'Please enter your ' . TEXT_GV_NAME . ' redemption code: ');
  define('TEXT_GV_REDEEM_ID', 'Redemption Code:');
?>