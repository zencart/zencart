<?php
/**
 * metatags retrieval functions for admin
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jul 07 Modified in v1.5.8-alpha $
 * @no-docs
 */

/**
 * product-specific meta tags
 */
function zen_get_product_metatag_fields($product_id, $language_id, $specific_field = null)
{
    global $db;
    $sql = "SELECT *
            FROM " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . "
            WHERE products_id = " . (int)$product_id . "
            AND language_id = " . (int)$language_id;
    $result = $db->Execute($sql, '1', true, 5);
    if ($specific_field !== null) {
        if ($result->EOF || !isset($result->fields[$specific_field])) return '';
        return $result->fields[$specific_field];
    }
    if ($result->EOF) return null;
    return $result->fields;
}

/**
 * Category-specific metatags
 */
function zen_get_category_metatag_fields($category_id, $language_id, $specific_field = null)
{
    global $db;
    $sql = "SELECT *
            FROM " . TABLE_METATAGS_CATEGORIES_DESCRIPTION . "
            WHERE categories_id = " . (int)$category_id . "
            AND language_id = " . (int)$language_id;
    $result = $db->Execute($sql, '1', true, 5);
    if ($specific_field !== null) {
        if ($result->EOF || !isset($result->fields[$specific_field])) return '';
        return $result->fields[$specific_field];
    }
    if ($result->EOF) return null;
    return $result->fields;
}

