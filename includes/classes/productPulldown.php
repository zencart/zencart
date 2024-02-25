<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: pRose on charmes 2024 Feb 19 Modified in v2.0.0-beta1 $
 */

    /**
     *
     */
    class productPulldown extends pulldown
    {
        /**
         * @var string[]
         */
        private $keyed_allowed_sort_array = [
            'products_name' => 'pd',
            'products_model' => 'p',
            'products_id' => 'p',
            'products_price' => 'p',
            'products_price_sorter' => 'p',
            'products_sort_order' => 'p',
        ];

        protected $categories_join;
        protected $output_string;
        protected $show_model;
        protected $show_price;

        /**
         *
         */
        public function __construct()
        {
            parent::__construct();

            $this->show_model = false;
            $this->show_price = true;
            $this->set_selected = 0;
            $this->categories_join = '';

            $this->sort = ' ORDER BY pd.products_name';

            $this->keyword_search_fields = [
                'pd.products_name',
                'p.products_model',
                'pd.products_description',
                'p.products_id',
            ];
        }

        /**
         * @param array $fieldnameArray
         *
         * @return $this
         */
        public function setSort(array $fieldnameArray)
        {
            if (empty($fieldnameArray)) {
                return $this;
            }

            $first = true;
            $this->sort = '';
            foreach ($fieldnameArray as $fieldname) {
                if (array_key_exists($fieldname, $this->keyed_allowed_sort_array)) {
                    $this->sort .= ($first ? ' ORDER BY ' : ', ') . $this->keyed_allowed_sort_array[$fieldname] . '.' . $fieldname;
                    $first = false;
                }
            }
            return $this;
        }

        /**
         * @param int $category_id
         *
         * @return $this
         */
        public function setCategory(int $category_id)
        {
            $this->categories_join = " LEFT JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc ON (ptc.products_id = p.products_id)";
            $this->condition .= " AND ptc.categories_id = " . (int)$category_id;
            return $this;
        }

        /**
         * @param bool $status
         *
         * @return $this
         */
        public function showModel(bool $status)
        {
            $this->show_model = $status;
            return $this;
        }

        /**
         * @param bool $status
         *
         * @return $this
         */
        public function showPrice(bool $status)
        {
            $this->show_price = $status;
            return $this;
        }

        /**
         * @param bool $status
         *
         * @return $this
         */
        public function onlyActive(bool $status)
        {
            $condition = " AND p.products_status = 1";
            $this->condition = str_replace($condition, '', $this->condition);
            if ($status) {
                $this->condition .= " AND p.products_status = 1";
            }
            return $this;
        }

        /**
         * @return mixed|void
         */
        protected function setSQL()
        {
            $this->sql = "SELECT DISTINCT pd.products_id, p.products_sort_order, p.products_price, p.products_model, pd.products_name
                FROM " . TABLE_PRODUCTS . " p"
                . $this->categories_join . "
                INNER JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON (p.products_id = pd.products_id)
                " . $this->attributes_join . "
                WHERE pd.language_id = " . (int)$_SESSION['languages_id'];
        }


        /**
         * @return mixed|void
         */
        protected function processSQL()
        {
            global $currencies;

            $this->setSQL();
            $this->runSQL();

            $parm_2 = '';
            $parm_3 = '';

            if ($this->show_model) {
                $parm_2 = '%2$s';
            }

            if ($this->show_price) {
                $parm_3 = ' (%3$s)';
            }

            $this->output_string = '%1$s ' . $parm_2 . $parm_3;  // format string with name first

            if (strpos($this->sort, 'model')) {                  // show model first when sorted by model
                $this->output_string = (!empty($parm_2) ? $parm_2 . '-' : '') . ' %1$s' . $parm_3; // format string with model first
            }


            foreach ($this->results as $result) {
                if (in_array($result['products_id'], $this->exclude)) {
                    continue;
                }
                $display_price = $this->show_price ? zen_get_products_base_price($result['products_id']) : '';
                $name = zen_get_products_name($result['products_id']);
                $this->values[] = [
                    'id' => $result['products_id'],
                    'text' => sprintf(
                        $this->output_string,
                        trim(zen_clean_html($name)),
                        ($this->show_model ? ' [' . $result['products_model'] . '] ' : ''),
                        $currencies->format($display_price)
                    ) . ($this->show_id ? ' - ID# ' . $result['products_id'] : ''),
                ];
            }
        }
    }
