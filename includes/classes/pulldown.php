<?php
/**
 * Class pulldown 
 * 
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott Wilson 2024 Aug 26 Modified in v2.1.0-alpha2 $
 */

    abstract class pulldown extends base
    {

        /**
         * @var string
         */
        protected $attributes_join;
        /**
         * @var false
         */
        protected $show_id;
        /**
         * @var int
         */
        protected $set_selected;
        /**
         * @var string
         */
        protected $parameters;
        /**
         * @var string
         */
        protected $condition;
        /**
         * @var array
         */
        protected $exclude = [];
        /**
         * @var int
         */
        protected $count = 0;
        
        protected $keywords;
        protected $keyword_search_fields; 
        protected $results;    
        protected $sort;
        protected $sql;
        protected $values = [];

        /**
         *
         */
        public function __construct()
        {
            $this->exclude = [];

            $this->show_id = false;

            $this->set_selected = 0;
            $this->values = [];

            $this->keywords = '';

            $this->attributes_join = '';

            $this->condition = ' ';

            // default styling
            $this->parameters = '';
            //$this->parameters = 'required size="15" class="form-control" id="products_id"';
        }

        /**
         * @param int $id
         *
         * @return $this
         */
        public function setDefault(int $id)
        {
            $this->set_selected = $id;
            return $this;
        }

        /**
         * @param bool $status
         *
         * @return $this
         */
        public function showID(bool $status)
        {
            $this->show_id = $status;
            return $this;
        }

        /**
         * @param int $filter_id
         *
         * @return $this
         */
        public function setOptionFilter(int $filter_id)
        {
            $this->includeAttributes(true);
            $this->condition .= " AND pa.options_id =" . (int)$filter_id;
            return $this;
        }

        /**
         * @param array $array
         *
         * @return $this
         */
        public function exclude(array $array)
        {
            $this->exclude = $array;
            return $this;
        }

        /**
         * @param bool $status
         *
         * @return $this
         */
        public function includeAttributes(bool $status)
        {
            $this->attributes_join = '';
            if ($status) {
                $this->attributes_join = " RIGHT JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa on (p.products_id = pa.products_id)";
            }
            return $this;
        }

        /**
         * @param string $keywords
         *
         * @return $this
         */
        public function setSearchTerms(string $keywords)
        {
            $this->keywords = $keywords;
            return $this;
        }

        /**
         * @return mixed
         */
        abstract protected function processSQL();

        /**
         * @return mixed
         */
        abstract protected function setSQL();

        /**
         * @return void
         */
        protected function runSQL()
        {
            global $db;

            $this->sql .= $this->condition;

            if (empty($this->keywords)) {
                $this->keywords = ($_REQUEST['keywords'] ?? '');
            }

            if (!empty($this->keywords)) {
                $this->sql .= zen_build_keyword_where_clause(
                    $this->keyword_search_fields,
                    zen_db_input(zen_db_prepare_input($this->keywords))
                );
            }

            $this->sql .= $this->sort;
            $this->results = $db->Execute($this->sql);
            $this->count = $this->results->count();
        }

        /**
         * @param string $name
         * @param string $parameters
         * @param bool   $required
         *
         * @return string
         */
        public function generatePulldownHtml(string $name, string $parameters = '', bool $required = false)
        {
            $this->processSQL();

            if (empty($parameters)) {
                $parameters = $this->parameters;
            }

            return zen_draw_pull_down_menu($name, $this->values, $this->set_selected, $parameters, $required);
        }
    }
