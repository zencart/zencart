<?php

declare(strict_types=1);

/**
 * site_map.php
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
 * site_map.php
 *
 * @since ZC v1.3.0
 */
class zen_SiteMapTree
{
    /**
     * The root category name
     */
    protected int|string $root_category_id = 0;
    /**
     * The maximum number of levels to display 0 = all;
     */
    protected int $max_level = 0;
    /**
     * The data required to build the sitemap tree
     */
    protected array $data = [];
    /**
     * String to preceed root category
     */
    protected string $root_start_string = '';
    /**
     * String to follow root category
     */
    protected string $root_end_string = '';
    /**
     * String to preceed parent string
     */
    protected string $parent_start_string = '';
    /**
     * String to follow parent string
     */
    protected string $parent_end_string = '';
    /**
     * String to preceed start of a parent group
     */
    protected string $parent_group_start_string = "\n<ul>";
    /**
     * String to follow end of a parent group
     */
    protected string $parent_group_end_string = "</ul>\n";
    /**
     * String to preceed start of a child entry
     */
    protected string $child_start_string = '<li>';
    /**
     * String to follow end of a child entry
     */
    protected string $child_end_string = "</li>\n";
    /**
     * String to use as separator
     */
    protected string $spacer_string = '';
    /**
     * Number of separators to use
     */
    protected int $spacer_multiplier = 1;

    public function __construct()
    {
        if (TOPMOST_CATEGORY_PARENT_ID > 0) {
            $this->root_category_id = TOPMOST_CATEGORY_PARENT_ID;
        }

        global $db;
        $this->data = [];
        $categories_query = "SELECT c.categories_id, cd.categories_name, c.parent_id
                      FROM " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
                      WHERE c.categories_id = cd.categories_id
                      AND cd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                      AND c.categories_status != '0'
                      ORDER BY c.parent_id, c.sort_order, cd.categories_name";
        $categories = $db->Execute($categories_query);
        while (!$categories->EOF) {
            $this->data[$categories->fields['parent_id']][$categories->fields['categories_id']] = ['name' => $categories->fields['categories_name'], 'count' => 0];
            $categories->MoveNext();
        }
    }

    /**
     * @since ZC v1.3.0
     */
    public function buildBranch($parent_id, $level = 0, $parent_link = ''): string
    {
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

                if (isset($this->data[$category_id]) && (empty($this->max_level) || ($this->max_level > $level + 1))) {
                    $result .= $this->buildBranch($category_id, $level + 1, $category_link . '_');
                }
                $result .= $this->child_end_string;
            }
        }

        $result .= $this->parent_group_end_string;

        return $result;
    }

    /**
     * @since ZC v1.3.0
     */
    public function buildTree(): string
    {
        return $this->buildBranch($this->root_category_id);
    }
}
