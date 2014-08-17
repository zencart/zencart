<?php
/**
 * @package shippingMethod
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: zones.php 14498 2009-10-01 20:16:16Z ajeh $
 */
/*

  USAGE
  By default, the module comes with support for 3 zones.  This can be
  easily changed by editing the line below in the zones constructor
  that defines $this->num_zones.

  Next, you will want to activate the module by going to the Admin screen,
  clicking on Modules, then clicking on Shipping.  A list of all shipping
  modules should appear.  Click on the green dot next to the one labeled
  zones.php.  A list of settings will appear to the right.  Click on the
  Edit button.

  PLEASE NOTE THAT YOU WILL LOSE YOUR CURRENT SHIPPING RATES AND OTHER
  SETTINGS IF YOU TURN OFF THIS SHIPPING METHOD.  Make sure you keep a
  backup of your shipping settings somewhere at all times.

  If you want an additional handling charge applied to orders that use this
  method, set the Handling Fee field.

  Next, you will need to define which countries are in each zone.  Determining
  this might take some time and effort.  You should group a set of countries
  that has similar shipping charges for the same weight.  For instance, when
  shipping from the US, the countries of Japan, Australia, New Zealand, and
  Singapore have similar shipping rates.  As an example, one of my customers
  is using this set of zones:
    1: USA
    2: Canada
    3: Austria, Belgium, Great Britain, France, Germany, Greenland, Iceland,
       Ireland, Italy, Norway, Holland/Netherlands, Denmark, Poland, Spain,
       Sweden, Switzerland, Finland, Portugal, Israel, Greece
    4: Japan, Australia, New Zealand, Singapore
    5: Taiwan, China, Hong Kong

  When you enter these country lists, enter them into the Zone X Countries
  fields, where "X" is the number of the zone.  They should be entered as
  two character ISO country codes in all capital letters.  They should be
  separated by commas with no spaces or other punctuation. For example:
    1: US
    2: CA
    3: AT,BE,GB,FR,DE,GL,IS,IE,IT,NO,NL,DK,PL,ES,SE,CH,FI,PT,IL,GR
    4: JP,AU,NZ,SG
    5: TW,CN,HK

  Now you need to set up the shipping rate tables for each zone.  Again,
  some time and effort will go into setting the appropriate rates.  You
  will define a set of weight ranges and the shipping price for each
  range.  For instance, you might want an order that weighs more than 0
  and less than or equal to 3 to cost 5.50 to ship to a certain zone.
  This would be defined by this:  3:5.5

  You should combine a bunch of these rates together in a comma delimited
  list and enter them into the "Zone X Shipping Table" fields where "X"
  is the zone number.  For example, this might be used for Zone 1:
    1:3.5,2:3.95,3:5.2,4:6.45,5:7.7,6:10.4,7:11.85, 8:13.3,9:14.75,10:16.2,11:17.65,
    12:19.1,13:20.55,14:22,15:23.45

  The above example includes weights over 0 and up to 15.  Note that
  units are not specified in this explanation since they should be
  specific to your locale.

  CAVEATS
  At this time, it does not deal with weights that are above the highest amount
  defined.  This will probably be the next area to be improved with the
  module.  For now, you could have one last very high range with a very
  high shipping rate to discourage orders of that magnitude.  For
  instance:  999:1000

  Or, you can use OFF to turn OFF this shipping module when it reaches 16:
    1:3.5,2:3.95,3:5.2,4:6.45,5:7.7,6:10.4,7:11.85, 8:13.3,9:14.75,10:16.2,11:17.65,
    12:19.1,13:20.55,14:22,15:23.45,16:OFF

  If you want to be able to ship to any country in the world, you will
  need to enter every country code into the Country fields. For most
  shops, you will not want to enter every country.  This is often
  because of too much fraud from certain places. If a country is not
  listed, then the module will add a $0.00 shipping charge and will
  indicate that shipping is not available to that destination.
  PLEASE NOTE THAT THE ORDER CAN STILL BE COMPLETED AND PROCESSED!

  Or, you can add a Zone defined as: 00 to indicate ALL countries not otherwise previously defined. Be
  sure to use the definition for: 00 as the LAST Zone definition.

  To prevent this module from working for some Countries, you can use the Skip Countries. Skip Countries,
  use a comma separated list of the two character ISO country codes.

  Or, to prevent this module from working based on a Zone Definition, you can enter the value of the zone id for the Zone Definition to be skipped.

  It appears that the osC shipping system automatically rounds the
  shipping weight up to the nearest whole unit.  This makes it more
  difficult to design precise shipping tables.  If you want to, you
  can hack the shipping.php file to get rid of the rounding.

*/


