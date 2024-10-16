<?php
/**
 * shipping class
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Sep 06 Modified in v2.1.0-beta1 $
 */
use Zencart\FileSystem\FileSystem;
use Zencart\ResourceLoaders\ModuleFinder;
use Zencart\Traits\NotifierManager;

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

/**
 * shipping class
 * Class used for interfacing with shipping modules
 *
 */
class shipping
{
    use NotifierManager;

    /**
     * $enabled public property used by notifiers to allow notifier to turn off a shipping method when querying available modules
     */
    public bool $enabled;
    /**
     * $modules is an array of installed shipping module names; notifier hook exists to alter if needed
     */
    public array $modules;
    /**
     * $abort_legacy_calculations public property allows a notifier to intercept the calculate_boxes_weight_and_tare method
     */
    public bool $abort_legacy_calculations;
    /**
     * Initialized modules whose status is "enabled"
     */
    protected array $initialized_modules = [];

    public function __construct($module = null)
    {
        if (defined('MODULE_SHIPPING_INSTALLED') && !empty(MODULE_SHIPPING_INSTALLED)) {
            $this->modules = explode(';', MODULE_SHIPPING_INSTALLED);
        }
        $this->notify('NOTIFY_SHIPPING_CLASS_GET_INSTALLED_MODULES', $module);

        if (empty($this->modules)) {
            return;
        }

        $this->initialize_modules($module);
    }

    /**
     * Load language files and check "enabled" configuration status of each module.
     * If $module is specified, limits the initialization to just that module; else processes all "installed" modules listed in Admin.
     */
    protected function initialize_modules($module = null): void
    {
        global $messageStack, $languageLoader, $installedPlugins;

        // -----
        // Locate all shipping modules, looking in both /includes/modules/shipping
        // and for those provided by zc_plugins.  Note that any module provided by a
        // zc_plugin overrides the processing present in any 'base' file.
        //
        $moduleFinder = new ModuleFinder('shipping', new Filesystem());
        $modules_found = $moduleFinder->findFromFilesystem($installedPlugins);

        $modules_to_quote = [];

        $module_name = (empty($module)) ? '0' : substr($module['id'], 0, strpos($module['id'], '_'));
        if (!empty($module) && in_array($module_name . '.php', $this->modules) && isset($modules_found[$module_name])) {
            $modules_to_quote[] = [
                'class' => $module_name,
                'file' => $module_name . '.php',
            ];
        } else {
            foreach ($this->modules as $value) {
                $class = pathinfo($value, PATHINFO_FILENAME);
                $modules_to_quote[] = [
                    'class' => $class,
                    'file' => $value,
                ];
            }
        }

        foreach ($modules_to_quote as $quote_module) {
            if (!$languageLoader->loadModuleLanguageFile($quote_module['file'], 'shipping')) {
                $language_dir = (IS_ADMIN_FLAG === false) ? DIR_WS_LANGUAGES : (DIR_FS_CATALOG . DIR_WS_LANGUAGES);
                $lang_file = zen_get_file_directory($language_dir . $_SESSION['language'] . '/modules/shipping/', $quote_module['file'], 'false');

                if (is_object($messageStack)) {
                    if (IS_ADMIN_FLAG === false) {
                        $messageStack->add('checkout_shipping', WARNING_COULD_NOT_LOCATE_LANG_FILE . $lang_file, 'caution');
                    } else {
                        $messageStack->add_session(WARNING_COULD_NOT_LOCATE_LANG_FILE . $lang_file, 'caution');
                    }
                }
                continue;
            }

            $this->enabled = true;

            $this->notify('NOTIFY_SHIPPING_MODULE_ENABLE', $quote_module['class'], $quote_module['class']);
            if ($this->enabled && isset($modules_found[$quote_module['file']])) {
                include_once DIR_FS_CATALOG . $modules_found[$quote_module['file']] . $quote_module['file'];
                $GLOBALS[$quote_module['class']] = new $quote_module['class']();

                $enabled = $this->check_enabled($GLOBALS[$quote_module['class']]);
                if ($enabled === false) {
                    unset($GLOBALS[$quote_module['class']]);
                } else {
                    $this->initialized_modules[] = $quote_module['class'];
                }
            }
        }
    }

