<?php
/**
 * attributes module
 *
 * Prepares attributes content for rendering in the template system
 * Prepares HTML for input fields with required uniqueness so template can display them as needed and keep collected data in proper fields
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2024 Apr 16 Modified in v2.0.1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

$show_onetime_charges_description = false;
$show_attributes_qty_prices_description = false;

// Determine number of attributes associated with this product
$sql = "SELECT COUNT(*) as total
        FROM " . TABLE_PRODUCTS_OPTIONS . " popt
        LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " patrib ON (popt.products_options_id = patrib.options_id)
        WHERE patrib.products_id = :products_id
        AND popt.language_id = :language_id
        LIMIT 1";
$sql = $db->bindVars($sql, ':products_id', $_GET['products_id'], 'integer');
$sql = $db->bindVars($sql, ':language_id', $_SESSION['languages_id'], 'integer');
$pr_attr = $db->Execute($sql);

if ($pr_attr->fields['total'] < 1) {
    return;
}

// Only process the rest of this file if attributes are defined for this product

$prod_id = $_GET['products_id'];
$number_of_uploads = 0;
$zv_display_select_option = 0;
$options_name = [];
$options_menu = [];
$options_html_id = [];
$options_inputfield_id = [];
$options_comment = [];
$options_comment_position = [];
$options_attributes_image = [];
$attributeDetailsArrayForJson = [];

$discount_type = zen_get_products_sale_discount_type((int)$_GET['products_id']);
$discount_amount = zen_get_discount_calc((int)$_GET['products_id']);
$products_price_is_priced_by_attributes = zen_get_products_price_is_priced_by_attributes((int)$_GET['products_id']);

if (PRODUCTS_OPTIONS_SORT_ORDER === '0') {
    $options_order_by = " ORDER BY LPAD(popt.products_options_sort_order,11,'0'), popt.products_options_name";
} else {
    $options_order_by = ' ORDER BY popt.products_options_name';
}

$sql = "SELECT DISTINCT popt.products_options_id, popt.products_options_name, popt.products_options_sort_order,
            popt.products_options_type, popt.products_options_length, popt.products_options_comment, popt.products_options_comment_position,
            popt.products_options_size,
            popt.products_options_images_per_row,
            popt.products_options_images_style,
            popt.products_options_rows
        FROM " . TABLE_PRODUCTS_OPTIONS . " popt
        LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " patrib ON (patrib.options_id = popt.products_options_id)
        WHERE patrib.products_id= :products_id
        AND popt.language_id = :language_id " .
        $options_order_by;
$sql = $db->bindVars($sql, ':products_id', $_GET['products_id'], 'integer');
$sql = $db->bindVars($sql, ':language_id', $_SESSION['languages_id'], 'integer');
$products_options_names = $db->Execute($sql);

if (PRODUCTS_OPTIONS_SORT_BY_PRICE === '1') {
    $order_by = " ORDER BY LPAD(pa.products_options_sort_order,11,'0'), pov.products_options_values_name";
} else {
    $order_by = " ORDER BY LPAD(pa.products_options_sort_order,11,'0'), pa.options_values_price";
}

foreach ($products_options_names as $next_option_name) {
    $products_options_array = [];

    $products_options_id = $next_option_name['products_options_id'];
    $products_options_type = $next_option_name['products_options_type'];
    $products_options_name = $next_option_name['products_options_name'];

    /* Field names for dev reference
        pov.products_options_values_id
        pov.products_options_values_name
        pa.options_values_price
        pa.price_prefix
        pa.products_options_sort_order
        pa.product_attribute_is_free
        pa.products_attributes_weight
        pa.products_attributes_weight_prefix
        pa.attributes_default
        pa.attributes_discounted
        pa.attributes_image
    */
    $sql = "SELECT pov.products_options_values_id, pov.products_options_values_name, pa.*
            FROM  " . TABLE_PRODUCTS_ATTRIBUTES . " pa
            LEFT JOIN " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov ON (pa.options_values_id = pov.products_options_values_id AND pov.language_id = :language_id)
            WHERE pa.products_id = :products_id
            AND   pa.options_id = :options_id " .
            $order_by;
    $sql = $db->bindVars($sql, ':products_id', $_GET['products_id'], 'integer');
    $sql = $db->bindVars($sql, ':options_id', $products_options_id, 'integer');
    $sql = $db->bindVars($sql, ':language_id', $_SESSION['languages_id'], 'integer');
    $products_options = $db->Execute($sql);

    $products_options_value_id = 0;
    $products_options_details = '';
    $products_options_details_noname = '';
    $tmp_radio = '';
    $tmp_checkbox = '';
    $tmp_html = '';
    $selected_attribute = false;
    $selected_dropdown_attribute = false; // boolean, used for radio/checkbox/select

    $tmp_attributes_image = '';
    $tmp_attributes_image_row = 0;
    $show_attributes_qty_prices_icon = false;
    $i = 0;

    // -----
    // Preset common variables for use in the option's values' loop.
    //
    $option_form_name = 'id[' . $products_options_id . ']';
    $option_is_text_or_file = ($products_options_type === PRODUCTS_OPTIONS_TYPE_FILE || $products_options_type === PRODUCTS_OPTIONS_TYPE_TEXT);
    $products_tax_rate ??= zen_get_tax_rate($product_data['products_tax_class_id']);

    $zco_notifier->notify('NOTIFY_ATTRIBUTES_MODULE_START_OPTION', $next_option_name);

    // loop through each Attribute
    foreach ($products_options as $next_option) {
        $products_options_value_id = $next_option['products_options_values_id'];

        // for generated html labels to match input fields, and client-side code to identify fields
        $inputFieldId = 'attrib-' . $products_options_id . ($products_options_type === PRODUCTS_OPTIONS_TYPE_SELECT ? '' : '-' . $products_options_value_id);

        $selected_attribute = false; // boolean, used for radio/checkbox/select
        $data_properties = ' data-key="' . $inputFieldId . '" '; // observers can insert data-x="y" values
        $field_disabled = ''; // empty or disabled="disabled"

        $products_options_display_price = '';
        $new_attributes_price = '';
        $price_onetime = '';

        $products_options_array[] = [
            'id'   => $products_options_value_id,
            'text' => $next_option['products_options_values_name'],
        ];

        $attributeDetailsArrayForJson[$inputFieldId] = array_merge(
            [
                'field_id' => $inputFieldId,
                'name' => $next_option_name['products_options_name'],
                'attr_id' => $next_option_name['products_options_id'],
            ],
            $next_option,
            $next_option_name
        );

        $zco_notifier->notify('NOTIFY_ATTRIBUTES_MODULE_START_OPTIONS_LOOP', $i++, $next_option, $next_option_name, $data_properties, $field_disabled, $attributeDetailsArrayForJson);

        // DEAL WITH PRICE AND WEIGHT DISPLAY
        if (
            ((CUSTOMERS_APPROVAL === '2' && !zen_is_logged_in()) || STORE_STATUS === '1')
            || ((CUSTOMERS_APPROVAL_AUTHORIZATION === '1' || CUSTOMERS_APPROVAL_AUTHORIZATION === '2') && $_SESSION['customers_authorization'] == '')
            || (CUSTOMERS_APPROVAL === '2' && $_SESSION['customers_authorization'] == '2')
            || (CUSTOMERS_APPROVAL_AUTHORIZATION === '2' && $_SESSION['customers_authorization'] != 0)
        ) {
            $new_attributes_price = 0;
            $new_options_values_price = 0;
            $products_options_display_price = '';
            $price_onetime = '';
        } else {
            // collect price information if it exists
            if ($next_option['attributes_discounted'] === '1') {
                // apply product discount to attributes if discount is on
                $new_attributes_price = zen_get_attributes_price_final($next_option['products_attributes_id'], 1, '', false, $products_price_is_priced_by_attributes);
                //$new_attributes_price = zen_get_discount_calc((int)$_GET['products_id'], true, $new_attributes_price);
            } else {
                // discount is off do not apply
                $new_attributes_price = $next_option['options_values_price'];

                // -----
                // If the attribute's price is 0, set it to an (int) 0 so that follow-on checks
                // using empty() will find that value 'empty'.
                //
                if ($new_attributes_price === '0.0000') {
                    $new_attributes_price = 0;
                }
            }

            // reverse negative values for display
            if ($new_attributes_price < 0) {
                $new_attributes_price = -$new_attributes_price;
            }

            if ($next_option['attributes_price_onetime'] != 0 || $next_option['attributes_price_factor_onetime'] != 0) {
                $show_onetime_charges_description = true;
                $new_onetime_charges = zen_get_attributes_price_final_onetime($next_option['products_attributes_id'], 1, '');
                $price_onetime = TEXT_ONETIME_CHARGE_SYMBOL . $currencies->display_price($new_onetime_charges, $products_tax_rate);
            } else {
                $price_onetime = '';
            }

            if (!empty($next_option['attributes_qty_prices']) || !empty($next_option['attributes_qty_prices_onetime'])) {
                $show_attributes_qty_prices_description = true;
                $show_attributes_qty_prices_icon = true;
            }

            if ($next_option['options_values_price'] != 0 && $next_option['product_attribute_is_free'] !== '1' && $product_data['product_is_free'] !== '1') {
                // show sale maker discount if a percentage
                $products_options_display_price =
                    ATTRIBUTES_PRICE_DELIMITER_PREFIX . $next_option['price_prefix'] .
                    $currencies->display_price($new_attributes_price, $products_tax_rate) .
                    ATTRIBUTES_PRICE_DELIMITER_SUFFIX;

                $zco_notifier->notify('NOTIFY_ATTRIBUTES_MODULE_SALEMAKER_DISPLAY_PRICE_PERCENTAGE', $next_option, $product_data, $products_options_display_price, $data_properties);

            } else {
                // if product_is_free and product_attribute_is_free
                if ($next_option['product_attribute_is_free'] === '1' && $product_data['product_is_free'] === '1') {
                    $products_options_display_price =
                        TEXT_ATTRIBUTES_PRICE_WAS . $next_option['price_prefix'] .
                        $currencies->display_price($new_attributes_price, $products_tax_rate) .
                        TEXT_ATTRIBUTE_IS_FREE;
                } else {
                    // normal price
                    if (empty($new_attributes_price)) {
                        $products_options_display_price = '';
                    } else {
                        $products_options_display_price =
                            ATTRIBUTES_PRICE_DELIMITER_PREFIX . $next_option['price_prefix'] .
                            $currencies->display_price($new_attributes_price, $products_tax_rate) .
                            ATTRIBUTES_PRICE_DELIMITER_SUFFIX;
                    }
                }
            }

            $products_options_display_price .= $price_onetime;

        } // approve

        $zco_notifier->notify('NOTIFY_ATTRIBUTES_MODULE_ORIGINAL_PRICE', $next_option, $products_options_array, $products_options_display_price, $data_properties);

        $products_options_array[count($products_options_array) - 1]['text'] .= $products_options_display_price;

        // collect weight information if it exists
        if ($flag_show_weight_attrib_for_this_prod_type == '1' && $next_option['products_attributes_weight'] != 0) {
            $products_options_display_weight =
                ATTRIBUTES_WEIGHT_DELIMITER_PREFIX . $next_option['products_attributes_weight_prefix'] .
                round($next_option['products_attributes_weight'], 2) .
                TEXT_PRODUCT_WEIGHT_UNIT . ATTRIBUTES_WEIGHT_DELIMITER_SUFFIX;
            $products_options_array[count($products_options_array) - 1]['text'] .= $products_options_display_weight;
        } else {
            // reset
            $products_options_display_weight = '';
        }

        // prepare product options details
        if ($products_options->RecordCount() == 1
            || in_array($products_options_type, [
                PRODUCTS_OPTIONS_TYPE_FILE,
                PRODUCTS_OPTIONS_TYPE_TEXT,
                PRODUCTS_OPTIONS_TYPE_CHECKBOX,
                PRODUCTS_OPTIONS_TYPE_RADIO,
                PRODUCTS_OPTIONS_TYPE_READONLY,
                ]
            )
        ) {
            $products_options_details = '';
            // don't show option value name on TEXT or filename
            if ($option_is_text_or_file === false) {
                $products_options_details = $next_option['products_options_values_name'];
            }
            $products_options_details .= $products_options_display_price;
            $products_options_details_noname = $products_options_display_price;

            if ($next_option['products_attributes_weight'] != 0) {
                if ($next_option_name['products_options_images_style'] >= 3) {
                    $products_options_details .= '<br>';
                    $products_options_details_noname .= '<br>';
                } else {
                    $products_options_details .= '  ';
                    $products_options_details_noname .= '  ';
                }
                $products_options_details .= $products_options_display_weight;
                $products_options_details_noname .= $products_options_display_weight;
            }
        }

        // DEAL WITH OPTION TYPES

        // radio buttons
        if ($products_options_type === PRODUCTS_OPTIONS_TYPE_RADIO) {
            if ($_SESSION['cart']->in_cart($prod_id)) {
                if (($_SESSION['cart']->contents[$prod_id]['attributes'][$products_options_id] ?? -99) == $products_options_value_id) {
                    $selected_attribute = $_SESSION['cart']->contents[$prod_id]['attributes'][$products_options_id];
                }
            } else {
                // $selected_attribute = ($next_option['attributes_default']=='1' ? true : false);
                // if an error, set to customer setting
                if (!empty($_POST['id']) && is_array($_POST['id'])) {
                    foreach ($_POST['id'] as $key => $value) {
                        if ($key == $products_options_id && $value == $products_options_value_id) {
                            $selected_attribute = true;
                            break;
                        }
                    }
                } else {
                    // select default but do NOT auto select single radio buttons
                    //$selected_attribute = ($next_option['attributes_default']=='1' ? true : false);
                    // select default radio button or auto select single radio buttons
                    $selected_attribute = ($next_option['attributes_default'] === '1' ? true : ($products_options->RecordCount() == 1));
                }
            }

            $zco_notifier->notify('NOTIFY_ATTRIBUTES_MODULE_RADIO_SELECTED', $next_option, $data_properties);

            switch ($next_option_name['products_options_images_style']) {
                case '0':
                    $tmp_radio .=
                        zen_draw_radio_field($option_form_name, $products_options_value_id, $selected_attribute, 'id="' . $inputFieldId . '" ' . $data_properties . $field_disabled) .
                        '<label class="attribsRadioButton zero" for="' . $inputFieldId . '">' .
                            $products_options_details .
                        '</label><br>' . "\n";
                    break;
                case '1':
                    $tmp_radio .=
                        zen_draw_radio_field($option_form_name, $products_options_value_id, $selected_attribute, 'id="' . $inputFieldId . '" ' . $data_properties . $field_disabled) .
                        '<label class="attribsRadioButton one" for="' . $inputFieldId . '">' .
                            (!empty($next_option['attributes_image']) ? zen_image(DIR_WS_IMAGES . $next_option['attributes_image'], '', '', '') . '  ' : '') . $products_options_details .
                        '</label><br>' . "\n";
                    break;
                case '2':
                    $tmp_radio .=
                        zen_draw_radio_field($option_form_name, $products_options_value_id, $selected_attribute, 'id="' . $inputFieldId . '" ' . $data_properties . $field_disabled) .
                        '<label class="attribsRadioButton two" for="' . $inputFieldId . '">' .
                            $products_options_details . (!empty($next_option['attributes_image']) ? '<br>' . zen_image(DIR_WS_IMAGES . $next_option['attributes_image'], '', '', '') : '') .
                        '</label><br>' . "\n";
                    break;
                case '3':
                    $tmp_attributes_image_row++;
                    if ($tmp_attributes_image_row > $next_option_name['products_options_images_per_row']) {
                        $tmp_attributes_image .= '<br class="clearBoth">' . "\n";
                        $tmp_attributes_image_row = 1;
                    }

                    if (!empty($next_option['attributes_image'])) {
                        $tmp_attributes_image .= 
                            '<div class="attribImg">' .
                                zen_draw_radio_field($option_form_name, $products_options_value_id, $selected_attribute, 'id="' . $inputFieldId . '" ' . $data_properties . $field_disabled) .
                                '<label class="attribsRadioButton three" for="' . $inputFieldId . '">' .
                                    zen_image(DIR_WS_IMAGES . $next_option['attributes_image']) .
                                    (PRODUCT_IMAGES_ATTRIBUTES_NAMES == '1' ? '<br>' . $next_option['products_options_values_name'] : '') . $products_options_details_noname .
                                '</label>' .
                            '</div>' . "\n";
                    } else {
                        $tmp_attributes_image .= 
                            '<div class="attribImg">' .
                                zen_draw_radio_field($option_form_name, $products_options_value_id, $selected_attribute, 'id="' . $inputFieldId . '" ' . $data_properties . $field_disabled) .
                                '<br>' .
                                '<label class="attribsRadioButton threeA" for="' . $inputFieldId . '">' .
                                    $next_option['products_options_values_name'] . $products_options_details_noname .
                                '</label>' .
                            '</div>' . "\n";
                    }
                    break;

                case '4':
                    $tmp_attributes_image_row++;

                    if ($tmp_attributes_image_row > $next_option_name['products_options_images_per_row']) {
                        $tmp_attributes_image .= '<br class="clearBoth">' . "\n";
                        $tmp_attributes_image_row = 1;
                    }

                    if (!empty($next_option['attributes_image'])) {
                        $tmp_attributes_image .=
                            '<div class="attribImg">' .
                                '<label class="attribsRadioButton four" for="' . $inputFieldId . '">' .
                                    zen_image(DIR_WS_IMAGES . $next_option['attributes_image']) . (PRODUCT_IMAGES_ATTRIBUTES_NAMES == '1' ? '<br>' . $next_option['products_options_values_name'] : '') .
                                    (!empty($products_options_details_noname) ? '<br>' . $products_options_details_noname : '') .
                                '</label><br>' .
                                zen_draw_radio_field($option_form_name, $products_options_value_id, $selected_attribute, 'id="' . $inputFieldId . '" ' . $data_properties . $field_disabled) .
                            '</div>' . "\n";
                    } else {
                        $tmp_attributes_image .=
                            '<div class="attribImg">' .
                                '<label class="attribsRadioButton fourA" for="' . $inputFieldId . '">' .
                                    $next_option['products_options_values_name'] . (!empty($products_options_details_noname) ? '<br>' . $products_options_details_noname : '') .
                                '</label><br>' .
                                zen_draw_radio_field($option_form_name, $products_options_value_id, $selected_attribute, 'id="' . $inputFieldId . '" ' . $data_properties . $field_disabled) .
                            '</div>' . "\n";
                    }
                    break;

                case '5':
                    $tmp_attributes_image_row++;

                    if ($tmp_attributes_image_row > $next_option_name['products_options_images_per_row']) {
                        $tmp_attributes_image .= '<br class="clearBoth">' . "\n";
                        $tmp_attributes_image_row = 1;
                    }

                    if (!empty($next_option['attributes_image'])) {
                        $tmp_attributes_image .=
                            '<div class="attribImg">' .
                                zen_draw_radio_field($option_form_name, $products_options_value_id, $selected_attribute, 'id="' . $inputFieldId . '" ' . $data_properties . $field_disabled) .
                                '<br>' .
                                '<label class="attribsRadioButton five" for="' . $inputFieldId . '">' .
                                    zen_image(DIR_WS_IMAGES . $next_option['attributes_image']) . (PRODUCT_IMAGES_ATTRIBUTES_NAMES == '1' ? '<br>' . $next_option['products_options_values_name'] : '') .
                                    (!empty($products_options_details_noname) ? '<br>' . $products_options_details_noname : '') .
                                '</label>' .
                            '</div>' . "\n";
                    } else {
                        $tmp_attributes_image .=
                            '<div class="attribImg">' .
                                zen_draw_radio_field($option_form_name, $products_options_value_id, $selected_attribute, 'id="' . $inputFieldId . '" ' . $data_properties . $field_disabled) .
                                '<br>' .
                                '<label class="attribsRadioButton fiveA" for="' . $inputFieldId . '">' .
                                    $next_option['products_options_values_name'] .
                                    (!empty($products_options_details_noname) ? '<br>' . $products_options_details_noname : '') .
                                '</label>' .
                            '</div>' . "\n";
                    }
                    break;
            }
        }

        // checkboxes
        if ($products_options_type === PRODUCTS_OPTIONS_TYPE_CHECKBOX) {
            $string = $products_options_id . '_chk' . $products_options_value_id;
            if ($_SESSION['cart']->in_cart($prod_id)) {
                if (isset($_SESSION['cart']->contents[$prod_id]['attributes'][$string]) && $_SESSION['cart']->contents[$prod_id]['attributes'][$string] == $products_options_value_id) {
                    $selected_attribute = true;
                }
            } else {
                // $selected_attribute = ($next_option['attributes_default']=='1' ? true : false);
                // if an error, set to customer setting
                if (!empty($_POST['id']) && is_array($_POST['id'])) {
                    foreach ($_POST['id'] as $key => $value) {
                        if (is_array($value)) {
                            foreach ($value as $kkey => $vvalue) {
                                if ($key == $products_options_id && $vvalue == $products_options_value_id) {
                                    $selected_attribute = true;
                                    break;
                                }
                            }
                        } else {
                            if ($key == $products_options_id && $value == $products_options_value_id) {
                                $selected_attribute = true;
                                break;
                            }
                        }
                    }
                } else {
                    $selected_attribute = ($next_option['attributes_default'] === '1');
                }
            }

            $zco_notifier->notify('NOTIFY_ATTRIBUTES_MODULE_CHECKBOX_SELECTED', $next_option, $data_properties);

            switch ($next_option_name['products_options_images_style']) {
                case '0':
                    $tmp_checkbox .=
                        zen_draw_checkbox_field($option_form_name . '[' . $products_options_value_id . ']', $products_options_value_id, $selected_attribute, 'id="' . $inputFieldId . '" ' . $data_properties . $field_disabled) .
                        '<label class="attribsCheckbox" for="' . $inputFieldId . '">' .
                            $products_options_details .
                        '</label><br>' . "\n";
                    break;
                case '1':
                    $tmp_checkbox .=
                        zen_draw_checkbox_field($option_form_name . '[' . $products_options_value_id . ']', $products_options_value_id, $selected_attribute, 'id="' . $inputFieldId . '" ' . $data_properties . $field_disabled) .
                        '<label class="attribsCheckbox" for="' . $inputFieldId . '">' .
                            (!empty($next_option['attributes_image']) ? zen_image(DIR_WS_IMAGES . $next_option['attributes_image'], '', '', '') . '  ' : '') .
                            $products_options_details .
                        '</label><br>' . "\n";
                    break;
                case '2':
                    $tmp_checkbox .=
                        zen_draw_checkbox_field($option_form_name . '[' . $products_options_value_id . ']', $products_options_value_id, $selected_attribute, 'id="' . $inputFieldId . '" ' . $data_properties . $field_disabled) .
                        '<label class="attribsCheckbox" for="' . $inputFieldId . '">' .
                            $products_options_details .
                            (!empty($next_option['attributes_image']) ? '<br>' . zen_image(DIR_WS_IMAGES . $next_option['attributes_image'], '', '', '') : '') .
                        '</label><br>' . "\n";
                    break;

                case '3':
                    $tmp_attributes_image_row++;

                    if ($tmp_attributes_image_row > $next_option_name['products_options_images_per_row']) {
                        $tmp_attributes_image .= '<br class="clearBoth">' . "\n";
                        $tmp_attributes_image_row = 1;
                    }

                    if (!empty($next_option['attributes_image'])) {
                        $tmp_attributes_image .=
                            '<div class="attribImg">' .
                                zen_draw_checkbox_field($option_form_name . '[' . $products_options_value_id . ']', $products_options_value_id, $selected_attribute, 'id="' . $inputFieldId . '" ' . $data_properties . $field_disabled) .
                                '<label class="attribsCheckbox" for="' . $inputFieldId . '">' .
                                    zen_image(DIR_WS_IMAGES . $next_option['attributes_image']) .
                                    (PRODUCT_IMAGES_ATTRIBUTES_NAMES === '1' ? '<br>' . $next_option['products_options_values_name'] : '') .
                                    $products_options_details_noname .
                                '</label>' .
                            '</div>' . "\n";
                    } else {
                        $tmp_attributes_image .=
                            '<div class="attribImg">' .
                                zen_draw_checkbox_field($option_form_name . '[' . $products_options_value_id . ']', $products_options_value_id, $selected_attribute, 'id="' . $inputFieldId . '" ' . $data_properties . $field_disabled) .
                                '<br>' .
                                '<label class="attribsCheckbox" for="' . $inputFieldId . '">' .
                                    $next_option['products_options_values_name'] .
                                    $products_options_details_noname .
                                '</label>' .
                            '</div>' . "\n";
                    }
                    break;

                case '4':
                    $tmp_attributes_image_row++;

                    if ($tmp_attributes_image_row > $next_option_name['products_options_images_per_row']) {
                        $tmp_attributes_image .= '<br class="clearBoth">' . "\n";
                        $tmp_attributes_image_row = 1;
                    }

                    if (!empty($next_option['attributes_image'])) {
                        $tmp_attributes_image .=
                            '<div class="attribImg">' .
                                '<label class="attribsCheckbox" for="' . $inputFieldId . '">' .
                                    zen_image(DIR_WS_IMAGES . $next_option['attributes_image']) .
                                    (PRODUCT_IMAGES_ATTRIBUTES_NAMES === '1' ? '<br>' . $next_option['products_options_values_name'] : '') .
                                    (!empty($products_options_details_noname) ? '<br>' . $products_options_details_noname : '') .
                                '</label><br>' .
                                zen_draw_checkbox_field($option_form_name . '[' . $products_options_value_id . ']', $products_options_value_id, $selected_attribute, 'id="' . $inputFieldId . '" ' . $data_properties . $field_disabled) .
                            '</div>' . "\n";
                    } else {
                        $tmp_attributes_image .=
                            '<div class="attribImg">' .
                                '<label class="attribsCheckbox" for="' . $inputFieldId . '">' .
                                    $next_option['products_options_values_name'] .
                                    (!empty($products_options_details_noname) ? '<br>' . $products_options_details_noname : '') .
                                '</label><br>' .
                                zen_draw_checkbox_field($option_form_name . '[' . $products_options_value_id . ']', $products_options_value_id, $selected_attribute, 'id="' . $inputFieldId . '" ' . $data_properties . $field_disabled) .
                            '</div>' . "\n";
                    }
                    break;

                case '5':
                    $tmp_attributes_image_row++;

                    if ($tmp_attributes_image_row > $next_option_name['products_options_images_per_row']) {
                        $tmp_attributes_image .= '<br class="clearBoth">' . "\n";
                        $tmp_attributes_image_row = 1;
                    }

                    if (!empty($next_option['attributes_image'])) {
                        $tmp_attributes_image .=
                            '<div class="attribImg">' .
                                zen_draw_checkbox_field($option_form_name . '[' . $products_options_value_id . ']', $products_options_value_id, $selected_attribute, 'id="' . $inputFieldId . '" ' . $data_properties . $field_disabled) .
                                '<br>' .
                                zen_image(DIR_WS_IMAGES . $next_option['attributes_image']) .
                                '<label class="attribsCheckbox" for="' . $inputFieldId . '">' .
                                    (PRODUCT_IMAGES_ATTRIBUTES_NAMES === '1' ? '<br>' . $next_option['products_options_values_name'] : '') .
                                    (!empty($products_options_details_noname) ? '<br>' . $products_options_details_noname : '') .
                                '</label>' .
                            '</div>' . "\n";
                    } else {
                        $tmp_attributes_image .=
                            '<div class="attribImg">' .
                                zen_draw_checkbox_field($option_form_name . '[' . $products_options_value_id . ']', $products_options_value_id, $selected_attribute, 'id="' . $inputFieldId . '" ' . $data_properties . $field_disabled) .
                                '<br>' .
                                '<label class="attribsCheckbox" for="' . $inputFieldId . '">' .
                                    $next_option['products_options_values_name'] .
                                    (!empty($products_options_details_noname) ? '<br>' . $products_options_details_noname : '') .
                                '</label>' .
                                '</div>' . "\n";
                    }
                    break;
            }
        }

        // text
        if ($products_options_type === PRODUCTS_OPTIONS_TYPE_TEXT) {
            $option_form_name = 'id[' . TEXT_PREFIX . $products_options_id . ']';
            if (!empty($_POST['id']) && is_array($_POST['id'])) {
                foreach ($_POST['id'] as $key => $value) {
                    if (preg_replace('/txt_/', '', $key) == $products_options_id) {
                        // use text area or input box based on setting of products_options_rows in the products_options table
                        if ($next_option_name['products_options_rows'] > 1) {
                            $tmp_html =
                                '  <input disabled="disabled" type="text" name="remaining' . TEXT_PREFIX . $products_options_id . '" size="3" maxlength="3" value="' . $next_option_name['products_options_length'] . '"> ' .
                                TEXT_MAXIMUM_CHARACTERS_ALLOWED .
                                '<br>';
                            $tmp_html .= '<textarea class="attribsTextarea" name="' . $option_form_name . '" rows="' . $next_option_name['products_options_rows'] . '" cols="' . $next_option_name['products_options_size'] . '" onkeydown="characterCount(this.form[\'' . $option_form_name . '\'],this.form.remaining' . TEXT_PREFIX . $products_options_id . ',' . $next_option_name['products_options_length'] . ');" onKeyUp="characterCount(this.form[\'' . $option_form_name . '\'],this.form.remaining' . TEXT_PREFIX . $products_options_id . ',' . $next_option_name['products_options_length'] . ');" id="' . $inputFieldId . '">' . stripslashes($value) . '</textarea>' . "\n";
                        } else {
                            $tmp_html = '<input type="text" name="' . $option_form_name . '" size="' . $next_option_name['products_options_size'] . '" maxlength="' . $next_option_name['products_options_length'] . '" value="' . htmlspecialchars($value, ENT_COMPAT, CHARSET, true) . '" id="' . $inputFieldId . '"'  . $data_properties . $field_disabled . '>  ';
                        }
                        $tmp_html .= $products_options_details;
                        break;
                    }
                }
            } else {
                $tmp_value = $_SESSION['cart']->contents[$_GET['products_id']]['attributes_values'][$products_options_id] ?? '';
                // use text area or input box based on setting of products_options_rows in the products_options table
                if ($next_option_name['products_options_rows'] > 1) {
                    $tmp_html = '  <input disabled="disabled" type="text" name="remaining' . TEXT_PREFIX . $products_options_id . '" size="3" maxlength="3" value="' . $next_option_name['products_options_length'] . '"> ' . TEXT_MAXIMUM_CHARACTERS_ALLOWED . '<br>';
                    $tmp_html .= '<textarea class="attribsTextarea" name="' . $option_form_name . '" rows="' . $next_option_name['products_options_rows'] . '" cols="' . $next_option_name['products_options_size'] . '" onkeydown="characterCount(this.form[\'' . $option_form_name . '\'],this.form.remaining' . TEXT_PREFIX . $products_options_id . ',' . $next_option_name['products_options_length'] . ');" onkeyup="characterCount(this.form[\'' . $option_form_name . '\'],this.form.remaining' . TEXT_PREFIX . $products_options_id . ',' . $next_option_name['products_options_length'] . ');" id="' . $inputFieldId . '">' . stripslashes($tmp_value) . '</textarea>' . "\n";
                    // $tmp_html .= '  <input type="reset">';
                } else {
                    $tmp_html = '<input type="text" name="' . $option_form_name . '" size="' . $next_option_name['products_options_size'] . '" maxlength="' . $next_option_name['products_options_length'] . '" value="' . htmlspecialchars($tmp_value, ENT_COMPAT, CHARSET, true) . '" id="' . $inputFieldId . '"'  . $data_properties . $field_disabled . '>  ';
                }
                $tmp_html .= $products_options_details;

                if (defined('ATTRIBUTES_ENABLED_TEXT_PRICES') && ATTRIBUTES_ENABLED_TEXT_PRICES === 'true') { // test ATTRIBUTES_ENABLED_TEXT_PRICES
                    $tmp_word_cnt_string = '';

                    // calculate word charges
                    $tmp_word_cnt_string = $tmp_value;
                    $tmp_word_cnt = zen_get_word_count($tmp_word_cnt_string, $next_option['attributes_price_words_free']);
                    $tmp_word_price = zen_get_word_count_price($tmp_word_cnt_string, $next_option['attributes_price_words_free'], $next_option['attributes_price_words']);

                    if ($next_option['attributes_price_words'] != 0) {
                        $tmp_html .= 
                            TEXT_PER_WORD .
                            $currencies->display_price($next_option['attributes_price_words'], $products_tax_rate) .
                            ($next_option['attributes_price_words_free'] != 0 ? TEXT_WORDS_FREE . $next_option['attributes_price_words_free'] : '');
                    }
                    if ($tmp_word_cnt != 0 && $tmp_word_price != 0) {
                        $tmp_word_price = $currencies->display_price($tmp_word_price, $products_tax_rate);
                        $tmp_html .= '<br>' . TEXT_CHARGES_WORD . ' ' . $tmp_word_cnt . ' = ' . $tmp_word_price;
                    }
                    // calculate letter charges
                    $tmp_letters_cnt_string = $tmp_value;
                    $tmp_letters_cnt = zen_get_letters_count($tmp_letters_cnt_string, $next_option['attributes_price_letters_free']);
                    $tmp_letters_price = zen_get_letters_count_price($tmp_letters_cnt_string, $next_option['attributes_price_letters_free'], $next_option['attributes_price_letters']);

                    if ($next_option['attributes_price_letters'] != 0) {
                        $tmp_html .=
                            TEXT_PER_LETTER .
                            $currencies->display_price($next_option['attributes_price_letters'], $products_tax_rate) .
                            ($next_option['attributes_price_letters_free'] != 0 ? TEXT_LETTERS_FREE . $next_option['attributes_price_letters_free'] : '');
                    }
                    if ($tmp_letters_cnt != 0 && $tmp_letters_price != 0) {
                        $tmp_letters_price = $currencies->display_price($tmp_letters_price, $products_tax_rate);
                        $tmp_html .= '<br>' . TEXT_CHARGES_LETTERS . ' ' . $tmp_letters_cnt . ' = ' . $tmp_letters_price;
                    }
                } // test ATTRIBUTES_ENABLED_TEXT_PRICES
                $tmp_html .= "\n";
            }
        }

        // file uploads
        if ($products_options_type === PRODUCTS_OPTIONS_TYPE_FILE) {
            $number_of_uploads++;
            $tmp_html = '';
            if (zen_run_normal() && zen_check_show_prices()) {
                $file_attribute_value = isset($_SESSION['cart']->contents[$prod_id]['attributes_values'][$products_options_id]) ? $_SESSION['cart']->contents[$prod_id]['attributes_values'][$products_options_id] : '';
                $tmp_html = '<input type="file" name="id[' . TEXT_PREFIX . $products_options_id . ']"  id="' . $inputFieldId . '" ' . $data_properties . '><br>' . $file_attribute_value . "\n" .
                    zen_draw_hidden_field(UPLOAD_PREFIX . $number_of_uploads, $products_options_id) . "\n" .
                    zen_draw_hidden_field(TEXT_PREFIX . UPLOAD_PREFIX . $number_of_uploads, $file_attribute_value);
            }
            $tmp_html .= $products_options_details;
        }

        $zco_notifier->notify('NOTIFY_ATTRIBUTES_MODULE_FORMAT_VALUE', array_merge($next_option, $next_option_name), $data_properties, $field_disabled, $attributeDetailsArrayForJson);

        // collect attribute image if it exists and to be drawn in table below
        if ($next_option_name['products_options_images_style'] == '0' || ($option_is_text_or_file || $products_options_type === '0')) {
            if (!empty($next_option['attributes_image'])) {
                $tmp_attributes_image_row++;

                if ($tmp_attributes_image_row > $next_option_name['products_options_images_per_row']) {
                    $tmp_attributes_image .= '<br class="clearBoth">' . "\n";
                    $tmp_attributes_image_row = 1;
                }

                // Do not show TEXT option value on images
                $tmp_attributes_image .=
                    '<div class="attribImg">' .
                        zen_image(DIR_WS_IMAGES . $next_option['attributes_image']) .
                        (PRODUCT_IMAGES_ATTRIBUTES_NAMES === '1' ? (($option_is_text_or_file === false) ? '<br>' . $next_option['products_options_values_name'] : '') : '') .
                    '</div>' . "\n";
            }
        }

        // Read Only - just for display purposes
        if ($products_options_type == PRODUCTS_OPTIONS_TYPE_READONLY) {
            // $tmp_html .= '<input type="hidden" name ="' . $option_form_name . '"' . '" value="' . stripslashes($next_option['products_options_values_name']) . ' SELECTED' . '">  ' . $next_option['products_options_values_name'];
            $tmp_html .= $products_options_details . '<br>';
        } else {
            $zv_display_select_option++;
        }

        // default
        // find default attribute if set for default dropdown
        if ($next_option['attributes_default'] === '1') {
            $selected_dropdown_attribute = $products_options_value_id;
        }
        $selected_attribute = $selected_dropdown_attribute;
    }

    $zco_notifier->notify('NOTIFY_ATTRIBUTES_MODULE_BEFORE_ASSEMBLE_OUTPUTS', $next_option, $data_properties, $inputFieldId, $field_disabled);

    $options_inputfield_id[] = $inputFieldId;
    $options_comment[] = $next_option_name['products_options_comment'];
    $options_comment_position[] = ($next_option_name['products_options_comment_position'] === '1' ? '1' : '0');

    // Option Name Type Display
    switch (true) {
        // text
        case ($products_options_type == PRODUCTS_OPTIONS_TYPE_TEXT):
            $options_name[] = '<label class="attribsInput" for="' . $inputFieldId . '">' . ($show_attributes_qty_prices_icon ? ATTRIBUTES_QTY_PRICE_SYMBOL : '') . $products_options_name . '</label>';
            $options_html_id[] = 'txt-attrib-' . $products_options_id;
            $options_menu[] = $tmp_html . "\n";
            break;
        // checkbox
        case ($products_options_type == PRODUCTS_OPTIONS_TYPE_CHECKBOX):
            $options_name[] = ($show_attributes_qty_prices_icon ? ATTRIBUTES_QTY_PRICE_SYMBOL : '') . $products_options_name;
            $options_html_id[] = 'chk-attrib-' . $products_options_id;
            $options_menu[] = $tmp_checkbox . "\n";
            break;
        // radio buttons
        case ($products_options_type == PRODUCTS_OPTIONS_TYPE_RADIO):
            $options_name[] = ($show_attributes_qty_prices_icon ? ATTRIBUTES_QTY_PRICE_SYMBOL : '') . $products_options_name;
            $options_html_id[] = 'rad-attrib-' . $products_options_id;
            $options_menu[] = $tmp_radio . "\n";
            break;
        // file upload
        case ($products_options_type == PRODUCTS_OPTIONS_TYPE_FILE):
            $options_name[] = '<label class="attribsUploads" for="' . $inputFieldId . '">' . ($show_attributes_qty_prices_icon ? ATTRIBUTES_QTY_PRICE_SYMBOL : '') . $products_options_name . '</label>';
            $options_html_id[] = 'upl-attrib-' . $products_options_id;
            $options_menu[] = $tmp_html . "\n";
            break;
        // READONLY
        case ($products_options_type == PRODUCTS_OPTIONS_TYPE_READONLY):
            $options_name[] = $products_options_name;
            $options_html_id[] = 'ro-attrib-' . $products_options_id;
            $options_menu[] = $tmp_html . "\n";
            break;
        // dropdown menu auto switch to selected radio button display
        case ($products_options->RecordCount() == 1):
            if ($show_attributes_qty_prices_icon) {
                $options_name[] = '<label class="switchedLabel ONE" for="' . $inputFieldId . '">' . ATTRIBUTES_QTY_PRICE_SYMBOL . $products_options_name . '</label>';
            } else {
                $options_name[] = $products_options_name;
            }
            $options_html_id[] = 'drprad-attrib-' . $products_options_id;
            $options_menu[] = zen_draw_radio_field('id[' . $products_options_id . ']', $products_options_value_id, true, 'id="' . $inputFieldId . '" ' . $data_properties . $field_disabled) . '<label class="attribsRadioButton" for="' . $inputFieldId . '">' . $products_options_details . '</label>' . "\n";
            break;

        // SELECT dropdown
        case ($products_options_type == PRODUCTS_OPTIONS_TYPE_SELECT):
            // normal dropdown menu display
            if (isset($_SESSION['cart']->contents[$prod_id]['attributes'][$products_options_id])) {
                $selected_attribute = $_SESSION['cart']->contents[$prod_id]['attributes'][$products_options_id];
            } else {
                // use customer-selected values
                if (!empty($_POST['id']) && is_array($_POST['id'])) {
                    foreach ($_POST['id'] as $key => $value) {
                        if ($key == $products_options_id) {
                            $selected_attribute = $value;
                            break;
                        }
                    }
                }
            }

            if ($show_attributes_qty_prices_icon) {
                $options_name[] = ATTRIBUTES_QTY_PRICE_SYMBOL . $products_options_name;
            } else {
                $options_name[] = '<label class="attribsSelect" for="' . $inputFieldId . '">' . $products_options_name . '</label>';
            }
            $options_html_id[] = 'drp-attrib-' . $products_options_id;
            $options_menu[] = zen_draw_pull_down_menu('id[' . $products_options_id . ']', $products_options_array, $selected_attribute, 'id="' . $inputFieldId . '" ' . $data_properties . $field_disabled) . "\n";
            break;

        default:
            $zco_notifier->notify('NOTIFY_ATTRIBUTES_MODULE_DEFAULT_SWITCH', $next_option_name, $options_name, $options_menu, $options_comment, $options_comment_position, $options_html_id, $data_properties, $options_inputfield_id);
            break;
    }

    // attributes images table
    $options_attributes_image[] = trim($tmp_attributes_image) . "\n";

    $zco_notifier->notify('NOTIFY_ATTRIBUTES_MODULE_OPTION_BUILT', $next_option_name, $options_name, $options_menu, $options_comment, $options_comment_position, $options_html_id, $options_attributes_image, $data_properties, $options_inputfield_id);
}

$zco_notifier->notify('NOTIFY_ATTRIBUTES_MODULE_END', $prod_id, $options_name, $options_menu, $options_comment, $options_comment_position, $options_html_id, $options_attributes_image, $options_inputfield_id, $attributeDetailsArrayForJson);


// manage filename uploads
$_GET['number_of_uploads'] = $number_of_uploads;
zen_draw_hidden_field('number_of_uploads', $number_of_uploads);
