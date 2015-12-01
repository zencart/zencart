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
//  $Id: salemaker.php 6369 2007-05-25 03:03:42Z ajeh $
//

define('HEADING_TITLE', 'SaleMaker');
define('TABLE_HEADING_SALE_NAME', 'SaleName');
define('TABLE_HEADING_SALE_DEDUCTION', 'Deduction');
define('TABLE_HEADING_SALE_DATE_START', 'Startdate');
define('TABLE_HEADING_SALE_DATE_END', 'Enddate');
define('TABLE_HEADING_STATUS', 'Status');
define('TABLE_HEADING_ACTION', 'Action');
define('TEXT_SALEMAKER_NAME', 'SaleName:');
define('TEXT_SALEMAKER_DEDUCTION', 'Deduction:');
define('TEXT_SALEMAKER_DEDUCTION_TYPE', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Type:&nbsp;&nbsp;');
define('TEXT_SALEMAKER_PRICERANGE_FROM', 'Products Pricerange:');
define('TEXT_SALEMAKER_PRICERANGE_TO', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;To&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
define('TEXT_SALEMAKER_SPECIALS_CONDITION', 'If a product is a Special:');
define('TEXT_SALEMAKER_DATE_START', 'Start Date:');
define('TEXT_SALEMAKER_DATE_END', 'End Date:');
define('TEXT_SALEMAKER_CATEGORIES', '<b>Or</b> check the categories to which this sale applies:');
define('TEXT_SALEMAKER_POPUP', '<a href="javascript:session_win();"><span class="errorText"><b>Click here for Salemaker Usage Tips.</b></span></a>');
define('TEXT_SALEMAKER_POPUP1', '<a href="javascript:session_win1();"><span class="errorText"><b>(More Info)</b></span></a>');
define('TEXT_SALEMAKER_IMMEDIATELY', 'Immediately');
define('TEXT_SALEMAKER_NEVER', 'Never');
define('TEXT_SALEMAKER_ENTIRE_CATALOG', 'Check this box if you want the sale to be applied to <b>all products</b>:');
define('TEXT_SALEMAKER_TOP', 'Entire catalog');
define('TEXT_INFO_DATE_ADDED', 'Date Added:');
define('TEXT_INFO_DATE_MODIFIED', 'Last Modified:');
define('TEXT_INFO_DATE_STATUS_CHANGE', 'Last Status Change:');
define('TEXT_INFO_SPECIALS_CONDITION', 'Specials Condition:');
define('TEXT_INFO_DEDUCTION', 'Deduction:');
define('TEXT_INFO_PRICERANGE_FROM', 'Pricerange:');
define('TEXT_INFO_PRICERANGE_TO', ' to ');
define('TEXT_INFO_DATE_START', 'Starts:');
define('TEXT_INFO_DATE_END', 'Expires:');
define('SPECIALS_CONDITION_DROPDOWN_0', 'Ignore Specials Price - Apply to Product Price and Replace Special');
define('SPECIALS_CONDITION_DROPDOWN_1', 'Ignore SaleCondition - No Sale Applied When Special Exists');
define('SPECIALS_CONDITION_DROPDOWN_2', 'Apply SaleDeduction to Specials Price - Otherwise Apply to Price');
// moved to english.php
/*
define('DEDUCTION_TYPE_DROPDOWN_0', 'Deduct amount');
define('DEDUCTION_TYPE_DROPDOWN_1', 'Percent');
define('DEDUCTION_TYPE_DROPDOWN_2', 'New Price');
*/
define('TEXT_INFO_HEADING_COPY_SALE', 'Copy Sale');
define('TEXT_INFO_COPY_INTRO', 'Enter a name for the copy of<br>&nbsp;&nbsp;"%s"');
define('TEXT_INFO_HEADING_DELETE_SALE', 'Delete Sale');
define('TEXT_INFO_DELETE_INTRO', 'Are you sure you want to permanently delete this sale?');
define('TEXT_MORE_INFO', '(More Info)');

define('TEXT_WARNING_SALEMAKER_PREVIOUS_CATEGORIES','&nbsp;Warning : %s sales already include this category');
?>