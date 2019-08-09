<?php
/**
 * disabled-upcoming products functions
 *
 * @copyright 2018
 * @license http://www.zen-cart.com/License/2_0.txt GNU Public License V2.0
 * @author mc12345678
 **/

function zen_set_disabled_upcoming_status($products_id, $status) {
    $sql = "UPDATE " . TABLE_PRODUCTS . "
              SET products_status = " . (int)$status . ", products_date_available = '0001-01-01' WHERE products_id = " . (int)$products_id;

    return $GLOBALS['db']->Execute($sql);
}

function zen_enable_disabled_upcoming() {

    $date_range = time();

    $zc_disabled_upcoming_date = date('Ymd', $date_range);

    $disabled_upcoming_query = "SELECT products_id
                                            FROM " . TABLE_PRODUCTS . "
                                            WHERE products_status = 0
                                            AND ((products_date_available <= " . $zc_disabled_upcoming_date . "
                                            AND products_date_available != '0001-01-01'))
                                            ";

    $disabled_upcoming = $GLOBALS['db']->Execute($disabled_upcoming_query);

    if ($disabled_upcoming->RecordCount() > 0) {
        while (!$disabled_upcoming->EOF) {
            zen_set_disabled_upcoming_status($disabled_upcoming->fields['products_id'], 1);
            $disabled_upcoming->MoveNext();
        }
    }
}
