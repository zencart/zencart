<?php
/**
 * @package shippingMethod
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: freeoptions.php 14498 2009-10-01 20:16:16Z ajeh $
 */

class freeoptions extends base {
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
   * var for individual freeoptions choices
   */
  var $ck_freeoptions_total, $ck_freeoptions_weight, $ck_freeoptions_items;
  /**
   * constructor
   *
   * @return freeoptions
   */
  function __construct() {
      $this->code = 'freeoptions';
      $this->title = MODULE_SHIPPING_FREEOPTIONS_TEXT_TITLE;
      $this->description = MODULE_SHIPPING_FREEOPTIONS_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_SHIPPING_FREEOPTIONS_SORT_ORDER;
      $this->icon = '';
      $this->tax_class = MODULE_SHIPPING_FREEOPTIONS_TAX_CLASS;
      $this->tax_basis = MODULE_SHIPPING_FREEOPTIONS_TAX_BASIS;
      $this->enabled = (MODULE_SHIPPING_FREEOPTIONS_STATUS == 'True') ? true : false;
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
    if ($this->enabled == true && (int)MODULE_SHIPPING_FREEOPTIONS_ZONE > 0) {
      $check_flag = false;
      $check = $db->Execute("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . "
                             where geo_zone_id = '" . MODULE_SHIPPING_FREEOPTIONS_ZONE . "'
                             and zone_country_id = '" . $order->delivery['country']['id'] . "'
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
    $order_weight = round($_SESSION['cart']->show_weight(),9);

    // check if anything is configured for total, weight or item
    if ((MODULE_SHIPPING_FREEOPTIONS_TOTAL_MIN !='' or MODULE_SHIPPING_FREEOPTIONS_TOTAL_MAX !='')) {
      $this->ck_freeoptions_total = true;
    } else {
      $this->ck_freeoptions_total = false;
    }
    if ((MODULE_SHIPPING_FREEOPTIONS_WEIGHT_MIN !='' or MODULE_SHIPPING_FREEOPTIONS_WEIGHT_MAX !='')) {
      $this->ck_freeoptions_weight = true;
    } else {
      $this->ck_freeoptions_weight = false;
    }
    if ((MODULE_SHIPPING_FREEOPTIONS_ITEMS_MIN !='' or MODULE_SHIPPING_FREEOPTIONS_ITEMS_MAX !='')) {
      $this->ck_freeoptions_items = true;
    } else {
      $this->ck_freeoptions_items = false;
    }
    if ($this->ck_freeoptions_total or $this->ck_freeoptions_weight or $this->ck_freeoptions_items) {
      $this->enabled = true;
    } else {
      $this->enabled = false;
    }

    // disabled if nothing validates for total, weight or item
    if ($this->enabled) {
      if ($this->ck_freeoptions_total) {
        switch (true) {
        case ((MODULE_SHIPPING_FREEOPTIONS_TOTAL_MIN !='' and MODULE_SHIPPING_FREEOPTIONS_TOTAL_MAX !='')):
// free shipping total should not need adjusting
//            if (($_SESSION['cart']->show_total() - $_SESSION['cart']->free_shipping_prices()) >= MODULE_SHIPPING_FREEOPTIONS_TOTAL_MIN and ($_SESSION['cart']->show_total() - $_SESSION['cart']->free_shipping_prices()) <= MODULE_SHIPPING_FREEOPTIONS_TOTAL_MAX) {
          if (($_SESSION['cart']->show_total()) >= MODULE_SHIPPING_FREEOPTIONS_TOTAL_MIN and ($_SESSION['cart']->show_total()) <= MODULE_SHIPPING_FREEOPTIONS_TOTAL_MAX) {
            $this->ck_freeoptions_total = true;
          } else {
            $this->ck_freeoptions_total = false;
          }
          break;
        case ((MODULE_SHIPPING_FREEOPTIONS_TOTAL_MIN !='')):
//            if (($_SESSION['cart']->show_total() - $_SESSION['cart']->free_shipping_prices()) >= MODULE_SHIPPING_FREEOPTIONS_TOTAL_MIN) {
          if (($_SESSION['cart']->show_total()) >= MODULE_SHIPPING_FREEOPTIONS_TOTAL_MIN) {
            $this->ck_freeoptions_total = true;
          } else {
            $this->ck_freeoptions_total = false;
          }
          break;
        case ((MODULE_SHIPPING_FREEOPTIONS_TOTAL_MAX !='')):
//            if (($_SESSION['cart']->show_total() - $_SESSION['cart']->free_shipping_prices()) <= MODULE_SHIPPING_FREEOPTIONS_TOTAL_MAX) {
          if (($_SESSION['cart']->show_total()) <= MODULE_SHIPPING_FREEOPTIONS_TOTAL_MAX) {
            $this->ck_freeoptions_total = true;
          } else {
            $this->ck_freeoptions_total = false;
          }
          break;
        }
      }

      if ($this->ck_freeoptions_weight) {
        switch (true) {
        case ((MODULE_SHIPPING_FREEOPTIONS_WEIGHT_MIN !='' and MODULE_SHIPPING_FREEOPTIONS_WEIGHT_MAX !='')):
          if ($order_weight >= MODULE_SHIPPING_FREEOPTIONS_WEIGHT_MIN and $order_weight <= MODULE_SHIPPING_FREEOPTIONS_WEIGHT_MAX) {
            $this->ck_freeoptions_weight = true;
          } else {
            $this->ck_freeoptions_weight = false;
          }
          break;
        case ((MODULE_SHIPPING_FREEOPTIONS_WEIGHT_MIN !='')):
          if ($order_weight >= MODULE_SHIPPING_FREEOPTIONS_WEIGHT_MIN) {
            $this->ck_freeoptions_weight = true;
          } else {
            $this->ck_freeoptions_weight = false;
          }
          break;
        case ((MODULE_SHIPPING_FREEOPTIONS_WEIGHT_MAX !='')):
          if ($order_weight <= MODULE_SHIPPING_FREEOPTIONS_WEIGHT_MAX) {
            $this->ck_freeoptions_weight = true;
          } else {
            $this->ck_freeoptions_weight = false;
          }
          break;
        }
      }

      if ($this->ck_freeoptions_items) {
        switch (true) {
        case ((MODULE_SHIPPING_FREEOPTIONS_ITEMS_MIN !='' and MODULE_SHIPPING_FREEOPTIONS_ITEMS_MAX !='')):
// free shipping items should not need adjusting
//            if (($_SESSION['cart']->count_contents() - $_SESSION['cart']->free_shipping_items()) >= MODULE_SHIPPING_FREEOPTIONS_ITEMS_MIN and ($_SESSION['cart']->count_contents() - $_SESSION['cart']->free_shipping_items()) <= MODULE_SHIPPING_FREEOPTIONS_ITEMS_MAX) {
          if (($_SESSION['cart']->count_contents()) >= MODULE_SHIPPING_FREEOPTIONS_ITEMS_MIN and ($_SESSION['cart']->count_contents()) <= MODULE_SHIPPING_FREEOPTIONS_ITEMS_MAX) {
            $this->ck_freeoptions_items = true;
          } else {
            $this->ck_freeoptions_items = false;
          }
          break;
        case ((MODULE_SHIPPING_FREEOPTIONS_ITEMS_MIN !='')):
//            if (($_SESSION['cart']->count_contents() - $_SESSION['cart']->free_shipping_items()) >= MODULE_SHIPPING_FREEOPTIONS_ITEMS_MIN) {
          if (($_SESSION['cart']->count_contents()) >= MODULE_SHIPPING_FREEOPTIONS_ITEMS_MIN) {
            $this->ck_freeoptions_items = true;
          } else {
            $this->ck_freeoptions_items = false;
          }
          break;
        case ((MODULE_SHIPPING_FREEOPTIONS_ITEMS_MAX !='')):
//            if (($_SESSION['cart']->count_contents() - $_SESSION['cart']->free_shipping_items())<= MODULE_SHIPPING_FREEOPTIONS_ITEMS_MAX) {
          if (($_SESSION['cart']->count_contents())<= MODULE_SHIPPING_FREEOPTIONS_ITEMS_MAX) {
            $this->ck_freeoptions_items = true;
          } else {
            $this->ck_freeoptions_items = false;
          }
          break;
        }
      }
    }

/*
echo 'I see count: ' . $_SESSION['cart']->count_contents() . ' free count: ' . $_SESSION['cart']->free_shipping_items() . '<br>' .
'I see weight: ' . $_SESSION['cart']->show_weight() . '<br>' .
'I see total: ' . $_SESSION['cart']->show_total() . ' free price: ' . $_SESSION['cart']->free_shipping_prices() . '<br>' .
'Final check ' . ($this->ck_freeoptions_total ? 'T: YES ' : 'T: NO ') . ($this->ck_freeoptions_weight ? 'W: YES ' : 'W: NO ') . ($this->ck_freeoptions_items ? 'I: YES ' : 'I: NO ') . '<br>';
*/

// final check for display of Free Options
    if ($this->ck_freeoptions_total or $this->ck_freeoptions_weight or $this->ck_freeoptions_items) {
      $this->enabled = true;
    } else {
      $this->enabled = false;
    }

    if ($this->enabled) {
      $this->quotes = array('id' => $this->code,
                            'module' => MODULE_SHIPPING_FREEOPTIONS_TEXT_TITLE,
                            'methods' => array(array('id' => $this->code,
                                                     'title' => MODULE_SHIPPING_FREEOPTIONS_TEXT_WAY,
                                                     'cost'  => MODULE_SHIPPING_FREEOPTIONS_COST + MODULE_SHIPPING_FREEOPTIONS_HANDLING)));

      if ($this->tax_class > 0) {
        $this->quotes['tax'] = zen_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
      }

      if (zen_not_null($this->icon)) $this->quotes['icon'] = zen_image($this->icon, $this->title);
    }

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
      $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_FREEOPTIONS_STATUS'");
      $this->_check = $check_query->RecordCount();
    }
    return $this->_check;
  }

  /**
   * Install the shipping module and its configuration settings
   */
  function install() {
    global $db;
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Free Options Shipping', 'MODULE_SHIPPING_FREEOPTIONS_STATUS', 'True', 'Free Options is used to display a Free Shipping option when other Shipping Modules are displayed.
It can be based on: Always show, Order Total, Order Weight or Order Item Count.
The Free Options module does not show when Free Shipper is displayed.<br /><br />
Setting Total to >= 0.00 and <= nothing (leave blank) will activate this module to show with all shipping modules, except for Free Shipping - freeshipper.<br /><br />
NOTE: Leaving all settings for Total, Weight and Item count blank will deactivate this module.<br /><br />
NOTE: Free Shipping Options does not display if Free Shipping is used based on 0 weight is Free Shipping.
See: freeshipper<br /><br />Do you want to offer per freeoptions rate shipping?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Shipping Cost', 'MODULE_SHIPPING_FREEOPTIONS_COST', '0.00', 'The shipping cost will be $0.00', '6', '0', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Handling Fee', 'MODULE_SHIPPING_FREEOPTIONS_HANDLING', '0', 'Handling fee for this shipping method.', '6', '0', now())");

    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Total >=', 'MODULE_SHIPPING_FREEOPTIONS_TOTAL_MIN', '0.00', 'Free Shipping when Total >=', '6', '0', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Total <=', 'MODULE_SHIPPING_FREEOPTIONS_TOTAL_MAX', '', 'Free Shipping when Total <=', '6', '0', now())");

    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Weight >=', 'MODULE_SHIPPING_FREEOPTIONS_WEIGHT_MIN', '', 'Free Shipping when Weight >=', '6', '0', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Weight <=', 'MODULE_SHIPPING_FREEOPTIONS_WEIGHT_MAX', '', 'Free Shipping when Weight <=', '6', '0', now())");

    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Item Count >=', 'MODULE_SHIPPING_FREEOPTIONS_ITEMS_MIN', '', 'Free Shipping when Item Count >=', '6', '0', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Item Count <=', 'MODULE_SHIPPING_FREEOPTIONS_ITEMS_MAX', '', 'Free Shipping when Item Count <=', '6', '0', now())");

    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Tax Class', 'MODULE_SHIPPING_FREEOPTIONS_TAX_CLASS', '0', 'Use the following tax class on the shipping fee.', '6', '0', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Tax Basis', 'MODULE_SHIPPING_FREEOPTIONS_TAX_BASIS', 'Shipping', 'On what basis is Shipping Tax calculated. Options are<br />Shipping - Based on customers Shipping Address<br />Billing Based on customers Billing address<br />Store - Based on Store address if Billing/Shipping Zone equals Store zone', '6', '0', 'zen_cfg_select_option(array(\'Shipping\', \'Billing\', \'Store\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Shipping Zone', 'MODULE_SHIPPING_FREEOPTIONS_ZONE', '0', 'If a zone is selected, only enable this shipping method for that zone.', '6', '0', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_SHIPPING_FREEOPTIONS_SORT_ORDER', '0', 'Sort order of display.', '6', '0', now())");
  }

  /**
   * Remove the module and all its settings
   */
 function remove() {
   global $db;
   $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key LIKE  'MODULE\_SHIPPING\_FREEOPTIONS\_%'");
 }

  /**
   * Internal list of configuration keys used for configuration of the module
   *
   * @return array
   */
  function keys() {
    return array('MODULE_SHIPPING_FREEOPTIONS_STATUS', 'MODULE_SHIPPING_FREEOPTIONS_COST', 'MODULE_SHIPPING_FREEOPTIONS_HANDLING', 'MODULE_SHIPPING_FREEOPTIONS_TOTAL_MIN', 'MODULE_SHIPPING_FREEOPTIONS_TOTAL_MAX', 'MODULE_SHIPPING_FREEOPTIONS_WEIGHT_MIN', 'MODULE_SHIPPING_FREEOPTIONS_WEIGHT_MAX', 'MODULE_SHIPPING_FREEOPTIONS_ITEMS_MIN', 'MODULE_SHIPPING_FREEOPTIONS_ITEMS_MAX', 'MODULE_SHIPPING_FREEOPTIONS_TAX_CLASS', 'MODULE_SHIPPING_FREEOPTIONS_TAX_BASIS', 'MODULE_SHIPPING_FREEOPTIONS_ZONE', 'MODULE_SHIPPING_FREEOPTIONS_SORT_ORDER');
  }
}
