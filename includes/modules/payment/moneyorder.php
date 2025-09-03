<?php

    /**
     * @copyright Copyright 2003-2023 Zen Cart Development Team
     * @copyright Portions Copyright 2003 osCommerce
     * @license   http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
     * @version   $Id: Scott C Wilson 2022 Oct 16 Modified in v1.5.8a $
     */
    class moneyorder extends base
    {
        /**
         * $_check is used to check the configuration key set up
         *
         * @var int
         */
        protected $_check;
        /**
         * $code determines the internal 'code' name used to designate "this" payment module
         *
         * @var string
         */
        public $code;
        /**
         * $description is a soft name for this payment method
         *
         * @var string
         */
        public $description;
        /**
         * $email_footer is the text to me placed in the footer of the email
         *
         * @var string
         */
        public $email_footer;
        /**
         * $enabled determines whether this module shows or not... during checkout.
         *
         * @var boolean
         */
        public $enabled;
        /**
         * $order_status is the order status to set after processing the payment
         *
         * @var int
         */
        public $order_status;
        /**
         * $title is the displayed name for this order total method
         *
         * @var string
         */
        public $title;
        /**
         * $sort_order is the order priority of this payment module when displayed
         *
         * @var int
         */
        public $sort_order;

// class constructor
        function __construct()
        {
            global $order;

            $this->code = 'moneyorder';
            $this->title = MODULE_PAYMENT_MONEYORDER_TEXT_TITLE;
            $this->description = MODULE_PAYMENT_MONEYORDER_TEXT_DESCRIPTION;
            $this->sort_order = defined('MODULE_PAYMENT_MONEYORDER_SORT_ORDER') ? MODULE_PAYMENT_MONEYORDER_SORT_ORDER : null;
            $this->enabled = (defined('MODULE_PAYMENT_MONEYORDER_STATUS') && MODULE_PAYMENT_MONEYORDER_STATUS == 'True');

            if (null === $this->sort_order) {
                return false;
            }

            if (IS_ADMIN_FLAG === true && (MODULE_PAYMENT_MONEYORDER_PAYTO == 'the Store Owner/Website Name' || MODULE_PAYMENT_MONEYORDER_PAYTO == '')) {
                $this->title .= '<span class="alert"> (not configured - needs pay-to)</span>';
            }

            if ((int)MODULE_PAYMENT_MONEYORDER_ORDER_STATUS_ID > 0) {
                $this->order_status = MODULE_PAYMENT_MONEYORDER_ORDER_STATUS_ID;
            }

            if (is_object($order)) {
                $this->update_status();
            }

            $this->notify('NOTIFY_MONEYORDER_CONSTRUCTOR');

            $this->email_footer = MODULE_PAYMENT_MONEYORDER_TEXT_EMAIL_FOOTER;
        }

// class methods
        function update_status()
        {
            global $order, $db;

            if ($this->enabled && (int)MODULE_PAYMENT_MONEYORDER_ZONE > 0 && isset($order->billing['country']['id'])) {
                $check_flag = false;
                $check = $db->Execute("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_MONEYORDER_ZONE . "' and zone_country_id = '" . (int)$order->billing['country']['id'] . "' order by zone_id");
                while (!$check->EOF) {
                    if ($check->fields['zone_id'] < 1) {
                        $check_flag = true;
                        break;
                    } elseif ($check->fields['zone_id'] == $order->billing['zone_id']) {
                        $check_flag = true;
                        break;
                    }
                    $check->MoveNext();
                }

                if ($check_flag == false) {
                    $this->enabled = false;
                }
            }

            // other status checks?
            if ($this->enabled) {
                // other checks here
            }
        }

        function javascript_validation()
        {
            return false;
        }

        function selection()
        {
            return [
                'id' => $this->code,
                'module' => $this->title,
            ];
        }

        function pre_confirmation_check()
        {
            return false;
        }

        function confirmation()
        {
            return ['title' => MODULE_PAYMENT_MONEYORDER_TEXT_DESCRIPTION];
        }

        function process_button()
        {
            return false;
        }

        function before_process()
        {
            return false;
        }

        function after_process()
        {
            return false;
        }

        function get_error()
        {
            return false;
        }

        function check()
        {
            global $db;
            if (!isset($this->_check)) {
                $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_MONEYORDER_STATUS'");
                $this->_check = $check_query->RecordCount();
            }
            return $this->_check;
        }

        function install()
        {
            global $db, $messageStack;
            if (defined('MODULE_PAYMENT_MONEYORDER_STATUS')) {
                $messageStack->add_session('MoneyOrder module already installed.', 'error');
                zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=moneyorder', 'NONSSL'));
                return 'failed';
            }
            $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Check/Money Order Module', 'MODULE_PAYMENT_MONEYORDER_STATUS', 'True', 'Do you want to accept Check/Money Order payments?', '6', '1', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now());");
            $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Make Payable to:', 'MODULE_PAYMENT_MONEYORDER_PAYTO', 'the Store Owner/Website Name', 'Who should payments be made payable to?', '6', '1', now());");
            $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_MONEYORDER_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
            $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_MONEYORDER_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
            $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_MONEYORDER_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
        }

        function remove()
        {
            global $db;
            $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
        }

        function keys()
        {
            return [
                'MODULE_PAYMENT_MONEYORDER_STATUS',
                'MODULE_PAYMENT_MONEYORDER_ZONE',
                'MODULE_PAYMENT_MONEYORDER_ORDER_STATUS_ID',
                'MODULE_PAYMENT_MONEYORDER_SORT_ORDER',
                'MODULE_PAYMENT_MONEYORDER_PAYTO',
                ];
        }
    }
