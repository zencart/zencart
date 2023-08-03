<?php
/**
 * Class productOptionsPulldown 
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2022 Aug 15 Modified in v1.5.8-alpha2 $
 */

    class productOptionsPulldown extends pulldown
    {
        /**
         *
         */
        public function __construct()
        {
            parent::__construct();

            $this->sort = " ORDER BY products_options_name";

            $this->keyword_search_fields = [
                'products_options_name',
            ];
        }

        /**
         * @return mixed|void
         */
        protected function setSQL()
        {
            $this->sql = "SELECT products_options_id, products_options_name
                                    FROM " . TABLE_PRODUCTS_OPTIONS . "
                                    WHERE language_id = " . $_SESSION['languages_id'];
        }

        /**
         * @return mixed|void
         */
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

        /**
         * @param $optionValue
         *
         * @return string
         */
        private function optionText($optionValue)
        {
            $return = "(" . $optionValue['products_options_id'] . ") " . $optionValue['products_options_name'];
            return $return;
        }
    }
