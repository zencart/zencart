<?php
// -----
// Part of the "Product Options Stock Manager" plugin by Cindy Merkin (cindy@vinosdefrutastropicales.com)
// Copyright (c) 2015-2024 Vinos de Frutas Tropicales
//
// Last updated:  POSM v5.0.0
//
$define = [
    'HEADING_TITLE' => 'Products\' Options\' Stock Manager &mdash; View All',

    'TEXT_LAST_UPDATED' => 'Last Updated: ',

    'TEXT_POS_INSTRUCTIONS' => "By default, this tool displays <b>only</b> products with option-combinations (e.g. variants) that have quantities less than or equal to the <em>Options' Stock: Re-order Level</em> (currently " . POSM_STOCK_REORDER_LEVEL . "). To view <b>all</b> products currently being managed by the <a href=\"" . zen_href_link (FILENAME_PRODUCTS_OPTIONS_STOCK) . "\">Products' Options' Stock Manager</a>, check the checkbox below and press the <samp>Go</samp> button.<br><br>The <b>Variant Name</b> column is formatted like <span class=\"option-name\">Option Name</span>: <span class=\"value-name\">Option Value Name</span>[,...].  Low-stock variants are highlighted like <span class=\"out-of-stock\">this</span>.<br><br>If you have configured <em>POSM</em>'s duplicate-model-handling to report duplicates, any <b>Variant Model</b> entry that has a <span style=\"color: red; \">red border</span> is duplicated, either in a product's definition or in another <em>POSM</em> option-combination. See <a href=\"" . zen_href_link (FILENAME_POSM_FIND_DUPLICATE_MODELNUMS) . "\"><em>Tools :: POSM: Find Duplicate Models</em></a> for additional information.<br><br>",
    'TEXT_POS_INSTRUCTIONS2' => "You can change the quantities of multiple items and click the &quot;Update&quot; button to apply all the changes or you can click on the <b>Product Name</b> link to manage the Options' Stock for that product.",

    'POSM_TEXT_PRODUCT_NAME' => 'Product Name',
    'POSM_TEXT_VARIANT_MODEL' => 'Variant Model',
    'POSM_TEXT_OPTIONS_LIST' => 'Variant Name',
    'TEXT_POS_STOCK_QUANTITY' => 'Quantity',

    'BUTTON_GO' => 'Go',

    'TEXT_UPDATE_ALT' => 'Click here to update all changed values.',

    'TEXT_CHECK_TO_VIEW_ALL' => 'View <em>all</em> managed variants?',

    'POSM_VIEW_ALL_UPDATED' => 'Your selected updates were successfully processed.',

    'POSM_VIEW_ALL_NO_PRODUCTS_TO_LIST' => 'There are no managed products to view.',
    'POSM_TEXT_DISPLAY_NUMBER_OF_PRODUCTS' => 'Displaying <b>%u</b> to <b>%u</b> (of <b>%u</b> products)',

    'ERROR_MISSING_INPUTS' => 'The update request could not be processed. You will need to increase your site\'s PHP configuration values for <code>post_max_size</code> (currently %1$s) and/or <code>max_input_size</code> (currently %2$s).',
];
return $define;
