<?php
/**
 * @package shippingMethod
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Drbyte Tue Nov 20 12:58:03 2018 -0500 Modified in v1.5.6 $
 */
/**
 * "Per Weight Unit" shipping module, allowing you to offer per-unit-rate shipping options
 *
 */
class perweightunit extends base {
  /**
   * $code determines the internal 'code' name used to designate "this" payment module
   *
   * @var string
   */
  var $code;
  /**
   * $title is the displayed name for this payment method
   *
   * @var string
   */
  var $title;
  /**
   * $description is a soft name for this payment method
   *
   * @var string
   */
  var $description;
  /**
   * module's icon
   *
   * @var string
   */
  var $icon;
  /**
   * $enabled determines whether this module shows or not... during checkout.
   *
   * @var boolean
   */
  var $enabled;
  /**
     * Constructor
   *
   * @return perweightunit
   */
  function __construct() {
    global $order, $db;

    $this->code = 'perweightunit';
    $this->title = MODULE_SHIPPING_PERWEIGHTUNIT_TEXT_TITLE;
    $this->description = MODULE_SHIPPING_PERWEIGHTUNIT_TEXT_DESCRIPTION;
    $this->sort_order = defined('MODULE_SHIPPING_PERWEIGHTUNIT_SORT_ORDER') ? MODULE_SHIPPING_PERWEIGHTUNIT_SORT_ORDER : null;
    if (null === $this->sort_order) return false;

    $this->icon = '';
    $this->tax_class = MODULE_SHIPPING_PERWEIGHTUNIT_TAX_CLASS;
    $this->tax_basis = MODULE_SHIPPING_PERWEIGHTUNIT_TAX_BASIS;

    // disable only when entire cart is free shipping
    if (zen_get_shipping_enabled($this->code)) {
      $this->enabled = ((MODULE_SHIPPING_PERWEIGHTUNIT_STATUS == 'True') ? true : false);
    }

    if ($this->enabled) {
      // check MODULE_SHIPPING_PERWEIGHTUNIT_HANDLING_METHOD is in
      $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_PERWEIGHTUNIT_HANDLING_METHOD'");
      if ($check_query->EOF) {
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Handling Per Order or Per Box', 'MODULE_SHIPPING_PERWEIGHTUNIT_HANDLING_METHOD', 'Order', 'Do you want to charge Handling Fee Per Order or Per Box?', '6', '0', 'zen_cfg_select_option(array(\'Order\', \'Box\'), ', now())");
      }
    }

    if ( ($this->enabled == true) && ((int)MODULE_SHIPPING_PERWEIGHTUNIT_ZONE > 0) ) {
      $check_flag = false;
      $check = $db->Execute("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . "
                             where geo_zone_id = '" . MODULE_SHIPPING_PERWEIGHTUNIT_ZONE . "'
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
  }
  /**
   * Obtain quote from shipping system/calculations
   *
   * @param string $method
   * @return array
   */
  function quote($method = '') {
    global $order, $shipping_weight, $shipping_num_boxes;

    $total_weight_units = $shipping_weight;
    $this->quotes = array('id' => $this->code,
                          'module' => MODULE_SHIPPING_PERWEIGHTUNIT_TEXT_TITLE,
                          'methods' => array(array('id' => $this->code,
                                                   'title' => MODULE_SHIPPING_PERWEIGHTUNIT_TEXT_WAY,
                                                   'cost' => (float)MODULE_SHIPPING_PERWEIGHTUNIT_COST * ($total_weight_units * $shipping_num_boxes) +
                                                   (MODULE_SHIPPING_PERWEIGHTUNIT_HANDLING_METHOD == 'Box' ? (float)MODULE_SHIPPING_PERWEIGHTUNIT_HANDLING * $shipping_num_boxes : (float)MODULE_SHIPPING_PERWEIGHTUNIT_HANDLING) ) ));


    if ($this->tax_class > 0) {
      $this->quotes['tax'] = zen_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
    }

    if (zen_not_null($this->icon)) $this->quotes['icon'] = zen_image($this->icon, $this->title);

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
      $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_PERWEIGHTUNIT_STATUS'");
      $this->_check = $check_query->RecordCount();
    }
    return $this->_check;
  }
  /**
   * Install the shipping module and its configuration settings
   *
   */
  function install() {
    global $db;
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Per Weight Unit Shipping', 'MODULE_SHIPPING_PERWEIGHTUNIT_STATUS', 'True', 'Do you want to offer per unit rate shipping?<br /><br />Product Quantity * Units (products_weight) * Cost per Unit', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Shipping Cost per Unit', 'MODULE_SHIPPING_PERWEIGHTUNIT_COST', '1', 'NOTE: When using this Shipping Module be sure to check the Tare settings in the Shipping/Packaging and set the Largest Weight high enough to handle the price, such as 5000.00 and the adjust the settings on Small and Large packages which will add to the price as well.<br /><br />The shipping cost will be used to determine shipping charges based on: Product Quantity * Units (products_weight) * Cost per Unit - in an order that uses this shipping method.', '6', '0', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Handling Fee', 'MODULE_SHIPPING_PERWEIGHTUNIT_HANDLING', '0', 'Handling fee for this shipping method.', '6', '0', now())");

    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Handling Per Order or Per Box', 'MODULE_SHIPPING_PERWEIGHTUNIT_HANDLING_METHOD', 'Order', 'Do you want to charge Handling Fee Per Order or Per Box?', '6', '0', 'zen_cfg_select_option(array(\'Order\', \'Box\'), ', now())");

    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Tax Class', 'MODULE_SHIPPING_PERWEIGHTUNIT_TAX_CLASS', '0', 'Use the following tax class on the shipping fee.', '6', '0', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Tax Basis', 'MODULE_SHIPPING_PERWEIGHTUNIT_TAX_BASIS', 'Shipping', 'On what basis is Shipping Tax calculated. Options are<br />Shipping - Based on customers Shipping Address<br />Billing Based on customers Billing address<br />Store - Based on Store address if Billing/Shipping Zone equals Store zone', '6', '0', 'zen_cfg_select_option(array(\'Shipping\', \'Billing\', \'Store\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Shipping Zone', 'MODULE_SHIPPING_PERWEIGHTUNIT_ZONE', '0', 'If a zone is selected, only enable this shipping method for that zone.', '6', '0', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_SHIPPING_PERWEIGHTUNIT_SORT_ORDER', '0', 'Sort order of display.', '6', '0', now())");
  }
  /**
   * Remove the module and all its settings
   *
   */
  function remove() {
    global $db;
    $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key LIKE  'MODULE\_SHIPPING\_PERWEIGHTUNIT\_%'");
  }
  /**
   * Internal list of configuration keys used for configuration of the module
   *
   * @return array
   */
  function keys() {
    return array('MODULE_SHIPPING_PERWEIGHTUNIT_STATUS', 'MODULE_SHIPPING_PERWEIGHTUNIT_COST', 'MODULE_SHIPPING_PERWEIGHTUNIT_HANDLING', 'MODULE_SHIPPING_PERWEIGHTUNIT_HANDLING_METHOD', 'MODULE_SHIPPING_PERWEIGHTUNIT_TAX_CLASS', 'MODULE_SHIPPING_PERWEIGHTUNIT_TAX_BASIS', 'MODULE_SHIPPING_PERWEIGHTUNIT_ZONE', 'MODULE_SHIPPING_PERWEIGHTUNIT_SORT_ORDER');
  }
}
