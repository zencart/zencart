<?php
/**
 * Attribute functions
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */

/**
 * Check if product has attributes
 *
 * (On catalog-side, this is often used to determine if attributes must be selected to add to cart)
 *
 * @param int $product_id
 * @param bool|string $not_readonly
 * @return bool
 */
function zen_has_product_attributes($product_id, $not_readonly = true)
{
    global $db;

    $exclude_readonly = ($not_readonly === true || $not_readonly === 'true');

    if (PRODUCTS_OPTIONS_TYPE_READONLY_IGNORED == '1' && $exclude_readonly) {
        // don't include READONLY attributes
        $sql = "SELECT pa.products_attributes_id
                FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                LEFT JOIN " . TABLE_PRODUCTS_OPTIONS . " po ON (pa.options_id = po.products_options_id)
                WHERE pa.products_id = " . (int)$product_id . "
                AND po.products_options_type != '" . $db->prepare_input(PRODUCTS_OPTIONS_TYPE_READONLY) . "'";
    } else {
        // regardless of READONLY attributes
        $sql = "SELECT pa.products_attributes_id
                FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                WHERE pa.products_id = " . (int)$product_id;
    }

    $result = $db->Execute($sql, 1);

    return $result->RecordCount() > 0 && $result->fields['products_attributes_id'] > 0;
}


/**
 *  Check if specified product has attributes which require selection before adding product to the cart.
 *  This is used by various parts of the code to determine whether to allow for add-to-cart actions
 *  since adding a product without selecting attributes could lead to undesired basket contents.
 *
 * @param int $products_id
 * @return int
 */
function zen_requires_attribute_selection($products_id)
{
    global $db, $zco_notifier;

    $noDoubles = array();
    $noDoubles[] = PRODUCTS_OPTIONS_TYPE_RADIO;
    $noDoubles[] = PRODUCTS_OPTIONS_TYPE_SELECT;

    $noSingles = array();
    $noSingles[] = PRODUCTS_OPTIONS_TYPE_CHECKBOX;
    $noSingles[] = PRODUCTS_OPTIONS_TYPE_FILE;
    $noSingles[] = PRODUCTS_OPTIONS_TYPE_TEXT;
    if (PRODUCTS_OPTIONS_TYPE_READONLY_IGNORED == '0') {
        $noSingles[] = PRODUCTS_OPTIONS_TYPE_READONLY;
    }

    $query = "SELECT products_options_id, count(pa.options_values_id) AS number_of_choices, po.products_options_type AS options_type
              FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
              LEFT JOIN " . TABLE_PRODUCTS_OPTIONS . " po ON (pa.options_id = po.products_options_id AND po.language_id = " . (int)$_SESSION['languages_id'] . ")
              WHERE pa.products_id = " . (int)$products_id . "
              GROUP BY products_options_id, options_type";

    $zco_notifier->notify('NOTIFY_FUNCTIONS_LOOKUPS_REQUIRES_ATTRIBUTES_SELECTION', '', $query, $noSingles, $noDoubles);

    $result = $db->Execute($query);

    // if no attributes found, return false
    if ($result->RecordCount() == 0) return false;

    // loop through the results, auditing for whether each kind of attribute requires "selection" or not
    // return whether selections must be made, so a more-info button needs to be presented, if true
    foreach ($result as $row => $field) {
        // if there's more than one for any $noDoubles type, can't add from listing
        if (in_array($field['options_type'], $noDoubles) && $field['number_of_choices'] > 1) {
            return true;
        }
        // if there's any type from $noSingles, can't add from listing
        if (in_array($field['options_type'], $noSingles)) {
            return true;
        }
    }

    // return false to indicate that defaults can be automatically added by just using a buy-now button
    return false;
}

/**
 * Check if option name is not expected to have an option value (ie. text field, or File upload field)
 * @param int|array $option_name_id_array
 * @return bool
 */
