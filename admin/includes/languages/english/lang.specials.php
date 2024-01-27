<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2023 Dec 08 Modified in v2.0.0-alpha1 $
*/

$define = [
    'HEADING_TITLE' => 'Specials',
    'TEXT_ADD_SPECIAL_SELECT' => 'Add Special by Selection',
    'TEXT_ADD_SPECIAL_PID' => 'Add Special by Product ID',
    'TEXT_SEARCH_SPECIALS' => 'Search current Specials',
    'TEXT_SPECIAL_ACTIVE' => 'Special Price Active',
    'TEXT_SPECIAL_INACTIVE' => 'Special Price Inactive',
    'TEXT_SPECIAL_STATUS_BY_DATE' => 'Status set by dates',
    'TEXT_SPECIALS_PRODUCT' => 'Product:',
    'TEXT_SPECIALS_SPECIAL_PRICE' => 'Special Price:',
    'TEXT_SPECIALS_AVAILABLE_DATE' => 'Date Special Active:',
    'TEXT_SPECIALS_EXPIRES_DATE' => 'Date Special Expires:',
    'TEXT_INFO_NEW_PRICE' => 'Special Price:',
    'TEXT_INFO_ORIGINAL_PRICE' => 'Original Price:',
    'TEXT_INFO_DISPLAY_PRICE' => 'Currently Displayed Price:',
    'TEXT_INFO_STATUS_CHANGED' => 'Status Changed:',
    'TEXT_INFO_HEADING_DELETE_SPECIALS' => 'Delete Special',
    'TEXT_INFO_DELETE_INTRO' => 'Are you sure you want to delete the Special Price for this product?',
    'WARNING_SPECIALS_PRE_ADD_PID_EMPTY' => 'Warning: No Product ID was specified.',
    'WARNING_SPECIALS_PRE_ADD_PID_DUPLICATE' => 'Warning: Product ID#%u already on Special.',
    'WARNING_SPECIALS_PRE_ADD_PID_NO_EXIST' => 'Warning: Product ID#%u does not exist.',
    'WARNING_SPECIALS_PRE_ADD_PID_GIFT' => 'Warning: Product ID#%u is a ' . '%%TEXT_GV_NAME%%' . '.',
    'TEXT_INFO_HEADING_PRE_ADD_SPECIALS' => 'Add Special Price by Product ID',
    'TEXT_INFO_PRE_ADD_INTRO' => 'You may add a Special Price by Product ID. This method may be appropriate for shops with many products if the selection page takes too long to render or selecting a product from the dropdown becomes unwieldy.',
    'TEXT_PRE_ADD_PRODUCTS_ID' => 'Enter the Product ID: ',
    'TEXT_SPECIALS_PRICE_NOTES_HEAD' => '<b>Notes:</b>', 
    'TEXT_SPECIALS_PRICE_NOTES_BODY' => '<li>Special Price may be a price (ex-tax). The decimal separator must be a "." (decimal-point), eg: <b>49.99</b>. The calculated percentage discount is shown next to the product\'s new price in the catalog.</li><li>Special Price may be a percentage discount, eg: <b>20%</b>.</li><li>Start/End dates are not obligatory. You may leave the expiry date empty for no expiration.</li><li>When dates are set, the status of the Special Price is automatically enabled/disabled accordingly.</li>',
    'ERROR_INVALID_ACTIVE_DATE' => 'The &quot;Active&quot; date is not valid, please re-enter.',
    'ERROR_INVALID_EXPIRES_DATE' => 'The &quot;Expires&quot; date is not valid, please re-enter.',
];

return $define;
