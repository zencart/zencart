<?php
/**
 * @package shippingMethod
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: table.php 14498 2009-10-01 20:16:16Z ajeh $
 */

class table extends base {
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
   * @return table
   */
  function __construct() {
    $this->code = 'table';
    $this->title = MODULE_SHIPPING_TABLE_TEXT_TITLE;
    $this->description = MODULE_SHIPPING_TABLE_TEXT_DESCRIPTION;
    $this->sort_order = MODULE_SHIPPING_TABLE_SORT_ORDER;
    $this->icon = '';
    $this->tax_class = MODULE_SHIPPING_TABLE_TAX_CLASS;
    $this->tax_basis = MODULE_SHIPPING_TABLE_TAX_BASIS;
    $this->enabled = (MODULE_SHIPPING_TABLE_STATUS == 'True') ? true : false;
    $this->update_status();
    $this->notify('MODULE_SHIPPING_' . strtoupper($this->code) . '_INSTANTIATED');
  }

  /**
   * Coders can add custom logic here in the update_status() method to allow for manipulating the $this->enabled status
   */
  function update_status() {
    global $order, $db;

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
  }

  /**
   * Sets $this->enabled based on zone restrictions applied to this module
   * @return boolean
   */
  function check_enabled_for_zone()
  {
    global $order, $db;
    if ($this->enabled == true && (int)MODULE_SHIPPING_TABLE_ZONE > 0) {
      $check_flag = false;
      $check = $db->Execute("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . "
                             where geo_zone_id = '" . MODULE_SHIPPING_TABLE_ZONE . "'
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
    global $order, $shipping_weight, $shipping_num_boxes, $total_count;

    // shipping adjustment
    switch (MODULE_SHIPPING_TABLE_MODE) {
      case ('price'):
        $order_total = $_SESSION['cart']->show_total() - $_SESSION['cart']->free_shipping_prices() ;
        break;
      case ('weight'):
        $order_total = $shipping_weight;
        break;
      case ('item'):
        $order_total = $total_count - $_SESSION['cart']->free_shipping_items();
        break;
    }

    $order_total_amount = $_SESSION['cart']->show_total() - $_SESSION['cart']->free_shipping_prices() ;

    $skip_shipping = false;
    $table_cost = preg_split("/[:,]/" , MODULE_SHIPPING_TABLE_COST);
    $size = sizeof($table_cost);
    for ($i=0, $n=$size; $i<$n; $i+=2) {
      if (round($order_total,9) <= $table_cost[$i]) {
        switch(true) {
          case (strstr($table_cost[$i+1], '%')):
            $shipping = ($table_cost[$i+1]/100) * $order_total_amount;
            break;
          case (strstr($table_cost[$i+1], '*')):
            if (MODULE_SHIPPING_TABLE_MODE == 'item') {
              $shipping = (($total_count - $_SESSION['cart']->free_shipping_items()) * $table_cost[$i+1]);
            } else {
              $skip_shipping = true;
            }
            break;
          case (strstr($table_cost[$i+1], 'OFF')):
            $skip_shipping = true;
            break;
          default:
            $shipping = $table_cost[$i+1];
            break;
        }
        break;
      } else {
        if (strstr($table_cost[$i+1], 'OFF')) {
          $skip_shipping = true;
          break;
        }
      }
    }

    if (MODULE_SHIPPING_TABLE_MODE == 'weight') {
      $shipping = $shipping * $shipping_num_boxes;
      // show boxes if weight
      switch (SHIPPING_BOX_WEIGHT_DISPLAY) {
        case (0):
        $show_box_weight = '';
        break;
        case (1):
        $show_box_weight = ' (' . $shipping_num_boxes . ' ' . TEXT_SHIPPING_BOXES . ')';
        break;
        case (2):
        $show_box_weight = ' (' . number_format($shipping_weight * $shipping_num_boxes,2) . TEXT_SHIPPING_WEIGHT . ')';
        break;
        default:
        $show_box_weight = ' (' . $shipping_num_boxes . ' x ' . number_format($shipping_weight,2) . TEXT_SHIPPING_WEIGHT . ')';
        break;
      }
    }

    if (!$skip_shipping) {
      $this->quotes = array('id' => $this->code,
      'module' => MODULE_SHIPPING_TABLE_TEXT_TITLE . $show_box_weight,
      'methods' => array(array('id' => $this->code,
      'title' => MODULE_SHIPPING_TABLE_TEXT_WAY,
      'cost' => $shipping + (MODULE_SHIPPING_TABLE_HANDLING_METHOD == 'Box' ? MODULE_SHIPPING_TABLE_HANDLING * $shipping_num_boxes : MODULE_SHIPPING_TABLE_HANDLING) ) ));
    } else {
      // skip shipping display
    }

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
      $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_TABLE_STATUS'");
      $this->_check = $check_query->RecordCount();
    }
    return $this->_check;
  }

  /**
   * Install the shipping module and its configuration settings
   */
  function install() {
    global $db;
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable Table Method', 'MODULE_SHIPPING_TABLE_STATUS', 'True', 'Do you want to offer table rate shipping?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Shipping Table', 'MODULE_SHIPPING_TABLE_COST', '3:8.50,25:5.50,10000:3.00', 'The shipping cost is based on the total cost or weight of items or count of the items quantity.<br />Example: 3:8.50,25:5.50,10000:3.00<br />Weight/Price/Item count less than or equal to 3&nbsp;would cost 8.50 4-25&nbsp;would cost 5.50 and 26+&nbsp;would cost 3.00 to ship.<br /><br />You can also use percentage amounts, such as 3:8.50,6:5%,9:7.50,12:6.25,15:4.5%,10000:3% to charge a percentage value of the Order Total<br /><br />To terminate quotes use OFF to no longer show this shipping module. To turn off quotes at 10 or more, use 3:8.50,7:10.50,9:15%,10:OFF<br /><br />On Item quotes, you can also use * to set a Rate per Item, such as 3:8.50,6:5%,9:1.50*,12:1.25*,10000:1.00*<br />This would charge for 7-9&nbsp;items as item count * 1.50 and 10-12&nbsp;items as item count * 1.25 and for 13+&nbsp;items as item count * 1.00<br /><br />NOTE: See additional information on Maximum weight and Tare Rate<br /><br />', '6', '0', 'zen_cfg_textarea(', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Table Method', 'MODULE_SHIPPING_TABLE_MODE', 'weight', 'The shipping cost is based on the order total or the total weight of the items ordered or the total number of items orderd.', '6', '0', 'zen_cfg_select_option(array(\'weight\', \'price\', \'item\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Handling Fee', 'MODULE_SHIPPING_TABLE_HANDLING', '0', 'Handling fee for this shipping method.', '6', '0', now())");

    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Handling Per Order or Per Box', 'MODULE_SHIPPING_TABLE_HANDLING_METHOD', 'Order', 'Do you want to charge Handling Fee Per Order or Per Box?', '6', '0', 'zen_cfg_select_option(array(\'Order\', \'Box\'), ', now())");

    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Tax Class', 'MODULE_SHIPPING_TABLE_TAX_CLASS', '0', 'Use the following tax class on the shipping fee.', '6', '0', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Tax Basis', 'MODULE_SHIPPING_TABLE_TAX_BASIS', 'Shipping', 'On what basis is Shipping Tax calculated. Options are<br />Shipping - Based on customers Shipping Address<br />Billing Based on customers Billing address<br />Store - Based on Store address if Billing/Shipping Zone equals Store zone', '6', '0', 'zen_cfg_select_option(array(\'Shipping\', \'Billing\', \'Store\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Shipping Zone', 'MODULE_SHIPPING_TABLE_ZONE', '0', 'If a zone is selected, only enable this shipping method for that zone.', '6', '0', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_SHIPPING_TABLE_SORT_ORDER', '0', 'Sort order of display.', '6', '0', now())");
  }

  /**
   * Remove the module and all its settings
   */
    function remove() {
      global $db;
      $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key like 'MODULE\_SHIPPING\_TABLE\_%'");
    }

  /**
   * Internal list of configuration keys used for configuration of the module
   *
   * @return array
   */
  function keys() {
    return array('MODULE_SHIPPING_TABLE_STATUS', 'MODULE_SHIPPING_TABLE_COST', 'MODULE_SHIPPING_TABLE_MODE', 'MODULE_SHIPPING_TABLE_HANDLING', 'MODULE_SHIPPING_TABLE_HANDLING_METHOD', 'MODULE_SHIPPING_TABLE_TAX_CLASS', 'MODULE_SHIPPING_TABLE_TAX_BASIS', 'MODULE_SHIPPING_TABLE_ZONE', 'MODULE_SHIPPING_TABLE_SORT_ORDER');
  }
}
