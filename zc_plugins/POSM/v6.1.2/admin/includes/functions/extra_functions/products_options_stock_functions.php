<?php
// -----
// Part of the "Product Options Stock Manager" plugin by Cindy Merkin (cindy@vinosdefrutastropicales.com)
// Copyright (c) 2014-2024 Vinos de Frutas Tropicales
//
// Last updated: POSM 5.0.0
//

// -----
// Used by invoice.php and packingslip.php to extract the installation-specific "stock type" from the product's name.
//
// When displaying on the above pages, the POSM configuration controls whether/not the stock message is included. When
// used by products_options_sales_report, the messages are not included so that different stock levels don't
// show up in the report as multiple products.
//
function pos_extract_stock_type($products_name, bool $messages_never_included = false): string
{
    global $posObserver;

    if (preg_match('/(.*)\[(.*)\]$/', $products_name, $matches)) {
        $products_name = $matches[1];
        if ($messages_never_included === false && $posObserver->show_stock_messages === true) {
            $products_name .= '<br>' . zen_draw_checkbox_field('check') . ' ' . str_replace(',', ' ' . zen_draw_checkbox_field('check2'), $matches[2]);
        }
    }
    return $products_name;
}

// -----
// Used to determine if a given model-number is used either in a product definition or *another* POSM-managed product entry.
//
function posm_modelnum_is_duplicate($pos_id, $model): bool
{
    global $db;

    $pos_id = (int)$pos_id;
    $model = zen_db_input($model);
    $check = $db->Execute(
        "SELECT p.products_id
           FROM " . TABLE_PRODUCTS_OPTIONS_STOCK . " pos
                LEFT JOIN " . TABLE_PRODUCTS . " p
                    ON p.products_id = pos.products_id
          WHERE p.products_status = 1
            AND (p.products_model = '$model' OR (pos.pos_id != $pos_id AND pos.pos_model = '$model'))
          LIMIT 1"
    );
    return !$check->EOF;
}

// -----
// Updates a 'base' product's quantity to be the sum of all POSM variants' quantities ...
// so long as the product is POSM-managed!
//
function posm_update_base_product_quantity($pID)
{
    global $db;

    $quantity_sum = $db->Execute(
        "SELECT SUM(products_quantity) as quantity
           FROM " . TABLE_PRODUCTS_OPTIONS_STOCK . "
          WHERE products_id = $pID"
    );
    $products_quantity = $quantity_sum->fields['quantity'];
    if ($products_quantity !== null) {
        $db->Execute(
            "UPDATE " . TABLE_PRODUCTS . "
                SET products_quantity = " . $products_quantity . "
              WHERE products_id = $pID
              LIMIT 1"
        );
    }
    return $products_quantity;
}
