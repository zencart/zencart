<?php
/**
 * Payment Class.
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: mc12345678 2020 Jul 20 Modified in v1.5.7a $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * Payment Class.
 * This class interfaces with payment modules
 *
 */
class payment extends base {
  var $modules, $selected_module, $doesCollectsCardDataOnsite;

  function __construct($module = '') {
      global $PHP_SELF, $language, $credit_covers, $messageStack;
      $this->doesCollectsCardDataOnsite = false;

      if (defined('MODULE_PAYMENT_INSTALLED') && !empty(MODULE_PAYMENT_INSTALLED)) {
        $this->modules = explode(';', MODULE_PAYMENT_INSTALLED);
      }
      $this->notify('NOTIFY_PAYMENT_CLASS_GET_INSTALLED_MODULES', $module);

      if (empty($this->modules)) return;

      $include_modules = array();

      if ( (zen_not_null($module)) && (in_array($module . '.' . substr($PHP_SELF, (strrpos($PHP_SELF, '.')+1)), $this->modules)) ) {
        $this->selected_module = $module;

        $include_modules[] = array('class' => $module, 'file' => $module . '.php');
      } else {

        // Free Payment Only shows
        $freecharger_enabled = (defined('MODULE_PAYMENT_FREECHARGER_STATUS') && MODULE_PAYMENT_FREECHARGER_STATUS == 'True');
        if ($freecharger_enabled && $_SESSION['cart']->show_total() == 0 && (!isset($_SESSION['shipping']['cost']) || $_SESSION['shipping']['cost'] == 0)) {
          $this->selected_module = $module;
          if (file_exists(DIR_FS_CATALOG . DIR_WS_MODULES . '/payment/' . 'freecharger.php')) {
            $include_modules[] = array('class'=> 'freecharger', 'file' => 'freecharger.php');
          }
        } else {
          // All Other Payment Modules show
          foreach($this->modules as $value) {
            // double check that the module really exists before adding to the array
            if (file_exists(DIR_FS_CATALOG . DIR_WS_MODULES . '/payment/' . $value)) {
              $class = substr($value, 0, strrpos($value, '.'));
              // Don't show Free Payment Module
              if ($class !='freecharger') {
                $include_modules[] = array('class' => $class, 'file' => $value);
              }
            }
          }
        }
      }

      for ($i=0, $n=sizeof($include_modules); $i<$n; $i++) {
        $lang_file = zen_get_file_directory(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/payment/', $include_modules[$i]['file'], 'false');
        if (@file_exists($lang_file)) {
          include_once($lang_file);
        } else {
          if (is_object($messageStack)) {
            if (IS_ADMIN_FLAG === false) {
              $messageStack->add('checkout_payment', WARNING_COULD_NOT_LOCATE_LANG_FILE . $lang_file, 'caution');
            } else {
              $messageStack->add_session(WARNING_COULD_NOT_LOCATE_LANG_FILE . $lang_file, 'caution');
            }
          }
          continue;
        }
        include_once(DIR_WS_MODULES . 'payment/' . $include_modules[$i]['file']);

        $this->paymentClass = new $include_modules[$i]['class'];
        $this->notify('NOTIFY_PAYMENT_MODULE_ENABLE');
        if ($this->paymentClass->enabled)
        {
          $GLOBALS[$include_modules[$i]['class']] = $this->paymentClass;
          if (isset($this->paymentClass->collectsCardDataOnsite) && $this->paymentClass->collectsCardDataOnsite == true) {
            $this->doesCollectsCardDataOnsite = true;
          }
        } else {
          unset($include_modules[$i]);
        }
      }
      $include_modules = array_values($include_modules);
      // if there is only one payment method, select it as default because in
      // checkout_confirmation.php the $payment variable is being assigned the
      // $_POST['payment'] value which will be empty (no radio button selection possible)
      if ( (zen_count_payment_modules() == 1) && (!isset($_SESSION['payment']) || (isset($_SESSION['payment']) && !is_object($_SESSION['payment']))) ) {
        if (!isset($credit_covers) || $credit_covers == FALSE) $_SESSION['payment'] = $include_modules[0]['class'];
      }

      if (zen_not_null($module) && in_array($module, $this->modules) && isset($GLOBALS[$module]->form_action_url)) {
        $this->form_action_url = $GLOBALS[$module]->form_action_url;
      }
  }

  /**
  The update_status() method is needed in the checkout_confirmation.php page
  due to a chicken and egg problem with the payment class and order class.
  The payment modules need the order destination data for the dynamic status
  feature, and the order class needs the payment module title.
  Modules should implement this method to inspect the current order address
  for to determine if the module should remain enabled,
  and set their own $this->enabled property accordingly.
  */
  function update_status() {
    if (empty($this->selected_module)) return;
    if (!is_array($this->modules)) return;
    if (!is_object($GLOBALS[$this->selected_module])) return;
    $function = __FUNCTION__;
    if (!method_exists($GLOBALS[$this->selected_module], $function)) return;
    return $GLOBALS[$this->selected_module]->$function();
  }

  function javascript_validation() {
    if (!is_array($this->modules) || empty($this->selection())) return '';
      $js = '<script type="text/javascript">' . "\n" .
      'function check_form() {' . "\n" .
      '  var error = 0;' . "\n" .
      '  var error_message = "' . JS_ERROR . '";' . "\n" .
      '  var payment_value = null;' . "\n" .
      '  if (document.checkout_payment.payment) {' . "\n" .
      '    if (document.checkout_payment.payment.length) {' . "\n" .
      '      for (var i=0; i<document.checkout_payment.payment.length; i++) {' . "\n" .
      '        if (document.checkout_payment.payment[i].checked) {' . "\n" .
      '          payment_value = document.checkout_payment.payment[i].value;' . "\n" .
      '        }' . "\n" .
      '      }' . "\n" .
      '    } else if (document.checkout_payment.payment.checked) {' . "\n" .
      '      payment_value = document.checkout_payment.payment.value;' . "\n" .
      '    } else if (document.checkout_payment.payment.value) {' . "\n" .
      '      payment_value = document.checkout_payment.payment.value;' . "\n" .
      '    }' . "\n" .
      '  }' . "\n\n";

      foreach($this->modules as $value) {
        $class = substr($value, 0, strrpos($value, '.'));
        if (isset($GLOBALS[$class]->enabled) && $GLOBALS[$class]->enabled == true) {
          $js .= $GLOBALS[$class]->javascript_validation();
        }
      }

       $js =  $js . "\n" . '  if (payment_value == null && submitter != 1) {' . "\n";
       $js =  $js .'    error_message = error_message + "' . JS_ERROR_NO_PAYMENT_MODULE_SELECTED . '";' . "\n";
       $js =  $js .'    error = 1;' . "\n";
       $js =  $js .'  }' . "\n\n";
       $js =  $js .'  if (error == 1 && submitter != 1) {' . "\n";
       $js =  $js .'    alert(error_message);' . "\n";
       $js =  $js . '    return false;' . "\n";
       $js =  $js .'  } else {' . "\n";
       $js =  $js .' var result = true; '  . "\n";
       if ($this->doesCollectsCardDataOnsite == true && PADSS_AJAX_CHECKOUT == '1') {
         $js .= '      result = !(doesCollectsCardDataOnsite(payment_value));' . "\n";
       }
       $js =  $js .' if (result == false) doCollectsCardDataOnsite();' . "\n";
       $js =  $js .'    return result;' . "\n";
       $js =  $js .'  }' . "\n" . '}' . "\n" . '</script>' . "\n";
    return $js;
  }

  function selection() {
    $selection_array = array();
    if (!is_array($this->modules)) return $selection_array;
    foreach($this->modules as $value) {
        $class = substr($value, 0, strrpos($value, '.'));
        if (!isset($GLOBALS[$class]->enabled) || $GLOBALS[$class]->enabled != true) {
            continue;
        }
        $selection = $GLOBALS[$class]->selection();

        if (isset($GLOBALS[$class]->collectsCardDataOnsite) && $GLOBALS[$class]->collectsCardDataOnsite == true) {
            $selection['fields'][] = array('title' => '',
                                         'field' => zen_draw_hidden_field($class . '_collects_onsite', 'true', 'id="' . $class . '_collects_onsite"'),
                                         'tag' => '');
        }
        if (is_array($selection)) $selection_array[] = $selection;
    }
    return $selection_array;
  }

  function in_special_checkout() {
    $result = false;
    if (!is_array($this->modules)) return $result;
    $function = __FUNCTION__;
    foreach($this->modules as $value) {
        $class = substr($value, 0, strrpos($value, '.'));
        if (isset($GLOBALS[$class]) && is_object($GLOBALS[$class]) && $GLOBALS[$class]->enabled && method_exists($GLOBALS[$class], $function)) {
          $module_result = $GLOBALS[$class]->$function();
          if ($module_result === true) $result = true;
        }
    }
    return $result;
  }

  function pre_confirmation_check() {
    global $credit_covers, $payment_modules;
    if (empty($this->selected_module)) return;
    if (!is_array($this->modules)) return;
    if (!is_object($GLOBALS[$this->selected_module]) || $GLOBALS[$this->selected_module]->enabled != true) return;
    $function = __FUNCTION__;
    if ($credit_covers) {
        $GLOBALS[$this->selected_module]->enabled = false;
        $GLOBALS[$this->selected_module] = NULL;
        $payment_modules = '';
    } else {
        $GLOBALS[$this->selected_module]->$function();
    }
  }

  function confirmation() {
    $default = array('title' => '', 'fields' => array());
    if (!is_array($this->modules)) return $default;
    if (!is_object($GLOBALS[$this->selected_module])) return $default;
    if (!$GLOBALS[$this->selected_module]->enabled) return $default;
    $function = __FUNCTION__;
    if (!method_exists($GLOBALS[$this->selected_module], $function)) return $default;
    $confirmation = $GLOBALS[$this->selected_module]->$function();
    if (!is_array($confirmation)) return $default;
    // use array_merge here to normalize the response - ie: so that both title/fields indices are populated even if the module doesn't return either of them
    return array_merge($default, $confirmation);
  }

  function process_button_ajax() {
    if (!is_array($this->modules)) return;
    if (!is_object($GLOBALS[$this->selected_module])) return;
    if (!$GLOBALS[$this->selected_module]->enabled) return;
    $function = __FUNCTION__;
    if (!method_exists($GLOBALS[$this->selected_module], $function)) return;
    return $GLOBALS[$this->selected_module]->$function();
  }
  function process_button() {
    if (!is_array($this->modules)) return;
    if (!is_object($GLOBALS[$this->selected_module])) return;
    if (!$GLOBALS[$this->selected_module]->enabled) return;
    $function = __FUNCTION__;
    if (!method_exists($GLOBALS[$this->selected_module], $function)) return;
    return $GLOBALS[$this->selected_module]->$function();
  }

  function before_process() {
    if (!is_array($this->modules)) return;
    if (!is_object($GLOBALS[$this->selected_module])) return;
    if (!$GLOBALS[$this->selected_module]->enabled) return;
    $function = __FUNCTION__;
    if (!method_exists($GLOBALS[$this->selected_module], $function)) return;
    return $GLOBALS[$this->selected_module]->$function();
  }

  function after_process() {
    if (!is_array($this->modules)) return;
    if (!is_object($GLOBALS[$this->selected_module])) return;
    if (!$GLOBALS[$this->selected_module]->enabled) return;
    $function = __FUNCTION__;
    if (!method_exists($GLOBALS[$this->selected_module], $function)) return;
    return $GLOBALS[$this->selected_module]->$function();
  }

  function after_order_create($zf_order_id) {
    if (!is_array($this->modules)) return;
    if (!is_object($GLOBALS[$this->selected_module])) return;
    if (!$GLOBALS[$this->selected_module]->enabled) return;
    $function = __FUNCTION__;
    if (!method_exists($GLOBALS[$this->selected_module], $function)) return;
    return $GLOBALS[$this->selected_module]->$function($zf_order_id);
  }

  function admin_notification($zf_order_id) {
    if (!is_array($this->modules)) return;
    if (!is_object($GLOBALS[$this->selected_module])) return;
    if (!$GLOBALS[$this->selected_module]->enabled) return;
    $function = __FUNCTION__;
    if (!method_exists($GLOBALS[$this->selected_module], $function)) return;
    return $GLOBALS[$this->selected_module]->$function($zf_order_id);
  }

  function get_error() {
    if (!is_array($this->modules)) return;
    if (!is_object($GLOBALS[$this->selected_module])) return;
    if (!$GLOBALS[$this->selected_module]->enabled) return;
    $function = __FUNCTION__;
    if (!method_exists($GLOBALS[$this->selected_module], $function)) return;
    return $GLOBALS[$this->selected_module]->$function();
  }

  function get_checkout_confirm_form_replacement() {
    $default = array(false, '');
    if (!is_array($this->modules)) return $default;
    if (!is_object($GLOBALS[$this->selected_module]) || $GLOBALS[$this->selected_module]->enabled != true) return $default;
    $function = __FUNCTION__;
    if (!method_exists($GLOBALS[$this->selected_module], $function)) return $default;
    return $GLOBALS[$this->selected_module]->$function();
  }

  function clear_payment()
  {
    if (!is_array($this->modules)) return;
    if (!is_object($GLOBALS[$this->selected_module])) return;
    if (!$GLOBALS[$this->selected_module]->enabled) return;
    $function = __FUNCTION__;
    if (!method_exists($GLOBALS[$this->selected_module], $function)) return;
    $GLOBALS[$this->selected_module]->$function();
  }
}
