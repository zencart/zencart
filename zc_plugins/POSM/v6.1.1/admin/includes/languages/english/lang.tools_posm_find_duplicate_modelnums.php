<?php
// -----
// Part of the "Product Options Stock Manager" plugin by Cindy Merkin (cindy@vinosdefrutastropicales.com)
// Copyright (c) 2015-2024 Vinos de Frutas Tropicales
//
// Last updated: POSM v5.0.0
// -----
// Based on the Find Duplicate Models plugin (https://www.zen-cart.com/downloads.php?do=file&id=1323) by swguy.
//
$define = [
    'HEADING_TITLE' => 'POSM: Find Duplicate Models',
    'HEADING_PRODUCTS_LINK' => 'Products Link',
    'HEADING_POSM_LINK' => 'POSM Link',
    'HEADING_PRODUCTS_MODEL' => 'Products Model', 
    'HEADING_POSM_MODEL' => 'POSM Model',
    'HEADING_PRODUCTS_NAME' => 'Products Name',
    'HEADING_PRODUCTS_DISABLED' => 'Product Enabled?',

    'INSTRUCTIONS' => "Use this tool to identify duplicate model numbers within your store's products, both as set in a base product's definition and as the option-combination models in your <em>POSM</em> settings.  By default, only <em>enabled</em> products are included in the report; to include <em>disabled</em> products, tick the checkbox below and then click the <b>go</b> button.<br><br><strong>Note:</strong> If a single product is listed in the duplicates' report, then that product has a <em>POSM</em> option-combination that has the same model-number as a base product.",
    'NO_DUPS_FOUND' => 'Congratulations &mdash; all model numbers are unique.', 

    'INCLUDE_DISABLED' => 'Include disabled products?',
    'POSM_MODEL_IS_EMPTY' => '--empty--',

    'BUTTON_GO' => 'Go',

    'DUPS_UNMANAGED_UNMANAGED' => 'Unmanaged Products with Models Duplicated in Unmanaged Products',
    'DUPS_UNMANAGED_MANAGED' => 'Unmanaged Products with Models Duplicated in Managed Products',
    'DUPS_MANAGED_MANAGED' => 'Managed Products with Models Duplicated in Managed Products',
];
return $define;
