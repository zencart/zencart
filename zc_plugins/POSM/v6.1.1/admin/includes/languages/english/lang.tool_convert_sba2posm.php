<?php
// -----
// Part of the "Product Options Stock Manager" plugin by Cindy Merkin (cindy@vinosdefrutastropicales.com)
// Copyright (c) 2014-2024 Vinos de Frutas Tropicales
//
$define = [
    'HEADING_TITLE' => 'Products\' Options\' Stock:  Convert Stock-by-Attribute',

    'TEXT_INSTRUCTIONS' => 'Use this tool to convert your existing <em>Stock by Attributes (SBA)</em> database tables into their associated <em>Products\' Options\' Stock (POSM)</em> database tables. Please note that <em>POSM</em> requires all option-combinations be specified, so some of your <em>SBA</em> combinations might not be &quot;convertible&quot;.  Refer to the display below; if the status column shows missing options or an unknown product, that <em>SBA</em> record will not be converted!<br /><br /><strong>Note:</strong> Clicking the &quot;Submit&quot; button will remove all existing entries in your POSM configuration!',

    'ERROR_NO_SBA_TABLE' => 'No conversion is possible &mdash; missing <em>products_with_attributes_stock</em> database table.',

    'TEXT_FORM_INSTRUCTIONS' => 'Review the information below, then click the <em>Submit</em> button to convert the <em>SBA</em> entries to their <em>POSM</em> equivalents.',
    'BUTTON_ALT_TEXT' => 'Click here to convert the tables',

    'TEXT_MISSING_OPTIONS' => '<span class="missing">&cross; Missing options (%s)</span>',
    'TEXT_UNSUPPORTED_OPTION_TYPE' => '<span class="missing">&cross; Option ID (%1$u) uses an unsupported options type (%2$u)</span>',
    'TEXT_MISSING_PRODUCT' => '<span class="missing">&cross; Product does not exist</span>',
    'TEXT_OK' => '<span class="ok">&check;</span>',

    'TABLE_HEADING_STOCK_ID' => 'Stock ID',
    'TABLE_HEADING_QUANTITY' => 'Quantity',
    'TABLE_HEADING_MODEL' => 'Model',
    'TABLE_HEADING_STATUS' => 'Status',

    'MESSAGE_CONVERTED_OK' => 'Your <em>Stock by Attributes</em> entries have been successfully converted to their <em>Products\' Options\' Stock</em> equivalents.',
    'MESSAGE_CONVERTED_MISSING' => 'Review the information below, some of your <em>Stock by Attributes</em> entries could not be converted.',

    'JS_MESSAGE_ARE_YOU_SURE' => 'This action will reset your POSM tables to contain only the SBA-converted information. Are you sure you want to continue?',
];
return $define;
