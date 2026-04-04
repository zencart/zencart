<?php
// -----
// Part of the "Product Options Stock Manager" plugin by Cindy Merkin (cindy@vinosdefrutastropicales.com)
// Copyright (c) 2014-2024 Vinos de Frutas Tropicales
//
// Last updated: POSM v5.0.0
//

// -----
// Since languages are loaded via a class method, need to globalize $db.
//
global $db;
$lowstock_option = $db->Execute(
    "SELECT configuration_group_id, configuration_id
       FROM " . TABLE_CONFIGURATION . " 
      WHERE configuration_key = 'POSM_STOCK_REORDER_LEVEL'
      LIMIT 1"
);
$lowstock_value_link = zen_href_link(FILENAME_CONFIGURATION, 'gID=' . $lowstock_option->fields['configuration_group_id'] . '&cID=' . $lowstock_option->fields['configuration_id'] . '&action=edit');

$text_pos_identifier = '(*)';
$text_oos_label = 'Out-of-Stock Label';
$define = [
    'HEADING_TITLE' => 'Products\' Options\' Stock Manager',
    'TEXT_POS_IDENTIFIER' => $text_pos_identifier,
    'TEXT_PRODUCT_DISABLED_IDENTIFIER' => ' [disabled]',
    'TEXT_LAST_UPDATED' => 'Last Updated: ',
    'TEXT_POS_INSTRUCTIONS' => "First, choose the category for which the products will be displayed (default: <em>All Products</em>); if a category's name is followed by an asterisk (*), then that category includes one or more products (not necessarily with attributes).  You can filter the display to include disabled products and identify whether or not each product's model number is included in the products' drop-down selection.  The products' sort-order is controlled by the next drop-down selection.  These four (4) choices are &quot;remembered&quot; in your admin login session.<br><br>Next, choose the product whose options' stock is to be managed from the drop-down list below.  If a product's name is followed by <b>" . $text_pos_identifier . "</b>, that product currently has options that are being managed. When a product includes option-combinations with a quantity less than or equal to " . POSM_STOCK_REORDER_LEVEL . " or an out-of-stock date that is close to expiration, the product's name is <span class=\"out-of-stock\">highlighted</span> within the drop-down list.<br><br><strong>Note:</strong> Product options are displayed (left-to-right) using the options' sort-order (as defined by <a href=\"" . zen_href_link(FILENAME_OPTIONS_NAME_MANAGER) . "\">Catalog :: Option Name Manager</a>); when you sort by <em>Attributes' Sort Order</em>, product option-combination-values are displayed (top-to-bottom) using the values' sort-order (as defined by <a href=\"" . zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER) . "\">Catalog :: Attributes Controller</a>).  If multiple option-names or option-values use the same sort-order, the options are further sorted in ascending name order.<br><br>You can sort your products either by their name or model number, depending on whether or not you've checked the &quot;Include products' model?&quot; box.<br><br>",

    'TEXT_POS_INSERT' => 'Use the &quot;Add&quot; or &quot;Set&quot; buttons to insert or update one or more option-combinations for the selected product. The &quot;Add&quot; button <em>adds</em> the specified quantity to any pre-existing option-combinations, while the &quot;Set&quot; button <em>replaces</em> any pre-existing combination\'s quantity. You will receive a pop-up confirmation to make sure that you want to add all the combinations you have chosen.<br><br>',

    'TEXT_POS_INSTRUCTIONS2' => "When a product has one or more option-combinations, they display as a list:<ol><li>If an option-combination's stock level is at or below the configured low-stock level (currently <a href=\"$lowstock_value_link\">" . POSM_STOCK_REORDER_LEVEL . "</a>) the combination's &quot;Qty&quot; input has a <span class=\"out-of-stock\">red border</span>.</li><li>If an option-combination's back-in-stock date is with or has passed the &quot;reminder period&quot; (currently " . POSM_BIS_DATE_REMINDER . " or more days), the entry's entire line shows a <span class=\"bg-warning\">warning background</span>.</li><li>If an option-combination or -value shows <span class=\"removed\">with strike-thru markup</span>, then that option-combination or -value has been removed from the product. You cannot update the quantity of an unknown option-combination or -value, but you can remove it.</li><li>When you click either of the &quot;Update&quot; buttons, all option-combination quantities and models will be updated.</li><li>When you click either of the &quot;Remove&quot; buttons, any option-combination that has its &quot;Remove?&quot; box checked will be removed.  If one or more &quot;Remove?&quot; boxes are checked, you will receive a pop-up message to confirm the removal(s).</li><li>If you have configured <em>POSM</em>'s duplicate-model-handling to report duplicates, any <b>Option Model/SKU</b> entry that has a <span class=\"dup-model\">red border</span> is duplicated, either in a product's definition or in another <em>POSM</em> option-combination. See <a href=\"" . zen_href_link(FILENAME_POSM_FIND_DUPLICATE_MODELNUMS) . "\"><em>Tools :: POSM: Find Duplicate Models</em></a> for additional information.</li></ol>",

    'TEXT_POS_OPTIONS_ADDED' => 'One or more options were added to this product since its option-records were created.  Please choose the value for each new option to be added to each existing option-record and then click the &quot;Insert&quot; button.  Quantities for those existing records will remain the same.<br><br>',

    'TEXT_POS_STOCK_QUANTITY' => 'Qty.',
    'TEXT_CURRENT_TOTAL' => 'Total: %u',
    'TEXT_POS_REMOVE' => 'Remove?',
    'TABLE_HEADING_CHECK_UNCHECK' => 'All/None?',
    'TEXT_ADD_TO_QUANTITY' => 'Add',
    'TEXT_REPLACE_QUANTITY' => 'Set',
    'TEXT_ALL' => '* (All)',
    'TEXT_OPTION_MODEL' => 'Option Model/SKU',
    'TEXT_OOS_LABEL' => $text_oos_label,
    'TEXT_OOS_DATE' => 'Back-in-Stock Date<br><span class="smaller">Enter as YYYY-MM-DD</span>',
    'TEXT_NONE_DEFINED' => '-- None Defined --',
    'TEXT_PLEASE_SELECT' => 'Please select ...',
    'TEXT_ALL_CATEGORIES' => 'All Categories',
    'TEXT_CHOOSE_CATEGORY' => 'Display products from category: ',
    'TEXT_CHOOSE_PRODUCT' => 'Choose the product to manage: ',
    'TEXT_NO_PRODUCTS_IN_CATEGORY' => 'No products with manageable options are present in the selected category.',

    'TEXT_MODEL_DEFAULT' => 'Set default?',
    'TEXT_MODEL_DEFAULT_TITLE' => 'Use this model number for any currently blank values?',

    'BUTTON_DEFINE_LABELS' => 'Define Labels',
    'BUTTON_DEFINE_LABELS_ALT' => 'Click here to define the labels used for out-of-stock products',
    'BUTTON_VIEW_ALL' => 'View All',
    'BUTTON_VIEW_ALL_ALT' => 'Click here to view all managed products on a single page',
    'BUTTON_GO' => 'Go',

    'TEXT_INCLUDE_DISABLED' => 'Include disabled products? ',
    'TEXT_INCLUDE_MODEL' => 'Include products\' model? ',

    'BUTTON_UPDATE' => 'Update',
    'TEXT_UPDATE_ALT' => 'Click here to update all quantities and model numbers.',
    'BUTTON_REMOVE' => 'Remove',
    'TEXT_REMOVE_ALT' => 'Click here to remove the selected option-combination(s).',

    'TEXT_SINGLE_LABEL_NAME' => '<b>Note:</b> Only one &quot;' . $text_oos_label . '&quot; (<em><b>%1$s</b></em>) is defined.  That label will be used for all <em>POSM</em>-managed products that are out-of-stock.',

    'ERROR_INVALID_DATE' => 'An <strong>Out-of-Stock Date</strong> must be entered as YYYY-MM-DD and be a valid date.',
    'ERROR_INVALID_FORM_VALUES' => 'Invalid values were found in the form\'s submission (code %s).',
    'SUCCESS_QUANTITY_UPDATED' => "One or more options' stock quantities and model numbers were updated.",
    'SUCCESS_NEW_OPTION_CREATED' => "One or more new options' stock records were created or updated.",
    'WARNING_DUPLICATE_COMBINATION' => 'A record with the option combination you selected already exists.',
    'SUCCESS_OPTION_RECORDS_REMOVED' => '%u option-records were successfully removed.',
    'SUCCESS_OPTIONS_ADDED' => 'New options were added to the existing option-records.',
    'ERROR_MISSING_INPUTS' => 'The update request could not be processed. You will need to increase your site\'s PHP configuration values for <code>post_max_size</code> (currently %1$s) and/or <code>max_input_vars</code> (currently %2$s).',

    'JS_MESSAGE_DELETE_ALL_CONFIRM' => 'Are you sure you want to remove these \'+n+\' option records?',
    'JS_MESSAGE_INSERT_NEW_CONFIRM' => 'This action will insert \'+items+\' option-combinations. Do you want to continue?',
    'JS_MESSAGE_INSERT_MULTIPLE_CONFIRM' => 'This action will potentially insert \'+items+\' option-combinations. All current combinations will have their quantities \'+add_replace+\'. Do you want to continue?',
    'JS_MESSAGE_UPDATED' => 'updated by \'+quantity+\'',
    'JS_MESSAGE_REPLACED' => 'replaced',
    'JS_MESSAGE_DELETE_SELECTED_CONFIRM' => 'Are you sure you want to remove the \'+selected+\' selected option-combination(s)?',
    'WARNING_NO_FILES_SELECTED' => 'No option-combinations were selected for removal!',
    'JS_MESSAGE_CONFIRM_MODEL_DEFAULT' => 'Apply the product\\\'s base model number (%s) to \'+emptyModels+\' options.  Continue?', //- %s is the product's base model
];
return $define;
