<?php
// -----
// Part of the "Product Options Stock Manager" plugin by Cindy Merkin (cindy@vinosdefrutastropicales.com)
// Copyright (c) 2014-2024 Vinos de Frutas Tropicales
//
// Last updated: POSM 5.0.0
//
// Loaded by the plugin's observer **only if** the plugin's operation is enabled for the storefront!
//
// Dependent-attributes processing occurs only if
// (a) we're on a product's _info page
// (b) the current product is configured with options' stock
// (c) there's not an Attribute: Image Swatch attribute associated with the product
// (d) dependent attributes are enabled (or an override is in place)
//
if (!isset($_GET['products_id']) || $current_page_base !== zen_get_info_page($_GET['products_id'])) {
    return;
}
if (is_pos_product($_GET['products_id']) === false) {
    return;
}

// -----
// Need to 'globalize' the $attribute_swatch object since this module is now
// loaded in function scope.  Ditto for the $zco_notifier, below.
//
global $attribute_swatch;
if (isset($attribute_swatch) && is_object($attribute_swatch) && method_exists($attribute_swatch, 'is_image_swatch_product') && $attribute_swatch->is_image_swatch_product($_GET['products_id'])) {
    return;
}

// -----
// Now that the environment has been determined to support POSM's dependent attributes, continue
// only if the processing is enabled via configuration or if an override is in place.
//
global $zco_notifier;
$posm_dependent_attrs_enable = (POSM_DEPENDENT_ATTRS_ENABLE === 'true');
$zco_notifier->notify('NOTIFY_POSM_DEPENDENCIES_ENABLE_OVERRIDE', [], $posm_dependent_attrs_enable);
if ($posm_dependent_attrs_enable !== true) {
    return;
}

// -----
// Need to 'globalize' the $db class, since now loaded in function scope.
//
global $db;
?>
<script>
let oosMessages = {};
<?php
$oos_messages = $db->Execute(
    "SELECT pos_name_id, pos_name
       FROM " . TABLE_PRODUCTS_OPTIONS_STOCK_NAMES . "
      WHERE language_id = " . (int)$_SESSION['languages_id']
);
foreach ($oos_messages as $oos_message) {
    echo 'oosMessages[' . $oos_message['pos_name_id'] . "] = '" . addslashes($oos_message['pos_name']) . "';\n";
}

$check_select = false;
$check_radio = false;
if (POSM_OPTIONS_TYPES_TO_MANAGE === '') {
    trigger_error("Products' Options' Stock Manager -- Option Types to Manage cannot be empty.", E_USER_ERROR);
    die();
}
$the_list = explode(',', POSM_OPTIONS_TYPES_TO_MANAGE);
foreach ($the_list as $the_type) {
    switch ($the_type) {
        case 0:
            $check_select = true;
            break;
        case 2:
            $check_radio = true;
            break;
        default:
            break;
    }
}
if (POSM_OPTIONAL_OPTION_TYPES_LIST !== '') {
    $the_list = explode(',', POSM_OPTIONAL_OPTION_TYPES_LIST);
    foreach ($the_list as $the_type) {
        switch ($the_type) {
            case 0:
                $check_select = false;
                break;
            case 2:
                $check_radio = false;
                break;
            default:
                break;
        }
    }
}
$input_types = '';
$input_types_first = '';
if ($check_select) {
    $input_types = 'select';
    $input_types_first = 'select:first';
}
if ($check_radio) {
    $separator = ($input_types === '') ? '' : ', ';
    $input_types .= ($separator . 'input[type="radio"]');
    $input_types_first .= ($separator . 'input[type="radio"]:first');
}
$option_check = $db->Execute(
    "SELECT DISTINCT pa.options_id
       FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_OPTIONS . " po
      WHERE pa.products_id = " . (int)$_GET['products_id'] . "
        AND pa.options_id = po.products_options_id
        AND po.products_options_type IN (" . POSM_OPTIONS_TYPES_TO_MANAGE . ")" . 
        ((POSM_OPTIONAL_OPTION_NAMES_LIST === '') ? '' : " AND pa.options_id NOT IN (" . POSM_OPTIONAL_OPTION_NAMES_LIST . ")") 
);
$is_single_option = ($option_check->RecordCount() < 2);
?>
let swatchOptions = {};
<?php
foreach ($option_check as $next_option) {
    $option_info = $db->Execute(
        "SELECT products_options_id, products_options_images_per_row, products_options_images_style 
           FROM " . TABLE_PRODUCTS_OPTIONS . "
          WHERE products_options_id = " . $next_option['options_id'] . " 
          LIMIT 1"
    );
?>
swatchOptions[<?= $option_info->fields['products_options_id'] ?>] = {
    'num': "<?= $option_info->fields['products_options_images_per_row'] ?>",
    'style': "<?= $option_info->fields['products_options_images_style'] ?>"
};
<?php
}

