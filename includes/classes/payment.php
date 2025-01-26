<?php
/**
 * Payment Class.
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: asiksami 2024 Oct 03 Modified in v2.1.0 $
 */
use Zencart\FileSystem\FileSystem;
use Zencart\ResourceLoaders\ModuleFinder;
use Zencart\Traits\NotifierManager;

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

/**
 * Payment Class.
 * This class interfaces with payment modules
 *
 */
class payment
{
    use NotifierManager;

    /**
     * $doesCollectsCardDataOnsite is a flag to indicate if card details are collected on site
     * @var boolean
     */
    public bool $doesCollectsCardDataOnsite;
    /**
     * $form_action_url is the URL to process the payment or not set for local processing
     * @var string
     */
    public string $form_action_url;
    /**
     * $modules array of payment module names
     * @var array 
     */
    public array $modules;
    /**
     * $paymentClass is a payment class
     * @var class
     */
    public $paymentClass;
    /**
     * $selected_module is the selected payment module
     * @var string
     */
    public string $selected_module;

    public function __construct($module = '')
    {
        global $language, $credit_covers, $messageStack, $languageLoader, $installedPlugins;

        $this->doesCollectsCardDataOnsite = false;

        if (defined('MODULE_PAYMENT_INSTALLED') && !empty(MODULE_PAYMENT_INSTALLED)) {
            $this->modules = explode(';', MODULE_PAYMENT_INSTALLED);
        }
        $this->notify('NOTIFY_PAYMENT_CLASS_GET_INSTALLED_MODULES', $module);

        if (empty($this->modules)) {
            return;
        }

        // -----
        // Locate all payment modules, looking in both /includes/modules/payment
        // and for those provided by zc_plugins.  Note that any module provided by a
        // zc_plugin overrides the processing present in any 'base' file.
        //
        $moduleFinder = new ModuleFinder('payment', new Filesystem());
        $modules_found = $moduleFinder->findFromFilesystem($installedPlugins);

        $include_modules = [];

        if (!empty($module) && in_array($module . '.php', $this->modules) && isset($modules_found[$module . '.php'])) {
            $this->selected_module = $module;

            $include_modules[] = ['class' => $module, 'file' => $module . '.php'];
        } else {
            // Free Payment Only shows
            $freecharger_enabled = (defined('MODULE_PAYMENT_FREECHARGER_STATUS') && MODULE_PAYMENT_FREECHARGER_STATUS === 'True' && isset($modules_found['freecharger.php']));
            if ($freecharger_enabled && $_SESSION['cart']->show_total() == 0 && (!isset($_SESSION['shipping']['cost']) || $_SESSION['shipping']['cost'] == 0)) {
                $this->selected_module = $module;
                $include_modules[] = ['class'=> 'freecharger', 'file' => 'freecharger.php'];
            } else {
                // All Other Payment Modules show
                foreach ($this->modules as $value) {
                    // double check that the module really exists before adding to the array
                    if (isset($modules_found[$value])) {
                        $class = pathinfo($value, PATHINFO_FILENAME);
                        // Don't show Free Payment Module
                        if ($class !== 'freecharger') {
                            $include_modules[] = ['class' => $class, 'file' => $value];
                        }
                    }
                }
            }
        }

        for ($i = 0, $n = count($include_modules); $i < $n; $i++) {
            $next_module = $include_modules[$i];

            if (!$languageLoader->loadModuleLanguageFile($next_module['file'], 'payment')) {
                $lang_file = zen_get_file_directory(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/payment/', $next_module['file'], 'false');
                if (is_object($messageStack)) {
                    if (IS_ADMIN_FLAG === false) {
                        $messageStack->add('checkout_payment', WARNING_COULD_NOT_LOCATE_LANG_FILE . $lang_file, 'caution');
                    } else {
                        $messageStack->add_session(WARNING_COULD_NOT_LOCATE_LANG_FILE . $lang_file, 'caution');
                    }
                }
                continue;
            }

            include_once DIR_FS_CATALOG . $modules_found[$next_module['file']] . $next_module['file'];

            $this->paymentClass = new $next_module['class']();
            $this->notify('NOTIFY_PAYMENT_MODULE_ENABLE');
            if ($this->paymentClass->enabled) {
                $GLOBALS[$next_module['class']] = $this->paymentClass;
                if (!empty($this->paymentClass->collectsCardDataOnsite)) {
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
        if (zen_count_payment_modules() == 1 && (!isset($_SESSION['payment']) || !is_object($_SESSION['payment']))) {
            if (empty($credit_covers)) {
                $_SESSION['payment'] = $include_modules[0]['class'];
            }
        }

        if (!empty($module) && in_array($module, $this->modules) && isset($GLOBALS[$module]->form_action_url)) {
            $this->form_action_url = $GLOBALS[$module]->form_action_url;
        }
    }

    public function checkCreditCovered(): bool
    {
        global $credit_covers;

        $credit_is_covered = false;
        if (isset($credit_covers) && $credit_covers === true) {
            $credit_is_covered = true;
            $this->modules = [];
            $this->selected_module = '';
        }
        return $credit_is_covered;
    }

    // -----
    // This protected method is used by various public methods to
    // perform common determination of whether the currently-selected
    // module is present, enabled and includes the submitted method.
    //
    protected function isPaymentModuleMethodPresent(string $method): bool
    {
        if (empty($this->selected_module) || !is_array($this->modules) || !is_object($GLOBALS[$this->selected_module])) {
            return false;
        }
        if (!method_exists($GLOBALS[$this->selected_module], $method)) {
            return false;
        }
        return true;
    }

    /**
     * The update_status() method is needed in the checkout_confirmation.php page
     * due to a chicken and egg problem with the payment class and order class.
     * The payment modules need the order destination data for the dynamic status
     * feature, and the order class needs the payment module title.
     * Modules should implement this method to inspect the current order address
     * for to determine if the module should remain enabled,
     * and set their own $this->enabled property accordingly.
     */
    public function update_status()
    {
        if ($this->isPaymentModuleMethodPresent('update_status') === false) {
            return;
        }
        return $GLOBALS[$this->selected_module]->update_status();
    }

    public function javascript_validation(): string
    {
        if (!is_array($this->modules) || empty($this->selection())) {
            return '';
        }

        $js = '<script>' . "\n" .
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

        foreach ($this->modules as $value) {
            $class = pathinfo($value, PATHINFO_FILENAME);
            if (!empty($GLOBALS[$class]->enabled)) {
                $js .= $GLOBALS[$class]->javascript_validation();
            }
        }

        $js .=  "\n" . '  if (payment_value == null && submitter != 1) {' . "\n";
        $js .=  '    error_message = error_message + "' . JS_ERROR_NO_PAYMENT_MODULE_SELECTED . '";' . "\n";
        $js .=  '    error = 1;' . "\n";
        $js .=  '  }' . "\n\n";
        $js .=  '  if (error == 1 && submitter != 1) {' . "\n";
        $js .=  '    alert(error_message);' . "\n";
        $js .=  '    return false;' . "\n";
        $js .=  '  } else {' . "\n";
        $js .=  ' var result = true; '  . "\n";
        if ($this->doesCollectsCardDataOnsite === true && PADSS_AJAX_CHECKOUT === '1') {
            $js .= '      result = !(doesCollectsCardDataOnsite(payment_value));' . "\n";
        }
        $js .=  ' if (result == false) doCollectsCardDataOnsite();' . "\n";
        $js .=  '    return result;' . "\n";
        $js .=  '  }' . "\n" . '}' . "\n" . '</script>' . "\n";

        return $js;
    }

    public function selection(): array
    {
        if (!is_array($this->modules)) {
            return [];
        }

        $selection_array = [];
        foreach ($this->modules as $value) {
            $class = pathinfo($value, PATHINFO_FILENAME);
            if (empty($GLOBALS[$class]->enabled)) {
                continue;
            }

            $selection = $GLOBALS[$class]->selection();

            if (!empty($GLOBALS[$class]->collectsCardDataOnsite)) {
                $selection['fields'][] = [
                    'title' => '',
                    'field' => zen_draw_hidden_field($class . '_collects_onsite', 'true', 'id="' . $class . '_collects_onsite"'),
                    'tag' => ''
                ];
            }
            if (is_array($selection)) {
                $selection_array[] = $selection;
            }
        }
        return $selection_array;
    }

    public function in_special_checkout(): bool
    {
        if (!is_array($this->modules)) {
            return false;
        }

        $result = false;
        foreach ($this->modules as $value) {
            $class = pathinfo($value, PATHINFO_FILENAME);
            if (isset($GLOBALS[$class]) && is_object($GLOBALS[$class]) && $GLOBALS[$class]->enabled && method_exists($GLOBALS[$class], 'in_special_checkout')) {
                $module_result = $GLOBALS[$class]->in_special_checkout();
                if ($module_result === true) {
                    $result = true;
                    break;
                }
            }
        }
        return $result;
    }

    public function pre_confirmation_check(): void
    {
        global $credit_covers, $payment_modules;

        if (empty($this->selected_module) || !is_array($this->modules)) {
            return;
        }
        if (!is_object($GLOBALS[$this->selected_module]) || $GLOBALS[$this->selected_module]->enabled != true) {
            return;
        }

        if ($credit_covers) {
            $GLOBALS[$this->selected_module]->enabled = false;
            $GLOBALS[$this->selected_module] = null;
            $payment_modules = '';
        } else {
            $GLOBALS[$this->selected_module]->pre_confirmation_check();
        }
    }

    public function confirmation(): array
    {
        $default = ['title' => '', 'fields' => []];
        if ($this->isPaymentModuleMethodPresent('confirmation') === false) {
            return $default;
        }

        $confirmation = $GLOBALS[$this->selected_module]->confirmation();
        if (!is_array($confirmation)) {
            return $default;
        }

        // use array_merge here to normalize the response - ie: so that both title/fields indices are populated even if the module doesn't return either of them
        return array_merge($default, $confirmation);
    }

    public function process_button_ajax()
    {
         if ($this->isPaymentModuleMethodPresent('process_button_ajax') === false) {
            return;
        }
        return $GLOBALS[$this->selected_module]->process_button_ajax();
    }

    public function process_button()
    {
        if ($this->isPaymentModuleMethodPresent('process_button') === false) {
            return;
        }
        return $GLOBALS[$this->selected_module]->process_button();
    }

    public function before_process()
    {
        if ($this->isPaymentModuleMethodPresent('before_process') === false) {
            return;
        }
        return $GLOBALS[$this->selected_module]->before_process();
    }

    public function after_process()
    {
        if ($this->isPaymentModuleMethodPresent('after_process') === false) {
            return;
        }
        return $GLOBALS[$this->selected_module]->after_process();
    }

    public function after_order_create($zf_order_id)
    {
        if ($this->isPaymentModuleMethodPresent('after_order_create') === false) {
            return;
        }
        return $GLOBALS[$this->selected_module]->after_order_create($zf_order_id);
    }

    public function admin_notification($zf_order_id)
    {
         if ($this->isPaymentModuleMethodPresent('admin_notification') === false) {
            return;
        }
        return $GLOBALS[$this->selected_module]->admin_notification($zf_order_id);
    }

    public function get_error()
    {
        if ($this->isPaymentModuleMethodPresent('get_error') === false) {
            return;
        }
        return $GLOBALS[$this->selected_module]->get_error();
    }

    public function get_checkout_confirm_form_replacement(): array
    {
        if ($this->isPaymentModuleMethodPresent('get_checkout_confirm_form_replacement') === false) {
            return [false, ''];
        }
        return $GLOBALS[$this->selected_module]->get_checkout_confirm_form_replacement();
    }

    public function clear_payment()
    {
        if ($this->isPaymentModuleMethodPresent('clear_payment') === false) {
            return;
        }
        return $GLOBALS[$this->selected_module]->clear_payment();
    }
}
