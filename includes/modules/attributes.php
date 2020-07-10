<?php
/**
 * attributes module
 *
 * Prepares attributes content for rendering in the template system
 * Prepares HTML for input fields with required uniqueness so template can display them as needed and keep collected data in proper fields
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2019 Dec 16 Modified in v1.5.7 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

$show_onetime_charges_description = false;
$show_attributes_qty_prices_description = false;

// Determine number of attributes associated with this product
$sql = "SELECT count(*) as total
        FROM " . TABLE_PRODUCTS_OPTIONS . " popt
        LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " patrib ON (popt.products_options_id = patrib.options_id)
        WHERE patrib.products_id = :products_id
        AND popt.language_id = :language_id
        LIMIT 1";
$sql = $db->bindVars($sql, ':products_id', $_GET['products_id'], 'integer');
$sql = $db->bindVars($sql, ':language_id', $_SESSION['languages_id'], 'integer');
$pr_attr = $db->Execute($sql);

if ($pr_attr->fields['total'] < 1) return;
// Only process the rest of this file if attributes are defined for this product


$prod_id = $_GET['products_id'];
$number_of_uploads = 0;
$zv_display_select_option = 0;
$options_name = $options_menu = $options_html_id = $options_inputfield_id = $options_comment = $options_comment_position = $options_attributes_image = array();
$attributeDetailsArrayForJson = array();

$discount_type = zen_get_products_sale_discount_type((int)$_GET['products_id']);
$discount_amount = zen_get_discount_calc((int)$_GET['products_id']);
$products_price_is_priced_by_attributes = zen_get_products_price_is_priced_by_attributes((int)$_GET['products_id']);


if (PRODUCTS_OPTIONS_SORT_ORDER == '0') {
    $options_order_by = ' order by LPAD(popt.products_options_sort_order,11,"0"), popt.products_options_name';
} else {
    $options_order_by = ' order by popt.products_options_name';
}

$sql = "SELECT DISTINCT popt.products_options_id, popt.products_options_name, popt.products_options_sort_order,
            popt.products_options_type, popt.products_options_length, popt.products_options_comment,
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


if (PRODUCTS_OPTIONS_SORT_BY_PRICE == '1') {
    $order_by = ' order by LPAD(pa.products_options_sort_order,11,"0"), pov.products_options_values_name';
} else {
    $order_by = ' order by LPAD(pa.products_options_sort_order,11,"0"), pa.options_values_price';
}

while (!$products_options_names->EOF) {
    $products_options_array = array();

    $products_options_id = $products_options_names->fields['products_options_id'];
    $products_options_type = $products_options_names->fields['products_options_type'];
    $products_options_name = $products_options_names->fields['products_options_name'];


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
            LEFT JOIN " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov ON (pa.options_values_id = pov.products_options_values_id)
            WHERE pa.products_id = :products_id
            AND   pa.options_id = :options_id
            AND   pov.language_id = :language_id " .
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

    $tmp_attributes_image = '';
    $tmp_attributes_image_row = 0;
    $show_attributes_qty_prices_icon = false;
    $i = 0;

    $zco_notifier->notify('NOTIFY_ATTRIBUTES_MODULE_START_OPTION', $products_options_names->fields);

    if (!isset($products_options_names->fields['products_options_comment_position'])) {
        $products_options_names->fields['products_options_comment_position'] = '0';
    }

    // loop through each Attribute
    while (!$products_options->EOF) {
        $products_options_value_id = $products_options->fields['products_options_values_id'];

        // for generated html labels to match input fields, and client-side code to identify fields
        $inputFieldId = 'attrib-' . $products_options_id . ($products_options_type == PRODUCTS_OPTIONS_TYPE_SELECT ? '' : '-' . $products_options_value_id);

        $selected_attribute = false; // boolean, used for radio/checkbox/select
        $data_properties = ' data-key="' . $inputFieldId . '" '; // observers can insert data-x="y" values
        $field_disabled = ''; // empty or disabled="disabled"

        $products_options_display_price = '';
        $new_attributes_price = '';
        $price_onetime = '';

        $products_options_array[] = array(
            'id'   => $products_options_value_id,
            'text' => $products_options->fields['products_options_values_name'],
        );

        $attributeDetailsArrayForJson[$inputFieldId] = array_merge(
            array(
                'field_id' => $inputFieldId,
                'name' => $products_options_names->fields['products_options_name'],
                'attr_id' => $products_options_names->fields['products_options_id'],
            ), $products_options->fields, $products_options_names->fields);

        $zco_notifier->notify('NOTIFY_ATTRIBUTES_MODULE_START_OPTIONS_LOOP', $i++, $products_options->fields, $products_options_names->fields, $data_properties, $field_disabled, $attributeDetailsArrayForJson);


        // DEAL WITH PRICE AND WEIGHT DISPLAY
        if (
            ((CUSTOMERS_APPROVAL == '2' && !zen_is_logged_in()) || STORE_STATUS == '1')
            || ((CUSTOMERS_APPROVAL_AUTHORIZATION == '1' || CUSTOMERS_APPROVAL_AUTHORIZATION == '2') && $_SESSION['customers_authorization'] == '')
            || (CUSTOMERS_APPROVAL == '2' && $_SESSION['customers_authorization'] == '2')
            || (CUSTOMERS_APPROVAL_AUTHORIZATION == '2' && $_SESSION['customers_authorization'] != 0)
        ) {

            $new_attributes_price = 0;
            $new_options_values_price = 0;
            $products_options_display_price = '';
            $price_onetime = '';
        } else {
            // collect price information if it exists
            if ($products_options->fields['attributes_discounted'] == 1) {
                // apply product discount to attributes if discount is on
                $new_attributes_price = zen_get_attributes_price_final($products_options->fields["products_attributes_id"], 1, '', 'false', $products_price_is_priced_by_attributes);
                //$new_attributes_price = zen_get_discount_calc((int)$_GET['products_id'], true, $new_attributes_price);
            } else {
                // discount is off do not apply
                $new_attributes_price = $products_options->fields['options_values_price'];
            }

            // reverse negative values for display
            if ($new_attributes_price < 0) {
                $new_attributes_price = -$new_attributes_price;
            }

            if ($products_options->fields['attributes_price_onetime'] != 0 || $products_options->fields['attributes_price_factor_onetime'] != 0) {
                $show_onetime_charges_description = true;
                $new_onetime_charges = zen_get_attributes_price_final_onetime($products_options->fields["products_attributes_id"], 1, '');
                $price_onetime = TEXT_ONETIME_CHARGE_SYMBOL . $currencies->display_price($new_onetime_charges, zen_get_tax_rate($product_info->fields['products_tax_class_id']));
            } else {
                $price_onetime = '';
            }

            if ($products_options->fields['attributes_qty_prices'] != '' || $products_options->fields['attributes_qty_prices_onetime'] != '') {
                $show_attributes_qty_prices_description = true;
                $show_attributes_qty_prices_icon = true;
            }

            if ($products_options->fields['options_values_price'] != '0' && ($products_options->fields['product_attribute_is_free'] != '1' && $product_info->fields['product_is_free'] != '1')) {
                // show sale maker discount if a percentage
                $products_options_display_price = ATTRIBUTES_PRICE_DELIMITER_PREFIX . $products_options->fields['price_prefix'] . $currencies->display_price($new_attributes_price, zen_get_tax_rate($product_info->fields['products_tax_class_id'])) . ATTRIBUTES_PRICE_DELIMITER_SUFFIX;

                $zco_notifier->notify('NOTIFY_ATTRIBUTES_MODULE_SALEMAKER_DISPLAY_PRICE_PERCENTAGE', $products_options->fields, $product_info->fields, $products_options_display_price, $data_properties);

            } else {
                // if product_is_free and product_attribute_is_free
                if ($products_options->fields['product_attribute_is_free'] == '1' && $product_info->fields['product_is_free'] == '1') {
                    $products_options_display_price = TEXT_ATTRIBUTES_PRICE_WAS . $products_options->fields['price_prefix'] . $currencies->display_price($new_attributes_price, zen_get_tax_rate($product_info->fields['products_tax_class_id'])) . TEXT_ATTRIBUTE_IS_FREE;
                } else {
                    // normal price
                    if (empty($new_attributes_price)) {
                        $products_options_display_price = '';
                    } else {
                        $products_options_display_price = ATTRIBUTES_PRICE_DELIMITER_PREFIX . $products_options->fields['price_prefix'] . $currencies->display_price($new_attributes_price, zen_get_tax_rate($product_info->fields['products_tax_class_id'])) . ATTRIBUTES_PRICE_DELIMITER_SUFFIX;
                    }
                }
            }

            $products_options_display_price .= $price_onetime;

        } // approve

        $zco_notifier->notify('NOTIFY_ATTRIBUTES_MODULE_ORIGINAL_PRICE', $products_options->fields, $products_options_array, $products_options_display_price, $data_properties);

        $products_options_array[count($products_options_array) - 1]['text'] .= $products_options_display_price;

        // collect weight information if it exists
        if ($flag_show_weight_attrib_for_this_prod_type == '1' && $products_options->fields['products_attributes_weight'] != '0') {
            $products_options_display_weight = ATTRIBUTES_WEIGHT_DELIMITER_PREFIX . $products_options->fields['products_attributes_weight_prefix'] . round($products_options->fields['products_attributes_weight'], 2) .  TEXT_PRODUCT_WEIGHT_UNIT . ATTRIBUTES_WEIGHT_DELIMITER_SUFFIX;
            $products_options_array[count($products_options_array) - 1]['text'] .= $products_options_display_weight;
        } else {
            // reset
            $products_options_display_weight = '';
        }

        // prepare product options details

        if ($products_options->RecordCount() == 1
            || in_array($products_options_type, array(
                PRODUCTS_OPTIONS_TYPE_FILE,
                PRODUCTS_OPTIONS_TYPE_TEXT,
                PRODUCTS_OPTIONS_TYPE_CHECKBOX,
                PRODUCTS_OPTIONS_TYPE_RADIO,
                PRODUCTS_OPTIONS_TYPE_READONLY,
                )
            )
        ) {
            $products_options_details = '';
            // don't show option value name on TEXT or filename
            if ($products_options_type != PRODUCTS_OPTIONS_TYPE_TEXT && $products_options_type != PRODUCTS_OPTIONS_TYPE_FILE) {
                $products_options_details = $products_options->fields['products_options_values_name'];
            }
            $products_options_details .= $products_options_display_price;
            $products_options_details_noname = $products_options_display_price;

            if ($products_options->fields['products_attributes_weight'] != 0) {
                if ($products_options_names->fields['products_options_images_style'] >= 3) {
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
        if ($products_options_type == PRODUCTS_OPTIONS_TYPE_RADIO) {
            if ($_SESSION['cart']->in_cart($prod_id)) {
                if (isset($_SESSION['cart']->contents[$prod_id]['attributes'][$products_options_id]) && $_SESSION['cart']->contents[$prod_id]['attributes'][$products_options_id] == $products_options_value_id) {
                    $selected_attribute = $_SESSION['cart']->contents[$prod_id]['attributes'][$products_options_id];
                }
            } else {
                // $selected_attribute = ($products_options->fields['attributes_default']=='1' ? true : false);
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
                        //$selected_attribute = ($products_options->fields['attributes_default']=='1' ? true : false);
                    // select default radio button or auto select single radio buttons
                    $selected_attribute = ($products_options->fields['attributes_default'] == '1' ? true : ($products_options->RecordCount() == 1));
                }
            }

            $zco_notifier->notify('NOTIFY_ATTRIBUTES_MODULE_RADIO_SELECTED', $products_options->fields, $data_properties);

            switch ($products_options_names->fields['products_options_images_style']) {
                case '0':
                    $tmp_radio .= zen_draw_radio_field('id[' . $products_options_id . ']', $products_options_value_id, $selected_attribute, 'id="' . $inputFieldId . '" ' . $data_properties . $field_disabled) . '<label class="attribsRadioButton zero" for="' . $inputFieldId . '">' . $products_options_details . '</label><br>' . "\n";
                    break;
                case '1':
                    $tmp_radio .= zen_draw_radio_field('id[' . $products_options_id . ']', $products_options_value_id, $selected_attribute, 'id="' . $inputFieldId . '" ' . $data_properties . $field_disabled) . '<label class="attribsRadioButton one" for="' . $inputFieldId . '">' . (!empty($products_options->fields['attributes_image']) ? zen_image(DIR_WS_IMAGES . $products_options->fields['attributes_image'], '', '', '') . '  ' : '') . $products_options_details . '</label><br>' . "\n";
                    break;
                case '2':
                    $tmp_radio .= zen_draw_radio_field('id[' . $products_options_id . ']', $products_options_value_id, $selected_attribute, 'id="' . $inputFieldId . '" ' . $data_properties . $field_disabled) . '<label class="attribsRadioButton two" for="' . $inputFieldId . '">' . $products_options_details . (!empty($products_options->fields['attributes_image']) ? '<br>' . zen_image(DIR_WS_IMAGES . $products_options->fields['attributes_image'], '', '', '') : '') . '</label><br>' . "\n";
                    break;
                case '3':
                    $tmp_attributes_image_row++;
                    if ($tmp_attributes_image_row > $products_options_names->fields['products_options_images_per_row']) {
                        $tmp_attributes_image .= '<br class="clearBoth">' . "\n";
                        $tmp_attributes_image_row = 1;
                    }

                    if (!empty($products_options->fields['attributes_image'])) {
                        $tmp_attributes_image .= '<div class="attribImg">' . zen_draw_radio_field('id[' . $products_options_id . ']', $products_options_value_id, $selected_attribute, 'id="' . $inputFieldId . '" ' . $data_properties . $field_disabled) . '<label class="attribsRadioButton three" for="' . $inputFieldId . '">' . zen_image(DIR_WS_IMAGES . $products_options->fields['attributes_image']) . (PRODUCT_IMAGES_ATTRIBUTES_NAMES == '1' ? '<br>' . $products_options->fields['products_options_values_name'] : '') . $products_options_details_noname . '</label></div>' . "\n";
                    } else {
                        $tmp_attributes_image .= '<div class="attribImg">' . zen_draw_radio_field('id[' . $products_options_id . ']', $products_options_value_id, $selected_attribute, 'id="' . $inputFieldId . '" ' . $data_properties . $field_disabled) . '<br>' . '<label class="attribsRadioButton threeA" for="' . $inputFieldId . '">' . $products_options->fields['products_options_values_name'] . $products_options_details_noname . '</label></div>' . "\n";
                    }
                    break;

                case '4':
                    $tmp_attributes_image_row++;

                    if ($tmp_attributes_image_row > $products_options_names->fields['products_options_images_per_row']) {
                        $tmp_attributes_image .= '<br class="clearBoth">' . "\n";
                        $tmp_attributes_image_row = 1;
                    }

                    if (!empty($products_options->fields['attributes_image'])) {
                        $tmp_attributes_image .= '<div class="attribImg">' . '<label class="attribsRadioButton four" for="' . $inputFieldId . '">' . zen_image(DIR_WS_IMAGES . $products_options->fields['attributes_image']) . (PRODUCT_IMAGES_ATTRIBUTES_NAMES == '1' ? '<br>' . $products_options->fields['products_options_values_name'] : '') . (!empty($products_options_details_noname) ? '<br>' . $products_options_details_noname : '') . '</label><br>' . zen_draw_radio_field('id[' . $products_options_id . ']', $products_options_value_id, $selected_attribute, 'id="' . $inputFieldId . '" ' . $data_properties . $field_disabled) . '</div>' . "\n";
                    } else {
                        $tmp_attributes_image .= '<div class="attribImg">' . '<label class="attribsRadioButton fourA" for="' . $inputFieldId . '">' . $products_options->fields['products_options_values_name'] . (!empty($products_options_details_noname) ? '<br>' . $products_options_details_noname : '') . '</label><br>' . zen_draw_radio_field('id[' . $products_options_id . ']', $products_options_value_id, $selected_attribute, 'id="' . $inputFieldId . '" ' . $data_properties . $field_disabled) . '</div>' . "\n";
                    }
                    break;

                case '5':
                    $tmp_attributes_image_row++;

                    if ($tmp_attributes_image_row > $products_options_names->fields['products_options_images_per_row']) {
                        $tmp_attributes_image .= '<br class="clearBoth">' . "\n";
                        $tmp_attributes_image_row = 1;
                    }

                    if (!empty($products_options->fields['attributes_image'])) {
                        $tmp_attributes_image .= '<div class="attribImg">' . zen_draw_radio_field('id[' . $products_options_id . ']', $products_options_value_id, $selected_attribute, 'id="' . $inputFieldId . '" ' . $data_properties . $field_disabled) . '<br>' . '<label class="attribsRadioButton five" for="' . $inputFieldId . '">' . zen_image(DIR_WS_IMAGES . $products_options->fields['attributes_image']) . (PRODUCT_IMAGES_ATTRIBUTES_NAMES == '1' ? '<br>' . $products_options->fields['products_options_values_name'] : '') . (!empty($products_options_details_noname) ? '<br>' . $products_options_details_noname : '') . '</label></div>';
                    } else {
                        $tmp_attributes_image .= '<div class="attribImg">' . zen_draw_radio_field('id[' . $products_options_id . ']', $products_options_value_id, $selected_attribute, 'id="' . $inputFieldId . '" ' . $data_properties . $field_disabled) . '<br>' . '<label class="attribsRadioButton fiveA" for="' . $inputFieldId . '">' . $products_options->fields['products_options_values_name'] . (!empty($products_options_details_noname) ? '<br>' . $products_options_details_noname : '') . '</label></div>';
                    }
                    break;
            }
        }

        // checkboxes
        if ($products_options_type == PRODUCTS_OPTIONS_TYPE_CHECKBOX) {
            $string = $products_options_id . '_chk' . $products_options_value_id;
            if ($_SESSION['cart']->in_cart($prod_id)) {
                if (isset($_SESSION['cart']->contents[$prod_id]['attributes'][$string]) && $_SESSION['cart']->contents[$prod_id]['attributes'][$string] == $products_options_value_id) {
                    $selected_attribute = true;
                }
            } else {
                // $selected_attribute = ($products_options->fields['attributes_default']=='1' ? true : false);
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
                    $selected_attribute = ($products_options->fields['attributes_default'] == '1');
                }
            }

            $zco_notifier->notify('NOTIFY_ATTRIBUTES_MODULE_CHECKBOX_SELECTED', $products_options->fields, $data_properties);

            switch ($products_options_names->fields['products_options_images_style']) {
                case '0':
                    $tmp_checkbox .= zen_draw_checkbox_field('id[' . $products_options_id . '][' . $products_options_value_id . ']', $products_options_value_id, $selected_attribute, 'id="' . $inputFieldId . '" ' . $data_properties . $field_disabled) . '<label class="attribsCheckbox" for="' . $inputFieldId . '">' . $products_options_details . '</label><br>' . "\n";
                    break;
                case '1':
                    $tmp_checkbox .= zen_draw_checkbox_field('id[' . $products_options_id . '][' . $products_options_value_id . ']', $products_options_value_id, $selected_attribute, 'id="' . $inputFieldId . '" ' . $data_properties . $field_disabled) . '<label class="attribsCheckbox" for="' . $inputFieldId . '">' . (!empty($products_options->fields['attributes_image']) ? zen_image(DIR_WS_IMAGES . $products_options->fields['attributes_image'], '', '', '') . '  ' : '') . $products_options_details . '</label><br>' . "\n";
                    break;
                case '2':
                    $tmp_checkbox .= zen_draw_checkbox_field('id[' . $products_options_id . '][' . $products_options_value_id . ']', $products_options_value_id, $selected_attribute, 'id="' . $inputFieldId . '" ' . $data_properties . $field_disabled) . '<label class="attribsCheckbox" for="' . $inputFieldId . '">' . $products_options_details . (!empty($products_options->fields['attributes_image']) ? '<br>' . zen_image(DIR_WS_IMAGES . $products_options->fields['attributes_image'], '', '', '') : '') . '</label><br>' . "\n";
                    break;

                case '3':
                    $tmp_attributes_image_row++;

                    if ($tmp_attributes_image_row > $products_options_names->fields['products_options_images_per_row']) {
                        $tmp_attributes_image .= '<br class="clearBoth">' . "\n";
                        $tmp_attributes_image_row = 1;
                    }

                    if (!empty($products_options->fields['attributes_image'])) {
                        $tmp_attributes_image .= '<div class="attribImg">' . zen_draw_checkbox_field('id[' . $products_options_id . '][' . $products_options_value_id . ']', $products_options_value_id, $selected_attribute, 'id="' . $inputFieldId . '" ' . $data_properties . $field_disabled) . '<label class="attribsCheckbox" for="' . $inputFieldId . '">' . zen_image(DIR_WS_IMAGES . $products_options->fields['attributes_image']) . (PRODUCT_IMAGES_ATTRIBUTES_NAMES == '1' ? '<br>' . $products_options->fields['products_options_values_name'] : '') . $products_options_details_noname . '</label></div>' . "\n";
                    } else {
                        $tmp_attributes_image .= '<div class="attribImg">' . zen_draw_checkbox_field('id[' . $products_options_id . '][' . $products_options_value_id . ']', $products_options_value_id, $selected_attribute, 'id="' . $inputFieldId . '" ' . $data_properties . $field_disabled) . '<br>' . '<label class="attribsCheckbox" for="' . $inputFieldId . '">' . $products_options->fields['products_options_values_name'] . $products_options_details_noname . '</label></div>' . "\n";
                    }
                    break;

                case '4':
                    $tmp_attributes_image_row++;

                    if ($tmp_attributes_image_row > $products_options_names->fields['products_options_images_per_row']) {
                        $tmp_attributes_image .= '<br class="clearBoth">' . "\n";
                        $tmp_attributes_image_row = 1;
                    }

                    if (!empty($products_options->fields['attributes_image'])) {
                        $tmp_attributes_image .= '<div class="attribImg">' . '<label class="attribsCheckbox" for="' . $inputFieldId . '">' . zen_image(DIR_WS_IMAGES . $products_options->fields['attributes_image']) . (PRODUCT_IMAGES_ATTRIBUTES_NAMES == '1' ? '<br>' . $products_options->fields['products_options_values_name'] : '') . (!empty($products_options_details_noname) ? '<br>' . $products_options_details_noname : '') . '</label><br>' . zen_draw_checkbox_field('id[' . $products_options_id . '][' . $products_options_value_id . ']', $products_options_value_id, $selected_attribute, 'id="' . $inputFieldId . '" ' . $data_properties . $field_disabled) . '</div>' . "\n";
                    } else {
                        $tmp_attributes_image .= '<div class="attribImg">' . '<label class="attribsCheckbox" for="' . $inputFieldId . '">' . $products_options->fields['products_options_values_name'] . (!empty($products_options_details_noname) ? '<br>' . $products_options_details_noname : '') . '</label><br>' . zen_draw_checkbox_field('id[' . $products_options_id . '][' . $products_options_value_id . ']', $products_options_value_id, $selected_attribute, 'id="' . $inputFieldId . '" ' . $data_properties . $field_disabled) . '</div>' . "\n";
                    }
                    break;

                case '5':
                    $tmp_attributes_image_row++;

                    if ($tmp_attributes_image_row > $products_options_names->fields['products_options_images_per_row']) {
                        $tmp_attributes_image .= '<br class="clearBoth">' . "\n";
                        $tmp_attributes_image_row = 1;
                    }

                    if (!empty($products_options->fields['attributes_image'])) {
                        $tmp_attributes_image .= '<div class="attribImg">' . zen_draw_checkbox_field('id[' . $products_options_id . '][' . $products_options_value_id . ']', $products_options_value_id, $selected_attribute, 'id="' . $inputFieldId . '" ' . $data_properties . $field_disabled) . '<br>' . zen_image(DIR_WS_IMAGES . $products_options->fields['attributes_image']) . '<label class="attribsCheckbox" for="' . $inputFieldId . '">' . (PRODUCT_IMAGES_ATTRIBUTES_NAMES == '1' ? '<br>' . $products_options->fields['products_options_values_name'] : '') . (!empty($products_options_details_noname) ? '<br>' . $products_options_details_noname : '') . '</label></div>' . "\n";
                    } else {
                        $tmp_attributes_image .= '<div class="attribImg">' . zen_draw_checkbox_field('id[' . $products_options_id . '][' . $products_options_value_id . ']', $products_options_value_id, $selected_attribute, 'id="' . $inputFieldId . '" ' . $data_properties . $field_disabled) . '<br>' . '<label class="attribsCheckbox" for="' . $inputFieldId . '">' . $products_options->fields['products_options_values_name'] . (!empty($products_options_details_noname) ? '<br>' . $products_options_details_noname : '') . '</label></div>' . "\n";
                    }
                    break;
            }
        }


        // text
        if ($products_options_type == PRODUCTS_OPTIONS_TYPE_TEXT) {
            if (!empty($_POST['id']) && is_array($_POST['id'])) {
                foreach ($_POST['id'] as $key => $value) {
                    if (preg_replace('/txt_/', '', $key) == $products_options_id) {
                        // use text area or input box based on setting of products_options_rows in the products_options table
                        if ($products_options_names->fields['products_options_rows'] > 1) {
                            $tmp_html = '  <input disabled="disabled" type="text" name="remaining' . TEXT_PREFIX . $products_options_id . '" size="3" maxlength="3" value="' . $products_options_names->fields['products_options_length'] . '"> ' . TEXT_MAXIMUM_CHARACTERS_ALLOWED . '<br>';
                            $tmp_html .= '<textarea class="attribsTextarea" name="id[' . TEXT_PREFIX . $products_options_id . ']" rows="' . $products_options_names->fields['products_options_rows'] . '" cols="' . $products_options_names->fields['products_options_size'] . '" onKeyDown="characterCount(this.form[\'' . 'id[' . TEXT_PREFIX . $products_options_id . ']\'],this.form.remaining' . TEXT_PREFIX . $products_options_id . ',' . $products_options_names->fields['products_options_length'] . ');" onKeyUp="characterCount(this.form[\'' . 'id[' . TEXT_PREFIX . $products_options_id . ']\'],this.form.remaining' . TEXT_PREFIX . $products_options_id . ',' . $products_options_names->fields['products_options_length'] . ');" id="' . $inputFieldId . '" >' . stripslashes($value) . '</textarea>' . "\n";
                        } else {
                            $tmp_html = '<input type="text" name="id[' . TEXT_PREFIX . $products_options_id . ']" size="' . $products_options_names->fields['products_options_size'] . '" maxlength="' . $products_options_names->fields['products_options_length'] . '" value="' . htmlspecialchars($value, ENT_COMPAT, CHARSET, true) . '" id="' . $inputFieldId . '"'  . $data_properties . $field_disabled . '>  ';
                        }
                        $tmp_html .= $products_options_details;
                        break;
                    }
                }
            } else {
                $tmp_value = isset($_SESSION['cart']->contents[$_GET['products_id']]['attributes_values'][$products_options_id]) ? $_SESSION['cart']->contents[$_GET['products_id']]['attributes_values'][$products_options_id] : '';
                // use text area or input box based on setting of products_options_rows in the products_options table
                if ($products_options_names->fields['products_options_rows'] > 1) {
                    $tmp_html = '  <input disabled="disabled" type="text" name="remaining' . TEXT_PREFIX . $products_options_id . '" size="3" maxlength="3" value="' . $products_options_names->fields['products_options_length'] . '"> ' . TEXT_MAXIMUM_CHARACTERS_ALLOWED . '<br>';
                    $tmp_html .= '<textarea class="attribsTextarea" name="id[' . TEXT_PREFIX . $products_options_id . ']" rows="' . $products_options_names->fields['products_options_rows'] . '" cols="' . $products_options_names->fields['products_options_size'] . '" onkeydown="characterCount(this.form[\'' . 'id[' . TEXT_PREFIX . $products_options_id . ']\'],this.form.remaining' . TEXT_PREFIX . $products_options_id . ',' . $products_options_names->fields['products_options_length'] . ');" onkeyup="characterCount(this.form[\'' . 'id[' . TEXT_PREFIX . $products_options_id . ']\'],this.form.remaining' . TEXT_PREFIX . $products_options_id . ',' . $products_options_names->fields['products_options_length'] . ');" id="' . $inputFieldId . '" >' . stripslashes($tmp_value) . '</textarea>' . "\n";
                    // $tmp_html .= '  <input type="reset">';
                } else {
                    $tmp_html = '<input type="text" name="id[' . TEXT_PREFIX . $products_options_id . ']" size="' . $products_options_names->fields['products_options_size'] . '" maxlength="' . $products_options_names->fields['products_options_length'] . '" value="' . htmlspecialchars($tmp_value, ENT_COMPAT, CHARSET, true) . '" id="' . $inputFieldId . '"'  . $data_properties . $field_disabled . '>  ';
                }
                $tmp_html .= $products_options_details;
                $tmp_word_cnt_string = '';
                // calculate word charges
                $tmp_word_cnt = 0;
                $tmp_word_cnt_string = $tmp_value;
                $tmp_word_cnt = zen_get_word_count($tmp_word_cnt_string, $products_options->fields['attributes_price_words_free']);
                $tmp_word_price = zen_get_word_count_price($tmp_word_cnt_string, $products_options->fields['attributes_price_words_free'], $products_options->fields['attributes_price_words']);

                if ($products_options->fields['attributes_price_words'] != 0) {
                    $tmp_html .= TEXT_PER_WORD . $currencies->display_price($products_options->fields['attributes_price_words'], zen_get_tax_rate($product_info->fields['products_tax_class_id'])) . ($products_options->fields['attributes_price_words_free'] !=0 ? TEXT_WORDS_FREE . $products_options->fields['attributes_price_words_free'] : '');
                }
                if ($tmp_word_cnt != 0 && $tmp_word_price != 0) {
                    $tmp_word_price = $currencies->display_price($tmp_word_price, zen_get_tax_rate($product_info->fields['products_tax_class_id']));
                    $tmp_html .= '<br>' . TEXT_CHARGES_WORD . ' ' . $tmp_word_cnt . ' = ' . $tmp_word_price;
                }
                // calculate letter charges
                $tmp_letters_cnt = 0;
                $tmp_letters_cnt_string = $tmp_value;
                $tmp_letters_cnt = zen_get_letters_count($tmp_letters_cnt_string, $products_options->fields['attributes_price_letters_free']);
                $tmp_letters_price = zen_get_letters_count_price($tmp_letters_cnt_string, $products_options->fields['attributes_price_letters_free'], $products_options->fields['attributes_price_letters']);

                if ($products_options->fields['attributes_price_letters'] != 0) {
                    $tmp_html .= TEXT_PER_LETTER . $currencies->display_price($products_options->fields['attributes_price_letters'], zen_get_tax_rate($product_info->fields['products_tax_class_id'])) . ($products_options->fields['attributes_price_letters_free'] != 0 ? TEXT_LETTERS_FREE . $products_options->fields['attributes_price_letters_free'] : '');
                }
                if ($tmp_letters_cnt != 0 && $tmp_letters_price != 0) {
                    $tmp_letters_price = $currencies->display_price($tmp_letters_price, zen_get_tax_rate($product_info->fields['products_tax_class_id']));
                    $tmp_html .= '<br>' . TEXT_CHARGES_LETTERS . ' ' . $tmp_letters_cnt . ' = ' . $tmp_letters_price;
                }
                $tmp_html .= "\n";
            }
        }

        // file uploads
        if ($products_options_type == PRODUCTS_OPTIONS_TYPE_FILE) {
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


        $zco_notifier->notify('NOTIFY_ATTRIBUTES_MODULE_FORMAT_VALUE', array_merge($products_options->fields, $products_options_names->fields), $data_properties, $field_disabled, $attributeDetailsArrayForJson);


        // collect attribute image if it exists and to be drawn in table below
        if ($products_options_names->fields['products_options_images_style'] == '0' || ($products_options_type == PRODUCTS_OPTIONS_TYPE_FILE || $products_options_type == PRODUCTS_OPTIONS_TYPE_TEXT || $products_options_type == '0')) {
            if (!empty($products_options->fields['attributes_image'])) {
                $tmp_attributes_image_row++;

                if ($tmp_attributes_image_row > $products_options_names->fields['products_options_images_per_row']) {
                    $tmp_attributes_image .= '<br class="clearBoth">' . "\n";
                    $tmp_attributes_image_row = 1;
                }

                // Do not show TEXT option value on images
                $tmp_attributes_image .= '<div class="attribImg">' . zen_image(DIR_WS_IMAGES . $products_options->fields['attributes_image']) . (PRODUCT_IMAGES_ATTRIBUTES_NAMES == '1' ? (($products_options_type != PRODUCTS_OPTIONS_TYPE_TEXT && $products_options_type != PRODUCTS_OPTIONS_TYPE_FILE) ? '<br>' . $products_options->fields['products_options_values_name'] : '') : '') . '</div>' . "\n";
            }
        }

        // Read Only - just for display purposes
        if ($products_options_type == PRODUCTS_OPTIONS_TYPE_READONLY) {
            // $tmp_html .= '<input type="hidden" name ="id[' . $products_options_id . ']"' . '" value="' . stripslashes($products_options->fields['products_options_values_name']) . ' SELECTED' . '">  ' . $products_options->fields['products_options_values_name'];
            $tmp_html .= $products_options_details . '<br>';
        } else {
            $zv_display_select_option++;
        }


        // default
        // find default attribute if set for default dropdown
        if ($products_options->fields['attributes_default'] == '1') {
            $selected_attribute = $products_options_value_id;
        }

        $products_options->MoveNext();
        // end of inner while() loop
    }



    $zco_notifier->notify('NOTIFY_ATTRIBUTES_MODULE_BEFORE_ASSEMBLE_OUTPUTS', $products_options->fields, $data_properties, $inputFieldId, $field_disabled);

    $options_inputfield_id[] = $inputFieldId;
    $options_comment[] = $products_options_names->fields['products_options_comment'];
    $options_comment_position[] = ($products_options_names->fields['products_options_comment_position'] == '1' ? '1' : '0');

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
            $zco_notifier->notify('NOTIFY_ATTRIBUTES_MODULE_DEFAULT_SWITCH', $products_options_names->fields, $options_name, $options_menu, $options_comment, $options_comment_position, $options_html_id, $data_properties, $options_inputfield_id);
            break;
    }

    // attributes images table
    $options_attributes_image[] = trim($tmp_attributes_image) . "\n";

    $zco_notifier->notify('NOTIFY_ATTRIBUTES_MODULE_OPTION_BUILT', $products_options_names->fields, $options_name, $options_menu, $options_comment, $options_comment_position, $options_html_id, $options_attributes_image, $data_properties, $options_inputfield_id);

    $products_options_names->MoveNext();
}

$zco_notifier->notify('NOTIFY_ATTRIBUTES_MODULE_END', $prod_id, $options_name, $options_menu, $options_comment, $options_comment_position, $options_html_id, $options_attributes_image, $options_inputfield_id, $attributeDetailsArrayForJson);


// manage filename uploads
$_GET['number_of_uploads'] = $number_of_uploads;
zen_draw_hidden_field('number_of_uploads', $number_of_uploads);