$in_stock_message = '';
if (POSM_SHOW_IN_STOCK_MESSAGE === 'true' && POSM_DEPENDENT_ATTRS_STOCK_STATUS === 'true') {
    $in_stock_message = (POSM_DEPENDENT_ATTRS_STOCK_STATUS_QTY === 'true') ? PRODUCTS_OPTIONS_STOCK_IN_STOCK_QTY : PRODUCTS_OPTIONS_STOCK_IN_STOCK;
}
?>
let inStockMessage = '<?= addslashes($in_stock_message) ?>';
let lastSelection = false;
let outOfStockClass = '';
let outOfStockMessage = '';
let wrapperAttribsOptions = '<?= POSM_ATTRIBUTE_SELECTOR ?>';
let optionNameSelector = '<?= POSM_OPTION_NAME_SELECTOR ?>';
let attributeWrapper = '<?= (POSM_ATTRIBUTE_WRAPPER_SELECTOR !== '') ? POSM_ATTRIBUTE_WRAPPER_SELECTOR : POSM_ATTRIBUTE_SELECTOR ?>';
let attribImgSelector = '<?= POSM_ATTRIBUTE_IMAGE_SELECTOR ?>';
let ignoreOptionsList = [<?= POSM_OPTIONAL_OPTION_NAMES_LIST ?>];
let checkSelect = <?= ($check_select === true) ? 'true' : 'false' ?>;
let inputTypes = '<?= $input_types ?>';
let inputTypesFirst = '<?= $input_types_first ?>';
let isSingleOption = <?= ($is_single_option === true) ? 'true' : 'false' ?>;
let callingPage = '<?= $current_page_base ?>';
let callingPid = <?= (int)$_GET['products_id'] ?>;
let insertPleaseChoose = <?= (POSM_DEPENDENT_ATTRS_PLEASE_CHOOSE === 'true' && $check_select === true && $is_single_option === false) ? 'true' : 'false' ?>;
let showModelNum = <?= POSM_DEPENDENT_ATTRS_SHOW_MODEL ?>;
<?php
// -----
// The "Please Choose" text associated with the first drop-down option depends on whether a drop-down option is first in a product's
// option-list!  Note that the "Please Choose" text-insertion applies **ONLY** to products with multiple options.
//
if (PRODUCTS_OPTIONS_SORT_ORDER === '0') {
    $options_order_by = " ORDER BY LPAD(po.products_options_sort_order,11,'0')";
} else {
    $options_order_by = ' ORDER BY po.products_options_name';
}
$sql =
    "SELECT DISTINCT po.products_options_id, po.products_options_name, po.products_options_sort_order,
                     po.products_options_type
       FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
            INNER JOIN " . TABLE_PRODUCTS_OPTIONS . " po
                ON po.products_options_id = pa.options_id
               AND po.language_id = " . (int)$_SESSION['languages_id'] . "
      WHERE pa.products_id = " . (int)$_GET['products_id'] .
        $options_order_by;
$option_check = $db->Execute($sql);

if (!$option_check->EOF && $option_check->fields['products_options_type'] !== '0') {
    $please_choose_text = PRODUCTS_OPTIONS_STOCK_PLEASE_CHOOSE_NEXT;
} else {
    $please_choose_text = PRODUCTS_OPTIONS_STOCK_PLEASE_CHOOSE;
}
?>
let pleaseChooseText = '<?= addslashes($please_choose_text) ?>';
let pleaseChooseNextText = '<?= addslashes(PRODUCTS_OPTIONS_STOCK_PLEASE_CHOOSE_NEXT) ?>';
let noSelectionText = '<?= addslashes(JS_ERROR_NO_SELECTION) ?>';
let radioButtonChoose = '<?= addslashes(PRODUCTS_OPTIONS_STOCK_RADIO_BUTTON_CHOOSE) ?>';
let allowCheckout = <?= (STOCK_ALLOW_CHECKOUT === 'false') ? 'false' : 'true' ?>;
</script>
<?php
if (POSM_USE_MINIFIED_JSCRIPT === 'true') {
    $jquery_filename = 'jquery.posm_dependencies.min.js';
} else {
    $jquery_filename = 'jquery.posm_dependencies.js';
}
?>
<script src="<?= $template->get_template_dir($jquery_filename, DIR_WS_TEMPLATE, $current_page_base, 'jscript') . '/' . $jquery_filename ?>"></script>
