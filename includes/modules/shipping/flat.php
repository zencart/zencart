<?php
/**
 * @package shippingMethod
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: flat.php  ajeh  Modified in v1.6.0 $
 */

class flat extends base {
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
   * @return flat
   */
  function __construct() {
    $this->code = 'flat';
    $this->title = MODULE_SHIPPING_FLAT_TEXT_TITLE;
    $this->description = MODULE_SHIPPING_FLAT_TEXT_DESCRIPTION;
      $this->sort_order = defined('MODULE_SHIPPING_FLAT_SORT_ORDER') ? MODULE_SHIPPING_FLAT_SORT_ORDER : null;
      if (null === $this->sort_order) return false;

      $this->icon = '';
    $this->tax_class = MODULE_SHIPPING_FLAT_TAX_CLASS;
    $this->tax_basis = MODULE_SHIPPING_FLAT_TAX_BASIS;
    $this->enabled = (MODULE_SHIPPING_FLAT_STATUS == 'True') ? true : false;
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

//echo 'FLAT function ' . __FUNCTION__ . ' $this->enabled: ' . ($this->enabled ? ' ON' : ' OFF') . ' $shipping_weight: ' . $shipping_weight . '<br>';
  }

  /**
   * Sets $this->enabled based on zone restrictions applied to this module
   * @return boolean
   */
  function check_enabled_for_zone()
  {
    global $order, $db;
    if ($this->enabled == true && (int)MODULE_SHIPPING_FLAT_ZONE > 0) {
      $check_flag = false;
      $check = $db->Execute("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . "
                             where geo_zone_id = '" . MODULE_SHIPPING_FLAT_ZONE . "'
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

    // calculate final shipping cost
    $final_shipping_cost = MODULE_SHIPPING_FLAT_COST;

    $this->quotes = array('id' => $this->code,
                          'module' => MODULE_SHIPPING_FLAT_TEXT_TITLE,
                          'methods' => array(array('id' => $this->code,
                                                   'title' => MODULE_SHIPPING_FLAT_TEXT_WAY,
                                                   'cost' => $final_shipping_cost)));

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
      $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_FLAT_STATUS'");
      $this->_check = $check_query->RecordCount();
    }
    return $this->_check;
  }

  /**
   * Install the shipping module and its configuration settings
   */
  function install() {
    global $db;
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Flat Shipping', 'MODULE_SHIPPING_FLAT_STATUS', 'True', 'Do you want to offer flat rate shipping?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Shipping Cost', 'MODULE_SHIPPING_FLAT_COST', '5.00', 'The shipping cost for all orders using this shipping method.', '6', '0', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Tax Class', 'MODULE_SHIPPING_FLAT_TAX_CLASS', '0', 'Use the following tax class on the shipping fee.', '6', '0', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Tax Basis', 'MODULE_SHIPPING_FLAT_TAX_BASIS', 'Shipping', 'On what basis is Shipping Tax calculated. Options are<br />Shipping - Based on customers Shipping Address<br />Billing Based on customers Billing address<br />Store - Based on Store address if Billing/Shipping Zone equals Store zone', '6', '0', 'zen_cfg_select_option(array(\'Shipping\', \'Billing\', \'Store\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Shipping Zone', 'MODULE_SHIPPING_FLAT_ZONE', '0', 'If a zone is selected, only enable this shipping method for that zone.', '6', '0', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_SHIPPING_FLAT_SORT_ORDER', '0', 'Sort order of display.', '6', '0', now())");
  }

  /**
   * Remove the module and all its settings
   */
  function remove() {
    global $db;
    $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key like 'MODULE\_SHIPPING\_FLAT\_%'");
  }

  /**
   * Internal list of configuration keys used for configuration of the module
   *
   * @return array
   */
  function keys() {
    return array('MODULE_SHIPPING_FLAT_STATUS', 'MODULE_SHIPPING_FLAT_COST', 'MODULE_SHIPPING_FLAT_TAX_CLASS', 'MODULE_SHIPPING_FLAT_TAX_BASIS', 'MODULE_SHIPPING_FLAT_ZONE', 'MODULE_SHIPPING_FLAT_SORT_ORDER');
  }
}
