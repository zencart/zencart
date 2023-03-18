<?php
/**
 * site_map.php
 *
 * @copyright Copyright 2003-2023 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2022 Oct 16 Modified in v1.5.8a $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * site_map.php
 *
 */
 class zen_SiteMapTree {
     
     /**
      * The root category name
      * @var string
      */
    protected $root_category_id = TOPMOST_CATEGORY_PARENT_ID;
    /**
     * The maximum number of levels to display 0 = all;
     * @var int
     */
    protected  $max_level = 0;
    /**
     * The data required to build the sitemap tree
     * @var array
     */
    protected  $data = [];
    /**
     * String to proceed root category
     * @var string
     */
    protected   $root_start_string = '';
    /**
     * String to follow root category
     * @var string
     */
    protected   $root_end_string = '';
    /**
     * String to proceed parent string
     * @var string
     */
    protected   $parent_start_string = '';
    /**
     * String to follow parent string
     * @var string
     */
    protected  $parent_end_string = '';
    /**
     * String to proceed start of a parent group
     * @var string
     */
    protected   $parent_group_start_string = "\n<ul>";
    /**
     * String to follow end of a parent group
     * @var string
     */
    protected   $parent_group_end_string = "</ul>\n";
    /**
     * String to proceed start of a child entry
     * @var string
     */
    protected   $child_start_string = '<li>';
    /**
     * String to follow end of a child entry
     * @var string
     */
    protected  $child_end_string = "</li>\n";
    /**
     * String to use as separator 
     * @var string
     */
    protected  $spacer_string = '';
    /**
     * Number of separators to use
     * @var int
     */
    protected  $spacer_multiplier = 1;

   function __construct() {
     global $db;
     $this->data = array();
     $categories_query = "select c.categories_id, cd.categories_name, c.parent_id
                      from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
                      where c.categories_id = cd.categories_id
                      and cd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                      and c.categories_status != '0'
                      order by c.parent_id, c.sort_order, cd.categories_name";
         $categories = $db->Execute($categories_query);
         while (!$categories->EOF) {
           $this->data[$categories->fields['parent_id']][$categories->fields['categories_id']] = array('name' => $categories->fields['categories_name'], 'count' => 0);
           $categories->MoveNext();
         }
   }

   function buildBranch($parent_id, $level = 0, $parent_link = '') {
    $parent_id = (int)$parent_id;
    $level = (int)$level;
    $result = $this->parent_group_start_string;

    if (isset($this->data[$parent_id])) {
      foreach ($this->data[$parent_id] as $category_id => $category) {
        $category_link = $parent_link . $category_id;
        $result .= $this->child_start_string;
        if (isset($this->data[$category_id])) {
          $result .= $this->parent_start_string;
        }

        if ($level == 0) {
          $result .= $this->root_start_string;
        }
        $result .= str_repeat($this->spacer_string, $this->spacer_multiplier * $level) . '<a href="' . zen_href_link(FILENAME_DEFAULT, 'cPath=' . $category_link) . '">';
        $result .= $category['name'];
        $result .= '</a>';

        if ($level == 0) {
          $result .= $this->root_end_string;
        }

        if (isset($this->data[$category_id])) {
          $result .= $this->parent_end_string;
        }

//        $result .= $this->child_end_string;

       if (isset($this->data[$category_id]) && (($this->max_level == '0') || ($this->max_level > $level+1))) {
         $result .= $this->buildBranch($category_id, $level+1, $category_link . '_');
       }
       $result .= $this->child_end_string;

     }
   }

    $result .= $this->parent_group_end_string;

    return $result;
  }
   function buildTree() {
     return $this->buildBranch($this->root_category_id);
   }
 }
