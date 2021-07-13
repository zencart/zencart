<?php

    abstract class pulldown extends base
    {
        var $pulldown, $attributes_join, $show_id, $set_selected, $parameters, $condition, $exclude;


        function __construct()
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

        public function setDefault(int $id)
        {
            $this->set_selected = $id;
            return $this;
        }

        public function showID(bool $status)
        {
            $this->show_id = $status;
            return $this;
        }

        public function setOptionFilter(int $filter_id)
        {
            $this->includeAttributes(true);
            $this->condition .= " AND pa.options_id =" . (int)$filter_id;
            return $this;
        }

        public function exclude(array $array)
        {
            $this->exclude = $array;
            return $this;
        }

        public function includeAttributes(bool $status)
        {
        	$this->attributes_join = '';
        	if ($status) {
		        $this->attributes_join = " RIGHT JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa on (p.products_id = pa.products_id)";
	        }
            return $this;
        }

        public function setSearchTerms(string $keywords)
        {
            $this->keywords = $keywords;
            return $this;
        }

        abstract protected function processSQL();

        abstract protected function setSQL();

        protected function runSQL()
        {
            global $db;

            $this->sql .= $this->condition;

            if (empty($this->keywords)) {
                $this->keywords = ($_REQUEST['keywords'] ?? '');
            }

            if (!empty($this->keywords)) {
                $this->sql .= zen_build_keyword_where_clause($this->keyword_search_fields,
                    zen_db_input(zen_db_prepare_input($this->keywords)));
            }

            $this->sql .= $this->sort;
            $this->results = $db->Execute($this->sql);
        }

        public function generatePullDownHtml(string $name, $parameters = '', bool $required = false)
        {
            $this->processSQL();

            if (empty($parameters)) {
                $parameters = $this->parameters;
            }

            return zen_draw_pull_down_menu($name, $this->values, $this->set_selected, $parameters, $required);
        }
    }