    public function getInitializedModules(): array
    {
        return $this->initialized_modules;
    }

    /**
     * NOTE: Could eventually replace zen_count_shipping_modules() function
     */
    public function countEnabledModules(): int
    {
        return count($this->initialized_modules);
    }

    /**
     * Check whether a module is enabled for the active checkout zone
     */
    public function check_enabled($module_class): bool
    {
        $enabled = $module_class->enabled;
        if (method_exists($module_class, 'check_enabled_for_zone') && $module_class->enabled) {
            $enabled = $module_class->check_enabled_for_zone();
        }
        $this->notify('NOTIFY_SHIPPING_CHECK_ENABLED_FOR_ZONE', [], $module_class, $enabled);

        if (method_exists($module_class, 'check_enabled') && $enabled) {
            $enabled = $module_class->check_enabled();
        }
        $this->notify('NOTIFY_SHIPPING_CHECK_ENABLED', [], $module_class, $enabled);

        return !empty($enabled);
    }

    /**
     * Legacy package calculation
     * Rudimentarily takes the sum of all weights and then divides into number of boxes required based on admin-configured max weight per box.
     * Includes adding tare/padding percentage based on box size.
     * DOES NOT TAKE PACKAGE DIMENSIONS INTO ACCOUNT.
     */
    public function calculate_boxes_weight_and_tare()
    {
        global $total_weight, $shipping_weight, $shipping_quoted, $shipping_num_boxes;

        $this->abort_legacy_calculations = false;
        $this->notify('NOTIFY_SHIPPING_MODULE_PRE_CALCULATE_BOXES_AND_TARE', [], $total_weight, $shipping_weight, $shipping_quoted, $shipping_num_boxes);
        if ($this->abort_legacy_calculations) {
            return;
        }

        if (!empty($this->modules)) {
            $shipping_quoted = '';
            $shipping_num_boxes = 1;
            $shipping_weight = $total_weight;

            $za_tare_array = preg_split("/[:,]/", str_replace(' ', '', !empty(SHIPPING_BOX_WEIGHT) ? SHIPPING_BOX_WEIGHT : '0:0'));
            $zc_tare_percent = (float)$za_tare_array[0];
            $zc_tare_weight = (float)$za_tare_array[1];

            $za_large_array = preg_split("/[:,]/", str_replace(' ', '', !empty(SHIPPING_BOX_PADDING) ? SHIPPING_BOX_PADDING : '0:0'));
            $zc_large_percent = (float)$za_large_array[0];
            $zc_large_weight = (float)$za_large_array[1];

            // SHIPPING_BOX_WEIGHT = tare
            // SHIPPING_BOX_PADDING = Large Box % increase
            // SHIPPING_MAX_WEIGHT = Largest package

            switch (true) {
                // large box add padding
                case (SHIPPING_MAX_WEIGHT <= $shipping_weight):
                    $shipping_weight = $shipping_weight + ($shipping_weight * ($zc_large_percent / 100)) + $zc_large_weight;
                    break;

                default:
                    // add tare weight < large
                    $shipping_weight = $shipping_weight + ($shipping_weight * ($zc_tare_percent / 100)) + $zc_tare_weight;
                    break;
            }

            // total weight with Tare
            $_SESSION['shipping_weight'] = $shipping_weight;
            if ($shipping_weight > SHIPPING_MAX_WEIGHT) { // Split into many boxes
                $zc_boxes = zen_round(($shipping_weight / SHIPPING_MAX_WEIGHT), 2);
                $shipping_num_boxes = ceil($zc_boxes);
                $shipping_weight = $shipping_weight / $shipping_num_boxes;
            }
        }
        $this->notify('NOTIFY_SHIPPING_MODULE_CALCULATE_BOXES_AND_TARE', [], $total_weight, $shipping_weight, $shipping_quoted, $shipping_num_boxes);
    }

