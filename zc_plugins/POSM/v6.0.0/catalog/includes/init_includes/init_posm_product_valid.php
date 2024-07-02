<?php
// -----
// Part of the "Product Options Stock Manager" plugin by Cindy Merkin (cindy@vinosdefrutastropicales.com)
// Copyright (c) 2014-2021 Vinos de Frutas Tropicales
//
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

if (isset($_GET['action']) && $_GET['action'] == 'add_product' && isset($_GET['products_id']) && is_pos_product($_GET['products_id'])) {
    $posm_product_ok = false;
    $products_id = (int)$_GET['products_id'];
    
    // -----
    // If this is a "normal" product with attributes, check that single product's validity.
    //
    if (isset($_POST['id']) && is_array($_POST['id'])) {
        // -----
        // 'Coerce' the add_product checkbox-type attributes into the format present in the shopping cart,
        // as expected by generate_pos_option_hash.
        //
        $attributes = array();
        foreach ($_POST['id'] as $option => $value) {
            if (!is_array($value)) {
                $attributes[$option] = $value;
            } else {
                foreach ($value as $next_value) {
                    $attributes[$option . '_chk' . $next_value] = $next_value;
                }
            }
        }
        
        $hash = generate_pos_option_hash($products_id, $attributes);
        $posm_check = $db->Execute(
            "SELECT pos_id, products_quantity, pos_model, pos_name_id, pos_date 
               FROM " . TABLE_PRODUCTS_OPTIONS_STOCK . "
              WHERE products_id = $products_id
                AND pos_hash = '$hash' 
              LIMIT 1"
        );
        if (!$posm_check->EOF) {
            $posm_product_ok = true;
        }
    // -----
    // Otherwise, if this is an "Attribute Image Swatch" product, then multiple attributed products
    // can be added to the cart in the same "push".
    //
    } elseif (isset($_POST['attrib_swatch_qty']) && is_array($_POST['attrib_swatch_qty'])) {
        $valid_options = 0;
        $num_selected = 0;
        foreach ($_POST['attrib_swatch_qty'] as $options => $qty) {
            if (((int)$qty) > 0) {
                $num_selected++;
                $options_list = explode('-', $options);
                $hash = generate_pos_option_hash($products_id, array($options_list[0] => $options_list[1]));
                $posm_check = $db->Execute(
                    "SELECT pos_id, products_quantity, pos_model, pos_name_id, pos_date 
                       FROM " . TABLE_PRODUCTS_OPTIONS_STOCK . "
                      WHERE products_id = $products_id
                        AND pos_hash = '$hash' 
                      LIMIT 1"
                );
                if (!$posm_check->EOF) {
                    $valid_options++;
                }
            }
        }
        if ($valid_options == $num_selected) {
            $posm_product_ok = true;
        }
    }
    
    if (!$posm_product_ok) {
        unset($_GET['action']);
        $messageStack->add_session('product_info', POSM_ERROR_INVALID_VARIANT, 'error');
    }
}