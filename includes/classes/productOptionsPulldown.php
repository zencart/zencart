<?php

    class productOptionsPulldown extends pulldown
    {

        function __construct()
        {
            parent::__construct();

            $this->sort = " ORDER BY products_options_name";

            $this->keyword_search_fields = [
                'products_options_name',
            ];

        }

        protected function setSQL()
        {
            $this->sql = "SELECT products_options_id, products_options_name
                                    FROM " . TABLE_PRODUCTS_OPTIONS . "
                                    WHERE language_id = " . $_SESSION['languages_id'];
        }

        protected function processSQL()
        {
            $this->setSQL();
            $this->runSQL();

            $this->values[] = [
                'id' => '',
                'text' => PLEASE_SELECT
            ];

            foreach ($this->results as $result) {
                $this->values[] = [
                    'id' => $result['products_options_id'],
                    'text' => $this->optionText($result),
                ];
            }
        }

        private function optionText($optionValue)
        {
            $return = "(" . $optionValue['products_options_id'] . ") " . $optionValue['products_options_name'];
            return $return;
        }

    }