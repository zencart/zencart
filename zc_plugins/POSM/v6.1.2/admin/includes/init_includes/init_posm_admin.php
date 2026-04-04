<?php
// -----
// Part of the "Product Options Stock Manager" plugin by Cindy Merkin (cindy@vinosdefrutastropicales.com)
// Copyright (c) 2014-2024 Vinos de Frutas Tropicales
//
// Last updated: POSM 5.0.0
//
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

// -----
// Since a POSM products' options' stock-levels are "micro-managed", make sure that the "Product Qty Min/Unit Max" value is set to "No".  If not,
// then the in_cart_mixed checks might would cause two "In Stock" variants (each of quantity 1) to disallow checkout since the sum of the variants'
// quantity is greater than either individual quantity.
//
$db->Execute(
    "UPDATE " . TABLE_PRODUCTS . " p, (SELECT DISTINCT products_id FROM " . TABLE_PRODUCTS_OPTIONS_STOCK . ") posm_pids
        SET p.products_quantity_mixed = 0
      WHERE p.products_id = posm_pids.products_id"
);

// -----
// Starting with v2.2.0 of POSM, the changes to the "Edit Orders" functions to add notifiers for POSM's proper operation are no longer included.
// Instead, check to see if Edit Orders is installed and, if so, that it's at version 4.2.0 or later (the version
// at which those required notifiers were added).
//
if (defined('EO_VERSION') && version_compare(EO_VERSION, '4.2.0', '<')) {
    $messageStack->add(sprintf(POSM_EO_DOWNLEVEL, EO_VERSION), 'warning');
}

// -----
// Starting with v2.3.0 of POSM, check (if enabled) to see if any back-in-stock dates are within the expiration period.
//
if (((int)POSM_BIS_DATE_REMINDER) !== 0) {
    $posm_check = $db->Execute (
        "SELECT pos.pos_id
           FROM " . TABLE_PRODUCTS_OPTIONS_STOCK . " pos
             LEFT JOIN " . TABLE_PRODUCTS_OPTIONS_STOCK_NAMES . " posn
                ON posn.pos_name_id = pos.pos_name_id
          WHERE posn.pos_name LIKE '%[date]%'
            AND pos.pos_date < DATE_SUB(now(), INTERVAL " . (int)POSM_BIS_DATE_REMINDER . " DAY)
          LIMIT 1"
    );
    if (!$posm_check->EOF) {
        $messageStack->add(sprintf(POSM_BIS_DATES_EXPIRED, (int)POSM_BIS_DATE_REMINDER, zen_href_link(FILENAME_PRODUCTS_OPTIONS_STOCK)), 'warning');
    }
}

// -----
// Finally, check that the built-in stock-related settings are set "properly" in Configuration->Stock and adjust, if necessary:
//
// - Subtract stock ............................... true
//
$configuration_array = [
    'STOCK_LIMITED' => 'true'
];
foreach ($configuration_array as $key => $value) {
    if (constant($key) !== $value) {
        $db->Execute(
            "UPDATE " . TABLE_CONFIGURATION . "
                SET configuration_value = '$value'
              WHERE configuration_key = '$key'
              LIMIT 1"
        );
    }
}
unset($configuration_array);
