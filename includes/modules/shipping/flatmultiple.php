<?php
/**
 * @package shippingMethod
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: flatmultiple.php  ajeh  Modified in v1.6.0 $
 */
/**
 * Flat Rate shipping method
 * with multiple names and prices choices as radio-buttons
 */
class flatmultiple extends base {
  /**
   * $code determines the internal 'code' name used to designate "this" shipping module
   *
   * @var string
   */
  var $code;
  /**
   * $title is the displayed name for this shipping method on the storefront
   *
   * @var string
   */
  var $title;
  /**
   * $description is a soft name for this shipping method, rarely used
   *
   * @var string
   */
  var $description;
  /**
   * module's icon, if any.  Must be manually uploaded to the server's images folder, and an appropriate call to zen_image() added to the constructor.
   *
   * @var string
   */
  var $icon;
  /**
   * $enabled determines whether this module shows or not during checkout.
   * Can be updated with custom code in the module's update_status() method.
   * Can be overridden with observers via notifier points NOTIFY_SHIPPING_CHECK_ENABLED_FOR_ZONE and NOTIFY_SHIPPING_CHECK_ENABLED
   * @var boolean
   */
  var $enabled;
  /**
   * constructor
   *
   * @return flatmultiple
   */
  function __construct() {
    $this->code = 'flatmultiple';
    $this->title = MODULE_SHIPPING_FLATMULTIPLE_TEXT_TITLE;
    $this->description = MODULE_SHIPPING_FLATMULTIPLE_TEXT_DESCRIPTION;
    $this->sort_order = MODULE_SHIPPING_FLATMULTIPLE_SORT_ORDER;
    $this->icon = ''; // add image filename here; must be uploaded to the /images/ subdirectory
    $this->tax_class = MODULE_SHIPPING_FLATMULTIPLE_TAX_CLASS;
    $this->tax_basis = MODULE_SHIPPING_FLATMULTIPLE_TAX_BASIS;
    $this->enabled = (MODULE_SHIPPING_FLATMULTIPLE_STATUS == 'True') ? true : false;
    $this->update_status();
    $this->notify('MODULE_SHIPPING_' . strtoupper($this->code) . '_INSTANTIATED');
  }

  /**
   * Coders can add custom logic here in the update_status() method to allow for manipulating the $this->enabled status
   */
  function update_status() {
    global $order, $db;
    if (IS_ADMIN_FLAG == TRUE) return;

    // disable only when entire cart is free shipping
    if (zen_get_shipping_enabled($this->code) == FALSE) $this->enabled = FALSE;

    /** CUSTOM ENABLE/DISABLE LOGIC CAN BE ADDED IN THE AREA SPECIFIED BELOW **/
    if ($this->enabled) {
      global $template, $current_page_base;
      // CUSTOMIZED CONDITIONS GO HERE
      // Optionally add additional code here to disable the module by changing $this->enabled to false based on whatever custom rules you require.
      // -----


      // -----
      // eof: optional additional code
    }
//echo 'FLATMULTIPLE function ' . __FUNCTION__ . ' $this->enabled: ' . ($this->enabled ? ' ON' : ' OFF') . ' $shipping_weight: ' . $shipping_weight . '<br>';
  }

