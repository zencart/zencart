<?php
// -----
// Part of the "Product Options Stock Manager" plugin by Cindy Merkin
// Copyright (c) 2022 Vinos de Frutas Tropicales
//
// Last updated: POSM v4.4.0
//
class zcAjaxProductsOptionsStockAdmin extends base
{
    // -----
    // Check to see if a submitted model number is a duplicate.
    //
    // $_POST['model_num'] ... The model number to check
    // $_POST['pos_id'] ...... The pos_id associated with the option-combination being checked.
    //
    // Returns isOk, indicating whether (true) or not (false) the model number is an acceptable length
    // and is not a duplicate.
    //
    public function isModelDuplicate()
    {
        global $db;

        $model_is_ok = false;
        if (isset($_POST['model_num']) && isset($_POST['pos_id']) && strlen($_POST['model_num']) <= zen_field_length (TABLE_PRODUCTS, 'products_model')) {
            $check_sql =
                "SELECT p.products_id
                   FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_OPTIONS_STOCK . " pos
                  WHERE p.products_model = :productsModel:
                     OR (pos.pos_model = :productsModel: AND pos.pos_id != :posID:)
                  LIMIT 1";
            $check_sql = $db->bindVars($check_sql, ':productsModel:', $_POST['model_num'], 'string');
            $check_sql = $db->bindVars($check_sql, ':posID:', $_POST['pos_id'], 'integer');
            $check = $db->Execute($check_sql);
            $model_is_ok = $check->EOF;
        }

        // -----
        // Return the indicator to identify whether/not the model number is OK.
        //
        return [
            'isOk' => $model_is_ok,
        ];
    }
}
