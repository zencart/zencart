<?php
/**
 * shipping class
 *
 * @package classes
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson  Wed Jul 24 17:24:08 2013 +0100 Modified in v1.5.2 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * shipping class
 * Class used for interfacing with shipping modules
 *
 * @package classes
 */
class shipping extends base {
  var $modules;

  // class constructor
  function shipping($module = '') {
    global $PHP_SELF, $messageStack;

    if (defined('MODULE_SHIPPING_INSTALLED') && zen_not_null(MODULE_SHIPPING_INSTALLED)) {
      $this->modules = explode(';', MODULE_SHIPPING_INSTALLED);

      $include_modules = array();

      if ( (zen_not_null($module)) && (in_array(substr($module['id'], 0, strpos($module['id'], '_')) . '.' . substr($PHP_SELF, (strrpos($PHP_SELF, '.')+1)), $this->modules)) ) {
        $include_modules[] = array('class' => substr($module['id'], 0, strpos($module['id'], '_')), 'file' => substr($module['id'], 0, strpos($module['id'], '_')) . '.' . substr($PHP_SELF, (strrpos($PHP_SELF, '.')+1)));
      } else {
        reset($this->modules);
        while (list(, $value) = each($this->modules)) {
          $class = substr($value, 0, strrpos($value, '.'));
          $include_modules[] = array('class' => $class, 'file' => $value);
        }
      }

      for ($i=0, $n=sizeof($include_modules); $i<$n; $i++) {
        //          include(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/shipping/' . $include_modules[$i]['file']);
        $lang_file = zen_get_file_directory(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/shipping/', $include_modules[$i]['file'], 'false');
        if (@file_exists($lang_file)) {
          include_once($lang_file);
        } else {
          if (IS_ADMIN_FLAG === false && is_object($messageStack)) {
            $messageStack->add('checkout_shipping', WARNING_COULD_NOT_LOCATE_LANG_FILE . $lang_file, 'caution');
          } else {
            $messageStack->add_session(WARNING_COULD_NOT_LOCATE_LANG_FILE . $lang_file, 'caution');
          }
        }
        $this->enabled = TRUE;
        $this->notify('NOTIFY_SHIPPING_MODULE_ENABLE', $include_modules[$i]['class']);
        if ($this->enabled)
        {
          include_once(DIR_WS_MODULES . 'shipping/' . $include_modules[$i]['file']);
          $GLOBALS[$include_modules[$i]['class']] = new $include_modules[$i]['class'];
        }
      }
    }
  }

  function calculate_boxes_weight_and_tare() {
    global $total_weight, $shipping_weight, $shipping_quoted, $shipping_num_boxes;

    $this->abort_legacy_calculations = FALSE;
    $this->notify('NOTIFY_SHIPPING_MODULE_PRE_CALCULATE_BOXES_AND_TARE');
    if ($this->abort_legacy_calculations) return;

    if (is_array($this->modules)) {
      $shipping_quoted = '';
      $shipping_num_boxes = 1;
      $shipping_weight = $total_weight;

      $za_tare_array = preg_split("/[:,]/" , SHIPPING_BOX_WEIGHT);
      $zc_tare_percent= $za_tare_array[0];
      $zc_tare_weight= $za_tare_array[1];

      $za_large_array = preg_split("/[:,]/" , SHIPPING_BOX_PADDING);
      $zc_large_percent= $za_large_array[0];
      $zc_large_weight= $za_large_array[1];

      // SHIPPING_BOX_WEIGHT = tare
      // SHIPPING_BOX_PADDING = Large Box % increase
      // SHIPPING_MAX_WEIGHT = Largest package

      /*
      if (SHIPPING_BOX_WEIGHT >= $shipping_weight*SHIPPING_BOX_PADDING/100) {
        $shipping_weight = $shipping_weight+SHIPPING_BOX_WEIGHT;
      } else {
        $shipping_weight = $shipping_weight + ($shipping_weight*SHIPPING_BOX_PADDING/100);
      }
      */

      switch (true) {
        // large box add padding
        case(SHIPPING_MAX_WEIGHT <= $shipping_weight):
          $shipping_weight = $shipping_weight + ($shipping_weight*($zc_large_percent/100)) + $zc_large_weight;
          break;
        default:
        // add tare weight < large
          $shipping_weight = $shipping_weight + ($shipping_weight*($zc_tare_percent/100)) + $zc_tare_weight;
          break;
      }

      if ($shipping_weight > SHIPPING_MAX_WEIGHT) { // Split into many boxes
//        $shipping_num_boxes = ceil($shipping_weight/SHIPPING_MAX_WEIGHT);
        $zc_boxes = zen_round(($shipping_weight/SHIPPING_MAX_WEIGHT), 2);
        $shipping_num_boxes = ceil($zc_boxes);
        $shipping_weight = $shipping_weight/$shipping_num_boxes;
      }
    }
    $this->notify('NOTIFY_SHIPPING_MODULE_CALCULATE_BOXES_AND_TARE');
  }

  function quote($method = '', $module = '', $calc_boxes_weight_tare = true) {
    $quotes_array = array();

    if ($calc_boxes_weight_tare) $this->calculate_boxes_weight_and_tare();

    if (is_array($this->modules)) {
      $include_quotes = array();

      reset($this->modules);
      while (list(, $value) = each($this->modules)) {
        $class = substr($value, 0, strrpos($value, '.'));
        if (zen_not_null($module)) {
          if ( ($module == $class) && (isset($GLOBALS[$class]) && $GLOBALS[$class]->enabled) ) {
            $include_quotes[] = $class;
          }
        } elseif (isset($GLOBALS[$class]) && $GLOBALS[$class]->enabled) {
          $include_quotes[] = $class;
        }
      }

      $size = sizeof($include_quotes);
      for ($i=0; $i<$size; $i++) {
        $quotes = $GLOBALS[$include_quotes[$i]]->quote($method);
        if (is_array($quotes)) $quotes_array[] = $quotes;
      }
    }
    $this->notify('NOTIFY_SHIPPING_MODULE_GET_ALL_QUOTES', $quotes_array);
    return $quotes_array;
  }

  function cheapest() {
    if (is_array($this->modules)) {
      $rates = array();

      reset($this->modules);
      while (list(, $value) = each($this->modules)) {
        $class = substr($value, 0, strrpos($value, '.'));
        if ($GLOBALS[$class]->enabled) {
          $quotes = $GLOBALS[$class]->quotes;
          $size = sizeof($quotes['methods']);
          for ($i=0; $i<$size; $i++) {
            //              if ($quotes['methods'][$i]['cost']) {
            if (isset($quotes['methods'][$i]['cost'])){
              $rates[] = array('id' => $quotes['id'] . '_' . $quotes['methods'][$i]['id'],
                               'title' => $quotes['module'] . ' (' . $quotes['methods'][$i]['title'] . ')',
                               'cost' => $quotes['methods'][$i]['cost'],
                               'module' => $quotes['id']
              );
            }
          }
        }
      }

      $cheapest = false;
      $size = sizeof($rates);
      for ($i=0; $i<$size; $i++) {
        if (is_array($cheapest)) {
          // never quote storepickup as lowest - needs to be configured in shipping module
          if ($rates[$i]['cost'] < $cheapest['cost'] and $rates[$i]['module'] != 'storepickup') {
            $cheapest = $rates[$i];
          }
        } else {
          if ($rates[$i]['module'] != 'storepickup') {
            $cheapest = $rates[$i];
          }
        }
      }
      $this->notify('NOTIFY_SHIPPING_MODULE_CALCULATE_CHEAPEST', $cheapest);
      return $cheapest;
    }
  }
}