class zones extends base {
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
   * additional var for zone rate
   */
  var $num_zones, $skip_shipping_zone;

  /**
   * constructor
   *
   * @return zones
   */
  function __construct() {
    $this->code = 'zones';
    $this->title = MODULE_SHIPPING_ZONES_TEXT_TITLE;
    $this->description = MODULE_SHIPPING_ZONES_TEXT_DESCRIPTION;
    $this->sort_order = MODULE_SHIPPING_ZONES_SORT_ORDER;
    $this->icon = '';
    $this->tax_class = MODULE_SHIPPING_ZONES_TAX_CLASS;
    $this->tax_basis = MODULE_SHIPPING_ZONES_TAX_BASIS;
    $this->enabled = (MODULE_SHIPPING_ZONES_STATUS == 'True') ? true : false;

    /*
    ** CUSTOMIZE SETTING FOR THE NUMBER OF ZONES NEEDED **
    If Zone Rates module is currently installed ...
      1. Change number below for the number of Zones for this->num_zones
      2. Upload changed zones.php
      3. Go to Modules ... Shipping ...
      4. Go to Modules ... Shipping ... a second time to clear any error messages
      5. Click on Zone Rates and Edit and Configure new Zones

    If Zone Rates module is not currently installed
      1. Change number below for the number of Zones for this->num_zones
      2. Upload changed zones.php
      3. Go to Modules ... Shipping ...
      4. Click on Zone Rates and Click Install
      5. Edit and Configure new Zones

    */
    $this->num_zones = 3;

    if (IS_ADMIN_FLAG === true) {
      // build in admin only additional zones if missing in the configuration table due to customization of default $this->num_zones = 3
      global $db;
      for ($i = 1; $i <= $this->num_zones; $i++) {
        $check = $db->Execute("select * from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_ZONES_COUNTRIES_" . $i . "'");
        if ($this->enabled && $check->EOF) {
          $default_countries = '';
          $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Zone " . $i ." Countries', 'MODULE_SHIPPING_ZONES_COUNTRIES_" . $i ."', '" . $default_countries . "', 'Comma separated list of two character ISO country codes that are part of Zone " . $i . ".<br />Set as 00 to indicate all two character ISO country codes that are not specifically defined.', '6', '0', 'zen_cfg_textarea(', now())");
          $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Zone " . $i ." Shipping Table', 'MODULE_SHIPPING_ZONES_COST_" . $i ."', '3:8.50,25:5.50,10000:3.00', 'Shipping rates to Zone " . $i . " destinations based on a group of maximum order weights/prices.<br />Example: 3:8.50,25:5.50,10000:3.00<br />Weight/Price/Item count less than or equal to 3&nbsp;would cost 8.50 4-25&nbsp;would cost 5.50 and 26+&nbsp;would cost 3.00 to ship for Zone " . $i . " destinations.<br /><br />You can also use percentage amounts, such as 3:8.50,6:5%,9:7.50,12:6.25,15:4.5%,10000:3% to charge a percentage value of the Order Total<br /><br />To terminate quotes use OFF to no longer show this shipping module. To turn off quotes at 10 or more, use 3:8.50,7:10.50,9:15%,10:OFF<br /><br />On Item quotes, you can also use * to set a Rate per Item, such as 3:8.50,6:5%,9:1.50*,12:1.25*,10000:1.00*<br />This would charge for 7-9&nbsp;items as item count * 1.50 and 10-12&nbsp;items as item count * 1.25 and for 13+&nbsp;items as item count * 1.00<br /><br />NOTE: See additional information on Maximum weight and Tare Rate<br /><br />', '6', '0', 'zen_cfg_textarea(', now())");

          $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Zone " . $i ." Handling Fee', 'MODULE_SHIPPING_ZONES_HANDLING_" . $i."', '0', 'Handling Fee for this shipping zone', '6', '0', now())");
          $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Handling Per Order or Per Box Zone " . $i . "  (when by weight)' , 'MODULE_SHIPPING_ZONES_HANDLING_METHOD_" . $i."', 'Order', 'Do you want to charge Handling Fee Per Order or Per Box?', '6', '0', 'zen_cfg_select_option(array(\'Order\', \'Box\'), ', now())");
        }
      }
    } // build in admin only

    // skip countries in defined Zone Definition
    if (!IS_ADMIN_FLAG && $this->enabled) {
      $this->skip_shipping_zone = $this->chk_skip_shipping();
      $this->enabled = !$this->skip_shipping_zone;
    }

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
//echo 'ZONES function ' . __FUNCTION__ . ' $this->enabled: ' . ($this->enabled ? ' ON' : ' OFF') . ' $shipping_weight: ' . $shipping_weight . '<br>';
  }

  /**
   * Sets $this->enabled based on zone restrictions applied to this module
   * @return boolean
   */
  function check_enabled_for_zone()
  {
  // NOT USED IN ZONE RATES
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
    $dest_country = $order->delivery['country']['iso_code_2'];
    $dest_zone = 0;
    $error = false;

    // works on adjusted weight, total and count in cart
    $order_total_amount = $_SESSION['cart']->show_total() - $_SESSION['cart']->free_shipping_prices();
    $order_total_item = $total_count - $_SESSION['cart']->free_shipping_items();
    $order_total_weight = $shipping_weight;

//@@TODO - add new NOTIFIER

    for ($i=1; $i<=$this->num_zones; $i++) {
      $countries_table = constant('MODULE_SHIPPING_ZONES_COUNTRIES_' . $i);
      $countries_table = strtoupper(str_replace(' ', '', $countries_table));
      $country_zones = preg_split("/[,]/", $countries_table);
      if (in_array($dest_country, $country_zones)) {
        $dest_zone = $i;
        break;
      }
      if (in_array('00', $country_zones)) {
        $dest_zone = $i;
        break;
      }
    }

    if ($dest_zone == 0) {
      $error = true;
    } else {
      $shipping = -1;
      $zones_cost = constant('MODULE_SHIPPING_ZONES_COST_' . $dest_zone);

      $zones_table = preg_split("/[:,]/" , str_replace(' ', '', $zones_cost));
      $size = sizeof($zones_table);
      $done = false;
//echo '<br>START ZONE RATE: ' . $zones_cost . '<br>';
      for ($i=0; $i<$size; $i+=2) {
        switch (MODULE_SHIPPING_ZONES_METHOD) {
          case (MODULE_SHIPPING_ZONES_METHOD == 'Weight'):
            if (round($order_total_weight,9) <= $zones_table[$i]) {
              $shipping = $zones_table[$i+1];

              switch (SHIPPING_BOX_WEIGHT_DISPLAY) {
              case (0):
                $show_box_weight = '';
                break;
              case (1):
                $show_box_weight = ' (' . $shipping_num_boxes . ' ' . TEXT_SHIPPING_BOXES . ')';
                break;
              case (2):
                $show_box_weight = ' (' . number_format($order_total_weight * $shipping_num_boxes,2) . MODULE_SHIPPING_ZONES_TEXT_UNITS . ')';
                break;
              default:
                $show_box_weight = ' (' . $shipping_num_boxes . ' x ' . number_format($order_total_weight,2) . MODULE_SHIPPING_ZONES_TEXT_UNITS . ')';
                break;
              }

              $shipping_method = MODULE_SHIPPING_ZONES_TEXT_WAY . ' ' . $dest_country . $show_box_weight;
              $done = true;
              switch (true) {
                case (strstr($zones_table[$i+1], '%')):
                  $shipping = ($zones_table[$i+1]/100) * $order_total_amount;
                  break;
                case (strstr($zones_table[$i+1], '*')):
                  if (MODULE_SHIPPING_ZONES_METHOD == 'Item') {
                    $shipping = ($order_total_item * $zones_table[$i+1]);
                  } else {
                    $skip_shipping = true;
                  }
                  break;
                case (strstr($zones_table[$i+1], 'OFF')):
                  $skip_shipping = true;
                  break;
                default:
                  $shipping = $zones_table[$i+1];
                  break;
              }
              break;
            } else {
              if (strstr($zones_table[$i+1], 'OFF')) {
                $skip_shipping = true;
                break;
              }
            }
            break;
          case (MODULE_SHIPPING_ZONES_METHOD == 'Price'):
// shipping adjustment
            if ($order_total_amount <= $zones_table[$i]) {
              $shipping = $zones_table[$i+1];
              $shipping_method = MODULE_SHIPPING_ZONES_TEXT_WAY . ' ' . $dest_country;

              $done = true;
              switch (true) {
                case (strstr($zones_table[$i+1], '%')):
                  $shipping = ($zones_table[$i+1]/100) * $order_total_amount;
                  break;
                case (strstr($zones_table[$i+1], '*')):
                  if (MODULE_SHIPPING_ZONES_METHOD == 'Item') {
                    $shipping = ($order_total_item * $zones_table[$i+1]);
                  } else {
                    $skip_shipping = true;
                  }
                  break;
                case (strstr($zones_table[$i+1], 'OFF')):
                  $skip_shipping = true;
                  break;
                default:
                  $shipping = $zones_table[$i+1];
                  break;
              }
              break;
            } else {
              if (strstr($zones_table[$i+1], 'OFF')) {
                $skip_shipping = true;
                break;
              }
            }
            break;
          case (MODULE_SHIPPING_ZONES_METHOD == 'Item'):
// shipping adjustment
            if ($order_total_item <= $zones_table[$i]) {
              $shipping = $zones_table[$i+1];
              $shipping_method = MODULE_SHIPPING_ZONES_TEXT_WAY . ' ' . $dest_country;
              $done = true;
              switch (true) {
                case (strstr($zones_table[$i+1], '%')):
                  $shipping = ($zones_table[$i+1]/100) * $order_total_amount;
                  break;
                case (strstr($zones_table[$i+1], '*')):
                  if (MODULE_SHIPPING_ZONES_METHOD == 'Item') {
                    $shipping = ($order_total_item * $zones_table[$i+1]);
                  } else {
                    $skip_shipping = true;
                  }
                  break;
                case (strstr($zones_table[$i+1], 'OFF')):
                  $skip_shipping = true;
                  break;
                default:
                  $shipping = $zones_table[$i+1];
                  break;
              }
              break;
            } else {
              if (strstr($zones_table[$i+1], 'OFF')) {
                $skip_shipping = true;
                break;
              }
            }
          break;
        }
        if ($done == true) {
          break;
        }
      }

      if ($shipping == -1 || $skip_shipping) {
        $shipping_cost = 0;
        $shipping_method = MODULE_SHIPPING_ZONES_UNDEFINED_RATE;
      } else {
        switch (MODULE_SHIPPING_ZONES_METHOD) {
          case (MODULE_SHIPPING_ZONES_METHOD == 'Weight'):
            // charge per box when done by Weight
            // Handling fee per box or order
            if (constant('MODULE_SHIPPING_ZONES_HANDLING_METHOD_' . $dest_zone) == 'Box') {
              $shipping_cost = ($shipping * $shipping_num_boxes) + constant('MODULE_SHIPPING_ZONES_HANDLING_' . $dest_zone) * $shipping_num_boxes;
            } else {
              $shipping_cost = ($shipping * $shipping_num_boxes) + constant('MODULE_SHIPPING_ZONES_HANDLING_' . $dest_zone);
            }
            break;
          case (MODULE_SHIPPING_ZONES_METHOD == 'Price'):
            // don't charge per box when done by Price
            $shipping_cost = ($shipping) + constant('MODULE_SHIPPING_ZONES_HANDLING_' . $dest_zone);
            break;
          case (MODULE_SHIPPING_ZONES_METHOD == 'Item'):
            // don't charge per box when done by Item
            $shipping_cost = ($shipping) + constant('MODULE_SHIPPING_ZONES_HANDLING_' . $dest_zone);
            break;
        }
      }
    }

    // calculate final shipping cost
    $final_shipping_cost = $shipping_cost;

    if (!$skip_shipping) {
      $this->quotes = array('id' => $this->code,
                            'module' => MODULE_SHIPPING_ZONES_TEXT_TITLE,
                            'methods' => array(array('id' => $this->code,
                                                     'title' => $shipping_method,
                                                     'cost' => $final_shipping_cost)));
    } else {
      // skip display of shipping
    }
    if ($this->tax_class > 0) {
      $this->quotes['tax'] = zen_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
    }

    if (zen_not_null($this->icon)) $this->quotes['icon'] = zen_image($this->icon, $this->title);

    if (strstr(MODULE_SHIPPING_ZONES_SKIPPED, $dest_country)) {
      // don't show anything for this country
      $this->quotes = array();
    } else {
      if ($error == true) $this->quotes['error'] = MODULE_SHIPPING_ZONES_INVALID_ZONE;
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
      $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_ZONES_STATUS'");
      $this->_check = $check_query->RecordCount();
    }
    return $this->_check;
  }

  /**
   * Install the shipping module and its configuration settings
   */
  function install() {
    global $db;
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable Zones Method', 'MODULE_SHIPPING_ZONES_STATUS', 'True', 'Do you want to offer zone rate shipping?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Calculation Method', 'MODULE_SHIPPING_ZONES_METHOD', 'Weight', 'Calculate cost based on Weight, Price or Item?', '6', '0', 'zen_cfg_select_option(array(\'Weight\', \'Price\', \'Item\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Tax Class', 'MODULE_SHIPPING_ZONES_TAX_CLASS', '0', 'Use the following tax class on the shipping fee.', '6', '0', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Tax Basis', 'MODULE_SHIPPING_ZONES_TAX_BASIS', 'Shipping', 'On what basis is Shipping Tax calculated. Options are<br />Shipping - Based on customers Shipping Address<br />Billing Based on customers Billing address<br />Store - Based on Store address if Billing/Shipping Zone equals Store zone', '6', '0', 'zen_cfg_select_option(array(\'Shipping\', \'Billing\', \'Store\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_SHIPPING_ZONES_SORT_ORDER', '0', 'Sort order of display.', '6', '0', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Skip Countries, use a comma separated list of the two character ISO country codes<br />Or, build a Zone Definition for Countries and Zones to be skipped and enter its id. ', 'MODULE_SHIPPING_ZONES_SKIPPED', '', 'Disable for the following Countries:', '6', '0', 'zen_cfg_textarea(', now())");

    for ($i = 1; $i <= $this->num_zones; $i++) {
      $default_countries = '';
      if ($i == 1) {
        $default_countries = 'US,CA';
      }
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Zone " . $i ." Countries', 'MODULE_SHIPPING_ZONES_COUNTRIES_" . $i ."', '" . $default_countries . "', 'Comma separated list of two character ISO country codes that are part of Zone " . $i . ".<br />Set as 00 to indicate all two character ISO country codes that are not specifically defined.', '6', '0', 'zen_cfg_textarea(', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Zone " . $i ." Shipping Table', 'MODULE_SHIPPING_ZONES_COST_" . $i ."', '3:8.50,25:5.50,10000:3.00', 'Shipping rates to Zone " . $i . " destinations based on a group of maximum order weights/prices.<br />Example: 3:8.50,25:5.50,10000:3.00<br />Weight/Price/Item count less than or equal to 3&nbsp;would cost 8.50 4-25&nbsp;would cost 5.50 and 26+&nbsp;would cost 3.00 to ship for Zone " . $i . " destinations.<br /><br />You can also use percentage amounts, such as 3:8.50,6:5%,9:7.50,12:6.25,15:4.5%,10000:3% to charge a percentage value of the Order Total<br /><br />To terminate quotes use OFF to no longer show this shipping module. To turn off quotes at 10 or more, use 3:8.50,7:10.50,9:15%,10:OFF<br /><br />On Item quotes, you can also use * to set a Rate per Item, such as 3:8.50,6:5%,9:1.50*,12:1.25*,10000:1.00*<br />This would charge for 7-9&nbsp;items as item count * 1.50 and 10-12&nbsp;items as item count * 1.25 and for 13+&nbsp;items as item count * 1.00<br /><br />NOTE: See additional information on Maximum weight and Tare Rate<br /><br />', '6', '0', 'zen_cfg_textarea(', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Zone " . $i ." Handling Fee', 'MODULE_SHIPPING_ZONES_HANDLING_" . $i."', '0', 'Handling Fee for this shipping zone', '6', '0', now())");

      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Handling Per Order or Per Box Zone " . $i . " (when by weight)' , 'MODULE_SHIPPING_ZONES_HANDLING_METHOD_" . $i."', 'Order', 'Do you want to charge Handling Fee Per Order or Per Box?', '6', '0', 'zen_cfg_select_option(array(\'Order\', \'Box\'), ', now())");

    }
  }

  /**
   * Remove the module and all its settings
   */
  function remove() {
    global $db;
    $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key like 'MODULE\_SHIPPING\_ZONES\_%'");
  }

  /**
   * Internal list of configuration keys used for configuration of the module
   *
   * @return array
   */
  function keys() {
    $keys = array('MODULE_SHIPPING_ZONES_STATUS', 'MODULE_SHIPPING_ZONES_METHOD', 'MODULE_SHIPPING_ZONES_TAX_CLASS', 'MODULE_SHIPPING_ZONES_TAX_BASIS', 'MODULE_SHIPPING_ZONES_SORT_ORDER', 'MODULE_SHIPPING_ZONES_SKIPPED');

    for ($i=1; $i<=$this->num_zones; $i++) {
      $keys[] = 'MODULE_SHIPPING_ZONES_COUNTRIES_' . $i;
      $keys[] = 'MODULE_SHIPPING_ZONES_COST_' . $i;
      $keys[] = 'MODULE_SHIPPING_ZONES_HANDLING_' . $i;
      $keys[] = 'MODULE_SHIPPING_ZONES_HANDLING_METHOD_' . $i;
    }

    return $keys;
  }

// skip zone from using shipping
  function chk_skip_shipping() {
    global $db, $order;

    if ((int)MODULE_SHIPPING_ZONES_SKIPPED > 0 ) {
      $check_flag = false;
      $check = $db->Execute("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_ZONES_SKIPPED . "' and zone_country_id = '" . (int)$order->delivery['country']['id'] . "' order by zone_id");
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

      return $check_flag;
    } else {
      return false;
    }
  }
}
