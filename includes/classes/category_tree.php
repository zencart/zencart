<?php

declare(strict_types=1);

/**
 * category_tree Class.
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

/**
 * category_tree Class.
 * This class is used to generate the category tree used for the categories sidebox
 *
 * @since ZC v1.2.0d
 */
class category_tree extends base
{
    /**
     * Array of category details for display
     */
    private array $box_categories_array = [];
    /**
     * String containing a concatenated list of categories with separator.
     */
    private string $categories_string;
    /*
     * Array of categories from database
     */
    private array $tree = [];

    /**
     * @var int|string $product_type int = product type id, 0 = all, 'all' = all (for legacy support)
     *
     * @since ZC v1.2.0d
     */
    public function zen_category_tree(int|string $product_type = 0): array
    {
        global $db, $cPath, $cPath_array;
        if ($product_type !== 0 && $product_type !== 'all') {
            $sql = "SELECT type_master_type FROM " . TABLE_PRODUCT_TYPES . "
                    WHERE type_master_type = " . (int)$product_type;
            $master_type_result = $db->Execute($sql);
            $master_type = (int)$master_type_result->fields['type_master_type'];
        }
        $this->tree = [];
        if ($product_type === 0 || $product_type === 'all') {
            $categories_query = "SELECT c.categories_id, cd.categories_name, c.parent_id, c.categories_image
                             FROM " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
                             WHERE c.parent_id = " . (int)TOPMOST_CATEGORY_PARENT_ID . "
                             AND c.categories_id = cd.categories_id
                             AND cd.language_id='" . (int)$_SESSION['languages_id'] . "'
                             AND c.categories_status= 1
                             ORDER BY sort_order, cd.categories_name";
        } else {
            $categories_query = "SELECT ptc.category_id as categories_id, cd.categories_name, c.parent_id, c.categories_image
                             FROM " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd, " . TABLE_PRODUCT_TYPES_TO_CATEGORY . " ptc
                             WHERE c.parent_id = " . (int)TOPMOST_CATEGORY_PARENT_ID . "
                             AND ptc.category_id = cd.categories_id
                             AND ptc.product_type_id = " . (int)$master_type . "
                             AND c.categories_id = ptc.category_id
                             AND cd.language_id=" . (int)$_SESSION['languages_id'] . "
                             AND c.categories_status= 1
                             ORDER BY sort_order, cd.categories_name";
        }
        $categories = $db->Execute($categories_query, '', true, 150);

        $parent_id = null;
        $first_element = null;

        foreach ($categories as $category) {
            $categoryId = $category['categories_id'];

            $this->tree[$categoryId] = [
                'name' => $category['categories_name'],
                'parent' => $category['parent_id'],
                'level' => 0,
                'path' => $categoryId,
                'image' => $category['categories_image'],
                'next_id' => false,
            ];

            if (isset($parent_id)) {
                $this->tree[$parent_id]['next_id'] = $categoryId;
            }

            $parent_id = $categoryId;

            if (!isset($first_element)) {
                $first_element = $categoryId;
            }
        }

        if (zen_not_null($cPath)) {
            $new_path = '';
            foreach ($cPath_array as $key => $value) {
                unset($parent_id);
                unset($first_id);
                if ($product_type === 0 || $product_type === 'all') {
                    $categories_query = "SELECT c.categories_id, cd.categories_name, c.parent_id, c.categories_image
                               FROM " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
                               WHERE c.parent_id = " . (int)$value . "
                               AND c.categories_id = cd.categories_id
                               AND cd.language_id=" . (int)$_SESSION['languages_id'] . "
                               AND c.categories_status= 1
                               ORDER BY sort_order, cd.categories_name";
                } else {
                    /*
                    $categories_query = "SELECT ptc.category_id as categories, cd.categories_name, c.parent_id, c.categories_image
                    FROM " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd, " . TABLE_PRODUCT_TYPES_TO_CATEGORY . " ptc
                    WHERE c.parent_id = '" . (int)$value . "'
                    AND ptc.category_id = cd.categories_id
                    AND ptc.product_type_id = '" . $master_type . "'
                    AND cd.language_id='" . (int)$_SESSION['languages_id'] . "'
                    AND c.categories_status= '1'
                    ORDER BY sort_order, cd.categories_name";
                    */
                    $categories_query = "SELECT ptc.category_id as categories_id, cd.categories_name, c.parent_id, c.categories_image
                             FROM " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd, " . TABLE_PRODUCT_TYPES_TO_CATEGORY . " ptc
                             WHERE c.parent_id = " . (int)$value . "
                             AND ptc.category_id = cd.categories_id
                             AND ptc.product_type_id = " . $master_type . "
                             AND c.categories_id = ptc.category_id
                             AND cd.language_id=" . (int)$_SESSION['languages_id'] . "
                             AND c.categories_status= 1
                             ORDER BY sort_order, cd.categories_name";
                }

                $rows = $db->Execute($categories_query);

                if ($rows->RecordCount() > 0) {
                    $new_path .= $value;
                    foreach ($rows as $row) {
                        $categoryId = $row['categories_id'];

                        $this->tree[$categoryId] = [
                            'name' => $row['categories_name'],
                            'parent' => $row['parent_id'],
                            'level' => $key + 1,
                            'path' => $new_path . '_' . $categoryId,
                            'image' => $row['categories_image'],
                            'next_id' => false,
                        ];

                        if (isset($parent_id)) {
                            $this->tree[$parent_id]['next_id'] = $categoryId;
                        }

                        $parent_id = $categoryId;
                        if (!isset($first_id)) {
                            $first_id = $categoryId;
                        }

                        $last_id = $categoryId;
                    }
                    if (!empty($value) && !empty($this->tree[$value]) /* Needed to thwart notice */) {
                        $this->tree[$last_id]['next_id'] = $this->tree[$value]['next_id'];
                    }
                    $this->tree[$value]['next_id'] = $first_id;
                    $new_path .= '_';
                } else {
                    break;
                }
            }
        }
        $row = 0;
        return $this->zen_show_category($first_element, $row);
    }

