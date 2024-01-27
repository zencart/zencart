<?php
/**
 * salemaker functions
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: jarndt 2023 Jun 03 Modified in v2.0.0-alpha1 $
 */

/**
 * Set the status of a salemaker sale
 * @param int $sale_id
 * @param int $status 0|1
 * @return queryFactoryResult
 */
function zen_set_salemaker_status($sale_id, $status)
{
    global $db;
    $sql = "UPDATE " . TABLE_SALEMAKER_SALES . "
            SET sale_status = " . (int)$status . ", sale_date_status_change = now()
            WHERE sale_id = " . (int)$sale_id;

    return $db->Execute($sql);
}

/**
 * Auto expire salemaker sales
 */
function zen_expire_salemaker()
{
    global $db;

    $sale_date = date('Y-m-d', time());

    $sql = "SELECT sale_id
            FROM " . TABLE_SALEMAKER_SALES . "
            WHERE sale_status = 1
            AND (
             ('" . $sale_date . "' >= sale_date_end AND sale_date_end != '0001-01-01')
             OR
             ('" . $sale_date . "' < sale_date_start AND sale_date_start != '0001-01-01')
            )";

    $results = $db->Execute($sql);

    foreach ($results as $result) {
        zen_set_salemaker_status($result['sale_id'], 0);
        zen_update_salemaker_product_prices($result['sale_id']);
    }
}

/**
 * Auto start salemaker sales
 */
function zen_start_salemaker()
{
    global $db;

    $sale_date = date('Y-m-d', time());

    $sql = "SELECT sale_id
            FROM " . TABLE_SALEMAKER_SALES . "
            WHERE sale_status = 0
            AND (
            (
                (sale_date_start <= '" . $sale_date . "' AND sale_date_start != '0001-01-01')
                AND
                (sale_date_end > '" . $sale_date . "')
            )
            OR
            (
                (sale_date_start <= '" . $sale_date . "' AND sale_date_start != '0001-01-01')
                AND
                (sale_date_end <= '0001-01-01')
            )
            OR (sale_date_start <= '0001-01-01' AND sale_date_end > '" . $sale_date . "')
            )
            ";

    $results = $db->Execute($sql);

    foreach ($results as $result) {
        zen_set_salemaker_status($result['sale_id'], 1);
        zen_update_salemaker_product_prices($result['sale_id']);
    }

    // turn off salemaker sales if not active yet
    $sql = "SELECT sale_id
            FROM " . TABLE_SALEMAKER_SALES . "
            WHERE sale_status = 1
            AND ('" . $sale_date . "' < sale_date_start AND sale_date_start != '0001-01-01')
            ";

    $results = $db->Execute($sql);

    foreach ($results as $result) {
        zen_set_salemaker_status($result['sale_id'], 0);
        zen_update_salemaker_product_prices($result['sale_id']);
    }
}