function zen_option_name_base_expects_no_values($option_name_id_array)
{
    global $db, $zco_notifier;

    $option_name_no_value = true;
    if (!is_array($option_name_id_array)) {
        $option_name_id_array = [$option_name_id_array];
    }

    $sql = "SELECT products_options_type FROM " . TABLE_PRODUCTS_OPTIONS . " WHERE products_options_id :option_name_id:";
    if (count($option_name_id_array) > 1) {
        $sql2 = 'IN (';
        foreach ($option_name_id_array as $option_id) {
            $sql2 .= ':option_id:,';
            $sql2 = $db->bindVars($sql2, ':option_id:', $option_id, 'integer');
        }
        $sql2 = rtrim($sql2, ','); // Need to remove the final comma off of the above.
        $sql2 .= ')';
    } else {
        $sql2 = ' = :option_id:';
        $sql2 = $db->bindVars($sql2, ':option_id:', $option_name_id_array[0], 'integer');
    }

    $sql = $db->bindVars($sql, ':option_name_id:', $sql2, 'noquotestring');

    $sql_result = $db->Execute($sql);

    foreach ($sql_result as $opt_type) {

        $test_var = true; // Set to false in observer if the name is not supposed to have a value associated
        $zco_notifier->notify('FUNCTIONS_LOOKUPS_OPTION_NAME_NO_VALUES_OPT_TYPE', $opt_type, $test_var);

        if ($test_var && $opt_type['products_options_type'] != PRODUCTS_OPTIONS_TYPE_TEXT && $opt_type['products_options_type'] != PRODUCTS_OPTIONS_TYPE_FILE) {
            $option_name_no_value = false;
            break;
        }
    }

    return $option_name_no_value;
}

/**
 *  Check if product has attributes values
 * @param int $product_id
 * @return bool|string
 */
function zen_has_product_attributes_values($product_id)
{
    global $db, $zco_notifier;

    // Allow a watching observer to override this function's return value.
    $value_to_return = '';
    $zco_notifier->notify('NOTIFY_ZEN_HAS_PRODUCT_ATTRIBUTES_VALUES', $product_id, $value_to_return);
    if ($value_to_return !== '') {
        return $value_to_return;
    }

    $sql = "SELECT options_values_price
            FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
            WHERE products_id = " . (int)$product_id . "
            AND options_values_price <> 0";

    $result = $db->Execute($sql, 1);

    return (!$result->EOF);
}


/**
 * check if Product is set to use downloads
 * does not validate download filename
 * @param int $product_id
 * @return bool
 */
function zen_has_product_attributes_downloads_status($product_id)
{
    if (!defined('DOWNLOAD_ENABLED') || DOWNLOAD_ENABLED != 'true') {
        return false;
    }

    global $db;

    $sql = "SELECT pad.products_attributes_id
            FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
            INNER JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad USING (products_attributes_id)
            WHERE pa.products_id = " . (int)$product_id;

    return ($db->Execute($sql, 1)->RecordCount() > 0);
}


/**
 * Return attributes products_options_sort_order
 * @param int $products_id
 * @param int $options_id
 * @param int $options_values_id
 * @return string|int
 */
function zen_get_attributes_sort_order($products_id, $options_id, $options_values_id)
{
    global $db;
    $sql = "SELECT products_options_sort_order
            FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
            WHERE products_id = " . (int)$products_id . "
            AND options_id = " . (int)$options_id . "
            AND options_values_id = " . (int)$options_values_id;
    $result = $db->Execute($sql, 1);

    return $result->fields['products_options_sort_order'];
}

/**
 *  return attribute products_options_sort_order
 * @param int $products_id
 * @param int $options_id
 * @param int $options_values_id
 * @param int $language_id
 * @return string
 */