    /**
     * Cycle through all enabled shipping modules and prepare quotes for all methods supported by those modules
     *
     * @param $method - If specified, limit to re-quoting the specified method
     * @param $module - If specified, limit to re-quoting for the specified module
     * @param $calc_boxes_weight_tare - Do box/tare calculations?
     * @param $insurance_exclusions - Pass rules for excluding from insurance calculations; requires customization.
     * @return array
     */
    public function quote($method = '', $module = '', $calc_boxes_weight_tare = true, $insurance_exclusions = []): array
    {
        global $shipping_weight, $uninsurable_value;

        $quotes_array = [];

        if ($calc_boxes_weight_tare) {
            $this->calculate_boxes_weight_and_tare();
        }

        // calculate amount not to be insured on shipping
        $uninsurable_value = (method_exists($this, 'get_uninsurable_value')) ? $this->get_uninsurable_value($insurance_exclusions) : 0;

        if (!empty($this->modules)) {
            $modules_to_quote = [];

            foreach ($this->modules as $value) {
                $class = pathinfo($value, PATHINFO_FILENAME);
                if (!empty($module)) {
                    if ($module === $class && isset($GLOBALS[$class]) && $GLOBALS[$class]->enabled) {
                        $modules_to_quote[] = $class;
                    }
                } elseif (isset($GLOBALS[$class]) && $GLOBALS[$class]->enabled) {
                    $modules_to_quote[] = $class;
                }
            }

            foreach ($modules_to_quote as $quoting_module) {
                if (method_exists($GLOBALS[$quoting_module], 'update_status')) {
                    $GLOBALS[$quoting_module]->update_status();
                }
                if (false === $GLOBALS[$quoting_module]->enabled) {
                    continue;
                }

                $save_shipping_weight = $shipping_weight;
                $quotes = $GLOBALS[$quoting_module]->quote($method);
                if (!isset($quotes['tax']) && !empty($quotes)) {
                    $quotes['tax'] = 0;
                }
                $shipping_weight = $save_shipping_weight;
                if (is_array($quotes)) {
                    $quotes_array[] = $quotes;
                }
            }
        }
        $this->notify('NOTIFY_SHIPPING_MODULE_GET_ALL_QUOTES', $quotes_array, $quotes_array);
        return $quotes_array;
    }

    /**
     * Determine cheapest-available shipping method.
     * Excludes store-pickup unless store-pickup is the only option
     */
    public function cheapest(): array|bool
    {
        if (empty($this->modules)) {
            return false;
        }

        $rates = [];
        $exclude_storepickup_module = false;
        foreach ($this->modules as $value) {
            $class = pathinfo($value, PATHINFO_FILENAME);
            if (isset($GLOBALS[$class]) && is_object($GLOBALS[$class]) && $GLOBALS[$class]->enabled) {
                $quotes = $GLOBALS[$class]->quotes ?? null;
                if (empty($quotes['methods']) || isset($quotes['error'])) {
                    continue;
                }

                foreach ($quotes['methods'] as $method) {
                    if (isset($method['cost'])) {
                        $rates[] = [
                            'id' => $quotes['id'] . '_' . $method['id'],
                            'title' => $quotes['module'] . ' (' . $method['title'] . ')',
                            'cost' => $method['cost'],
                            'module' => $quotes['id'],
                        ];

                        if ($quotes['id'] !== 'storepickup') {
                            $exclude_storepickup_module = true;
                        }
                    }
                }
            }
        }

        $cheapest = false;
        foreach ($rates as $rate) {
            if ($cheapest !== false) {
                // never quote storepickup as lowest, unless it's the only active module - needs to be configured in shipping module
                if ($rate['cost'] < $cheapest['cost']) {
                    if ($exclude_storepickup_module === true && $rate['module'] === 'storepickup') {
                        continue;
                    }

                    // -----
                    // Give a customized shipping module the opportunity to exclude itself from being quoted as the cheapest.
                    // The observer must set the $exclude_from_cheapest to (bool)true to be excluded.
                    //
                    $exclude_from_cheapest = false;
                    $this->notify('NOTIFY_SHIPPING_EXCLUDE_FROM_CHEAPEST', $rate['module'], $exclude_from_cheapest);
                    if ($exclude_from_cheapest === true) {
                        continue;
                    }
                    $cheapest = $rate;
                }
            } elseif ($exclude_storepickup_module === false || $rate['module'] !== 'storepickup') {
                $cheapest = $rate;
            }
        }
        $this->notify('NOTIFY_SHIPPING_MODULE_CALCULATE_CHEAPEST', $cheapest, $cheapest, $rates);
        return $cheapest;
    }
}
