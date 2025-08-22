<?php
// -----
// Part of the "Product Options Stock Manager" plugin by Cindy Merkin (cindy@vinosdefrutastropicales.com)
// Copyright (c) 2014-2024 Vinos de Frutas Tropicales
//
// Last updated: POSM v5.0.0
//
$define = [
    'HEADING_TITLE' => "Products' Options' Stock Manager: Manage Out-of-Stock Labels",
    'TEXT_INSTRUCTIONS' => 'Use this page to define and manage the text labels that you associate with products that are being managed by the <em>Products\' Options\' Stock</em> plugin.  The label that you assign to a product will be displayed to your customers when an option-combination is &quot;out-of-stock&quot;. You can use the special symbol <b>[date]</b> to (optionally) identify where an associated stock-related date is to be inserted.',

    'TABLE_HEADING_NAME_ID' => 'Label ID',
    'TABLE_HEADING_LABEL_NAME' => 'Label Name',
    'TABLE_HEADING_ACTION' => 'Action',

    'BUTTON_MANAGE' => 'Manage',
    'BUTTON_MANAGE_ALT' => 'Click here to manage your &quot;Products\' Options\' Stock&quot;',

    'TEXT_INFO_EDIT_INTRO' => 'Please make any necessary changes',
    'TEXT_INFO_LABEL_NAME' => 'Label Name:',
    'TEXT_INFO_INSERT_INTRO' => 'Please enter the new &quot;Out-of-Stock Label&quot;.',
    'TEXT_INFO_DELETE_INTRO' => 'Are you sure you want to delete this &quot;Out-of-Stock Label&quot;?',
    'TEXT_INFO_HEADING_NEW' => 'New &quot;Out-of-Stock Label&quot;',
    'TEXT_INFO_HEADING_EDIT' => 'Edit &quot;Out-of-Stock Label&quot;',
    'TEXT_INFO_HEADING_DELETE' => 'Delete &quot;Out-of-Stock Label&quot;',

    'CAUTION_NO_LABEL_NAMES_FOUND' => 'No &quot;Out-of-Stock Labels&quot; were found.',
    'MESSAGE_ERROR_NO_ID' => 'Missing ID for operation.',
    'ERROR_USED_IN_OPTIONS_STOCK' => 'The stock label &mdash; <b>%s</b> &mdash; is used in one or more product\'s options and cannot be removed.',
    'ERROR_DATE_MULTI_LANG' => 'The <b>[date]</b> symbol, if present, must be used in all language values.',
    'ERROR_COMMA_IN_NAME' => 'A stock <b>Label Name</b> cannot contain a comma (,) &mdash; please re-enter.',
    'ERROR_NAME_TOO_LONG' => 'The stock label &mdash; <b>%s</b> &mdash; has too many characters; please re-enter.',
];
return $define;