function zen_get_attributes_options_sort_order($products_id, $options_id, $options_values_id, $language_id = 0)
{
    global $db;
    if (empty($language_id)) $language_id = (int)$_SESSION['languages_id'];

    $check = $db->Execute("SELECT products_options_sort_order
                             FROM " . TABLE_PRODUCTS_OPTIONS . "
                             WHERE products_options_id = " . (int)$options_id . "
                             AND language_id = " . $language_id, 1);

    $check_options_id = $db->Execute("SELECT products_id, options_id, options_values_id, products_options_sort_order
                             FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
                             WHERE products_id=" . (int)$products_id . "
                             AND options_id=" . (int)$options_id . "
                             AND options_values_id = " . (int)$options_values_id, 1);

    return $check->fields['products_options_sort_order'] . '.' . str_pad($check_options_id->fields['products_options_sort_order'], 5, '0', STR_PAD_LEFT);
}

/**
 * check if attribute is display only
 * @param int $product_id
 * @param string $option
 * @param string|mixed $value
 * @return bool
 */
function zen_get_attributes_valid($product_id, $option, $value)
{
    global $db;

    // regular attribute validation
    $check_attributes = $db->Execute("SELECT attributes_display_only, attributes_required FROM " . TABLE_PRODUCTS_ATTRIBUTES . " WHERE products_id=" . (int)$product_id . " AND options_id=" . (int)$option . " AND options_values_id=" . (int)$value, 1);

    $check_valid = true;

    // display only cannot be selected
    if (!$check_attributes->EOF && $check_attributes->fields['attributes_display_only'] == '1') {
        $check_valid = false;
    }

    // text required validation
    if (preg_match('/^txt_/', $option)) {
        $lookup = preg_replace('/^txt_/', '', $option);
        $check_attributes = $db->Execute("SELECT attributes_display_only, attributes_required FROM " . TABLE_PRODUCTS_ATTRIBUTES . " WHERE products_id=" . (int)$product_id . " AND options_id=" . (int)$lookup . " AND options_values_id=0", 1);
        // TEXT attribute cannot be blank
        if ($check_attributes->fields['attributes_required'] == '1' && (empty($value) && !is_numeric($value))) {
            $check_valid = false;
        }
    }

    return $check_valid;
}

/**
 * Return Options_Name from ID
 * @param int $options_id
 * @return string
 */
function zen_options_name($options_id)
{
    global $db;

    $options_id = str_replace('txt_', '', $options_id);

    $options_values = $db->Execute("SELECT products_options_name
                                    FROM " . TABLE_PRODUCTS_OPTIONS . "
                                    WHERE products_options_id = " . (int)$options_id . "
                                    AND language_id = " . (int)$_SESSION['languages_id']);
    if ($options_values->EOF) return '';
    return $options_values->fields['products_options_name'];
}


/**
 * Return Options_values_name from value-ID
 * @param int $values_id
 * @return string
 */
function zen_values_name($values_id)
{
    global $db;

    $values_values = $db->Execute("SELECT products_options_values_name
                                   FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                                   WHERE products_options_values_id = " . (int)$values_id . "
                                   AND language_id = " . (int)$_SESSION['languages_id']);
    if ($values_values->EOF) return '';
    return $values_values->fields['products_options_values_name'];
}


/**
 * Validate Option Name and Option Type Match
 * @param int $products_options_id
 * @param int $products_options_values_id
 * @return bool
 */
function zen_validate_options_to_options_value($products_options_id, $products_options_values_id)
{
    global $db;
    $sql = "SELECT products_options_id
            FROM " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . "
            WHERE products_options_id= " . (int)$products_options_id . "
            AND products_options_values_id=" . (int)$products_options_values_id;
    $result = $db->Execute($sql, 1);
    return $result->RecordCount() > 0;
}

/**
 * look-up Attributues Options Name products_options_values_to_products_options
 * @param int $option_values_id
 * @return string
 */
function zen_get_products_options_name_from_value($option_values_id)
{
    global $db;

    if ($option_values_id == 0) {
        return 'RESERVED FOR TEXT/FILES ONLY ATTRIBUTES';
    }

    $result = $db->Execute("SELECT products_options_id
                            FROM " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . "
                            WHERE products_options_values_id=" . (int)$option_values_id, 1);
    if ($result->EOF) return '';

    $sql = "SELECT products_options_name
            FROM " . TABLE_PRODUCTS_OPTIONS . "
            WHERE products_options_id=" . (int)$result->fields['products_options_id'] . "
            AND language_id=" . (int)$_SESSION['languages_id'];
    $result2 = $db->Execute($sql, 1);
    if ($result2->EOF) return '';
    return $result2->fields['products_options_name'];
}

/**
 * @param int $product_id
 * @param int $option_id
 * @param int $value_id
 * @return mixed|string
 */
function zen_get_attributes_image(int $product_id, $option_id, $value_id)
{
    global $db;
    $sql = "SELECT attributes_image FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
            WHERE products_id = " . (int)$product_id . "
            AND options_id = " . (int)$option_id . "
            AND options_values_id = " . (int)$value_id;
    $result = $db->Execute($sql, 1);
    if ($result->EOF) return '';
    return $result->fields['attributes_image'];
}

/**
 * @param int $products_id_from
 * @param int $products_id_to
 * @return bool
 */
function zen_copy_products_attributes($products_id_from, $products_id_to)
{
    global $db, $zco_notifier;
    global $messageStack;
    global $copy_attributes_delete_first, $copy_attributes_duplicates_skipped, $copy_attributes_duplicates_overwrite, $copy_attributes_include_downloads, $copy_attributes_include_filename;

    // same products_id
    if ($products_id_to == $products_id_from) {
        $messageStack->add_session(sprintf(WARNING_ATTRIBUTE_COPY_SAME_ID, $products_id_from, $products_id_to), 'caution');
        return false;
    }
    // no attributes found to copy
    if (!zen_has_product_attributes($products_id_from, false)) {
        $messageStack->add_session(sprintf(WARNING_ATTRIBUTE_COPY_NO_ATTRIBUTES, $products_id_from, zen_get_products_name($products_id_from)), 'caution');
        return false;
    }
    // invalid products_id
    if (!zen_products_id_valid($products_id_to)) {
        $messageStack->add_session(sprintf(WARNING_ATTRIBUTE_COPY_INVALID_ID, $products_id_to), 'caution');
        return false;
    }

    // Notify that the attribute-copying has started for the product.
    $zco_notifier->notify('ZEN_COPY_PRODUCTS_ATTRIBUTES_START', ['from' => (int)$products_id_from, 'to' => (int)$products_id_to]);

    // check if product already has attributes
    $already_has_attributes = zen_has_product_attributes($products_id_to, false);

    if ($copy_attributes_delete_first == '1' and $already_has_attributes == true) {
        // delete all attributes first from destination products_id_to
        zen_products_attributes_download_delete($products_id_to);
        // delete the attributes
        $db->Execute("DELETE FROM " . TABLE_PRODUCTS_ATTRIBUTES . " WHERE products_id = " . (int)$products_id_to);

        // Notify that attributes have been deleted for the product.
        $zco_notifier->notify('ZEN_COPY_PRODUCTS_ATTRIBUTES_DELETE', (int)$products_id_to);
    }

    // get attributes to copy from
    $products_copy_from = $db->Execute("SELECT * FROM " . TABLE_PRODUCTS_ATTRIBUTES . " WHERE products_id=" . (int)$products_id_from . " ORDER BY products_attributes_id");

    foreach ($products_copy_from as $copy_from) {
        $update_attribute = false;
        $add_attribute = true;

        $sql = "SELECT * from " . TABLE_PRODUCTS_ATTRIBUTES . "
                WHERE products_id=" . (int)$products_id_to . "
                AND options_id=" . (int)$copy_from['options_id'] . "
                AND options_values_id=" . (int)$copy_from['options_values_id'];
        $check_duplicate = $db->Execute($sql);

        if ($already_has_attributes == true) {
            if ($check_duplicate->RecordCount() == 0) {
                $update_attribute = false;
                $add_attribute = true;
            } else {
                if ($check_duplicate->RecordCount() == 0) {
                    $update_attribute = false;
                    $add_attribute = true;
                } else {
                    $update_attribute = true;
                    $add_attribute = false;
                }
            }
        } else {
            $update_attribute = false;
            $add_attribute = true;
        }

        if ($copy_attributes_duplicates_skipped == '1' and $check_duplicate->RecordCount() != 0) {
            $messageStack->add_session(sprintf(TEXT_ATTRIBUTE_COPY_SKIPPING, (int)$copy_from['products_attributes_id'], (int)$products_id_to), 'caution');
            // skip it
            $copy_from();
        }

        // New attribute - insert it
        if ($add_attribute == true) {
            $db->Execute("INSERT INTO " . TABLE_PRODUCTS_ATTRIBUTES . "
              (products_id, options_id, options_values_id, options_values_price, price_prefix, products_options_sort_order,
              product_attribute_is_free, products_attributes_weight, products_attributes_weight_prefix, attributes_display_only,
              attributes_default, attributes_discounted, attributes_image, attributes_price_base_included,
              attributes_price_onetime, attributes_price_factor, attributes_price_factor_offset, attributes_price_factor_onetime,
              attributes_price_factor_onetime_offset, attributes_qty_prices, attributes_qty_prices_onetime,
              attributes_price_words, attributes_price_words_free, attributes_price_letters, attributes_price_letters_free,
              attributes_required)
              VALUES (" . (int)$products_id_to . ",
              '" . $copy_from['options_id'] . "',
              '" . $copy_from['options_values_id'] . "',
              '" . $copy_from['options_values_price'] . "',
              '" . $copy_from['price_prefix'] . "',
              '" . $copy_from['products_options_sort_order'] . "',
              '" . $copy_from['product_attribute_is_free'] . "',
              '" . $copy_from['products_attributes_weight'] . "',
              '" . $copy_from['products_attributes_weight_prefix'] . "',
              '" . $copy_from['attributes_display_only'] . "',
              '" . $copy_from['attributes_default'] . "',
              '" . $copy_from['attributes_discounted'] . "',
              '" . $copy_from['attributes_image'] . "',
              '" . $copy_from['attributes_price_base_included'] . "',
              '" . $copy_from['attributes_price_onetime'] . "',
              '" . $copy_from['attributes_price_factor'] . "',
              '" . $copy_from['attributes_price_factor_offset'] . "',
              '" . $copy_from['attributes_price_factor_onetime'] . "',
              '" . $copy_from['attributes_price_factor_onetime_offset'] . "',
              '" . $copy_from['attributes_qty_prices'] . "',
              '" . $copy_from['attributes_qty_prices_onetime'] . "',
              '" . $copy_from['attributes_price_words'] . "',
              '" . $copy_from['attributes_price_words_free'] . "',
              '" . $copy_from['attributes_price_letters'] . "',
              '" . $copy_from['attributes_price_letters_free'] . "',
              '" . $copy_from['attributes_required'] . "')"
            );
            $messageStack->add_session(sprintf(TEXT_ATTRIBUTE_COPY_INSERTING, (int)$copy_from['products_attributes_id'], (int)$products_id_from, (int)$products_id_to), 'success');

            $new_products_attributes_id = $db->Insert_ID();

            // Notify that an attribute has been added for the product.
            $zco_notifier->notify('ZEN_COPY_PRODUCTS_ATTRIBUTES_ADD', ['pID' => (int)$products_id_to, 'fields' => $copy_from]);


            // Downloads
            if (DOWNLOAD_ENABLED == 'true') {
                $sql = "SELECT products_attributes_id, products_attributes_filename, products_attributes_maxdays, products_attributes_maxcount
                        FROM " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . "
                        WHERE products_attributes_id = " . (int)$copy_from['products_attributes_id'];
                $results = $db->Execute($sql);
                foreach ($results as $result) {
                    $db->Execute("INSERT INTO " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . "
                        (products_attributes_id, products_attributes_filename, products_attributes_maxdays, products_attributes_maxcount)
                        VALUES (" . (int)$new_products_attributes_id . ",
                                '" . zen_db_input($result['products_attributes_filename']) . "',
                                " . (int)$result['products_attributes_maxdays'] . ",
                                " . (int)$result['products_attributes_maxcount'] . ")");

                    $new_attribute_id = $db->Insert_ID();
                    $zco_notifier->notify('ZEN_COPY_PRODUCTS_ATTRIBUTES_ADDED_DOWNLOAD', (int)$products_id_to, $new_products_attributes_id, $new_attribute_id);
                }
            }
        }

        // Update attribute - Just attribute settings not ids
        if ($update_attribute == true) {
            $db->Execute("UPDATE " . TABLE_PRODUCTS_ATTRIBUTES . " SET
                  options_values_price='" . $copy_from['options_values_price'] . "',
                  price_prefix='" . $copy_from['price_prefix'] . "',
                  products_options_sort_order='" . $copy_from['products_options_sort_order'] . "',
                  product_attribute_is_free='" . $copy_from['product_attribute_is_free'] . "',
                  products_attributes_weight='" . $copy_from['products_attributes_weight'] . "',
                  products_attributes_weight_prefix='" . $copy_from['products_attributes_weight_prefix'] . "',
                  attributes_display_only='" . $copy_from['attributes_display_only'] . "',
                  attributes_default='" . $copy_from['attributes_default'] . "',
                  attributes_discounted='" . $copy_from['attributes_discounted'] . "',
                  attributes_image='" . $copy_from['attributes_image'] . "',
                  attributes_price_base_included='" . $copy_from['attributes_price_base_included'] . "',
                  attributes_price_onetime='" . $copy_from['attributes_price_onetime'] . "',
                  attributes_price_factor='" . $copy_from['attributes_price_factor'] . "',
                  attributes_price_factor_offset='" . $copy_from['attributes_price_factor_offset'] . "',
                  attributes_price_factor_onetime='" . $copy_from['attributes_price_factor_onetime'] . "',
                  attributes_price_factor_onetime_offset='" . $copy_from['attributes_price_factor_onetime_offset'] . "',
                  attributes_qty_prices='" . $copy_from['attributes_qty_prices'] . "',
                  attributes_qty_prices_onetime='" . $copy_from['attributes_qty_prices_onetime'] . "',
                  attributes_price_words='" . $copy_from['attributes_price_words'] . "',
                  attributes_price_words_free='" . $copy_from['attributes_price_words_free'] . "',
                  attributes_price_letters='" . $copy_from['attributes_price_letters'] . "',
                  attributes_price_letters_free='" . $copy_from['attributes_price_letters_free'] . "',
                  attributes_required='" . $copy_from['attributes_required'] . "'
                  WHERE products_id=" . (int)$products_id_to . "
                   AND options_id=" . (int)$copy_from['options_id'] . "
                   AND options_values_id=" . (int)$copy_from['options_values_id']
// and attributes_image='" . $copy_from['attributes_image'] . "'
// and attributes_price_base_included=" . $copy_from['attributes_price_base_included']
            );
            $messageStack->add_session(sprintf(TEXT_ATTRIBUTE_COPY_UPDATING, (int)$copy_from['products_attributes_id'], (int)$products_id_to), 'success');

            // Notify that an attribute has been updated for the product.
            $zco_notifier->notify('ZEN_COPY_PRODUCTS_ATTRIBUTES_UPDATE', ['pID' => (int)$products_id_to, 'fields' => $copy_from]);
        }
    }

    // Notify that the attribute-copying has been completed for the product.
    $zco_notifier->notify('ZEN_COPY_PRODUCTS_ATTRIBUTES_COMPLETE', ['from' => (int)$products_id_from, 'to' => (int)$products_id_to]);

    // reset products_price_sorter for searches etc.
    zen_update_products_price_sorter($products_id_to);

    return true;
}


/**
 * Get the Option Name for a particular language
 * @param int $option_id
 * @param int $language_id
 * @return string
 */
function zen_get_option_name_language($option_id, $language_id)
{
    global $db;
    $sql = "SELECT products_options_id, products_options_name FROM " . TABLE_PRODUCTS_OPTIONS . " WHERE products_options_id= " . (int)$option_id . " AND language_id = " . (int)$language_id;
    $result = $db->Execute($sql);
    if ($result->EOF) return '';
    return $result->fields['products_options_name'];
}

/**
 * Get the Option Name sort-order for a particular language
 * @param int $option_id
 * @param int $language_id
 * @return string|mixed
 */
function zen_get_option_name_language_sort_order($option_id, $language_id)
{
    global $db;
    $sql = "SELECT products_options_id, products_options_name, products_options_sort_order FROM " . TABLE_PRODUCTS_OPTIONS . " WHERE products_options_id= " . (int)$option_id . " AND language_id = " . (int)$language_id;
    $result = $db->Execute($sql);
    if ($result->EOF) return '';
    return $result->fields['products_options_sort_order'];
}


/**
 * Delete all attributes for a specified product
 * @param int $product_id
 */
function zen_delete_products_attributes($product_id)
{
    global $db, $zco_notifier;
    $zco_notifier->notify('NOTIFIER_ADMIN_ZEN_DELETE_PRODUCTS_ATTRIBUTES', [], $product_id);

    $sql = "SELECT pa.products_id, pad.products_attributes_id
            FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
            LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad USING (products_attributes_id)
            WHERE pa.products_id=" . (int)$product_id;
    $results = $db->Execute($sql);
    foreach ($results as $result) {
        $db->Execute("DELETE FROM " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " WHERE products_attributes_id = " . (int)$results->fields['products_attributes_id']);
    }

    $db->Execute("DELETE FROM " . TABLE_PRODUCTS_ATTRIBUTES . " WHERE products_id = " . (int)$product_id);
}

/**
 * Set Product Attributes Sort Order to Products Option Value Sort Order for specified product
 * @param int $product_id
 */
function zen_update_attributes_products_option_values_sort_order($product_id)
{
    global $db;
    $sql = "SELECT DISTINCT pa.products_attributes_id, pa.options_id, pa.options_values_id, pa.products_options_sort_order, pov.products_options_values_sort_order
            FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
            LEFT JOIN " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov ON (pa.options_values_id = pov.products_options_values_id)
            WHERE pa.products_id = " . (int)$product_id;
    $results = $db->Execute($sql);
    foreach ($results as $result) {
        $db->Execute("UPDATE " . TABLE_PRODUCTS_ATTRIBUTES . "
                      SET products_options_sort_order = '" . $results->fields['products_options_values_sort_order'] . "'
                      WHERE products_id = " . (int)$product_id . "
                      AND products_attributes_id = " . (int)$results->fields['products_attributes_id']);
    }
}


/**
 * @param int $product_id
 * @param bool $check_if_valid
 * @return string
 */
function zen_has_product_attributes_downloads($product_id, $check_if_valid = false)
{
    global $db;
    if (DOWNLOAD_ENABLED != 'true') {
        return 'disabled';
    }
    $sql = "SELECT pa.products_attributes_id, pad.products_attributes_filename
            FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
            INNER JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad USING (products_attributes_id)
            WHERE pa.products_id=" . (int)$product_id;
    $results = $db->Execute($sql);

    if ($check_if_valid) {
        $valid_downloads = '';
        foreach ($results as $result) {
            if (!zen_orders_products_downloads($result['products_attributes_filename'])) {
                $valid_downloads .= '<br>&nbsp;&nbsp;' . zen_image(DIR_WS_IMAGES . 'icon_status_red.gif') . ' Invalid: ' . $result['products_attributes_filename'];
            } else {
                $valid_downloads .= '<br>&nbsp;&nbsp;' . zen_image(DIR_WS_IMAGES . 'icon_status_green.gif') . ' Valid&nbsp;&nbsp;: ' . $result['products_attributes_filename'];
            }
        }
        return $valid_downloads;
    }

    if ($results->RecordCount()) {
        return $results->RecordCount() . ' files';
    }
    return 'none';
}


/**
 * Is the option_id a File option-type?
 * @param int $option_id
 * @return bool
 */
function zen_is_option_file($option_id)
{
    global $db;
    $result = $db->Execute("SELECT products_options_type FROM " . TABLE_PRODUCTS_OPTIONS . " WHERE products_options_id = " . (int)$option_id);
    $option_type = $result->fields['products_options_type'];
    $result = $db->Execute("SELECT products_options_types_name FROM " . TABLE_PRODUCTS_OPTIONS_TYPES . " WHERE products_options_types_id = " . (int)$option_type);
    return ($result->fields['products_options_types_name'] == 'File');
}

/**
 * Check that the specified download filename exists on the filesystem (or is defined as a downloadable URL)
 * @param string $check_filename
 * @return bool
 */
function zen_orders_products_downloads($check_filename)
{
    global $zco_notifier;

    if (empty($check_filename)) return false;

    $handler = zen_get_download_handler($check_filename);

    if ($handler == 'local') {
        return file_exists(DIR_FS_DOWNLOAD . $check_filename);
    }

    /**
     * An observer hooking this notifier should set $handler to blank if it tries a validation and fails.
     * Or, if validation passes, simply set $handler to the service name (first chars before first colon in filename)
     * Or, or there is no way to verify, do nothing to $handler.
     */
    $zco_notifier->notify('NOTIFY_TEST_DOWNLOADABLE_FILE_EXISTS', $check_filename, $handler);

    // if handler is set but isn't local (internal) then we simply return true since there's no way to "test"
    if ($handler != '') return true;

    // else if the notifier caused $handler to be empty then that means it failed verification, so we return false
    return false;
}

/**
 * Check if the specified download filename matches a handler for an external download service
 * If yes, it will be because the filename contains colons as delimiters ... service:filename:filesize
 */
function zen_get_download_handler($filename)
{
    $file_parts = explode(':', $filename);

    // if the filename doesn't contain any colons, then there's no delimiter to return, so must be using built-in file handling
    if (count($file_parts) < 2) {
        return 'local';
    }

    return $file_parts[0];
}

/***
 * Do the misconfiguration check which Admin > Catalog > Downloads Manager 
 * does to verify that downloads don't have invalid shipping settings.
 */
function zen_check_for_misconfigured_downloads() {
   global $db; 
   if (DOWNLOAD_ENABLED == 'false') return true; 
   // use SELECT from admin/downloads_manager.php
   $sql = "SELECT pad.*, pa.*, pd.*, p.*
                      FROM " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                      LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa ON pad.products_attributes_id = pa.products_attributes_id
                      LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON pa.products_id = pd.products_id
                        AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
                      LEFT JOIN " . TABLE_PRODUCTS . " p ON p.products_id = pd.products_id
                      WHERE pa.products_attributes_id = pad.products_attributes_id";
   
   $results = $db->Execute($sql); 
   foreach ($results as $result) {

      if ($result['product_is_always_free_shipping'] == 1 || $result['products_virtual'] == 1) {
         return false; 
      }
   }
   return true; 
}
