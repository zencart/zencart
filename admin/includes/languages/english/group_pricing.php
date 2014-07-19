<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2004 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                                 |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
//  $Id: group_pricing.php 2770 2006-01-02 07:52:42Z drbyte $
//

define('HEADING_TITLE', 'Group Pricing');

define('TABLE_HEADING_GROUP_ID', 'ID');
define('TABLE_HEADING_GROUP_NAME', 'Group Name');
define('TABLE_HEADING_GROUP_AMOUNT', '% Discount');
define('TABLE_HEADING_ACTION', 'Action');

define('TEXT_HEADING_NEW_PRICING_GROUP', 'New Pricing Group');
define('TEXT_HEADING_EDIT_PRICING_GROUP', 'Edit Pricing Group');
define('TEXT_HEADING_DELETE_PRICING_GROUP', 'Delete Pricing Group');

define('TEXT_NEW_INTRO', 'Please fill out the following information for the new group');
define('TEXT_EDIT_INTRO', 'Please make any necessary changes');
define('TEXT_DELETE_INTRO', 'Are you sure you want to delete this group?');
define('TEXT_DELETE_PRICING_GROUP', 'Delete Pricing Group');
define('TEXT_DELETE_WARNING_GROUP_MEMBERS','<b>WARNING:</b> There are %s customers still linked to this category!');

define('TEXT_GROUP_PRICING_NAME', 'Group Name: ');
define('TEXT_GROUP_PRICING_AMOUNT', 'Percentage Discount: ');
define('TEXT_DATE_ADDED', 'Date Added:');
define('TEXT_LAST_MODIFIED', 'Date Modified:');
define('TEXT_CUSTOMERS', 'Customers in Group:');

define('ERROR_GROUP_PRICING_CUSTOMERS_EXIST','ERROR: Customers exist in that group. Please confirm that you wish to remove all members from the group and delete it.');
define('ERROR_MODULE_NOT_CONFIGURED','NOTE: You have group pricing definitions, but you have not enabled the group-pricing Order Total module.<br />Please go to Admin->Modules->Order Total->Membership Discount (ot_group_pricing) and install/configure the module.');

?>