<?php
// -----
// Part of the "Product Options Stock Manager" plugin by Cindy Merkin (cindy@vinosdefrutastropicales.com)
// Copyright (c) 2014-2024 Vinos de Frutas Tropicales
//
// Last updated: POSM 5.0.0
//
$products_options_stock_in_stock = 'In Stock';
$define = [
    'PRODUCTS_OPTIONS_STOCK_WRAPPER' => '<div class="stock-wrapper">%s</div>',
    'PRODUCTS_OPTIONS_STOCK_STOCK_HTML' => '<div class="stock-msg %1$s">%2$s</div>',
    'PRODUCTS_OPTIONS_STOCK_STOCK_TEXT' => ' [%s]',
    'PRODUCTS_OPTIONS_STOCK_IN_STOCK' => $products_options_stock_in_stock,
        'PRODUCTS_OPTIONS_STOCK_IN_STOCK_QTY' => '%u ' . $products_options_stock_in_stock,  //-The %u (required) is the place-holder for the in-stock quantity
    'PRODUCTS_OPTIONS_STOCK_NOT_IN_STOCK' => 'Back-ordered',
        'PRODUCTS_OPTIONS_STOCK_MIXED' => '%u %s, %u %s',

    // -----
    // If your store has products with multiple options and you've enabled the 'Dependent Attributes: Insert "Please Choose"?' setting,
    // the following constants are used.
    //
    // The "First Choose" text is applied to the first option (if it's a drop-down) and the "Next Choose" text is
    // applied to all subsequent options.
    //
    'PRODUCTS_OPTIONS_STOCK_PLEASE_CHOOSE' => 'First Choose &hellip;',
    'PRODUCTS_OPTIONS_STOCK_PLEASE_CHOOSE_NEXT' => 'Next Choose &hellip;',

    'PRODUCTS_OPTIONS_STOCK_RADIO_BUTTON_CHOOSE' => ' (choose all previous options to view selections)',

    'POS_EMAIL_TEXT_SUBJECT_LOWSTOCK' => "Warning: Low Products' Options' Stock",
    'POS_SEND_EXTRA_LOW_STOCK_EMAIL_TITLE' => "Low Products' Options' Stock Report: ",

    // -- %1$ ... product's ID
    // -- %2$ ... product's model
    // -- %3$ ... product's name
    // -- %4$ ... product's stock attributes list
    // -- %5$ ... remaining quantity
    'POS_LOW_STOCK_EMAIL_CONTENTS' => "Low Products' Options' Stock Report:\n\nID# %1\$u\t\t%2\$s\n%3\$s (%4\$s)\nQty Left: %5\$u\n",

    'ERROR_LIMITED_STOCK_CART_REDUCTION' => 'The quantity of <b>%s</b> has been adjusted to %u in your cart, due to stock limitations.',

    'ERROR_INVALID_VARIABLES' => 'Invalid inputs received, please contact the store owner [msgCode: %u]',

    'JS_ERROR_NO_SELECTION' => 'Please choose a ',  //-appended with the name of the option that needs a selection by jscript_posm_dependencies.php

    // -----
    // This message is issued if an add-to-cart action includes a POSM-managed product ... but the requested option-combination isn't configured.
    //
    'POSM_ERROR_INVALID_VARIANT' => 'The option-combinations that you chose are not available; please update your choices and try again.',
];
return $define;
