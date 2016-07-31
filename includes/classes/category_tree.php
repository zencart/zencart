<?php
/**
 * category_tree Class.
 *
 * @package classes
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: category_tree.php  Modified in v1.6.0 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * category_tree Class.
 * This class is used to generate the category tree used for the categories sidebox
 *
 * @package classes
 */
class category_tree extends base {

  function zen_category_tree($product_type = "all") {
    global $db, $cPath, $cPath_array;
    if ($product_type != 'all') {
      $sql = "select type_master_type from " . TABLE_PRODUCT_TYPES . "
                where type_master_type = " . $product_type . "";
      $master_type_result = $db->Execute($sql);
      $master_type = $master_type_result->fields['type_master_type'];
    }
    $this->tree = array();
    if ($product_type == 'all') {
      $categories_query = "select c.categories_id, cd.categories_name, c.parent_id, c.categories_image
                             from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
                             where c.parent_id = " . (int)TOPMOST_CATEGORY_PARENT_ID . "
                             and c.categories_id = cd.categories_id
                             and cd.language_id='" . (int)$_SESSION['languages_id'] . "'
                             and c.categories_status= 1
                             order by sort_order, cd.categories_name";
    } else {
      $categories_query = "select ptc.category_id as categories_id, cd.categories_name, c.parent_id, c.categories_image
                             from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd, " . TABLE_PRODUCT_TYPES_TO_CATEGORY . " ptc
                             where c.parent_id = " . (int)TOPMOST_CATEGORY_PARENT_ID . "
                             and ptc.category_id = cd.categories_id
                             and ptc.product_type_id = " . $master_type . "
                             and c.categories_id = ptc.category_id
                             and cd.language_id=" . (int)$_SESSION['languages_id'] ."
                             and c.categories_status= 1
                             order by sort_order, cd.categories_name";
    }
    $categories = $db->Execute($categories_query, '', true, 150);
    while (!$categories->EOF)  {
      $this->tree[$categories->fields['categories_id']] = array(
              'name' => $categories->fields['categories_name'],
              'parent' => $categories->fields['parent_id'],
              'level' => 0,
              'path' => $categories->fields['categories_id'],
              'image' => $categories->fields['categories_image'],
              'next_id' => false,
              );

      if (isset($parent_id)) {
        $this->tree[$parent_id]['next_id'] = $categories->fields['categories_id'];
      }

      $parent_id = $categories->fields['categories_id'];

      if (!isset($first_element)) {
        $first_element = $categories->fields['categories_id'];
      }
      $categories->MoveNext();
    }
    if (zen_not_null($cPath)) {
      $new_path = '';
      reset($cPath_array);
      while (list($key, $value) = each($cPath_array)) {
        unset($parent_id);
        unset($first_id);
        if ($product_type == 'all') {
          $categories_query = "select c.categories_id, cd.categories_name, c.parent_id, c.categories_image
                               from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
                               where c.parent_id = " . (int)$value . "
                               and c.categories_id = cd.categories_id
                               and cd.language_id=" . (int)$_SESSION['languages_id'] . "
                               and c.categories_status= 1
                               order by sort_order, cd.categories_name";
        } else {
          /*
          $categories_query = "select ptc.category_id as categories, cd.categories_name, c.parent_id, c.categories_image
          from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd, " . TABLE_PRODUCT_TYPES_TO_CATEGORY . " ptc
          where c.parent_id = '" . (int)$value . "'
          and ptc.category_id = cd.categories_id
          and ptc.product_type_id = '" . $master_type . "'
          and cd.language_id='" . (int)$_SESSION['languages_id'] . "'
          and c.categories_status= '1'
          order by sort_order, cd.categories_name";
          */
          $categories_query = "select ptc.category_id as categories_id, cd.categories_name, c.parent_id, c.categories_image
                             from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd, " . TABLE_PRODUCT_TYPES_TO_CATEGORY . " ptc
                             where c.parent_id = " . (int)$value . "
                             and ptc.category_id = cd.categories_id
                             and ptc.product_type_id = " . $master_type . "
                             and c.categories_id = ptc.category_id
                             and cd.language_id=" . (int)$_SESSION['languages_id'] ."
                             and c.categories_status= 1
                             order by sort_order, cd.categories_name";
        }

        $rows = $db->Execute($categories_query);

        if ($rows->RecordCount()>0) {
          $new_path .= $value;
          while (!$rows->EOF) {
            $this->tree[$rows->fields['categories_id']] = array('name' => $rows->fields['categories_name'],
            'parent' => $rows->fields['parent_id'],
            'level' => $key+1,
            'path' => $new_path . '_' . $rows->fields['categories_id'],
            'image' => $categories->fields['categories_image'],
            'next_id' => false);

            if (isset($parent_id)) {
              $this->tree[$parent_id]['next_id'] = $rows->fields['categories_id'];
            }

            $parent_id = $rows->fields['categories_id'];
            if (!isset($first_id)) {
              $first_id = $rows->fields['categories_id'];
            }

            $last_id = $rows->fields['categories_id'];
            $rows->MoveNext();
          }
          $this->tree[$last_id]['next_id'] = $this->tree[$value]['next_id'];
          $this->tree[$value]['next_id'] = $first_id;
          $new_path .= '_';
        } else {
          break;
        }
      }
    }
    return $this->zen_show_category($first_element);
  }

  function zen_show_category($first_element) {
    global $cPath_array;
    $catID = (int)$first_element;
    $row = 0;
    # checks that the item is not 0, false or not set
    while (!empty($this->tree[$catID]))
    {
        # NOTE: $this->tree[$catID]['parent'] is a string, but would make more sense as integer - consider forcing (int) when setting the var
        if ($this->tree[$catID]['parent'] == (int)TOPMOST_CATEGORY_PARENT_ID) {
            $cPath_new = 'cPath=' . $catID;
            $this->box_categories_array[$row]['top'] = 'true';
        } else {
            $this->box_categories_array[$row]['top'] = 'false';
            $cPath_new = 'cPath=' . $this->tree[$catID]['path'];
        }
        $this->box_categories_array[$row]['path'] = $cPath_new;

        $this->box_categories_array[$row]['current'] = (isset($cPath_array) && in_array($catID, $cPath_array)); 

        // display category name
        if ($this->tree[$catID]['parent'] != (int)TOPMOST_CATEGORY_PARENT_ID) { 
           $this->box_categories_array[$row]['name'] = str_repeat(CATEGORIES_SUBCATEGORIES_INDENT, (int)$this->tree[$catID]['level']) . CATEGORIES_SEPARATOR_SUBS; 
        }
        $this->box_categories_array[$row]['name'] .= $this->tree[$catID]['name']; 

        // make category image available in case needed
        $this->box_categories_array[$row]['image'] = $this->tree[$catID]['image'];

        $this->box_categories_array[$row]['has_sub_cat'] = zen_has_category_subcategories($catID); 

        if (SHOW_COUNTS == 'true') {
            $this->box_categories_array[$row]['count'] = zen_count_products_in_category($catID);
        }
        # break loop if there's no next_id
        if (empty($this->tree[$catID]['next_id']))
        {
            break;
        }
        # get next category ID
        $catID = (int)$this->tree[$catID]['next_id'];
        $row++;
    }
    return $this->box_categories_array;
  }  
}