    /**
     * @since ZC v1.2.0d
     */
    public function zen_show_category($counter, $ii): array
    {
        global $cPath_array;

        $this->categories_string = "";

        for ($i = 0; $i < $this->tree[$counter]['level']; $i++) {
            if ($this->tree[$counter]['parent'] != TOPMOST_CATEGORY_PARENT_ID) {
                $this->categories_string .= zen_config('CATEGORIES_SUBCATEGORIES_INDENT');
            }
        }


        if ($this->tree[$counter]['parent'] == TOPMOST_CATEGORY_PARENT_ID) {
            $cPath_new = 'cPath=' . $counter;
            $this->box_categories_array[$ii]['top'] = 'true';
        } else {
            $this->box_categories_array[$ii]['top'] = 'false';
            $cPath_new = 'cPath=' . $this->tree[$counter]['path'];
            $this->categories_string .= zen_config('CATEGORIES_SEPARATOR_SUBS');
        }
        $this->box_categories_array[$ii]['path'] = $cPath_new;

        if (isset($cPath_array) && in_array($counter, $cPath_array)) {
            $this->box_categories_array[$ii]['current'] = true;
        } else {
            $this->box_categories_array[$ii]['current'] = false;
        }

        // display category name
        $this->box_categories_array[$ii]['name'] = $this->categories_string . $this->tree[$counter]['name'];

        // make category image available in case needed
        $this->box_categories_array[$ii]['image'] = $this->tree[$counter]['image'];

        if (zen_has_category_subcategories($counter)) {
            $this->box_categories_array[$ii]['has_sub_cat'] = true;
        } else {
            $this->box_categories_array[$ii]['has_sub_cat'] = false;
        }

        if (zen_config('SHOW_COUNTS') === 'true') {
            $products_in_category = zen_count_products_in_category($counter);
            if ($products_in_category > 0) {
                $this->box_categories_array[$ii]['count'] = $products_in_category;
            } else {
                $this->box_categories_array[$ii]['count'] = 0;
            }
        }

        if ($this->tree[$counter]['next_id'] !== false) {
            $ii++;
            $this->zen_show_category($this->tree[$counter]['next_id'], $ii);
        }
        return $this->box_categories_array;
    }
}
