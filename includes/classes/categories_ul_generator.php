<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 * @since ZC v1.5.5
 */

class zen_categories_ul_generator
{
    protected array $data = [];

    public function __construct(
        protected int|string $root_category_id = TOPMOST_CATEGORY_PARENT_ID,
        protected int $max_level = 0,
        protected string $parent_group_start_string = "\n<ul%s>",
        protected string $parent_group_end_string = "</ul>\n",
        protected string $child_start_string = "<li%s>",
        protected string $child_end_string = "</li>\n",
        protected string $spacer_string = '
', // line-break and new line are intentional
        protected int $spacer_multiplier = 1,
    ) {
        global $db;
        $this->data = [];
        $categories_query = "SELECT c.categories_id, cd.categories_name, c.parent_id
                             FROM " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
                             WHERE c.categories_id = cd.categories_id
                             AND c.categories_status = 1
                             AND cd.language_id = " . (int)$_SESSION['languages_id'] . "
                             ORDER BY c.parent_id, c.sort_order, cd.categories_name";

        $results = $db->Execute($categories_query, null, true, 300);

        foreach ($results as $result) {
            $this->data[$result['parent_id']][$result['categories_id']] = ['name' => $result['categories_name'], 'count' => 0];
        }
    }

    /**
     * @since ZC v1.5.5
     */
    public function buildBranch($parent_id, $level = 0, $submenu = true, string $parent_link = ''): string
    {
        $parent_id = (int)$parent_id;
        $level = (int)$level;
        $class_attribute = ($submenu === true) ? ' class="level' . ($level + 1) . '"' : '';
        $result = sprintf($this->parent_group_start_string, $class_attribute);

        if (($this->data[$parent_id])) {
            foreach ($this->data[$parent_id] as $category_id => $category) {
                $category_link = $parent_link . $category_id;

                $class_attribute = '';
                if (isset($this->data[$category_id])) {
                    $class_attribute = ($submenu === true) ? ' class="submenu"' : '';
                }
                $result .= sprintf($this->child_start_string, $class_attribute);
                $result .= str_repeat($this->spacer_string, $this->spacer_multiplier * 1) . '<a href="' . zen_href_link(FILENAME_DEFAULT, 'cPath=' . $category_link) . '">';
                $result .= $category['name'];
                $result .= '</a>';

                if (isset($this->data[$category_id]) && (empty($this->max_level) || $this->max_level > $level + 1)) {
                    $result .= $this->buildBranch($category_id, $level + 1, $submenu, $category_link . '_');
                }
                $result .= $this->child_end_string;
            }
        }

        $result .= $this->parent_group_end_string;
        return $result;
    }

    /**
     * @since ZC v1.5.5
     */
    public function buildTree($submenu = false, ?int $max_levels = null): string
    {
        if (empty($this->data)) {
            return '';
        }

        if ($max_levels !== null) {
            $this->max_level = $max_levels;
        }

        return $this->buildBranch($this->root_category_id, 0, $submenu);
    }
}
