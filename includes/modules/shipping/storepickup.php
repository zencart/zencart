<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott Wilson 2024 May 15 Modified in v2.0.1 $
 */

/**
 * Store-Pickup / Will-Call shipping method
 * with multiple location choices as radio-buttons
 */
class storepickup extends ZenShipping
{
    /**
     * $locations is an array of locations for the customer to pickup order
     * @var array
     */
    protected $locations = [];
    /**
     * $methodsList is an array of translations for the $locations
     * @var array
     */
    protected $methodsList = [];

    /**
     * constructor
     *
     * @return storepickup
     */
    function __construct()
    {
        $this->code = 'storepickup';
        $this->title = MODULE_SHIPPING_STOREPICKUP_TEXT_TITLE;
        $this->description = MODULE_SHIPPING_STOREPICKUP_TEXT_DESCRIPTION;
        $this->sort_order = defined('MODULE_SHIPPING_STOREPICKUP_SORT_ORDER') ? MODULE_SHIPPING_STOREPICKUP_SORT_ORDER : null;
        if (null === $this->sort_order) {
            return false;
        }

        $this->icon = ''; // add image filename here; must be uploaded to the /images/ subdirectory
        $this->tax_class = MODULE_SHIPPING_STOREPICKUP_TAX_CLASS;
        $this->tax_basis = MODULE_SHIPPING_STOREPICKUP_TAX_BASIS;
        $this->enabled = (MODULE_SHIPPING_STOREPICKUP_STATUS == 'True');
        $this->update_status();
    }

    /**
     * Perform various checks to see whether this module should be visible
     */
    function update_status()
    {
        global $order, $db;
        if ($this->enabled === false || IS_ADMIN_FLAG === true) {
            return;
        }

        if (isset($order->delivery) && (int)MODULE_SHIPPING_STOREPICKUP_ZONE > 0) {
            $check_flag = false;
            $check = $db->Execute(
                "SELECT zone_id
                   FROM " . TABLE_ZONES_TO_GEO_ZONES . "
                  WHERE geo_zone_id = " . (int)MODULE_SHIPPING_STOREPICKUP_ZONE . "
                    AND zone_country_id = " . (int)($order->delivery['country']['id'] ?? -1) . "
                  ORDER BY zone_id"
            );
            foreach ($check as $next_zone) {
                if ($next_zone['zone_id'] < 1) {
                    $check_flag = true;
                    break;
                } elseif ($next_zone['zone_id'] == $order->delivery['zone_id']) {
                    $check_flag = true;
                    break;
                }
            }

            if ($check_flag === false) {
                $this->enabled = false;
            }
        }

        // other status checks?
        if ($this->enabled) {
            // other checks here

            // -----
            // Give a watching observer the opportunity to disable the overall shipping module.
            //
            $this->notify('NOTIFY_SHIPPING_STOREPICKUP_UPDATE_STATUS', [], $this->enabled);
        }
    }

    /**
     * Obtain quote from shipping system/calculations
     *
     * @param string $method
     * @return array
     */
    function quote($method = ''): array
    {
        global $order;

        // this code looks to see if there's a language-specific translation for the available shipping locations/methods, to override what is entered in the Admin (since the admin setting is in the default language)
        $ways_translated = (defined('MODULE_SHIPPING_STOREPICKUP_MULTIPLE_WAYS')) ? trim(MODULE_SHIPPING_STOREPICKUP_MULTIPLE_WAYS) : '';
        $ways_default = trim(MODULE_SHIPPING_STOREPICKUP_LOCATIONS_LIST);
        $methodsToParse = ($ways_translated == '') ? $ways_default : $ways_translated;

        if ($methodsToParse == '') {
            $this->methodsList[] = [
                'id' => $this->code,
                'title' => trim((string)MODULE_SHIPPING_STOREPICKUP_TEXT_WAY),
                'cost' => MODULE_SHIPPING_STOREPICKUP_COST,
            ];
        } else {
            $this->locations = explode(';', (string)$methodsToParse);
            $this->methodsList = [];
            foreach ($this->locations as $key => $val) {
                if ($method != '' && $method != $this->code . (string)$key) {
                    continue;
                }
                $cost = MODULE_SHIPPING_STOREPICKUP_COST;
                $title = $val;
                if (strstr($val, ',')) {
                    [$title, $cost] = explode(',', $val);
                }
                $this->methodsList[] = [
                    'id' => $this->code . (string)$key,
                    'title' => trim($title),
                    'cost' => $cost,
                ];
            }
        }

        $this->quotes = [
            'id' => $this->code,
            'module' => MODULE_SHIPPING_STOREPICKUP_TEXT_TITLE,
            'methods' => $this->methodsList,
        ];

        if ($this->tax_class > 0) {
            $this->quotes['tax'] = zen_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
        }

        if (!empty($this->icon)) {
            $this->quotes['icon'] = zen_image($this->icon, $this->title);
        }

        return $this->quotes;
    }