  /**
   * Sets $this->enabled based on zone restrictions applied to this module
   * @return boolean
   */
  function check_enabled_for_zone()
  {
    global $order, $db;
      if ($this->enabled == true && (int)MODULE_SHIPPING_FLATMULTIPLE_ZONE > 0) {
      $check_flag = false;
      $check = $db->Execute("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . "
                             where geo_zone_id = '" . MODULE_SHIPPING_FLATMULTIPLE_ZONE . "'
                             and zone_country_id = '" . (int)$order->delivery['country']['id'] . "'
                             order by zone_id");
      while (!$check->EOF) {
        if ($check->fields['zone_id'] < 1) {
          $check_flag = true;
          break;
        } elseif ($check->fields['zone_id'] == $order->delivery['zone_id']) {
          $check_flag = true;
          break;
        }
        $check->MoveNext();
      }

      if ($check_flag == false) {
        $this->enabled = false;
      }
    }
    return $this->enabled;
  }

  /**
   * Returns the value of $this->enabled variable
   * @return boolean
   */
  function check_enabled()
  {
    return $this->enabled;
  }

  /**
   * Obtain quote from shipping system/calculations
   *
   * @param string $method
   * @return array
   */
  function quote($method = '') {
    global $order;

    // this code looks to see if there's a language-specific translation for the available shipping locations/methods, to override what is entered in the Admin (since the admin setting is in the default language)
    $ways_translated = (defined('MODULE_SHIPPING_FLATMULTIPLE_MULTIPLE_WAYS')) ? trim(MODULE_SHIPPING_FLATMULTIPLE_MULTIPLE_WAYS) : '';
    $ways_default = trim(MODULE_SHIPPING_FLATMULTIPLE_TITLES_LIST);
    $methodsToParse = ($ways_translated == '') ? $ways_default : $ways_translated;

    if ($methodsToParse == '') {

      // calculate final shipping cost
      $final_shipping_cost = MODULE_SHIPPING_FLATMULTIPLE_COST;

      $this->methodsList[] = array('id' => $this->code,
                                   'title' => trim((string)MODULE_SHIPPING_FLATMULTIPLE_TEXT_WAY),
                                   'cost' => $final_shipping_cost);
    } else {
      $this->locations = explode(';', (string)$methodsToParse);
      $this->methodsList = array();
      foreach ($this->locations as $key => $val)
      {
        if ($method != '' && $method != $this->code . (string)$key) continue;
        $cost = MODULE_SHIPPING_FLATMULTIPLE_COST;
        $title = $val;
        if (strstr($val, ',')) {
          list($title, $cost) = explode(',', $val);
        }

        // calculate final shipping cost
        $final_shipping_cost = $cost;

        $this->methodsList[] = array('id' => $this->code . (string)$key,
                                     'title' => trim($title),
                                     'cost' => $final_shipping_cost);
      }
    }

    $this->quotes = array('id' => $this->code,
                          'module' => MODULE_SHIPPING_FLATMULTIPLE_TEXT_TITLE,
                          'methods' => $this->methodsList);

    if ($this->tax_class > 0) {
      $this->quotes['tax'] = zen_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
    }

    if (zen_not_null($this->icon)) $this->quotes['icon'] = zen_image($this->icon, $this->title);

    $this->notify('MODULE_SHIPPING_' . strtoupper($this->code) . '_QUOTES_PREPARED');
    return $this->quotes;
  }

  /**
   * Check to see whether module is installed
   *
   * @return boolean
   */
  function check() {
    global $db;
    if (!isset($this->_check)) {
      $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_FLATMULTIPLE_STATUS'");
      $this->_check = $check_query->RecordCount();
    }
    if ($this->_check > 0 && !defined('MODULE_SHIPPING_FLATMULTIPLE_TITLES_LIST')) $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Flat Titles', 'MODULE_SHIPPING_FLATMULTIPLE_TITLES_LIST', 'Flat Rate', 'Enter a list of Flat Rate Titles, separated by semicolons (;).<br>Optionally you may specify a fee/surcharge for each Flat Rate Title by adding a comma and an amount. If no amount is specified, then the generic Shipping Cost amount from the next setting will be applied.<br><br>Examples:<br>Ground;Two Day,2.00;<br>Next Day,3.00;Express,4.00;Over Night,10.00<br>For multilanguage use, see the define-statement in the language file for this module.', '6', '0', now())");
    return $this->_check;
  }

  /**
   * Install the shipping module and its configuration settings
   */
  function install() {
    global $db;
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Flat Multiple Shipping', 'MODULE_SHIPPING_FLATMULTIPLE_STATUS', 'True', 'Do you want to offer In Store rate shipping?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Flat Titles', 'MODULE_SHIPPING_FLATMULTIPLE_TITLES_LIST', 'Flat Rate', 'Enter a list of Flat Rate Titles, separated by semicolons (;).<br>Optionally you may specify a fee/surcharge for each Flat Rate Title by adding a comma and an amount. If no amount is specified, then the generic Shipping Cost amount from the next setting will be applied.<br><br>Examples:<br>Ground;Two Day,2.00;<br>Next Day,3.00;Express,4.00;Over Night,10.00', '6', '0', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Shipping Cost', 'MODULE_SHIPPING_FLATMULTIPLE_COST', '5.00', 'The shipping cost for all orders using this shipping method.', '6', '0', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Tax Class', 'MODULE_SHIPPING_FLATMULTIPLE_TAX_CLASS', '0', 'Use the following tax class on the shipping fee.', '6', '0', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Tax Basis', 'MODULE_SHIPPING_FLATMULTIPLE_TAX_BASIS', 'Shipping', 'On what basis is Shipping Tax calculated. Options are<br />Shipping - Based on customers Shipping Address<br />Billing Based on customers Billing address<br />Store - Based on Store address if Billing/Shipping Zone equals Store zone', '6', '0', 'zen_cfg_select_option(array(\'Shipping\', \'Billing\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Shipping Zone', 'MODULE_SHIPPING_FLATMULTIPLE_ZONE', '0', 'If a zone is selected, only enable this shipping method for that zone.', '6', '0', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_SHIPPING_FLATMULTIPLE_SORT_ORDER', '0', 'Sort order of display.', '6', '0', now())");
  }

  /**
   * Remove the module and all its settings
   */
  function remove() {
    global $db;
    $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key like 'MODULE\_SHIPPING\_FLATMULTIPLE\_%'");
  }

  /**
   * Internal list of configuration keys used for configuration of the module
   *
   * @return array
   */
  function keys() {
    return array('MODULE_SHIPPING_FLATMULTIPLE_STATUS', 'MODULE_SHIPPING_FLATMULTIPLE_TITLES_LIST', 'MODULE_SHIPPING_FLATMULTIPLE_COST', 'MODULE_SHIPPING_FLATMULTIPLE_TAX_CLASS', 'MODULE_SHIPPING_FLATMULTIPLE_TAX_BASIS', 'MODULE_SHIPPING_FLATMULTIPLE_ZONE', 'MODULE_SHIPPING_FLATMULTIPLE_SORT_ORDER');
  }
}

