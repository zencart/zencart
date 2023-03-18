<?php
/**
 * Class categoryPulldown 
 *
 * @copyright Copyright 2003-2023 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2022 Oct 16 Modified in v1.5.8a $
 */

    class categoryPulldown extends pulldown
    {

        private $show_full_path;
        private $show_parent;

        /**
         *
         */
        public function __construct()
        {
            parent::__construct();

            $this->show_parent = false;
            $this->show_full_path = false;

            $this->sort = " ORDER BY categories_name";

            $this->keyword_search_fields = [
                'cd.categories_name',
                'c.parent_id',
                'cd.categories_description',
                'c.categories_id',
            ];
        }

        /**
         * @param bool $status
         *
         * @return $this
         */
        public function showParent(bool $status)
        {
            $this->show_parent = $status;
            return $this;
        }

        /**
         * @param bool $status
         *
         * @return $this
         */
        public function showFullPath(bool $status)
        {
            $this->show_full_path = $status;
            return $this;
        }

        /**
         * @return mixed|void
         */
        protected function setSQL()
        {
            $this->attributes_join = str_replace('p.products_id', 'ptoc.products_id', $this->attributes_join);
            $this->sql = "SELECT DISTINCT c.categories_id, cd.categories_name
            FROM " . TABLE_CATEGORIES . " c
            LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd ON (c.categories_id = cd.categories_id AND cd.language_id = " . (int)$_SESSION['languages_id'] . ")
            LEFT JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " ptoc on (c.categories_id = ptoc.categories_id) 
            " . $this->attributes_join . "
            WHERE TRUE ";
        }

        /**
         * @return mixed|void
         */
        protected function processSQL()
        {
            $this->setSQL();
            $this->runSQL();

            foreach ($this->results as $result) {
                if (in_array($result['categories_id'], $this->exclude)) {
                    continue;
                }
                $this->values[] = [
                    'id' => $result['categories_id'],
                    'text' => $this->categoryText($result),
                ];
            }
        }

        /**
         * @param $category
         *
         * @return string|string[]|null
         */
        private function categoryText($category)
        {
            if (!empty($this->attributes_join)) {
                $text = $category['categories_name'];
                if ($this->show_full_path) {
                    $text = zen_output_generated_category_path($category['categories_id']);
                }
                return $text;
            }
            $parent = '';
            if ($this->show_parent) {
                $parent = zen_get_categories_parent_name($category['categories_id']);
                if ($parent != '') {
                    $parent = ' : in ' . $parent;
                }
            }
            return $category['categories_name'] . $parent . ($this->show_id ? ' - ID# ' . $category['categories_id'] : '');
        }
    }