    /**
     * Check to see whether module is installed
     *
     * @return boolean
     */
    function check()
    {
        global $db;
        if (!isset($this->_check)) {
            $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_STOREPICKUP_STATUS'");
            $this->_check = $check_query->RecordCount();
        }
        if ($this->_check > 0 && !defined('MODULE_SHIPPING_STOREPICKUP_LOCATIONS_LIST')) {
            $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Pickup Locations', 'MODULE_SHIPPING_STOREPICKUP_LOCATIONS_LIST', 'Walk In', 'Enter a list of locations, separated by semicolons (;).<br>Optionally you may specify a fee/surcharge for each location by adding a comma and an amount. If no amount is specified, then the generic Shipping Cost amount from the next setting will be applied.<br><br>Examples:<br>121 Main Street;20 Church Street<br>Sunnyside,4.00;Lee Park,5.00;High Street,0.00<br>Dallas;Tulsa,5.00;Phoenix,0.00<br>For multilanguage use, see the define-statement in the language file for this module.', '6', '0', now())");
        }
        return $this->_check;
    }

    /**
     * Install the shipping module and its configuration settings
     *
     */
    function install(): void
    {
        global $db;
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Store Pickup Shipping', 'MODULE_SHIPPING_STOREPICKUP_STATUS', 'True', 'Do you want to offer In Store rate shipping?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Pickup Locations', 'MODULE_SHIPPING_STOREPICKUP_LOCATIONS_LIST', 'Walk In', 'Enter a list of locations, separated by semicolons (;).<br>Optionally you may specify a fee/surcharge for each location by adding a comma and an amount. If no amount is specified, then the generic Shipping Cost amount from the next setting will be applied.<br><br>Examples:<br>121 Main Street;20 Church Street<br>Sunnyside,4.00;Lee Park,5.00;High Street,0.00<br>Dallas;Tulsa,5.00;Phoenix,0.00<br>For multilanguage use, see the define-statement in the language file for this module.', '6', '0', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Shipping Cost', 'MODULE_SHIPPING_STOREPICKUP_COST', '0.00', 'The shipping cost for all orders using this shipping method.', '6', '0', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Tax Class', 'MODULE_SHIPPING_STOREPICKUP_TAX_CLASS', '0', 'Use the following tax class on the shipping fee.', '6', '0', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Tax Basis', 'MODULE_SHIPPING_STOREPICKUP_TAX_BASIS', 'Shipping', 'On what basis is Shipping Tax calculated. Options are<br>Shipping - Based on Store Pickup Address <br>Billing - Based on customers Billing address', '6', '0', 'zen_cfg_select_option(array(\'Shipping\', \'Billing\'), ', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Shipping Zone', 'MODULE_SHIPPING_STOREPICKUP_ZONE', '0', 'If a zone is selected, only enable this shipping method for that zone.', '6', '0', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_SHIPPING_STOREPICKUP_SORT_ORDER', '0', 'Sort order of display.', '6', '0', now())");
    }

    /**
     * Internal list of configuration keys used for configuration of the module
     *
     * @return array
     */
    function keys(): array
    {
        return ['MODULE_SHIPPING_STOREPICKUP_STATUS', 'MODULE_SHIPPING_STOREPICKUP_LOCATIONS_LIST', 'MODULE_SHIPPING_STOREPICKUP_COST', 'MODULE_SHIPPING_STOREPICKUP_TAX_CLASS', 'MODULE_SHIPPING_STOREPICKUP_TAX_BASIS', 'MODULE_SHIPPING_STOREPICKUP_ZONE', 'MODULE_SHIPPING_STOREPICKUP_SORT_ORDER'];
    }
}
