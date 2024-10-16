<?php
/**
 * File contains the order-totals-processing class ("order-total")
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Sep 06 Modified in v2.1.0-beta1 $
 */
use Zencart\FileSystem\FileSystem;
use Zencart\ResourceLoaders\ModuleFinder;
use Zencart\Traits\NotifierManager;

/**
 * order-total class
 *
 * Handles all order-total processing functions
 *
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

class order_total
{
    use NotifierManager;

    /**
     * $modules is an array of installed order totals module names
     * @var array
     */
    public array $modules;

    /**
     * $module_order_total_installed indicates whether/not at least
     * one order-total module is installed.
     * @var bool
     */
    protected bool $module_order_total_installed = false;

    // class constructor
    public function __construct()
    {
        global $messageStack, $languageLoader, $installedPlugins;

        if (defined('MODULE_ORDER_TOTAL_INSTALLED') && MODULE_ORDER_TOTAL_INSTALLED !== '') {
            // -----
            // Locate all order_total modules, looking in both /includes/modules/order_total
            // and for those provided by zc_plugins.  Note that any module provided by a
            // zc_plugin overrides the processing present in any 'base' file.
            //
            $moduleFinder = new ModuleFinder('order_total', new Filesystem());
            $modules_found = $moduleFinder->findFromFilesystem($installedPlugins);

            $module_list = explode(';', MODULE_ORDER_TOTAL_INSTALLED);

            foreach ($module_list as $value) {
                if (!$languageLoader->loadModuleLanguageFile($value, 'order_total')) {
                    $language_dir = (IS_ADMIN_FLAG === false) ? DIR_WS_LANGUAGES : (DIR_FS_CATALOG . DIR_WS_LANGUAGES);
                    $lang_file = zen_get_file_directory($language_dir . $_SESSION['language'] . '/modules/order_total/', $value, 'false');
 
                    if (is_object($messageStack)) {
                        if (IS_ADMIN_FLAG === false) {
                            $messageStack->add('header', WARNING_COULD_NOT_LOCATE_LANG_FILE . $lang_file, 'caution');
                        } else {
                            $messageStack->add_session(WARNING_COULD_NOT_LOCATE_LANG_FILE . $lang_file, 'caution');
                        }
                    }
                    continue;
                }

                if (isset($modules_found[$value])) {
                    include_once DIR_FS_CATALOG . $modules_found[$value] . $value;
                    $class = pathinfo($value, PATHINFO_FILENAME);
                    $GLOBALS[$class] = new $class();
                    $this->modules[] = $value;
                    $this->module_order_total_installed = true;
                }
            }
        }
    }

    public function process(): array
    {
        global $order;

        $order_total_array = [];
        if ($this->module_order_total_installed === true) {
            $this->notify('NOTIFY_ORDER_TOTAL_PROCESS_STARTS', ['order_info' => $order->info]);
            foreach ($this->modules as $value) {
                $class = pathinfo($value, PATHINFO_FILENAME);
                if (!isset($GLOBALS[$class])) {
                    continue;
                }

                $GLOBALS[$class]->process();
                $this->notify('NOTIFY_ORDER_TOTAL_PROCESS_NEXT', ['class' => $class, 'order_info' => $order->info, 'ot_output' => $GLOBALS[$class]->output]);
                if (empty($GLOBALS[$class]->output)) {
                    continue;
                }

                foreach ($GLOBALS[$class]->output as $next_element) {
                    if (!empty($next_element['title']) && !empty($next_element['text'])) {
                        $order_total_array[] = [
                            'code' => $GLOBALS[$class]->code,
                            'title' => $next_element['title'],
                            'text' => $next_element['text'],
                            'value' => $next_element['value'],
                            'sort_order' => $GLOBALS[$class]->sort_order,
                        ];
                    }
                }
            }
        }

        return $order_total_array;
    }

    public function output(bool $return_html = false): string
    {
        global $template, $current_page_base;

        $output_string = '';
        if ($this->module_order_total_installed === true) {
            foreach ($this->modules as $value) {
                $class = pathinfo($value, PATHINFO_FILENAME);

                // ideally, the first part of this IF statement should be dropped, and the ELSE portion is all that should be kept
                if ($return_html == true) {
                    $class_code = str_replace('_', '-', $GLOBALS[$class]->code);
                    foreach ($GLOBALS[$class]->output as $next_output) {
                        $output_string .=
                            '              <tr>' . "\n" .
                            '                <td align="right" class="' . $class_code . '-Text">' . $next_output['title'] . '</td>' . "\n" .
                            '                <td align="right" class="' . $class_code . '-Amount">' . $next_output['text'] . '</td>' . "\n" .
                            '              </tr>';
                    }
                } else {
                    // use a template file for output instead of hard-coded HTML
                    $size = count($GLOBALS[$class]->output);
                    require $template->get_template_dir('tpl_modules_order_totals.php', DIR_WS_TEMPLATE, $current_page_base, 'templates') . '/tpl_modules_order_totals.php';
                }
            }
        }
        return $output_string;
    }

    //
    // This function is called in checkout payment after display of payment methods. It actually calls
    // two credit class functions.
    //
    // use_credit_amount() is normally a checkbox used to decide whether the credit amount should be applied to reduce
    // the order total. Whether this is a Gift Voucher, or discount coupon or reward points etc.
    //
    // The second function called is credit_selection(). This in the credit classes already made is usually a redeem box.
    // for entering a Gift Voucher number. Note credit classes can decide whether this part is displayed depending on
    // E.g. a setting in the admin section.
    //
    public function credit_selection(): array
    {
        $selection_array = [];
        if ($this->module_order_total_installed === true) {
            foreach ($this->modules as $value) {
                $class = pathinfo($value, PATHINFO_FILENAME);
                if (!empty($GLOBALS[$class]->credit_class)) {
                    $selection = $GLOBALS[$class]->credit_selection();
                    if (is_array($selection)) {
                        $selection_array[] = $selection;
                    }
                }
            }
        }
        return $selection_array;
    }

    // update_credit_account is called in checkout process on a per product basis. Its purpose
    // is to decide whether each product in the cart should add something to a credit account.
    // e.g. for the Gift Voucher it checks whether the product is a Gift voucher and then adds the amount
    // to the Gift Voucher account.
    // Another use would be to check if the product would give reward points and add these to the points/reward account.
    //
    public function update_credit_account($i): void
    {
        if ($this->module_order_total_installed === true) {
            foreach ($this->modules as $value) {
                $class = pathinfo($value, PATHINFO_FILENAME);
                if (!empty($GLOBALS[$class]->credit_class)) {
                    $GLOBALS[$class]->update_credit_account($i);
                }
            }
        }
    }

    // This function is called in checkout confirmation.
    // Its main use is for credit classes that use the credit_selection() method. This is usually for
    // entering redeem codes(Gift Vouchers/Discount Coupons). This function is used to validate these codes.
    // If they are valid then the necessary actions are taken, if not valid we are returned to checkout payment
    // with an error
    public function collect_posts(): void
    {
        if ($this->module_order_total_installed === true) {
            foreach ($this->modules as $value) {
                $class = pathinfo($value, PATHINFO_FILENAME);
                if (!empty($GLOBALS[$class]->credit_class)) {
                    $post_var = 'c' . $GLOBALS[$class]->code;
                    if (!empty($_POST[$post_var])) {
                        $_SESSION[$post_var] = $_POST[$post_var];
                    }
                    $GLOBALS[$class]->collect_posts();
                }
            }
        }
    }

    // pre_confirmation_check is called on checkout confirmation. Its function is to decide whether the
    // credits available are greater than the order total. If they are then a variable (credit_covers) is set to
    // true. This is used to bypass the payment method. In other words if the Gift Voucher is more than the order
    // total, we don't want to go to paypal etc.
    //
    public function pre_confirmation_check(bool $returnOrderTotalOnly = false)
    {
        global $order, $credit_covers;

        if ($this->module_order_total_installed === true) {
            $orderInfoSaved = $order->info;
            $this->notify('NOTIFY_ORDER_TOTAL_PRE_CONFIRMATION_CHECK_STARTS', ['order_info' => $orderInfoSaved]);
            foreach ($this->modules as $value) {
                $class = pathinfo($value, PATHINFO_FILENAME);
                $GLOBALS[$class]->process();
                $this->notify('NOTIFY_ORDER_TOTAL_PRE_CONFIRMATION_CHECK_NEXT', ['class' => $class, 'order_info' => $order->info, 'ot_output' => $GLOBALS[$class]->output]);
                $GLOBALS[$class]->output = [];
            }
            $reCalculatedOrderTotal = $order->info['total'];
            if ($reCalculatedOrderTotal <= 0.009 && !(isset($_SESSION['payment']) && $_SESSION['payment'] === 'freecharger')) {
                $credit_covers = true;
            }
            $order->info = $orderInfoSaved;
            if ($returnOrderTotalOnly === true) {
                return $reCalculatedOrderTotal;
            }
        }
    }

    // this function is called in checkout process. it tests whether a decision was made at checkout payment to use
    // the credit amount be applied aginst the order. If so some action is taken. E.g. for a Gift voucher the account
    // is reduced the order total amount.
    //
    public function apply_credit(): void
    {
        if ($this->module_order_total_installed === true) {
            foreach ($this->modules as $value) {
                $class = pathinfo($value, PATHINFO_FILENAME);
                if (!empty($GLOBALS[$class]->credit_class)) {
                    $GLOBALS[$class]->apply_credit();
                }
            }
        }
    }

    // Called in checkout process to clear session variables created by each credit class module.
    //
    public function clear_posts(): void
    {
        if ($this->module_order_total_installed === true) {
            foreach ($this->modules as $value) {
                $class = pathinfo($value, PATHINFO_FILENAME);
                if (!empty($GLOBALS[$class]->credit_class) && method_exists($GLOBALS[$class], 'clear_posts')) {
                    $GLOBALS[$class]->clear_posts();
                }
            }
        }
    }

    // Called at various times. This function calulates the total value of the order that the
    // credit will be applied against. This varies depending on whether the credit class applies
    // to shipping & tax
    //
    public function get_order_total_main($class, $order_total)
    {
        return $order_total;
    }
}
