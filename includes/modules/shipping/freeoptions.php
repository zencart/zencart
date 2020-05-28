<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2020 Apr 09 Modified in v1.5.7 $
 */

  class freeoptions extends base {
    var $code, $title, $description, $icon, $enabled;
    var $ck_freeoptions_total, $ck_freeoptions_weight, $ck_freeoptions_items;

// class constructor
    function __construct() {
      global $order, $db;

      $this->code = 'freeoptions';
      $this->title = MODULE_SHIPPING_FREEOPTIONS_TEXT_TITLE;
      $this->description = MODULE_SHIPPING_FREEOPTIONS_TEXT_DESCRIPTION;
      $this->sort_order = defined('MODULE_SHIPPING_FREEOPTIONS_SORT_ORDER') ? MODULE_SHIPPING_FREEOPTIONS_SORT_ORDER : null;
      if (null === $this->sort_order) return false;

      $this->icon = '';
      $this->tax_class = MODULE_SHIPPING_FREEOPTIONS_TAX_CLASS;
      $this->tax_basis = MODULE_SHIPPING_FREEOPTIONS_TAX_BASIS;

      // disable only when entire cart is free shipping
      if (zen_get_shipping_enabled($this->code)) {
          $this->enabled = ((MODULE_SHIPPING_FREEOPTIONS_STATUS == 'True') ? true : false);
      }

      if ( ($this->enabled == true) && ((int)MODULE_SHIPPING_FREEOPTIONS_ZONE > 0) ) {
        $check_flag = false;
        $check = $db->Execute("SELECT zone_id FROM " . TABLE_ZONES_TO_GEO_ZONES . " WHERE geo_zone_id = '" . MODULE_SHIPPING_FREEOPTIONS_ZONE . "' AND zone_country_id = '" . $order->delivery['country']['id'] . "' ORDER BY zone_id");
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

// class methods
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
                                                       'cost'  => (float)MODULE_SHIPPING_FREEOPTIONS_COST + (float)MODULE_SHIPPING_FREEOPTIONS_HANDLING)));

        if ($this->tax_class > 0) {
          $this->quotes['tax'] = zen_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
        }

        if (zen_not_null($this->icon)) $this->quotes['icon'] = zen_image($this->icon, $this->title);
      }

      return $this->quotes;
    }

    function check() {
      global $db;
      if (!isset($this->_check)) {
        $check_query = $db->Execute("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_SHIPPING_FREEOPTIONS_STATUS'");
        $this->_check = $check_query->RecordCount();
      }
      return $this->_check;
    }

    function install() {
      global $db;
      $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable Free Options Shipping', 'MODULE_SHIPPING_FREEOPTIONS_STATUS', 'True', 'Free Options is used to display a Free Shipping option when other Shipping Modules are displayed.
It can be based on: Always show, Order Total, Order Weight or Order Item Count.
The Free Options module does not show when Free Shipper is displayed.<br /><br />
Setting Total to >= 0.00 and <= nothing (leave blank) will activate this module to show with all shipping modules, except for Free Shipping - freeshipper.<br /><br />
NOTE: Leaving all settings for Total, Weight and Item count blank will deactivate this module.<br /><br />
NOTE: Free Shipping Options does not display if Free Shipping is used based on 0 weight is Free Shipping.
See: freeshipper<br /><br />Do you want to offer per freeoptions rate shipping?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
      $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Shipping Cost', 'MODULE_SHIPPING_FREEOPTIONS_COST', '0.00', 'The shipping cost will be $0.00', '6', '0', now())");
      $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Handling Fee', 'MODULE_SHIPPING_FREEOPTIONS_HANDLING', '0', 'Handling fee for this shipping method.', '6', '0', now())");

      $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Total >=', 'MODULE_SHIPPING_FREEOPTIONS_TOTAL_MIN', '0.00', 'Free Shipping when Total >=', '6', '0', now())");
      $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Total <=', 'MODULE_SHIPPING_FREEOPTIONS_TOTAL_MAX', '', 'Free Shipping when Total <=', '6', '0', now())");

      $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Weight >=', 'MODULE_SHIPPING_FREEOPTIONS_WEIGHT_MIN', '', 'Free Shipping when Weight >=', '6', '0', now())");
      $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Weight <=', 'MODULE_SHIPPING_FREEOPTIONS_WEIGHT_MAX', '', 'Free Shipping when Weight <=', '6', '0', now())");

      $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Item Count >=', 'MODULE_SHIPPING_FREEOPTIONS_ITEMS_MIN', '', 'Free Shipping when Item Count >=', '6', '0', now())");
      $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Item Count <=', 'MODULE_SHIPPING_FREEOPTIONS_ITEMS_MAX', '', 'Free Shipping when Item Count <=', '6', '0', now())");

      $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Tax Class', 'MODULE_SHIPPING_FREEOPTIONS_TAX_CLASS', '0', 'Use the following tax class on the shipping fee.', '6', '0', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
      $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Tax Basis', 'MODULE_SHIPPING_FREEOPTIONS_TAX_BASIS', 'Shipping', 'On what basis is Shipping Tax calculated. Options are<br />Shipping - Based on customers Shipping Address<br />Billing Based on customers Billing address<br />Store - Based on Store address if Billing/Shipping Zone equals Store zone', '6', '0', 'zen_cfg_select_option(array(\'Shipping\', \'Billing\', \'Store\'), ', now())");
      $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Shipping Zone', 'MODULE_SHIPPING_FREEOPTIONS_ZONE', '0', 'If a zone is selected, only enable this shipping method for that zone.', '6', '0', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
      $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Sort Order', 'MODULE_SHIPPING_FREEOPTIONS_SORT_ORDER', '0', 'Sort order of display.', '6', '0', now())");
    }

   function remove() {
     global $db;
     $db->Execute("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE  'MODULE\_SHIPPING\_FREEOPTIONS\_%'");
   }

    function keys() {
      return array('MODULE_SHIPPING_FREEOPTIONS_STATUS', 'MODULE_SHIPPING_FREEOPTIONS_COST', 'MODULE_SHIPPING_FREEOPTIONS_HANDLING', 'MODULE_SHIPPING_FREEOPTIONS_TOTAL_MIN', 'MODULE_SHIPPING_FREEOPTIONS_TOTAL_MAX', 'MODULE_SHIPPING_FREEOPTIONS_WEIGHT_MIN', 'MODULE_SHIPPING_FREEOPTIONS_WEIGHT_MAX', 'MODULE_SHIPPING_FREEOPTIONS_ITEMS_MIN', 'MODULE_SHIPPING_FREEOPTIONS_ITEMS_MAX', 'MODULE_SHIPPING_FREEOPTIONS_TAX_CLASS', 'MODULE_SHIPPING_FREEOPTIONS_TAX_BASIS', 'MODULE_SHIPPING_FREEOPTIONS_ZONE', 'MODULE_SHIPPING_FREEOPTIONS_SORT_ORDER');
    }
  }
