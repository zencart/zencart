<?php
/**
 * paypalr.php payment module class for PayPal RESTful API payment method
 *
 * @copyright Copyright 2023-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 *
 * Last updated: v1.3.1
 */
/**
 * Load the support class' auto-loader.
 */
require_once DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/paypal/pprAutoload.php';

use PayPalRestful\Admin\AdminMain;
use PayPalRestful\Admin\DoAuthorization;
use PayPalRestful\Admin\DoCapture;
use PayPalRestful\Admin\DoRefund;
use PayPalRestful\Admin\DoVoid;
use PayPalRestful\Admin\GetPayPalOrderTransactions;
use PayPalRestful\Api\PayPalRestfulApi;
use PayPalRestful\Api\Data\CountryCodes;
use PayPalRestful\Common\ErrorInfo;
use PayPalRestful\Common\Helpers;
use PayPalRestful\Common\Logger;
use PayPalRestful\Zc2Pp\Amount;
use PayPalRestful\Zc2Pp\ConfirmPayPalPaymentChoiceRequest;
use PayPalRestful\Zc2Pp\CreatePayPalOrderRequest;

/**
 * The PayPal payment module using PayPal's RESTful API (v2)
 */
class paypalr extends base
{
    const CURRENT_VERSION = '1.3.1';

    const REDIRECT_LISTENER = HTTP_SERVER . DIR_WS_CATALOG . 'ppr_listener.php';

    /**
     * name of this module
     *
     * @var string
     */
    public $code;

    /**
     * displayed module title
     *
     * @var string
     */
    public $title;

    /**
     * displayed module description
     *
     * @var string
     */
    public $description = '';

    /**
     * module status - set based on various config and zone criteria
     *
     * @var boolean
     */
    public $enabled;

    /**
     * Installation 'check' flag
     *
     * @var boolean
     */
    protected $_check;

    /**
     * the zone to which this module is restricted for use
     *
     * @var int
     */
    public $zone;

    /**
     * debugging flags
     *
     * @var boolean
     */
    protected $emailAlerts;

    /**
     * sort order of display
     *
     * @var int/null
     */
    public $sort_order = 0;

    /**
     * order status setting for completed orders
     *
    * @var int
     */
    public $order_status;

    /**
     * URLs used during checkout if this is the selected payment method
     *
     * @var string
     */
    public $form_action_url;

    /**
     * The credit-card portion of this payment-module *does* collect card-data
     * on-site. If credit-card payments are to be offered, the module's
     * initialization will change this to (bool)true.
     */
    public $collectsCardDataOnsite = false;

    /**
     * The orders::orders_id for a just-created order, supplied during
     * the 'checkout_process' step.
     */
    protected $orders_id;

    /**
     * Debug interface, shared with the PayPalRestfulApi class.
     */
    protected $log; //- An instance of the Logger class, logs debug tracing information.

    /**
     * An array to maintain error information returned by various PayPalRestfulApi methods.
     */
    protected $errorInfo; //- An instance of the ErrorInfo class, logs debug tracing information.

    /**
     * An instance of the PayPalRestfulApi class.
     */
    protected $ppr;

    /**
     * An array (set by before_process) containing the captured/authorized order's
     * PayPal response information, for use by after_order_create to populate the
     * paypal table's record once the associated order's ID is known.
     */
    protected $orderInfo = [];

    /**
     * An array (set by validateCardInformation) containing the card-related information
     * to be sent to PayPal for a 'card' transaction.
     */
    private $ccInfo = [];

    /**
     * Indicates whether/not credit-card payments are to be accepted during storefront
     * processing and, if so, an array that maps a credit-card's name to its associated
     * image.
     */
    protected $cardsAccepted = false;
    protected $cardImages = [];

    /**
     * Indicates whether/not an otherwise approved payment is pending review.
     */
    protected $paymentIsPending = false;

    /**
     * Indicates whether/not we're on the One-Page-Checkout confirmation page.  Possibly
     * set true by the update_status method.  Also captured on this page is the original,
     * pre-confirmation check, value of the $_SESSION['PayPalRestful'] element.
     *
     * It'll be needed if this payment method is configured by OPC to 'not need' confirmation
     * so that we can fake out the before/after session-check.
     */
    protected $onOpcConfirmationPage = false;
    protected $paypalRestfulSessionOnEntry = [];

    /**
     * A couple of flags (used by the 'selection' method) which are set by the
     * class-constuctor to indicate whether/not the storefront's currently active billing/shipping
     * addresses are associated with countries not supported by PayPal.
     */
    protected $billingCountryIsSupported = true;
    protected $shippingCountryIsSupported = true;

    /**
     * class constructor
     */
    public function __construct()
    {
        global $order, $messageStack, $loaderPrefix;

        $this->code = 'paypalr';

        $curl_installed = (function_exists('curl_init'));

        if (IS_ADMIN_FLAG === false) {
            $this->title = MODULE_PAYMENT_PAYPALR_TEXT_TITLE;
        } else {
            $this->title = MODULE_PAYMENT_PAYPALR_TEXT_TITLE_ADMIN . (($curl_installed === true) ? '' : $this->alertMsg(MODULE_PAYMENT_PAYPALR_ERROR_NO_CURL));
            $this->description = sprintf(MODULE_PAYMENT_PAYPALR_TEXT_ADMIN_DESCRIPTION, self::CURRENT_VERSION);
        }

        $this->sort_order = defined('MODULE_PAYMENT_PAYPALR_SORT_ORDER') ? ((int)MODULE_PAYMENT_PAYPALR_SORT_ORDER) : null;
        if (null === $this->sort_order) {
            return;
        }

        // @TODO - "Retired" check should accommodate 'webhook' mode too, because we do want to still respond to webhooks when in Retired mode.
        $this->enabled = (MODULE_PAYMENT_PAYPALR_STATUS === 'True' || (IS_ADMIN_FLAG === true && MODULE_PAYMENT_PAYPALR_STATUS === 'Retired'));

        $this->errorInfo = new ErrorInfo();

        $this->log = new Logger();
        $debug = (strpos(MODULE_PAYMENT_PAYPALR_DEBUGGING, 'Log') !== false);
        if ($debug === true) {
            $this->log->enableDebug();
        }
        $this->emailAlerts = (MODULE_PAYMENT_PAYPALR_DEBUGGING === 'Alerts Only' || MODULE_PAYMENT_PAYPALR_DEBUGGING === 'Log and Email');

        // -----
        // An order's *initial* order-status depending on the mode in which the PayPal transaction
        // is to be performed.
        //
        $ppr_type = $_SESSION['PayPalRestful']['ppr_type'] ?? 'paypal';
        if (MODULE_PAYMENT_PAYPALR_TRANSACTION_MODE === 'Final Sale' || ($ppr_type !== 'card' && MODULE_PAYMENT_PAYPALR_TRANSACTION_MODE === 'Auth Only (Card-Only)')) {
            $order_status = (int)MODULE_PAYMENT_PAYPALR_ORDER_STATUS_ID;
        } else {
            $order_status = (int)MODULE_PAYMENT_PAYPALR_ORDER_PENDING_STATUS_ID;
        }
        $this->order_status = ($order_status > 1) ? $order_status : (int)DEFAULT_ORDERS_STATUS_ID;

        $this->zone = (int)MODULE_PAYMENT_PAYPALR_ZONE;

        if (IS_ADMIN_FLAG === true) {
            if (MODULE_PAYMENT_PAYPALR_STATUS === 'Retired') {
                $this->title .= ' <strong>(Retired)</strong>';
            }
            if (MODULE_PAYMENT_PAYPALR_SERVER === 'sandbox') {
                $this->title .= $this->alertMsg(' (sandbox active)');
            }
            if ($debug === true) {
                $this->title .= ' <strong>(Debug)</strong>';
            }
            $this->tableCheckup();

            // -----
            // Make sure that the root-directory files copied during install/upgrade are
            // actually present.  If not, the payment module is auto-disabled.
            //
            // Starting with v1.3.1, the payment module **always** checks that
            // its root-directory listeners/handlers have been copied from within the module's
            // storefront includes directory.
            //
            $this->enabled = $this->manageRootDirectoryFiles();
        } elseif ($this->enabled === true) {
            // -----
            // Ensure that the payment-module's observer-class is loaded (auto.paypalrestful.php).  That
            // observer gathers order-totals' changes to the order's 'info', enabling the order-creation
            // request to PayPal to be properly formed.
            //
            // No observer?  No payment via this module and it's auto-disabled.
            //
            // The two exception to the above 'rule' is any load from the ipn_main_handler or a webhook listener.
            // Webhook classes don't need the Observer, so we just return.
            // The paypal_ipn.core.php (for whatever reason) doesn't load the auto-loaded observers,
            // so the paypalr one won't be there.  If that's the case, just indicate that the
            // payment module is disabled and return.
            //
            global $zcObserverPaypalrestful;
            if (!isset($zcObserverPaypalrestful)) {
                $this->enabled = false;

                if (in_array($loaderPrefix ?? '', ['paypal_ipn', 'webhook'], true)) {
                    return;
                }
                $this->setConfigurationDisabled(MODULE_PAYMENT_PAYPALR_ALERT_MISSING_OBSERVER);
                return;
            }

            // -----
            // Determine whether credit-card payments are to be accepted on the storefront.
            //
            // Note: For this type of payment to be enabled on the storefront:
            // 1) The payment-method needs to be enabled via configuration.
            // 2) The site must _either_ be running on the 'sandbox' server or using SSL-encryption.
            // 3) If credit-card payments should be offered *only* to account-holders, the current
            //    checkout must not be a guest-checkout.
            //
            // If enabled via configuration, further check to see that at least one of the card types
            // supported by PayPal is also supported by the site.
            //
            $cards_accepted = (MODULE_PAYMENT_PAYPALR_ACCEPT_CARDS === 'true' || (MODULE_PAYMENT_PAYPALR_ACCEPT_CARDS === 'Account-Holders Only' && !zen_in_guest_checkout()));
            $this->cardsAccepted = ($cards_accepted === true && (MODULE_PAYMENT_PAYPALR_SERVER === 'sandbox' || strpos(HTTP_SERVER, 'https://') === 0));
            if ($this->cardsAccepted === true) {
                $this->cardsAccepted = $this->checkCardsAcceptedForSite();
            }
        }

        // -----
        // Validate the configuration, e.g. that the supplied Client ID/Secret are
        // valid for the active PayPal server. If valid, we check the webhook registrations.
        // If the configuration's invalid (admin/storefront)
        // or if we're processing for the admin or a webhook, all finished here!
        //
        $this->enabled = ($this->enabled === true && $this->validateConfiguration($curl_installed));

        if ($this->enabled && IS_ADMIN_FLAG === true) {
            // register/update known webhooks
            $this->ppr->registerAndUpdateSubscribedWebhooks();
        }
        if ($this->enabled === false || IS_ADMIN_FLAG === true || $loaderPrefix === 'webhook') {
            return;
        }

        // -----
        // If loaded in the presence of an order ...
        //
        if (is_object($order)) {
            // -----
            // Check whether/not this payment module is to be active for the current
            // payment zone and/or order-total-value limitations.
            //
            $this->update_status();
            if ($this->enabled === false) {
                return;
            }

            // -----
            // Check to make sure that the shipping/billing address countries are supported by PayPal.
            //
            // Note: The payment-module will remain enabled, with a customer message indicating
            // that the payment module cannot be used due to the currently-active address(es).
            //
            if (isset($order->billing['country'])) {
                $this->billingCountryIsSupported = (CountryCodes::ConvertCountryCode($order->billing['country']['iso_code_2']) !== '');
            }
            if ($_SESSION['cart']->get_content_type() !== 'virtual') {
                $this->shippingCountryIsSupported = (CountryCodes::ConvertCountryCode($order->delivery['country']['iso_code_2'] ?? '??') !== '');
            }
        }

        // -----
        // Still here?  Check to see if we're on the storefront's OPC confirmation page and 'watch'
        // for OPC's session-hash operation.
        //
        global $current_page_base;
        if (defined('FILENAME_CHECKOUT_ONE_CONFIRMATION') && $current_page_base === FILENAME_CHECKOUT_ONE_CONFIRMATION) {
            $this->onOpcConfirmationPage = true;
            $this->paypalRestfulSessionOnEntry = $_SESSION['PayPalRestful'] ?? [];
            $this->attach($this, ['NOTIFY_OPC_OBSERVER_SESSION_FIXUPS']);
        }

        // -----
        // NOTE: The checkout_process phase and zcAjaxPayment class instantiate the selected payment module **prior to**
        // instantiating the order-object.
        //
        // Determine the currency to be used to send the order to PayPal and whether it's usable.
        //
        $order_currency = $order->info['currency'] ?? $_SESSION['currency'] ?? DEFAULT_CURRENCY;
        $paypal_default_currency = (MODULE_PAYMENT_PAYPALR_CURRENCY === 'Selected Currency') ? $order_currency : str_replace('Only ', '', MODULE_PAYMENT_PAYPALR_CURRENCY);
        $amount = new Amount($paypal_default_currency);

        $paypal_currency = $amount->getDefaultCurrencyCode();
        if ($paypal_currency !== $order_currency) {
            $this->log->write("==> order_status: Paypal currency ($paypal_currency) different from order's ($order_currency); checking validity.");

            global $currencies;
            if (!isset($currencies)) {
                $currencies = new currencies();
            }
            if ($currencies->is_set($paypal_currency) === false) {
                $this->log->write('  --> Payment method disabled; Paypal currency is not configured.');
                $this->enabled = false;
                return;
            }
        }

        if (isset($order)) {
            $this->collectsCardDataOnsite = $this->cardsAccepted;
        } elseif (isset($_POST['ppr_type']) && $_POST['ppr_type'] === 'card') {
            $this->collectsCardDataOnsite = true;
        }
    }

    // -----
    // One-Page-Checkout's session-hash will have hissy-fits with the $_SESSION['PayPalRestful'] element, since
    // that's likely to change during the pre-confirmation step for the 'paypal' payment type.
    //
    // This method just replaces the 'after-hash' value of $_SESSION['PayPalRestful'] with the 'before-hash'
    // value; there's nothing in that element that affects the ability for the customer to 'pay now'.
    //
    public function updateNotifyOpcObserverSessionFixups(&$class, $eventID, $empty_string, &$session_data)
    {
        $session_data['PayPalRestful'] = $this->paypalRestfulSessionOnEntry;
        $this->paypalRestfulSessionOnEntry = [];
    }

    protected function alertMsg(string $msg)
    {
        return '<b class="text-danger">' . $msg . '</b>';
    }

    // -----
    // This method, called during admin processing, gives the payment module the opportunity
    // to make version-to-version configuration fix-ups.
    //
    protected function tableCheckup()
    {
        // -----
        // Remove any PayPal RESTful storefront logs that were created for v1.0.3 (202408).
        //
        if (defined('MODULE_PAYMENT_PAYPALR_VERSION') && version_compare(MODULE_PAYMENT_PAYPALR_VERSION, '1.0.2', '>') && version_compare(MODULE_PAYMENT_PAYPALR_VERSION, '1.0.4-beta3', '<')) {
            $logfiles = glob(DIR_FS_LOGS . '/paypalr-c-*-202408*.log');
            foreach ($logfiles as $next_log) {
                unlink($next_log);
            }
        }

        // -----
        // If the payment module is installed and at the current version, nothing to be done.
        //
        $current_version = self::CURRENT_VERSION;
        if (defined('MODULE_PAYMENT_PAYPALR_VERSION') && MODULE_PAYMENT_PAYPALR_VERSION === $current_version) {
            return;
        }

        global $db;

        // -----
        // Check for version-specific configuration updates.
        //
        if (defined('MODULE_PAYMENT_PAYPALR_VERSION')) {
            switch (true) {
                case version_compare(MODULE_PAYMENT_PAYPALR_VERSION, '1.1.1', '<'):
                    $db->Execute(
                        "UPDATE " . TABLE_CONFIGURATION . "
                            SET set_function = 'zen_cfg_select_option([\'Auth Only (All Txns)\', \'Final Sale\', \'Auth Only (Card-Only)\'] ,'
                          WHERE configuration_key = 'MODULE_PAYMENT_PAYPALR_TRANSACTION_MODE'
                          LIMIT 1"
                    );
                    $db->Execute(
                        "INSERT IGNORE INTO " . TABLE_CONFIGURATION . "
                            (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added)
                         VALUES
                            ('Trigger 3D Secure on <b>Every</b> Txn?', 'MODULE_PAYMENT_PAYPALR_SCA_ALWAYS', 'false', 'Choose <var>true</var> to trigger 3D Secure for <b>every</b> transaction, regardless of SCA requirements.<br><br><b>Default</b>: <var>false</var>', 6, 0, 'zen_cfg_select_option([\'true\', \'false\'], ', NULL, now())"
                    );

                /* falls through */
                case version_compare(MODULE_PAYMENT_PAYPALR_VERSION, '1.2.0', '<'): //- Fall through from above
                    $db->Execute(
                        "INSERT IGNORE INTO " . TABLE_CONFIGURATION . "
                            (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added)
                         VALUES
                            ('Store (Sub-Brand) Identifier at PayPal', 'MODULE_PAYMENT_PAYPALR_SOFT_DESCRIPTOR', '', 'On customer credit card statements, your company name will show as <code>PAYPAL*(yourname)*(your-sub-brand-name)</code> (max 22 letters for (yourname)*(your-sub-brand-name)). You can add the sub-brand-name here if you want to differentiate purchases from this store vs any other PayPal sales you make.', 6, 0, NULL, NULL, now())"
                    );

                /* falls through */
                case version_compare(MODULE_PAYMENT_PAYPALR_VERSION, '1.3.0', '<'): //- Fall through from above
                    $db->Execute(
                        "INSERT IGNORE INTO " . TABLE_CONFIGURATION . "
                            (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added)
                         VALUES
                            ('PayLater Messaging', 'MODULE_PAYMENT_PAYPALR_PAYLATER_MESSAGING', 'Checkout, Shopping Cart, Product Pages', 'On which pages should PayPal PayLater messaging be displayed? (It will automatically not be displayed in regions where it is not available. Only available in USD, GBP, EUR, AUD.) When enabled, it will show the lower installment-based pricing for the presented product or cart amount. This may accelerate buying decisions.<br>To disable, leave all unticked.', 6, 0, 'zen_cfg_select_multioption_pairs([\'Checkout\', \'Shopping Cart\', \'Product Pages\', \'Product Listings and Search Results\'], ', NULL, now())"
                    );
                    $db->Execute(
                        "UPDATE " . TABLE_CONFIGURATION . "
                            SET set_function = 'zen_cfg_select_multioption_pairs([\'Checkout\', \'Shopping Cart\', \'Product Pages\', \'Product Listings and Search Results\'], '
                          WHERE configuration_key = 'MODULE_PAYMENT_PAYPALR_PAYLATER_MESSAGING'
                          LIMIT 1"
                    );

                /* falls through */
                default:
                    break;
            }
        }

        // -----
        // Record the current version of the payment module into its database configuration setting.
        //
        $db->Execute(
            "UPDATE " . TABLE_CONFIGURATION . "
                SET configuration_value = '$current_version',
                    last_modified = now()
              WHERE configuration_key = 'MODULE_PAYMENT_PAYPALR_VERSION'
              LIMIT 1"
        );
    }

    protected function checkCardsAcceptedForSite(): bool
    {
        global $db;

        // -----
        // See which cards are enabled via Configuration :: Credit Cards.  The
        // SQL query is defined in /includes/classes/db/mysql/define_queries.php.
        //
        $cards_accepted_db = $db->Execute(SQL_CC_ENABLED);

        // -----
        // No cards enabled, no cards can be accepted.
        //
        if ($cards_accepted_db->EOF) {
            return false;
        }

        // -----
        // Loop through the cards accepted on the site, ensuring that at
        // least one supported by PayPal is enabled.
        //
        foreach ($cards_accepted_db as $next_card) {
            $next_card = str_replace('CC_ENABLED_', '', $next_card['configuration_key']);
            switch ($next_card) {
                case 'AMEX':
                    $this->cardImages['American Express'] = 'american_express.png';
                    break;
                case 'DISCOVER':
                    $this->cardImages['Discover'] = 'discover.png';
                    break;
                case 'JCB':
                    $this->cardImages['JCB'] = 'jcb.png';
                    break;
                case 'MAESTRO':
                    $this->cardImages['Maestro'] = 'maestro.png';
                    break;
                case 'MC':
                    $this->cardImages['MasterCard'] = 'mastercard.png';
                    break;
                case 'SOLO':
                    $this->cardImages['SOLO'] = 'solo.png';
                    break;
                case 'VISA':
                    $this->cardImages['VISA'] = 'visa.png';
                    break;
                default:
                    break;
            }
        }
        return (count($this->cardImages) !== 0);
    }

    // ----- Static function since used by the root-directory web-hook, too.
    public static function getEnvironmentInfo(): array
    {
        // -----
        // Determine and return which (live vs. sandbox) credentials are in use.
        //
        if (MODULE_PAYMENT_PAYPALR_SERVER === 'live') {
            $client_id = MODULE_PAYMENT_PAYPALR_CLIENTID_L;
            $secret = MODULE_PAYMENT_PAYPALR_SECRET_L;
        } else {
            $client_id = MODULE_PAYMENT_PAYPALR_CLIENTID_S;
            $secret = MODULE_PAYMENT_PAYPALR_SECRET_S;
        }

        return [
            $client_id,
            $secret,
        ];
    }

    // -----
    // Validate the configuration settings to ensure that the payment module
    // can be enabled for use.
    //
    // Side effects:
    //
    // - The payment module is auto-disabled if any configuration issues are found.
    //
    protected function validateConfiguration(bool $curl_installed): bool
    {
        // -----
        // No CURL, no payment module!  The PayPalRestApi class requires
        // CURL to 'do its business'.
        //
        if ($curl_installed === false) {
            $this->setConfigurationDisabled(MODULE_PAYMENT_PAYPALR_ERROR_NO_CURL);
            return false;
        }

        // -----
        // CURL installed, make sure that the configured credentials are valid ...
        //
        // Determine which (live vs. sandbox) credentials are in use.
        //
        list($client_id, $secret) = self::getEnvironmentInfo();

        // -----
        // Ensure that the current environment's credentials are set and, if so,
        // that they're valid PayPal credentials.
        //
        $error_message = '';
        if ($client_id === '' || $secret === '') {
            $error_message = sprintf(MODULE_PAYMENT_PAYPALR_ERROR_CREDS_NEEDED, MODULE_PAYMENT_PAYPALR_SERVER);
        } else {
            $this->ppr = new PayPalRestfulApi(MODULE_PAYMENT_PAYPALR_SERVER, $client_id, $secret);

            global $current_page;
            $use_saved_credentials = (IS_ADMIN_FLAG === false || $current_page === FILENAME_MODULES);
            $this->log->write("validateCredentials: Checking ($use_saved_credentials).", true, 'before');
            if ($this->ppr->validatePayPalCredentials($use_saved_credentials) === false) {
                $error_message = sprintf(MODULE_PAYMENT_PAYPALR_ERROR_INVALID_CREDS, MODULE_PAYMENT_PAYPALR_SERVER);
            }
            $this->log->write('', false, 'after');
        }

        // -----
        // Any credential errors detected, the payment module's auto-disabled.
        //
        if ($error_message !== '') {
            $this->setConfigurationDisabled($error_message);
            return false;
        }

        // -----
        // Got here?  The configuration's valid.
        //
        return true;
    }
    protected function setConfigurationDisabled(string $error_message)
    {
        global $db, $messageStack;

        trigger_error("Setting configuration disabled: $error_message", E_USER_WARNING);

        $db->Execute(
            "UPDATE " . TABLE_CONFIGURATION . "
                SET configuration_value = 'False'
              WHERE configuration_key = 'MODULE_PAYMENT_PAYPALR_STATUS'
              LIMIT 1"
        );

        $error_message .= MODULE_PAYMENT_PAYPALR_AUTO_DISABLED;
        if (IS_ADMIN_FLAG === true) {
            $messageStack->add_session($error_message, 'error');
            $this->description = $this->alertMsg($error_message) . '<br><br>' . $this->description;
        } else {
            $this->sendAlertEmail(MODULE_PAYMENT_PAYPALR_ALERT_SUBJECT_CONFIGURATION, $error_message, true);
        }
    }

    /**
     *  Sets payment module status based on zone restrictions etc
     */
    public function update_status()
    {
        global $order;

        // -----
        // NOTE: The zcAjaxPayment class instantiates the selected payment module **prior to**
        // instantiating the order-object. If an update_status request is received in the absence
        // of the order, the assumption made here is that we're running in that path and these
        // checks were previously made during the checkout's payment phase!
        //
        if (!isset($order)) {
            return;
        }

        if ($this->enabled === false) {
            return;
        }

        $order_total = $order->info['total'];
        if ($order_total == 0) {
            $this->enabled = false;
            $this->log->write("update_status: Module disabled because purchase amount is set to 0.00." . Logger::logJSON($order->info));
            return;
        }

        // module cannot be used for purchase > 1000000 JPY
        if ($order->info['currency'] === 'JPY' && (($order_total * $order->info['currency_value']) > 1000000)) {
            $this->enabled = false;
            $this->log->write("update_status: Module disabled because purchase price ($order_total) exceeds PayPal-imposed maximum limit of 1000000 JPY.");
            return;
        }

        if ($this->zone > 0 && isset($order->billing['country']['id'])) {
            global $db;

            $sql =
                "SELECT zone_id
                   FROM " . TABLE_ZONES_TO_GEO_ZONES . "
                  WHERE geo_zone_id = :zoneId
                    AND zone_country_id = :countryId
                  ORDER BY zone_id";
            $sql = $db->bindVars($sql, ':zoneId', $this->zone, 'integer');
            $sql = $db->bindVars($sql, ':countryId', $order->billing['country']['id'], 'integer');
            $check = $db->Execute($sql);
            $check_flag = false;
            foreach ($check as $next_zone) {
                if ($next_zone['zone_id'] < 1 || $next_zone['zone_id'] == $order->billing['zone_id']) {
                    $check_flag = true;
                    break;
                }
            }

            if ($check_flag === false) {
                $this->enabled = false;
                $this->log->write('update_status: Module disabled due to zone restriction. Billing address is not within the Payment Zone selected in the module settings.');
                return;
            }
        }
    }

    protected function resetOrder()
    {
        unset($_SESSION['PayPalRestful']['Order'], $_SESSION['PayPalRestful']['ppr_type']);
    }

    // --------------------------------------------
    // Issued during the "payment" phase of the checkout process.
    // --------------------------------------------

    /**
     * Validate the credit card information via javascript (Number, Owner, and CVV lengths),
     * if card payments are to be accepted.
     */
    public function javascript_validation(): string
    {
        if ($this->cardsAccepted === false) {
            return '';
        }

        return
            'if (payment_value == "' . $this->code . '") {' . "\n" .
                'if (document.checkout_payment.ppr_type.value === "card") {' . "\n" .
                    'var cc_owner = document.checkout_payment.paypalr_cc_owner.value;' . "\n" .
                    'var cc_number = document.checkout_payment.paypalr_cc_number.value;' . "\n" .
                    'var cc_cvv = document.checkout_payment.paypalr_cc_cvv.value;' . "\n" .
                    'if (cc_owner == "" || eval(cc_owner.length) < ' . CC_OWNER_MIN_LENGTH . ') {' . "\n" .
                        'error_message = error_message + "' . MODULE_PAYMENT_PAYPALR_TEXT_JS_CC_OWNER . '";' . "\n" .
                        'error = 1;' . "\n" .
                    '}' . "\n" .
                    'if (cc_number == "" || cc_number.length < ' . CC_NUMBER_MIN_LENGTH . ') {' . "\n" .
                        'error_message = error_message + "' . MODULE_PAYMENT_PAYPALR_TEXT_JS_CC_NUMBER . '";' . "\n" .
                        'error = 1;' . "\n" .
                    '}' . "\n" .
                    'if (cc_cvv == "" || cc_cvv.length < 3 || cc_cvv.length > 4) {' . "\n".
                        'error_message = error_message + "' . MODULE_PAYMENT_PAYPALR_TEXT_JS_CC_CVV . '";' . "\n" .
                        'error = 1;' . "\n" .
                    '}' . "\n" .
                '}' . "\n" .
            '}' . "\n";
    }

    /**
     * At this point (checkout_payment in the 3-page version), we've got all the information
     * required to "Create" the order at PayPal.  If the order can't be created, no selection
     * is rendered.
     */
    public function selection(): array
    {
        global $order, $zcDate;

        // -----
        // If we're back in the "payment" phase of checkout, make sure that any previous payment confirmation
        // for the PayPal payment 'source' is cleared so that the customer will need to (possibly again) validate
        // their payment means.
        //
        unset($_SESSION['PayPalRestful']['Order']['wallet_payment_confirmed']);

        // -----
        // Determine which color button to use.  The color-choice constants are defined in
        // /includes/languages/modules/payment{/YOUR_TEMPLATE}/lang.paypalr.php
        //
        $chosen_button_color = 'MODULE_PAYMENT_PAYPALR_BUTTON_IMG_' . MODULE_PAYMENT_PAYPALR_BUTTON_COLOR;
        $paypal_button = (defined($chosen_button_color)) ? constant($chosen_button_color) : MODULE_PAYMENT_PAYPALR_BUTTON_IMG_YELLOW;

        // -----
        // Create the default (PayPal only) selection.  This might be modified below to add a note
        // to the customer if either their shipping or billing address' country isn't supported by
        // PayPal.
        //
        $selection = [
            'id' => $this->code,
            'module' =>
                '<img src="' . $paypal_button . '" alt="' . MODULE_PAYMENT_PAYPALR_BUTTON_ALTTEXT . '" title="' . MODULE_PAYMENT_PAYPALR_BUTTON_ALTTEXT . '">' .
                zen_draw_hidden_field('ppr_type', 'paypal'),
        ];

        // -----
        // Return **only** the PayPal selection as a button, if cards aren't to be accepted. If the customer is
        // shipping to a country unsupported by PayPal, add some jQuery to disable the associated payment-module
        // selection and display a note to the customer.
        //
        if ($this->cardsAccepted === false || $this->shippingCountryIsSupported === false) {
            if ($this->shippingCountryIsSupported === false) {
                $selection['fields'] = [
                    [
                        'title' => '<b>' . MODULE_PAYMENT_PAYPALR_TEXT_PLEASE_NOTE . '</b>',
                        'field' =>
                            '<script>' . file_get_contents(DIR_WS_MODULES . 'payment/paypal/PayPalRestful/jquery.paypalr.disable.js') . '</script>' .
                            '<small>' . MODULE_PAYMENT_PAYPALR_UNSUPPORTED_SHIPPING_COUNTRY . '</small>',
                        ],
                ];
            }
            return $selection;
        }

        // -----
        // If cards *can* be selected, but the billing country isn't supported by PayPal,
        // add a 'field' to the PayPal Checkout payment's display, noting the condition.
        //
        if ($this->billingCountryIsSupported === false) {
            $selection['fields'] = [
                [
                    'title' => '<b>' . MODULE_PAYMENT_PAYPALR_TEXT_PLEASE_NOTE . '</b>',
                    'field' =>
                        '<small>' . MODULE_PAYMENT_PAYPALR_UNSUPPORTED_BILLING_COUNTRY . '</small>',
                    ],
            ];
            return $selection;
        }

        // -----
        // Create dropdowns for the credit-card's expiry year and month
        //
        $expires_month = [];
        $expires_year = [];
        for ($month = 1; $month < 13; $month++) {
            $expires_month[] = ['id' => sprintf('%02u', $month), 'text' => $zcDate->output('%B - (%m)', mktime(0, 0, 0, $month, 1))];
        }
        $this_year = date('Y');
        for ($year = $this_year; $year < (int)$this_year + 15; $year++) {
            $expires_year[] = ['id' => $year, 'text' => $year];
        }

        // -----
        // Determine which of the payment methods is currently selected, noting
        // that if this module is not the one currently selected that the
        // customer needs to choose one to select the payment module!
        //
        $selected_payment_module = $_SESSION['payment'] ?? '';
        if ($selected_payment_module !== $this->code) {
            $paypal_selected = false;
            $card_selected = false;
            $this->resetOrder();
        } else {
            $ppr_type = $_SESSION['PayPalRestful']['ppr_type'] ?? 'paypal';
            $paypal_selected = ($ppr_type === 'paypal');
            $card_selected = !$paypal_selected;
        }

        // -----
        // If the site's active template has overridden the styling for the button-choices,
        // load that version instead of the default.
        //
        // Note: CSS 'inspired' by: https://codepen.io/phusum/pen/VQrQqy
        //
        global $template, $current_page_base;

        $is_bootstrap_template = (function_exists('zca_bootstrap_active') && zca_bootstrap_active() === true);
        $css_file = ($is_bootstrap_template === true) ? 'paypalr_bootstrap.css' : 'paypalr.css';
        $css_file_name = $template->get_template_dir("^$css_file", DIR_WS_TEMPLATE, $current_page_base, 'css') . '/' . $css_file;
        if (!file_exists($css_file_name)) {
            $css_file_name = DIR_WS_MODULES . 'payment/paypal/PayPalRestful/' . $css_file;
        }

        // -----
        // Set 'id' attribute values, shared between input/label for credit-card entry
        // fields.
        //
        $ppr_cc_owner_id = $this->code . '-cc-owner';
        $ppr_cc_number_id = $this->code . '-cc-number';
        $ppr_cc_expires_year_id = $this->code . '-cc-expires-year';
        $ppr_cc_expires_month_id = $this->code . '-cc-expires-month';
        $ppr_cc_cvv_id = $this->code . '-cc-cvv';

        $billing_name = zen_output_string_protected($order->billing['firstname'] . ' ' . $order->billing['lastname']);
        $selection = [
            'id' => $this->code,
            'module' => MODULE_PAYMENT_PAYPALR_TEXT_TITLE . ' <span id="ppr-subtitle" class="small">' . MODULE_PAYMENT_PAYPALR_SUBTITLE . '</span>',
            'fields' => [
                [
                    'title' =>
                        '<style nonce="">' . file_get_contents($css_file_name) . '</style>' .
                        '<span class="ppr-choice-label">' . MODULE_PAYMENT_PAYPALR_CHOOSE_PAYPAL . '</span>',
                    'field' =>
                        '<div id="paypal-message-container"></div>' .
                        '<div id="ppr-choice-paypal" class="ppr-button-choice">' .
                            zen_draw_radio_field('ppr_type', 'paypal', $paypal_selected, 'id="ppr-paypal" class="ppr-choice"') .
                            '<label for="ppr-paypal" class="ppr-choice-label">' .
                                '<img src="' . $paypal_button . '" alt="' . MODULE_PAYMENT_PAYPALR_BUTTON_ALTTEXT . '" title="' . MODULE_PAYMENT_PAYPALR_BUTTON_ALTTEXT . '">' .
                            '</label>' .
                        '</div>',
                    'tag' => 'ppr-paypal',
                ],
                [
                    'title' => '<span class="ppr-choice-label">' . MODULE_PAYMENT_PALPALR_CHOOSE_CARD . '</span>' ,
                    'field' =>
                        '<script>' . file_get_contents(DIR_WS_MODULES . 'payment/paypal/PayPalRestful/jquery.paypalr.checkout.js') . '</script>' .
                        '<div id="ppr-choice-card" class="ppr-button-choice">' .
                            zen_draw_radio_field('ppr_type', 'card', $card_selected, 'id="ppr-card" class="ppr-choice"') .
                            '<label for="ppr-card" class="ppr-choice-label">' .
                                $this->buildCardsAccepted() .
                            '</label>' .
                        '</div>',
                    'tag' => 'ppr-card',
                ],
                [
                    'title' => MODULE_PAYMENT_PAYPALR_CC_OWNER,
                    'field' =>
                        zen_draw_input_field('paypalr_cc_owner', $billing_name, 'class="ppr-cc" id="' . $ppr_cc_owner_id . '" autocomplete="off"'),
                    'tag' => $ppr_cc_owner_id,
                ],
                [
                    'title' => MODULE_PAYMENT_PAYPALR_CC_NUMBER,
                    'field' => zen_draw_input_field('paypalr_cc_number', '', 'class="ppr-cc" id="' . $ppr_cc_number_id . '" autocomplete="off"'),
                    'tag' => $ppr_cc_number_id,
                ],
                [
                    'title' => MODULE_PAYMENT_PAYPALR_CC_EXPIRES,
                    'field' =>
                        zen_draw_pull_down_menu('paypalr_cc_expires_month', $expires_month, $zcDate->output('%m'), 'class="ppr-cc" id="' . $ppr_cc_expires_month_id . '"') .
                        '&nbsp;' .
                        zen_draw_pull_down_menu('paypalr_cc_expires_year', $expires_year, $this_year, 'class="ppr-cc" id="' . $ppr_cc_expires_year_id . '"'),
                    'tag' => $ppr_cc_expires_month_id,
                ],
                [
                    'title' => MODULE_PAYMENT_PAYPALR_CC_CVV,
                    'field' => zen_draw_input_field('paypalr_cc_cvv', '', 'class="ppr-cc" id="' . $ppr_cc_cvv_id . '" autocomplete="off"'),
                    'tag' => $ppr_cc_cvv_id,
                ],
                [
                    'title' => '&nbsp;',
                    'field' =>
                        '<small class="ppr-cc">' .
                            sprintf(MODULE_PAYMENT_PAYPALR_CARD_PROCESSING, '<a href="' . MODULE_PAYMENT_PAYPALR_PAYPAL_PRIVACY_LINK . '" target="_blank">' . MODULE_PAYMENT_PAYPALR_PAYPAL_PRIVACY_STMT . '</a>') .
                        '</small>',
                ],
            ],
        ];

        // -----
        // If running in the sandbox environment, add a checkbox input to enable SCA
        // testing.
        //
        if (MODULE_PAYMENT_PAYPALR_SERVER === 'sandbox') {
            if ($is_bootstrap_template === false) {
                $selection['fields'][] = [
                    'title' => 'Enable SCA Always?',
                    'field' => zen_draw_checkbox_field('paypalr_cc_sca_always', 'on', false, 'class="ppr-cc" id="ppr-cc-sca-always"'),
                    'tag' => 'ppr-cc-sca-always',
                ];
            } else {
                $selection['fields'][] = [
                    'title' => '&nbsp;',
                    'field' =>
                        '<div class="custom-control custom-checkbox ppr-cc">' .
                            zen_draw_checkbox_field('paypalr_cc_sca_always', 'on', false, 'id="ppr-cc-sca-always"') .
                            '<label class="custom-control-label checkboxLabel" for="ppr-cc-sca-always">Enable SCA Always</label>' .
                        '</div>',
                ];
            }
        }

        return $selection;
    }
    protected function buildCardsAccepted(): string
    {
        $cards_accepted = '';
        $card_image_directory = DIR_WS_MODULES . 'payment/paypal/PayPalRestful/images/';
        foreach ($this->cardImages as $card_name => $card_image) {
            $cards_accepted .= '<img src="' . $card_image_directory . $card_image . '" alt="' . $card_name . '" title="' . $card_name . '"> ';
        }
        return $cards_accepted;
    }

    // --------------------------------------------
    // Issued during the "confirmation" phase of the checkout process, called
    // here IFF this payment module was selected during the "payment" phase!
    // --------------------------------------------

    public function pre_confirmation_check()
    {
        $this->log->write("pre_confirmation_check starts ...\n", true, 'before');

        // -----
        // If some other processing has set a message for 'checkout_payment', there's
        // no need/sense in continuing since page-based processing will be redirecting
        // back to the checkout's payment phase.
        //
        // This can happen, for instance, if the customer sets a coupon, selects paypalr
        // and continues to the checkout phase all together.
        //
        global $messageStack;
        if ($messageStack->size('checkout_payment') > 0) {
            return;
        }

        // -----
        // If the PayPal Checkout payment-type isn't included in the posted data,
        // send the customer back to the payment phase of checkout to ensure that
        // the selection is made.
        //
        // Noting that this "shouldn't" happen unless someone's fussing with the form,
        // so no message!
        //
        if (!isset($_POST['ppr_type']) || !in_array($_POST['ppr_type'], ['paypal', 'card'], true)) {
            zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
        }

        // -----
        // If a card-payment was selected, validate the information entered.  Any
        // errors detected, the called method will have set the appropriate messages
        // on the messageStack.
        //
        $ppr_type = $_POST['ppr_type'];
        $_SESSION['PayPalRestful']['ppr_type'] = $ppr_type;

        if ($ppr_type === 'card' && $this->validateCardInformation(true) === false) {
            $log_only = true;
            $this->setMessageAndRedirect("pre_confirmation_check, card failed initial validation.", FILENAME_CHECKOUT_PAYMENT, $log_only);
        }

        // -----
        // Build the *inital* request for the PayPal order's "Create" and send to off to PayPal.
        //
        // This extra step for credit-card payments ensures that the order's content is acceptable
        // to PayPal (e.g. the order-totals sum up properly), so that corrective action can be taken
        // during this checkout phase rather than having a PayPal 'unacceptance' count against the
        // customer as a slamming count.
        //
        $paypal_order_created = $this->createPayPalOrder('paypal');
        if ($paypal_order_created === false) {
            $error_info = $this->ppr->getErrorInfo();
            $error_code = $error_info['details'][0]['issue'] ?? 'OTHER';
            $this->sendAlertEmail(MODULE_PAYMENT_PAYPALR_ALERT_SUBJECT_ORDER_ATTN, MODULE_PAYMENT_PAYPALR_ALERT_ORDER_CREATE . Logger::logJSON($error_info));
            $this->setMessageAndRedirect(sprintf(MODULE_PAYMENT_PAYPALR_TEXT_CREATE_ORDER_ISSUE, MODULE_PAYMENT_PAYPALR_TEXT_TITLE, $error_code), FILENAME_CHECKOUT_PAYMENT);
        }

        // -----
        // If the payment is *not* to be processed by PayPal wallet (type='paypal') (e.g. a 'card' payment) or if
        // the customer has already confirmed their payment choice at PayPal, nothing further to do
        // at this time.
        //
        // Note: The 'wallet_payment_confirmed' element of the payment-module's session-based order is set by the
        // ppr_listener.php processing when the customer returns from selecting a payment means for
        // the 'paypal' payment-source.
        //
        if ($ppr_type !== 'paypal' || isset($_SESSION['PayPalRestful']['Order']['wallet_payment_confirmed'])) {
            $_SESSION['PayPalRestful']['Order']['payment_source'] = $ppr_type;
            $this->log->write("pre_confirmation_check, completed for payment-source $ppr_type.", true, 'after');
            return;
        }

        // -----
        // The payment is to be processed by PayPal wallet (type='paypal'), send the customer off to
        // PayPal to confirm their payment source.  That'll either come back to the checkout_confirmation
        // page (via the payment module's ppr_listener) if they choose a payment means
        // or back to the checkout_payment page if they cancelled-out from PayPal.
        //
        global $order;
        $confirm_payment_choice_request = new ConfirmPayPalPaymentChoiceRequest(self::REDIRECT_LISTENER, $order);
        $payment_choice_response = $this->ppr->confirmPaymentSource($_SESSION['PayPalRestful']['Order']['id'], $confirm_payment_choice_request->get());
        if ($payment_choice_response === false || $payment_choice_response['status'] !== PayPalRestfulApi::STATUS_PAYER_ACTION_REQUIRED) {
            $this->sendAlertEmail(
                MODULE_PAYMENT_PAYPALR_ALERT_SUBJECT_CONFIRMATION_ERROR,
                MODULE_PAYMENT_PAYPALR_ALERT_CONFIRMATION_ERROR . "\n" . Logger::logJSON($payment_choice_response) . "\n" . Logger::logJSON($this->ppr->getErrorInfo())
            );
            $this->setMessageAndRedirect(sprintf(MODULE_PAYMENT_PAYPALR_TEXT_GENERAL_ERROR, MODULE_PAYMENT_PAYPALR_TEXT_TITLE), FILENAME_CHECKOUT_PAYMENT);
        }

        // -----
        // Locate the URL to which the customer is redirected at PayPal
        // to confirm their payment choice.
        //
        $action_link = '';
        foreach ($payment_choice_response['links'] as $next_link) {
            if ($next_link['rel'] === 'payer-action') {
                $action_link = $next_link['href'];
                break;
            }
        }
        if ($action_link === '') {
            $this->sendAlertEmail(
                MODULE_PAYMENT_PAYPALR_ALERT_SUBJECT_CONFIRMATION_ERROR,
                MODULE_PAYMENT_PAYPALR_ALERT_CONFIRMATION_ERROR . "\n" . Logger::logJSON($payment_choice_response)
            );
            $this->setMessageAndRedirect(sprintf(MODULE_PAYMENT_PAYPALR_TEXT_GENERAL_ERROR, MODULE_PAYMENT_PAYPALR_TEXT_TITLE), FILENAME_CHECKOUT_PAYMENT);
        }

        // -----
        // Save the posted variables from the payment phase of checkout; the ppr_listener will use those to restore after
        // PayPal returns.
        //
        global $current_page_base;
        $_SESSION['PayPalRestful']['Order']['PayerAction'] = [
            'current_page_base' => $current_page_base,
            'savedPosts' => $_POST,
        ];
        $this->log->write('pre_confirmation_check, sending the payer-action off to PayPal.', true, 'after');
        zen_redirect($action_link);
    }
    protected function validateCardInformation(bool $is_preconfirmation): bool
    {
        global $messageStack, $order;

        $postvar_prefix = ($is_preconfirmation === true) ? 'paypalr' : 'ppr';
        require DIR_WS_CLASSES . 'cc_validation.php';
        $cc_validation = new cc_validation();
        $result = $cc_validation->validate(
            $_POST[$postvar_prefix . '_cc_number'] ?? '',
            $_POST[$postvar_prefix . '_cc_expires_month'] ?? '',
            $_POST[$postvar_prefix . '_cc_expires_year'] ?? ''
        );
        switch ((int)$result) {
            case -1:
                $error = MODULE_PAYMENT_PAYPALR_TEXT_BAD_CARD;
                if (empty($_POST[$postvar_prefix . '_cc_number'])) {
                    $error = trim(MODULE_PAYMENT_PAYPALR_TEXT_JS_CC_NUMBER, '* \\n');
                }
                break;
            case -2:
            case -3:
            case -4:
                $error = TEXT_CCVAL_ERROR_INVALID_DATE;
                break;
            case 0:
                $error = TEXT_CCVAL_ERROR_INVALID_NUMBER;
                break;
            default:
                $error = '';
                break;
        }
        if ($error !== '') {
            $messageStack->add_session('checkout_payment', $error, 'error');
            return false;
        }

        $cvv_posted = $_POST[$postvar_prefix . '_cc_cvv'] ?? '';
        $cvv_required_length = ($cc_validation->cc_type === 'American Express') ? 4 : 3;
        if (!ctype_digit($cvv_posted) || strlen($cvv_posted) !== $cvv_required_length) {
            $messageStack->add_session('checkout_payment', sprintf(MODULE_PAYMENT_PAYPALR_TEXT_CVV_LENGTH, $cc_validation->cc_type, substr($cc_validation->cc_number, -4), $cvv_required_length), 'error');
            return false;
        }

        $cc_owner = $_POST[$postvar_prefix . '_cc_owner'] ?? '';
        if (strlen($cc_owner) < CC_OWNER_MIN_LENGTH) {
            $messageStack->add_session('checkout_payment', trim(MODULE_PAYMENT_PAYPALR_TEXT_JS_CC_OWNER, '* \\n'), 'error');
            return false;
        }

        $this->ccInfo = [
            'type' => $cc_validation->cc_type,
            'number' => $cc_validation->cc_number,
            'expiry_month' => $cc_validation->cc_expiry_month,
            'expiry_year' => $cc_validation->cc_expiry_year,
            'name' => $cc_owner,
            'security_code' => $cvv_posted,
            'redirect' => self::REDIRECT_LISTENER,
        ];
        return true;
    }
    protected function createPayPalOrder(string $ppr_type): bool
    {
        // -----
        // If the required notifications in the order_total.php class haven't been applied, the
        // order's amount-breakdown can't be properly formed.  This method checks that and
        // either kicks the customer back to the payment phase or returns the $order_info
        // that the observer has collected.
        //
        $order_info = $this->getOrderTotalsInfo();

        // -----
        // Create a GUID (Globally Unique IDentifier) for the order's
        // current 'state'.
        //
        global $order;
        $order_guid = $this->createOrderGuid($order, $ppr_type);

        // -----
        // If the PayPal order been previously created and the order's GUID
        // has not changed, the original PayPal order information can
        // be safely used.
        //
        if (isset($_SESSION['PayPalRestful']['Order']['guid']) && $_SESSION['PayPalRestful']['Order']['guid'] === $order_guid) {
            $this->log->write("\ncreatePayPalOrder($ppr_type), no change in order GUID ($order_guid); nothing further to do.\n");
            return true;
        }

        // -----
        // Build the request for the PayPal order's initial creation.
        //
        global $zcObserverPaypalrestful;
        $create_order_request = new CreatePayPalOrderRequest($ppr_type, $order, $this->ccInfo, $order_info, $zcObserverPaypalrestful->getOrderTotalChanges());

        // -----
        // If the order's request-creation resulted in a calculation mismatch,
        // send an alert if configured.
        //
        $order_amount_mismatch = $create_order_request->getBreakdownMismatch();
        if (count($order_amount_mismatch) !== 0) {
            $this->sendAlertEmail(
                MODULE_PAYMENT_PAYPALR_ALERT_SUBJECT_TOTAL_MISMATCH,
                MODULE_PAYMENT_PAYPALR_ALERT_TOTAL_MISMATCH . "\n\n" . Logger::logJSON($order_amount_mismatch)
            );
        }

        // -----
        // Send the request off to register the order at PayPal.
        //
        $this->ppr->setPayPalRequestId($order_guid);
        $order_request = $create_order_request->get();
        $order_response = $this->ppr->createOrder($order_request);
        if ($order_response === false) {
            $this->errorInfo->copyErrorInfo($this->ppr->getErrorInfo());
            return false;
        }

        // -----
        // Save the created PayPal order in the session and indicate that the
        // operation was successful.
        //
        $paypal_id = $order_response['id'];
        $status = $order_response['status'];
        unset(
            $order_response['id'],
            $order_response['status'],
            $order_response['create_time'],
            $order_response['links'],
            $order_response['purchase_units'][0]['reference_id'],
            $order_response['purchase_units'][0]['payee']
        );
        $_SESSION['PayPalRestful']['Order'] = [
            'current' => $order_response,
            'id' => $paypal_id,
            'status' => $status,
            'guid' => $order_guid,
            'payment_source' => $ppr_type,
            'amount_mismatch' => $order_amount_mismatch,
        ];
        return true;
    }

    // -----
    // A multi-use function that gathers and validates the payment-module's observer's results.
    //
    // If the observer's results are an empty array, the implication is that the required
    // notifications haven't been added to the order_total.php class; the payment module cannot
    // proceed with its order amounts' calculations without that information.  The customer
    // is redirected back to the payment phase of the checkout process and a *forced* admin
    // alert email is issued to notify the store owner/admin of the condition.
    //
    // Otherwise, the observer's order-total additions to the order are returned to the caller.
    //
    protected function getOrderTotalsInfo(): array
    {
        // -----
        // If the required notifications in the order_total.php class haven't been applied, the
        // order's amount-breakdown can't be properly formed.
        //
        // Force the module's status to disabled and kick the customer back to the payment phase of the checkout process.
        //
        /** @var zcObserverPaypalrestful $zcObserverPaypalrestful */
        global $zcObserverPaypalrestful;
        $order_info = $zcObserverPaypalrestful->getLastOrderValues();
        if (count($order_info) === 0) {
            $this->setConfigurationDisabled(MODULE_PAYMENT_PAYPALR_ALERT_MISSING_NOTIFICATIONS);
            $this->setMessageAndRedirect(sprintf(MODULE_PAYMENT_PAYPALR_TEXT_NOTIFICATION_MISSING, MODULE_PAYMENT_PAYPALR_TEXT_TITLE), FILENAME_CHECKOUT_PAYMENT);
        }
        $order_info['free_shipping_coupon'] = $zcObserverPaypalrestful->orderHasFreeShippingCoupon();

        return $order_info;
    }

    // -----
    // Create an idempotent GUID to accompany the to-be-created PayPal order by
    // hashing the base order's information and, if paying via card, the card
    // information as well.
    //
    // Note: Including the transaction-mode (AUTHORIZE vs. CAPTURE), too ... just
    // in case the site changes that mode while a customer's order is in-progress.
    //
    protected function createOrderGuid(\order $order, string $ppr_type): string
    {
        $_SESSION['PayPalRestful']['CompletedOrders'] = $_SESSION['PayPalRestful']['CompletedOrders'] ?? 0;
        unset($order->info['ip_address']);
        $hash_data = MODULE_PAYMENT_PAYPALR_TRANSACTION_MODE . json_encode($order) . $_SESSION['securityToken'] . $_SESSION['PayPalRestful']['CompletedOrders'];
        if ($ppr_type !== 'paypal') {
            $hash_data .= json_encode($this->ccInfo);
        }
        $hash = hash('sha256', $hash_data);
        return
            substr($hash,  0,  8) . '-' .
            substr($hash,  8,  4) . '-' .
            substr($hash, 12,  4) . '-' .
            substr($hash, 16,  4) . '-' .
            substr($hash, 20, 12);
    }

    /**
     * Display additional payment information for review on the 'checkout_confirmation' page.
     *
     * Issued by the page's template-rendering.
     */
    public function confirmation()
    {
        if ($_SESSION['PayPalRestful']['Order']['payment_source'] !== 'card') {
            return [
                'title' => MODULE_PAYMENT_PALPALR_PAYING_WITH_PAYPAL,
            ];
        }

        global $zcDate;
        return [
            'title' => '',
            'fields' => [
                ['title' => MODULE_PAYMENT_PAYPALR_CC_OWNER, 'field' => '&nbsp;' . $_POST['paypalr_cc_owner']],
                ['title' => MODULE_PAYMENT_PAYPALR_CC_TYPE, 'field' => '&nbsp;' . $this->ccInfo['type']],
                ['title' => MODULE_PAYMENT_PAYPALR_CC_NUMBER, 'field' => '&nbsp;' . $this->obfuscateCcNumber($_POST['paypalr_cc_number'])],
                [
                    'title' => MODULE_PAYMENT_PAYPALR_CC_EXPIRES,
                    'field' => '&nbsp;' . $zcDate->output('%B, ', mktime(0, 0, 0, (int)$_POST['paypalr_cc_expires_month'], 1)) . $_POST['paypalr_cc_expires_year'],
                ],
            ],
        ];
    }

    /**
     * Prepare the hidden fields comprising the parameters for the Submit button on the checkout confirmation page.
     *
     * These values, for a credit-card, are subsequently passed to the 'process' phase of the checkout process.
     */
    public function process_button()
    {
        if ($_SESSION['PayPalRestful']['Order']['payment_source'] !== 'card') {
            return false;
        }

        $hidden_fields =
            zen_draw_hidden_field('ppr_cc_owner', $_POST['paypalr_cc_owner']) . "\n" .
            zen_draw_hidden_field('ppr_cc_expires_month', $_POST['paypalr_cc_expires_month']) . "\n" .
            zen_draw_hidden_field('ppr_cc_expires_year', $_POST['paypalr_cc_expires_year']) . "\n" .
            zen_draw_hidden_field('ppr_cc_number', $_POST['paypalr_cc_number']) . "\n" .
            zen_draw_hidden_field('ppr_cc_cvv', $_POST['paypalr_cc_cvv']) . "\n";
        if (isset($_POST['paypalr_cc_sca_always'])) {
            $hidden_fields .= zen_draw_hidden_field('ppr_cc_sca_always', $_POST['paypalr_cc_sca_always']);
        }
        return $hidden_fields;
    }
    public function process_button_ajax()
    {
        if ($_SESSION['PayPalRestful']['Order']['payment_source'] !== 'card') {
            return false;
        }

        $ccFields = [
            'ccFields' => [
                'ppr_cc_owner' => 'paypalr_cc_owner',
                'ppr_cc_expires_month' => 'paypalr_cc_expires_month',
                'ppr_cc_expires_year' => 'paypalr_cc_expires_year',
                'ppr_cc_number' => 'paypalr_cc_number',
                'ppr_cc_cvv' => 'paypalr_cc_cvv',
            ],
        ];
        if (isset($_POST['paypalr_cc_sca_always'])) {
            $ccFields['ccFields']['ppr_cc_sca_always'] = 'paypalr_cc_sca_always';
        }
        return $ccFields;
    }

    /**
     * Determine whether the shipping-edit button should be displayed or not (also used on the
     * "shipping" phase of the checkout process).
     *
     * For now, the shipping address entered during the Zen Cart checkout process *cannot* be changed.
     */
    public function alterShippingEditButton()
    {
        return false;
    }

    // --------------------------------------------
    // Issued during the "process" phase of the checkout process.
    // --------------------------------------------

    /**
     * Issued by the checkout_process page's header_php.php when a change
     * in the cart's contents are detected, given a payment module the
     * opportunity to reset any related variables.
     */
    public function clear_payments()
    {
        $this->resetOrder();
    }

    /**
     * Prepare and submit the final authorization to PayPal via the appropriate means as configured.
     * Issued close to the start of the 'checkout_process' phase.
     *
     * Note: Any failure here bumps the checkout_process page's slamming_count.
     */
    public function before_process()
    {
        global $order;

        // -----
        // If the required notifications in the order_total.php class haven't been applied, the
        // order's amount-breakdown can't be properly formed.  This method checks that and
        // either kicks the customer back to the payment phase or returns the $order_info
        // that the observer has collected.
        //
        $order_info = $this->getOrderTotalsInfo();

        // -----
        // Initially a successful payment is not 'pending'.  This might be changed by
        // the checkCardPaymentResponse method.
        //
        $this->paymentIsPending = false;

        // -----
        // Determine the 'payment_source' for the order (either 'paypal' or 'card'). If
        // the customer's paying with a card, call an additional method to deal with
        // those complications.
        //
        // Note: That method won't return here if an issue was found with the
        // credit-card or if a 3DS verification is required.
        //
        $payment_source = $_SESSION['PayPalRestful']['Order']['payment_source'];
        if ($payment_source === 'card') {
            $response = $this->createCreditCardOrder($order, $order_info);

        // -----
        // Otherwise, the customer's paying with their PayPal Wallet.
        //
        } else {
            if (!isset($_SESSION['PayPalRestful']['Order']['status']) || $_SESSION['PayPalRestful']['Order']['status'] !== PayPalRestfulApi::STATUS_APPROVED) {
                $this->log->write('paypalr::before_process, cannot capture/authorize paypal order; wrong status' . "\n" . Logger::logJSON($_SESSION['PayPalRestful']['Order'] ?? []));
                unset($_SESSION['PayPalRestful']['Order'], $_SESSION['payment']);
                $this->setMessageAndRedirect(MODULE_PAYMENT_PAYPALR_TEXT_STATUS_MISMATCH . "\n" . MODULE_PAYMENT_PAYPALR_TEXT_TRY_AGAIN, FILENAME_CHECKOUT_PAYMENT);
            }
            $response = $this->captureOrAuthorizePayment('paypal');
        }

        // -----
        // If we've gotten this far, the order has been created at PayPal.  Save
        // the pertinent information in the session and, for use by the 'after_order_create'
        // method, in a class property.
        //
        $_SESSION['PayPalRestful']['Order']['status'] = $response['status'];
        unset($response['links']);
        $this->orderInfo = $response;

        // -----
        // If the payment is pending (e.g. under-review), set the payment module's order
        // status to indicate as such.
        //
        // Note: Saving this updated status in the order itself, since the process-phase
        // processing prior to zc200 doesn't recognize that the status could have been
        // changed from that at the start of the process-phase.
        //
        if ($this->paymentIsPending === true) {
            $pending_status = (int)MODULE_PAYMENT_PAYPALR_HELD_STATUS_ID;
            if ($pending_status > 0) {
                $this->order_status = $pending_status;
                $order->info['order_status'] = $pending_status;
            }
        }

        // -----
        // Determine the payment's status to be recorded in the paypal table and to accompany the
        // additional order-status-history record to be created by the after_process method.
        //
        $txn_type = $this->orderInfo['intent'];
        $payment = $this->orderInfo['purchase_units'][0]['payments']['captures'][0] ?? $this->orderInfo['purchase_units'][0]['payments']['authorizations'][0];
        $payment_status = ($payment['status'] !== PayPalRestfulApi::STATUS_COMPLETED) ? $payment['status'] : (($txn_type === 'CAPTURE') ? PayPalRestfulApi::STATUS_CAPTURED : PayPalRestfulApi::STATUS_APPROVED);

        $this->orderInfo['payment_status'] = $payment_status;
        $this->orderInfo['paypal_payment_status'] = $payment['status'];
        $this->orderInfo['txn_type'] = $txn_type;

        // -----
        // If an expiration is present (e.g. this is a payment authorization), record the expiration
        // time for follow-on recording in the database.
        //
        $this->orderInfo['expiration_time'] = $payment['expiration_time'] ?? null;

        // -----
        // If the order's PayPal status doesn't indicate successful completion, ensure that
        // the overall order's status is set to this payment-module's PENDING status and set
        // a processing flag so that the after_process method will alert the store admin if
        // configured.
        //
        // Setting the order's overall status here, since zc158a and earlier don't acknowlege
        // a payment-module's change in status during the payment processing!
        //
        $this->orderInfo['admin_alert_needed'] = false;
        if ($payment_status !== PayPalRestfulApi::STATUS_CAPTURED && $payment_status !== PayPalRestfulApi::STATUS_CREATED) {
            global $order;

            $this->order_status = (int)MODULE_PAYMENT_PAYPALR_ORDER_PENDING_STATUS_ID;
            $order->info['order_status'] = $this->order_status;
            $this->orderInfo['admin_alert_needed'] = true;

            $this->log->write("==> paypalr::before_process ($payment_source): Payment status {$payment['status']} received from PayPal; order's status forced to pending.");
        }

        $this->notify('NOTIFY_PAYPALR_BEFORE_PROCESS_FINISHED', $this->orderInfo);
    }
    protected function captureOrAuthorizePayment(string $payment_source): array
    {
        $paypal_id = $_SESSION['PayPalRestful']['Order']['id'];
        $this->ppr->setPayPalRequestId($_SESSION['PayPalRestful']['Order']['guid']);
        if (MODULE_PAYMENT_PAYPALR_TRANSACTION_MODE === 'Final Sale' || ($payment_source !== 'card' && MODULE_PAYMENT_PAYPALR_TRANSACTION_MODE === 'Auth Only (Card-Only)')) {
            $response = $this->ppr->captureOrder($paypal_id);
        } else {
            $response = $this->ppr->authorizeOrder($paypal_id);
        }

        if ($response === false) {
            $this->setMessageAndRedirect(sprintf(MODULE_PAYMENT_PAYPALR_TEXT_GENERAL_ERROR, MODULE_PAYMENT_PAYPALR_TEXT_TITLE), FILENAME_CHECKOUT_PAYMENT);
        }

        if ($payment_source !== 'card') {
            return $response;
        }

        // -----
        // For a card payment-source, we've just come back from a 3DS authorization
        // and the response from PayPal needs to be checked to see if the card's
        // OK.  If not, set the appropriate message for the customer and redirect
        // back to the payment phase of checkout.
        //
        $card_message = $this->checkCardPaymentResponse($response);
        if ($card_message !== '') {
            $this->setMessageAndRedirect($card_message, FILENAME_CHECKOUT_PAYMENT);
        }

        return $response;
    }
    protected function createCreditCardOrder(\order $order, array $order_info): array
    {
        // -----
        // If we came back from a 3DS response (as set by ppr_listener.php), check
        // that response, redirecting back to the payment phase of checkout if an issue
        // was reported.  Otherwise, the payment is captured or authorized (depending on
        // the site's configuration) and the response from that action is returned to
        // the caller.
        //
        if (isset($_SESSION['PayPalRestful']['Order']['3DS_response'])) {
            // -----
            // Save the pertinent credit-card information into the order so that it'll be
            // recorded as part of the order.
            //
            $cc_info = $_SESSION['PayPalRestful']['Order']['3DS_response'];
            $order->info['cc_type'] = $cc_info['cc_type'];
            $order->info['cc_number'] = $cc_info['cc_number'];
            $order->info['cc_owner'] = $cc_info['cc_owner'];
            $order->info['cc_expires'] = $cc_info['cc_expires'];
            unset($_SESSION['PayPalRestful']['Order']['3DS_response']);

            return $this->captureOrAuthorizePayment('card');
        }

        // -----
        // Otherwise, this is the initial 'create' of the credit-card payment.  Check
        // that the card-related information submitted is valid, returning the customer
        // to the payment phase of the checkout process if something's not kosher.
        //
        // Note that the validateCardInformation method has already set a specific
        // message for the customer if one of its checks has failed.
        //
        if ($this->validateCardInformation(false) === false) {
            zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
        }

        // -----
        // Save the pertinent credit-card information into the order so that it'll be
        // included in the GUID.
        //
        $order->info['cc_type'] = $this->ccInfo['type'];
        $order->info['cc_number'] = $this->obfuscateCcNumber($this->ccInfo['number']);
        $order->info['cc_owner'] = $this->ccInfo['name'];
        $order->info['cc_expires'] = ''; //- $this->ccInfo['expiry_month'] . substr($this->ccInfo['expiry_year'], -2);

        // -----
        // Create a GUID (Globally Unique IDentifier) for the order's
        // current 'state'.
        //
        $order_guid = $this->createOrderGuid($order, 'card');

        // -----
        // Build the request for the PayPal card-payment order's creation.
        //
        global $zcObserverPaypalrestful;
        $create_order_request = new CreatePayPalOrderRequest('card', $order, $this->ccInfo, $order_info, $zcObserverPaypalrestful->getOrderTotalChanges());

        // -----
        // Send the request off to register the credit-card order at PayPal.
        //
        $this->ppr->setPayPalRequestId($order_guid);
        $this->ppr->setKeepTxnLinks(true);

        $response = $this->ppr->createOrder($create_order_request->get());
        if ($response === false) {
            $this->setMessageAndRedirect(MODULE_PAYMENT_PAYPALR_TEXT_CC_ERROR . ' ' . MODULE_PAYMENT_PAYPALR_TEXT_TRY_AGAIN, FILENAME_CHECKOUT_PAYMENT);
        }

        // -----
        // Check the response for the card-payment order creation to see if there was
        // an issue with the card reported by the card processor.
        //
        $payment_response_message = $this->checkCardPaymentResponse($response);
        if ($payment_response_message !== '') {
            $this->setMessageAndRedirect($payment_response_message, FILENAME_CHECKOUT_PAYMENT);
        }

        // -----
        // If we've gotten this far, the order has been created at PayPal.
        // Save the pertinent information in the session.
        //
        $_SESSION['PayPalRestful']['Order']['status'] = $response['status'];
        $_SESSION['PayPalRestful']['Order']['id'] = $response['id'];

        // -----
        // See if a 'payer-action' link is sent back (it will be if the customer needs to
        // perform an SCA verification).  If such a link is found, send the customer off
        // to that verification link; they'll come back to the store via a listener redirect
        // back to the payment module's /ppr_listener.php.
        //
        foreach ($response['links'] as $next_link) {
            if ($next_link['rel'] === 'payer-action') {
                // -----
                // Since the customer will be coming back through the process phase of checkout
                // in their response to the SCA verification link, don't count this pass through
                // "against them".
                //
                $_SESSION['payment_attempt']--;

                // -----
                // Save confirmation page to which the customer should be redirected upon
                // a submittal on the SCA verification link.  That's used by the webhook.
                //
                // Note: The webhook copies the ccInfo element of the PayerAction into its
                // 3DS_response for use by the top-of-method processing to ensure that the
                // credit-card information gets recorded into the order itself.
                //
                global $current_page_base;
                $_SESSION['PayPalRestful']['Order']['PayerAction'] = [
                    'current_page_base' => $current_page_base,
                    'savedPosts' => [
                        'securityToken' => $_SESSION['securityToken'],
                    ],
                    'ccInfo' => [
                        'cc_type' => $order->info['cc_type'],
                        'cc_number' => $order->info['cc_number'],
                        'cc_owner' => $order->info['cc_owner'],
                        'cc_expires' => $order->info['cc_expires'],
                    ],
                ];

                $sca_link = $next_link['href'];
                $this->log->write("before_process, sending the customer off to a 3DS link ($sca_link).", true, 'after');
                zen_redirect($sca_link);
            }
        }

        // -----
        // If we've reached this point, the credit-card order was successfully
        // created (no SCA verification required); return the response to the caller.
        //
        return $response;
    }
    protected function checkCardPaymentResponse(array $response): string
    {
        // -----
        // If a payer-action is required (i.e. an SCA link), there's no additional information
        // in the payment-response, other than the rel="payer-action" link.  Let the caller
        // deal with that SCA redirection.
        //
        if ($response['status'] === 'PAYER_ACTION_REQUIRED') {
            foreach ($response['links'] as $next_link) {
                if ($next_link['rel'] === 'payer-action') {
                    return '';
                }
            }
        }

        // -----
        // Grab the 'payments' portion of the response; the element depends on whether
        // the payment-module is configured for 'Final Sale' or 'Auth Only'.
        //
        $payment = $response['purchase_units'][0]['payments']['captures'][0] ?? $response['purchase_units'][0]['payments']['authorizations'][0];

        // -----
        // There was an issue of some sort with the credit-card payment.  Determine whether/not
        // it's recoverable, e.g. a payment that was placed on PENDING/PENDING_REVIEW.
        //
        // See https://developer.paypal.com/api/rest/sandbox/card-testing/ for additional information.
        //
        $response_message = '';
        switch ($payment['status']) {
            // -----
            // If an authorization was CREATED or a funds' capture was COMPLETED, the caller
            // can safely proceed processing the order.
            //
            case 'CREATED':
            case 'COMPLETED':
                return '';
                break;

            // -----
            // If the authorization or capture is PENDING, then there's most likely a 'hold'
            // placed by PayPal.  The order can be processed, but the order's status will remain
            // in its 'pending' state.
            //
            case 'PENDING':
                $this->paymentIsPending = true;
                break;

            case 'FAILED':
                $response_message = MODULE_PAYMENT_PAYPALR_TEXT_CC_ERROR;
                break;

            // -----
            // If an authorization was DENIED or a funds' capture was DECLINED, there
            // was an issue of some sort with the card.  Determine the underlying issue.
            //
            case 'DENIED':
            case 'DECLINED':
                $card_payment_source = $response['payment_source']['card'];
                $card_type = $card_payment_source['brand'] ?? '';
                $last_digits = $card_payment_source['last_digits'];

                $response_code = $payment['processor_response']['response_code'] ?? '-- not supplied --';
                switch ($response_code) {
                    case '5400':    //- Expired card
                        $response_message = sprintf(MODULE_PAYMENT_PAYPALR_TEXT_CC_EXPIRED, $card_type, $last_digits);
                        break;

                    case '5120':    //- Insufficient funds
                        $response_message = sprintf(MODULE_PAYMENT_PAYPALR_TEXT_INSUFFICIENT_FUNDS, $card_type, $last_digits);
                        break;

                    case '00N7':    //- CVV check failed
                    case '1380':    //- Invalid card verification value
                    case '5110':    //- CVV check failed
                        $response_message = sprintf(MODULE_PAYMENT_PAYPALR_TEXT_CVV_FAILED, $card_type, $last_digits);
                        break;

                    case '0500':    //- Card refused
                    case '1330':    //- Card not valid
                    case '1380':    //- Invalid expiration
                    case '5100':    //- Generic decline
                    case '5140':    //- Card closed
                    case '5180':    //- Luhn check failed; don't use card
                    case '5930':    //- Card not activated
                    case '5950':    //- External decline as an updated card has been issued.
                    case '9100':    //- Declined, please retry
                    case '9510':    //- Security violation (not sure what this means)
                    case '9540':    //- Card refused
                        $response_message = sprintf(MODULE_PAYMENT_PAYPALR_TEXT_CARD_DECLINED, $last_digits);
                        break;

                    case '9500':    //- Fraudalent card
                    case '9520':    //- Lost or stolen card
                        $response_message = sprintf(MODULE_PAYMENT_PAYPALR_TEXT_CARD_DECLINED, $last_digits);

                        // -----
                        // Note: An alert-email is forced for these conditions!
                        //
                        $this->sendAlertEmail(
                            MODULE_PAYMENT_PAYPALR_ALERT_SUBJECT_LOST_STOLEN_CARD,
                            sprintf(MODULE_PAYMENT_PAYPALR_ALERT_LOST_STOLEN_CARD,
                                ($response_code === '9500') ? MODULE_PAYMENT_PAYPALR_CARD_FRAUDULENT : MODULE_PAYMENT_PAYPALR_CARD_LOST,
                                $_SESSION['customers_ip_address'],
                                $_SESSION['customer_first_name'],
                                $_SESSION['customer_last_name'],
                                $_SESSION['customer_id']
                            ) . "\n" .
                            json_encode($card_payment_source, JSON_PRETTY_PRINT),
                            true
                        );
                        break;

                    default:
                        $response_message =
                            sprintf(MODULE_PAYMENT_PAYPALR_TEXT_CARD_DECLINED, $last_digits) .
                            ' ' .
                            sprintf(MODULE_PAYMENT_PAYPALR_TEXT_DECLINED_REASON_UNKNOWN, $response_code);

                        $this->sendAlertEmail(
                            MODULE_PAYMENT_PAYPALR_ALERT_SUBJECT_UNKNOWN_DENIAL,
                            sprintf(MODULE_PAYMENT_PAYPALR_ALERT_UNKNOWN_DENIAL,
                                $response_code,
                                $_SESSION['customer_first_name'],
                                $_SESSION['customer_last_name'],
                                $_SESSION['customer_id']
                            ) . "\n" .
                            json_encode($card_payment_source, JSON_PRETTY_PRINT)
                        );
                        break;
                }
                break;

            default:
                break;
        }

        unset($_SESSION['PayPalRestful']['Order']['authentication_result']);
        if ($response_message !== '') {
            $response_message .= ' ' . MODULE_PAYMENT_PAYPALR_TEXT_TRY_AGAIN;
        }
        return $response_message;
    }

    protected function setMessageAndRedirect(string $error_message, string $redirect_page, bool $log_only = false)
    {
        if ($log_only === false) {
            global $messageStack;
            $messageStack->add_session('checkout', $error_message, 'error');
        }
        $this->log->write($error_message);
        $this->resetOrder();
        zen_redirect(zen_href_link($redirect_page, '', 'SSL'));
    }

    protected function obfuscateCcNumber(string $cc_number): string
    {
        return substr($cc_number, 0, 4) . str_repeat('X', (strlen($cc_number) - 8)) . substr($cc_number, -4);
    }

    /**
     * Issued by /modules/checkout_process.php after the main order-record has
     * been provided in the database, supplying the just-created order's 'id'.
     *
     * The before_process method has stored the successful PayPal response from the
     * payment's capture (or authorization), based on the site's configuration, in
     * the class' orderInfo property.
     *
     * Unlike other payment modules, paypalr stores its database information during
     * the after_order_create method's processing, just in case some email issue arises,
     * so that the information's written.
     */
    public function after_order_create($orders_id)
    {
        $this->orderInfo['orders_id'] = $orders_id;

        $purchase_unit = $this->orderInfo['purchase_units'][0];
        $address_info = [];
        if (isset($purchase_unit['shipping']['address'])) {
            $shipping_address = $purchase_unit['shipping']['address'];
            $address_street = $shipping_address['address_line_1'];
            if (!empty($shipping_address['address_line_2'])) {
                $address_street .= ', ' . $shipping_address['address_line_2'];
            }
            $address_street = substr($address_street, 0, 254);
            $address_info = [
                'address_name' => substr($purchase_unit['shipping']['name']['full_name'], 0, 64),
                'address_street' => $address_street,
                'address_city' => substr($shipping_address['admin_area_2'] ?? '', 0, 120),
                'address_state' => substr($shipping_address['admin_area_1'] ?? '', 0, 120),
                'address_zip' => substr($shipping_address['postal_code'] ?? '', 0, 10),
                'address_country' => substr($shipping_address['country_code'] ?? '', 0, 64),
            ];
        }

        $payment = $purchase_unit['payments']['captures'][0] ?? $purchase_unit['payments']['authorizations'][0];

        $payment_info = [];
        if (isset($payment['seller_receivable_breakdown'])) {
            $seller_receivable = $payment['seller_receivable_breakdown'];
            $payment_info = [
                'payment_date' => Helpers::convertPayPalDatePay2Db($payment['create_time']),
                'payment_gross' => $seller_receivable['gross_amount']['value'],
                'payment_fee' => $seller_receivable['paypal_fee']['value'],
                'settle_amount' => $seller_receivable['receivable_amount']['value'] ?? $seller_receivable['net_amount']['value'],
                'settle_currency' => $seller_receivable['receivable_amount']['currency_code'] ?? $seller_receivable['net_amount']['currency_code'],
                'exchange_rate' => $seller_receivable['exchange_rate']['value'] ?? 'null',
            ];
        }

        // -----
        // Set information used by the after_process method's status-history record creation.
        //
        $payment_type = array_key_first($this->orderInfo['payment_source']);
        $this->orderInfo['payment_info'] = [
            'payment_type' => $payment_type,
            'amount' => $payment['amount']['value'] . ' ' . $payment['amount']['currency_code'],
            'created_date' => $payment['created_date'] ?? '',
        ];

        // -----
        // Payer information returned is different for 'paypal' and 'card' payments.
        //
        $payment_source = $this->orderInfo['payment_source'][$payment_type];
        if ($payment_type !== 'card') {
            $first_name = $payment_source['name']['given_name'];
            $last_name = $payment_source['name']['surname'];
            $email_address = $payment_source['email_address'];
            $payer_id = $this->orderInfo['payer']['payer_id'];
            $memo = [];
        } else {
            $name_elements = explode(' ', $payment_source['name']);
            $first_name = $name_elements[0];
            unset($name_elements[0]);
            $last_name = implode(' ', $name_elements);
            $email_address = '';
            $payer_id = '';
            $memo = [
                'card_info' => $payment_source,
                'processor_response' => $payment['processor_response'] ?? 'n/a',
                'network_transaction_reference' => $payment['network_transaction_reference'] ?? 'n/a',
            ];
        }
        if (isset($payment['seller_protection'])) {
            $memo['seller_protection'] = $payment['seller_protection'];
        }
        if (isset($_SESSION['PayPalRestful']['Order']['authentication_result'])) {
            $memo['authentication_result'] = $_SESSION['PayPalRestful']['Order']['authentication_result'];
        }
        $memo['amount_mismatch'] = $_SESSION['PayPalRestful']['Order']['amount_mismatch'];

        $expiration_time = (isset($this->orderInfo['expiration_time'])) ? Helpers::convertPayPalDatePay2Db($this->orderInfo['expiration_time']) : 'null';
        $num_cart_items = $_SESSION['cart']->count_contents();
        $sql_data_array = [
            'order_id' => $orders_id,
            'txn_type' => 'CREATE',
            'module_name' => $this->code,
            'module_mode' => $this->orderInfo['txn_type'],
            'reason_code' => $payment['status_details']['reason'] ?? '',
            'payment_type' => $payment_type,
            'payment_status' => $this->orderInfo['payment_status'],
            'invoice' => $purchase_unit['invoice_id'] ?? $purchase_unit['custom_id'] ?? '',
            'mc_currency' => $payment['amount']['currency_code'],
            'first_name' => substr($first_name, 0, 32),
            'last_name' => substr($last_name, 0, 32),
            'payer_email' => $email_address,
            'payer_id' => $payer_id,
            'payer_status' => $this->orderInfo['payment_source'][$payment_type]['account_status'] ?? 'UNKNOWN',
            'receiver_email' => $purchase_unit['payee']['email_address'],
            'receiver_id' => $purchase_unit['payee']['merchant_id'],
            'txn_id' => $this->orderInfo['id'],
            'num_cart_items' => $num_cart_items,
            'mc_gross' => $payment['amount']['value'],
            'date_added' => Helpers::convertPayPalDatePay2Db($this->orderInfo['create_time']),
            'last_modified' => Helpers::convertPayPalDatePay2Db($this->orderInfo['update_time']),
            'notify_version' => self::CURRENT_VERSION,
            'expiration_time' => $expiration_time,
            'memo' => json_encode($memo),
        ];
        $sql_data_array = array_merge($sql_data_array, $address_info, $payment_info);
        zen_db_perform(TABLE_PAYPAL, $sql_data_array);

        $sql_data_array = [
            'order_id' => $orders_id,
            'txn_type' => $this->orderInfo['txn_type'],
            'final_capture' => (int)($this->orderInfo['txn_type'] === 'CAPTURE'),
            'module_name' => $this->code,
            'module_mode' => '',
            'reason_code' => $payment['status_details']['reason'] ?? '',
            'payment_type' => $payment_type,
            'payment_status' => $payment['status'],
            'mc_currency' => $payment['amount']['currency_code'],
            'txn_id' => $payment['id'],
            'parent_txn_id' => $this->orderInfo['id'],
            'num_cart_items' => $num_cart_items,
            'mc_gross' => $payment['amount']['value'],
            'notify_version' => self::CURRENT_VERSION,
            'date_added' => Helpers::convertPayPalDatePay2Db($payment['create_time']),
            'last_modified' => Helpers::convertPayPalDatePay2Db($payment['update_time']),
            'expiration_time' => $expiration_time,
        ];
        $sql_data_array = array_merge($sql_data_array, $payment_info);
        zen_db_perform(TABLE_PAYPAL, $sql_data_array);

        // -----
        // If funds have been captured, fire a notification so that sites that
        // manage payments are aware of the incoming funds.
        //
        if ($this->orderInfo['txn_type'] === 'CAPTURE') {
            global $zco_notifier;
            $zco_notifier->notify('NOTIFY_PAYPALR_FUNDS_CAPTURED', $sql_data_array);
        }
    }

    /**
     * Issued at the tail-end of the checkout_process' header_php.php, indicating that the
     * order's been recorded in the database and any required emails sent.
     *
     * Bump the count of orders completed in the session to ensure that back-to-back
     * identical order-contents within the same session have a unique GUID.
     *
     * Add a customer-visible order-status-history record identifying the
     * associated transaction ID, payment method, timestamp, status, amount,
     * and payment-source, buyer email and addresses, if not blank.
     */
    public function after_process()
    {
        $_SESSION['PayPalRestful']['CompletedOrders']++;

        $payment_info = $this->orderInfo['payment_info'];
        $timestamp = '';
        if ($payment_info['created_date'] !== '') {
            $timestamp = 'Timestamp: ' . $payment_info['created_date'] . "\n";
        }

        $message =
            MODULE_PAYMENT_PAYPALR_TRANSACTION_ID . $this->orderInfo['id'] . "\n" .
            sprintf(MODULE_PAYMENT_PAYPALR_TRANSACTION_TYPE, $payment_info['payment_type']) . "\n" .
            $timestamp .
            MODULE_PAYMENT_PAYPALR_TRANSACTION_PAYMENT_STATUS . $this->orderInfo['payment_status'] . "\n" .
            MODULE_PAYMENT_PAYPALR_TRANSACTION_AMOUNT . $payment_info['amount'] . "\n";

        $payment_type = $this->orderInfo['payment_info']['payment_type'];
        $message .= MODULE_PAYMENT_PAYPALR_FUNDING_SOURCE . $payment_type . "\n";

        if (!empty($this->orderInfo['payment_source'][$payment_type]['email_address'])) {
            $message .= MODULE_PAYMENT_PAYPALR_BUYER_EMAIL . $this->orderInfo['payment_source'][$payment_type]['email_address'] . "\n";
        }

        zen_update_orders_history($this->orderInfo['orders_id'], $message, null, -1, 0);

        // -----
        // If the order's processing requires an admin-alert, send one if so configured.
        //
        if ($this->orderInfo['admin_alert_needed'] === true) {
            $this->sendAlertEmail(
                MODULE_PAYMENT_PAYPALR_ALERT_SUBJECT_ORDER_ATTN,
                sprintf(MODULE_PAYMENT_PAYPALR_ALERT_ORDER_CREATION, $this->orderInfo['orders_id'], $this->orderInfo['paypal_payment_status'])
            );
        }

        $this->resetOrder();
    }

    // -----
    // Issued during admin processing (on the customers::orders/details page).
    // -----

    /**
      * Build admin-page components
      *
      * @param int $zf_order_id
      * @return string
      */
    public function admin_notification($zf_order_id)
    {
        $zf_order_id = (int)$zf_order_id;
        $admin_main = new AdminMain($this->code, self::CURRENT_VERSION, $zf_order_id, $this->ppr);

        if ($admin_main->externalTxnAdded() === true) {
            zen_update_orders_history($zf_order_id, MODULE_PAYMENT_PAYPALR_EXTERNAL_ADDITION);
            $this->sendAlertEmail(MODULE_PAYMENT_PAYPALR_ALERT_SUBJECT_ORDER_ATTN, sprintf(MODULE_PAYMENT_PAYPALR_ALERT_EXTERNAL_TXNS, $zf_order_id));
        }

        return $admin_main->get();
    }

    public function help()
    {
        return [
            'link' => 'https://github.com/lat9/paypalr/wiki'
        ];
    }

    /**
     * Used to submit a refund for a given payment-capture for an order.
     */
    public function _doRefund($oID)
    {
        $do_refund = new DoRefund((int)$oID, $this->ppr, $this->code, self::CURRENT_VERSION);
    }

    /**
     * Used to re-authorize a previously-created transaction, possibly changing the
     * authorized value.
     */
    public function _doAuth($oID, $order_amt, $currency = 'USD')
    {
        $do_auth = new DoAuthorization((int)$oID, $this->ppr, $this->code, self::CURRENT_VERSION);
    }

    /**
     * Used to capture part or all of a given previously-authorized transaction.  A capture is
     * performed against the most recent authorization (or re-authorization).
     */
    public function _doCapt($oID, $captureType = 'Complete', $order_amt = 0, $order_currency = 'USD')
    {
        $do_capt = new DoCapture((int)$oID, $this->ppr, $this->code, self::CURRENT_VERSION);
    }

    /**
     * Used to void a given previously-authorized transaction.
     *
     * NOTE: Once a PayPal transaction is voided, it is REMOVED from PayPal's
     * history and any request to retrieve the order's PayPal status will result
     * a RESOURCE_NOT_FOUND (404) error!
     */
    public function _doVoid($oID)
    {
        $do_void = new DoVoid((int)$oID, $this->ppr, $this->code, self::CURRENT_VERSION);
    }

    /**
     * Evaluate installation status of this module. Returns true if the status key is found.
     */
    public function check(): bool
    {
        global $db;

        if (!isset($this->_check)) {
            $check_query = $db->Execute(
                "SELECT configuration_value
                   FROM " . TABLE_CONFIGURATION . "
                  WHERE configuration_key = 'MODULE_PAYMENT_PAYPALR_STATUS'
                  LIMIT 1"
            );
            $this->_check = !$check_query->EOF;
        }
        return $this->_check;
    }

    /**
     * Installs all the configuration keys for this module
     */
    public function install()
    {
        global $db, $sniffer;

        $amount = new Amount(DEFAULT_CURRENCY);
        $supported_currencies = $amount->getSupportedCurrencyCodes();
        $currencies_list = '';
        foreach ($supported_currencies as $next_currency) {
            $currencies_list .= "\'Only $next_currency\',";
        }
        $currencies_list = rtrim($currencies_list, ',');

        $current_version = self::CURRENT_VERSION;
        $db->Execute(
            "INSERT INTO " . TABLE_CONFIGURATION . "
                (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added)
             VALUES
                ('Module Version', 'MODULE_PAYMENT_PAYPALR_VERSION', '$current_version', 'Currently-installed module version.', 6, 0, 'zen_cfg_read_only(', NULL, now()),

                ('Enable this Payment Module?', 'MODULE_PAYMENT_PAYPALR_STATUS', 'False', 'Do you want to enable this payment module? Use the <b>Retired</b> setting if you are planning to remove this payment module but still have administrative actions to perform against orders placed with this module.', 6, 0, 'zen_cfg_select_option([\'True\', \'False\', \'Retired\'], ', NULL, now()),

                ('PayLater Messaging', 'MODULE_PAYMENT_PAYPALR_PAYLATER_MESSAGING', 'Checkout, Shopping Cart, Product Pages', 'On which pages should PayPal PayLater messaging be displayed? (It will automatically not be displayed in regions where it is not available. Only available in USD, GBP, EUR, AUD.) When enabled, it will show the lower installment-based pricing for the presented product or cart amount. This may accelerate buying decisions.<br>To disable, leave all unticked.', 6, 0, 'zen_cfg_select_multioption_pairs([\'Checkout\', \'Shopping Cart\', \'Product Pages\', \'Product Listings and Search Results\'], ', NULL, now()),

                ('Environment', 'MODULE_PAYMENT_PAYPALR_SERVER', 'live', '<b>Live: </b> Used to process Live transactions<br><b>Sandbox: </b>For developers and testing', 6, 0, 'zen_cfg_select_option([\'live\', \'sandbox\'], ', NULL, now()),

                ('Client ID (live)', 'MODULE_PAYMENT_PAYPALR_CLIENTID_L', '', 'The <em>Client ID</em> from your PayPal API Signature settings under *API Access* for your <b>live</b> site. Required if using the <b>live</b> environment.', 6, 0, NULL, 'zen_cfg_password_display', now()),

                ('Client Secret (live)', 'MODULE_PAYMENT_PAYPALR_SECRET_L', '', 'The <em>Client Secret</em> from your PayPal API Signature settings under *API Access* for your <b>live</b> site. Required if using the <b>live</b> environment.', 6, 0, NULL, 'zen_cfg_password_display', now()),

                ('Client ID (sandbox)', 'MODULE_PAYMENT_PAYPALR_CLIENTID_S', '', 'The <em>Client ID</em> from your PayPal API Signature settings under *API Access* for your <b>sandbox</b> site. Required if using the <b>sandbox</b> environment.', 6, 0, NULL, 'zen_cfg_password_display', now()),

                ('Client Secret (sandbox)', 'MODULE_PAYMENT_PAYPALR_SECRET_S', '', 'The <em>Client Secret</em> from your PayPal API Signature settings under *API Access* for your <b>sandbox</b> site. Required if using the <b>sandbox</b> environment.', 6, 0, NULL, 'zen_cfg_password_display', now()),

                ('Sort order of display.', 'MODULE_PAYMENT_PAYPALR_SORT_ORDER', '-1', 'Sort order of display. Lowest is displayed first.', 6, 0, NULL, NULL, now()),

                ('Payment Zone', 'MODULE_PAYMENT_PAYPALR_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', 6, 0, 'zen_cfg_pull_down_zone_classes(', 'zen_get_zone_class_title', now()),

                ('Completed Order Status', 'MODULE_PAYMENT_PAYPALR_ORDER_STATUS_ID', '2', 'Set the status of orders whose payment has been successfully <em>captured</em> to this status.<br>Recommended: <b>Processing[2]</b><br>', 6, 0, 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now()),

                ('Set Unpaid Order Status', 'MODULE_PAYMENT_PAYPALR_ORDER_PENDING_STATUS_ID', '1', 'Set the status of orders whose payment has been successfully <em>authorized</em> to this status.<br>Recommended: <b>Pending[1]</b><br>', 6, 0, 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now()),

                ('Set Refunded Order Status', 'MODULE_PAYMENT_PAYPALR_REFUNDED_STATUS_ID', '1', 'Set the status of <em><b>fully</b>-refunded</em> orders to this status.<br>Recommended: <b>Pending[1]</b><br>', 6, 0, 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now()),

                ('Set Voided Order Status', 'MODULE_PAYMENT_PAYPALR_VOIDED_STATUS_ID', '1', 'Set the status of <em>voided</em> orders to this status.<br>Recommended: <b>Pending[1]</b><br>', 6, 0, 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now()),

                ('Set Held Order Status', 'MODULE_PAYMENT_PAYPALR_HELD_STATUS_ID', '1', 'Set the status of orders that are held for review to this status.<br>Recommended: <b>Pending[1]</b><br>', 6, 0, 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now()),

                ('Store (Brand) Name at PayPal', 'MODULE_PAYMENT_PAYPALR_BRANDNAME', '', 'The name of your store as it should appear on the PayPal login page. If blank, your store name will be used.', 6, 0, NULL, NULL, now()),

                ('Store (Sub-Brand) Identifier at PayPal', 'MODULE_PAYMENT_PAYPALR_SOFT_DESCRIPTOR', '', 'On customer credit card statements, your company name will show as <code>PAYPAL*(yourname)*(your-sub-brand-name)</code> (max 22 letters for (yourname)*(your-sub-brand-name)). You can add the sub-brand-name here if you want to differentiate purchases from this store vs any other PayPal sales you make.', 6, 0, NULL, NULL, now()),

                ('Payment Action', 'MODULE_PAYMENT_PAYPALR_TRANSACTION_MODE', 'Final Sale', 'How do you want to obtain payment?<br><b>Default: Final Sale</b>', 6, 0, 'zen_cfg_select_option([\'Auth Only (All Txns)\', \'Final Sale\', \'Auth Only (Card-Only)\'], ', NULL,  now()),

                ('Transaction Currency', 'MODULE_PAYMENT_PAYPALR_CURRENCY', 'Selected Currency', 'In which currency should the order be sent to PayPal?<br>NOTE: If an unsupported currency is sent to PayPal, it will be auto-converted to the <em>Fall-back Currency</em>.<br><b>Default: Selected Currency</b>', 6, 0, 'zen_cfg_select_option([\'Selected Currency\', $currencies_list], ', NULL, now()),

                ('Fall-back Currency', 'MODULE_PAYMENT_PAYPALR_CURRENCY_FALLBACK', 'USD', 'If the <b>Transaction Currency</b> is set to <em>Selected Currency</em>, what currency should be used as a fall-back when the customer\'s selected currency is not supported by PayPal?<br><b>Default: USD</b>', 6, 0, 'zen_cfg_select_option([\'USD\', \'GBP\'], ', NULL, now()),

                ('Accept Credit Cards?', 'MODULE_PAYMENT_PAYPALR_ACCEPT_CARDS', 'false', 'Should the payment-module accept credit-card payments? If running <var>live</var> transactions, your storefront <b>must</b> be configured to use <var>https</var> protocol for the card-payments to be accepted!<br><br>If your store uses One-Page Checkout, you can limit credit-card payments to account-holders.<br><b>Default: false</b>', 6, 0, 'zen_cfg_select_option([\'true\', \'false\', \'Account-Holders Only\'], ', NULL, now()),

                ('List <var>handling-fee</var> Order-Totals', 'MODULE_PAYMENT_PAYPALR_HANDLING_OT', '', 'Identify, using a comma-separated list (intervening spaces are OK), any order-total modules &mdash; <em>other than</em> <code>ot_loworderfee</code> &mdash; that add a <em>handling-fee</em> element to an order.  Leave the setting as an empty string if there are none (the default).', 6, 0, NULL, NULL, now()),

                ('List <var>insurance</var> Order-Totals', 'MODULE_PAYMENT_PAYPALR_INSURANCE_OT', '', 'Identify, using a comma-separated list (intervening spaces are OK), any order-total modules that add an <em>insurance</em> element to an order.  Leave the setting as an empty string if there are none (the default).', 6, 0, NULL, NULL, now()),

                ('List <var>discount</var> Order-Totals', 'MODULE_PAYMENT_PAYPALR_DISCOUNT_OT', '', 'Identify, using a comma-separated list (intervening spaces are OK), any order-total modules &mdash; <em>other than</em> <code>ot_coupon</code>, <code>ot_gv</code> and <code>ot_group_pricing</code> &mdash; that add a <em>discount</em> element to an order.  Leave the setting as an empty string if there are none (the default).', 6, 0, NULL, NULL, now()),

                ('Debug Mode', 'MODULE_PAYMENT_PAYPALR_DEBUGGING', 'Off', 'Would you like to enable debug mode?  A complete detailed log of failed transactions will be emailed to the store owner.', 6, 0, 'zen_cfg_select_option([\'Off\', \'Alerts Only\', \'Log File\', \'Log and Email\'], ', NULL, now())"
        );

        // -----
        // Make any modifications to the 'paypal' table, if not already done.

        //
        // 1. Adding an order's re-authorize time limit, so it's always available.
        //
        if ($sniffer->field_exists(TABLE_PAYPAL, 'expiration_time') === false) {
            $db->Execute(
                "ALTER TABLE " . TABLE_PAYPAL . "
                   ADD expiration_time datetime default NULL AFTER date_added"
            );
        }

        // -----
        // 2. Increasing the number of characters in 'notify_version' (was varchar(6) NOT NULL default '')
        // since the payment-module's version will be stored there.
        //
        if ($sniffer->field_type(TABLE_PAYPAL, 'notify_version', 'varchar(20)') === false) {
            $db->Execute(
                "ALTER TABLE " . TABLE_PAYPAL . "
                   MODIFY notify_version varchar(20) NOT NULL default ''"
            );
        }

        // -----
        // 3. Adding a final_capture flag (0/1) for use in the payment module's admin
        // notifications handling.
        //
        if ($sniffer->field_exists(TABLE_PAYPAL, 'final_capture') === false) {
            $db->Execute(
                "ALTER TABLE " . TABLE_PAYPAL . "
                   ADD final_capture tinyint(1) NOT NULL default 0 AFTER txn_type"
            );
        }

        // -----
        // 4. Increasing the number of characters in 'pending_reason' (was varchar(32) default NULL)
        // since some of the status_details::reason codes, e.g. RECEIVING_PREFERENCE_MANDATES_MANUAL_ACTION,
        // won't fit and will result in a MySQL error otherwise.
        //
        if ($sniffer->field_type(TABLE_PAYPAL, 'pending_reason', 'varchar(64)') === false) {
            $db->Execute(
                "ALTER TABLE " . TABLE_PAYPAL . "
                   MODIFY pending_reason varchar(64) default NULL"
            );
        }

        // -----
        // 5. Increasing the number of decimal digits in 'exchange_rate' (was decimal(15,4) default NULL)
        // to increase accuracy of currency conversions.
        //
        if ($sniffer->field_type(TABLE_PAYPAL, 'exchange_rate', 'decimal(15,6)') === false) {
            $db->Execute(
                "ALTER TABLE " . TABLE_PAYPAL . "
                   MODIFY exchange_rate decimal(15,6) default NULL"
            );
        }

        // -----
        // Define the module's current version so that the tableCheckup method
        // will apply all changes since the module's introduction.
        //
        define('MODULE_PAYMENT_PAYPALR_VERSION', '0.0.0');
        $this->tableCheckup();

        $this->notify('NOTIFY_PAYMENT_PAYPALR_INSTALLED');
    }

    protected function manageRootDirectoryFiles(): bool
    {
        if ($this->enabled === false) {
            return false;
        }

        $files_ok = true;
        $problem_files = [];

        // -----
        // Starting with v1.2.0, installing the payment module includes creating
        // its root-directory listeners/handlers from a copy within the module's
        // storefront includes directory.
        //
        $root_files = ['ppr_listener.php', 'ppr_webhook.php'];
        foreach ($root_files as $next_file) {
            $source_file = DIR_FS_CATALOG . DIR_WS_MODULES . "payment/paypal/PayPalRestful/$next_file";
            $target_file = DIR_FS_CATALOG . $next_file;

            $file_contents = file_get_contents($source_file);
            file_put_contents($target_file, $file_contents);

            if (!file_exists($target_file) || filesize($source_file) !== filesize($target_file)) {
                $files_ok = false;
                $problem_files[] = $next_file;
            }
        }

        // We also delete the old ppr_webhook_main.php file, if present
        if (file_exists(DIR_FS_CATALOG . 'ppr_webhook_main.php')) {
            unlink(DIR_FS_CATALOG . 'ppr_webhook_main.php');
        }

        if ($files_ok === false) {
            $this->setConfigurationDisabled(sprintf(MODULE_PAYMENT_PAYPALR_ALERT_MISSING_ROOT_FILES, implode(', ', $problem_files)));
        }
        return $files_ok;
    }

    public function keys(): array
    {
        return [
            'MODULE_PAYMENT_PAYPALR_VERSION',
            'MODULE_PAYMENT_PAYPALR_STATUS',
            'MODULE_PAYMENT_PAYPALR_BRANDNAME',
            'MODULE_PAYMENT_PAYPALR_SOFT_DESCRIPTOR',
            'MODULE_PAYMENT_PAYPALR_TRANSACTION_MODE',
            'MODULE_PAYMENT_PAYPALR_SCA_ALWAYS',
            'MODULE_PAYMENT_PAYPALR_ACCEPT_CARDS',
            'MODULE_PAYMENT_PAYPALR_PAYLATER_MESSAGING',
            'MODULE_PAYMENT_PAYPALR_SORT_ORDER',
            'MODULE_PAYMENT_PAYPALR_ZONE',
            'MODULE_PAYMENT_PAYPALR_SERVER',
            'MODULE_PAYMENT_PAYPALR_CLIENTID_L',
            'MODULE_PAYMENT_PAYPALR_SECRET_L',
            'MODULE_PAYMENT_PAYPALR_CLIENTID_S',
            'MODULE_PAYMENT_PAYPALR_SECRET_S',
            'MODULE_PAYMENT_PAYPALR_CURRENCY',
            'MODULE_PAYMENT_PAYPALR_CURRENCY_FALLBACK',
            'MODULE_PAYMENT_PAYPALR_ORDER_STATUS_ID',
            'MODULE_PAYMENT_PAYPALR_ORDER_PENDING_STATUS_ID',
            'MODULE_PAYMENT_PAYPALR_REFUNDED_STATUS_ID',
            'MODULE_PAYMENT_PAYPALR_VOIDED_STATUS_ID',
            'MODULE_PAYMENT_PAYPALR_HELD_STATUS_ID',
            'MODULE_PAYMENT_PAYPALR_HANDLING_OT',
            'MODULE_PAYMENT_PAYPALR_INSURANCE_OT',
            'MODULE_PAYMENT_PAYPALR_DISCOUNT_OT',
            'MODULE_PAYMENT_PAYPALR_DEBUGGING',
        ];
    }

    /**
     * Uninstall this module
     */
    public function remove()
    {
        global $db;

        // de-register known webhooks
        if (isset($this->ppr)) {
            $this->ppr->unsubscribeWebhooks();
        }

        $db->Execute("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE\_PAYMENT\_PAYPALR\_%'");

        // -----
        // Starting with v1.1.1, removing the payment module includes deleting its root-directory
        // listener and webhook handlers, and the prior versions' ppr_webhook_main.php handler.
        //
        foreach (['ppr_listener.php', 'ppr_webhook.php', 'ppr_webhook_main.php'] as $file) {
            if (file_exists(DIR_FS_CATALOG . $file)) {
                unlink(DIR_FS_CATALOG . $file);
            }
        }

        $this->notify('NOTIFY_PAYMENT_PAYPALR_UNINSTALLED');
    }

    /**
     * Send email to store-owner, if configured.
     *
     */
    public function sendAlertEmail(string $subject_detail, string $message, bool $force_send = false)
    {
        if ($this->emailAlerts === true || $force_send === true) {
            zen_mail(
                STORE_NAME,
                STORE_OWNER_EMAIL_ADDRESS,
                sprintf(MODULE_PAYMENT_PAYPALR_ALERT_SUBJECT, $subject_detail),
                $message,
                STORE_OWNER,
                STORE_OWNER_EMAIL_ADDRESS,
                ['EMAIL_MESSAGE_HTML' => nl2br($message, false)],   //- Replace new-lines with HTML5 <br>
                'paymentalert'
            );
        }
    }

    public function getCurrentVersion(): string
    {
        return self::CURRENT_VERSION;
    }
}

if (!function_exists('zen_in_guest_checkout')) {
    /** @since ZC v1.5.6 */
    function zen_in_guest_checkout(): bool
    {
        global $zco_notifier;
        $in_guest_checkout = false;
        $zco_notifier->notify('NOTIFY_ZEN_IN_GUEST_CHECKOUT', null, $in_guest_checkout);
        return (bool)$in_guest_checkout;
    }
}

if (!function_exists('zen_cfg_select_multioption_pairs')) {
    /** @since ZC v2.2.0 */
    function zen_cfg_select_multioption_pairs(array $choices_array, string $stored_value, string $config_key_name = ''): string
    {
        $string = '';
        $name = (($config_key_name) ? 'configuration[' . $config_key_name . '][]' : 'configuration_value');
        $chosen_already = explode(", ", $stored_value);
        foreach ($choices_array as $value) {
            // Account for cases where an = sign is used to allow key->value pairs where the value is friendly display text
            $beforeEquals = strstr($value, '=', true);
            // this entry's checkbox should be pre-selected if the key matches
            $ticked = (in_array($value, $chosen_already, true) || in_array($beforeEquals, $chosen_already, true));
            // determine the value to show (the part after the =; if no =, just the whole string)
            $display_value = strpos($value, '=') !== false ? explode('=', $value, 2)[1] : $value;
            $string .= '<div class="checkbox"><label>' . zen_draw_checkbox_field($name, $value, $ticked, 'id="' . strtolower($value . '-' . $name) . '"') . $display_value . '</label></div>' . "\n";
        }
        $string .= zen_draw_hidden_field($name, '--none--');
        return $string;
    }
}

if (IS_ADMIN_FLAG === true && isset($sniffer)) {
    // only check db on Admin side
    if (is_object($sniffer) && $sniffer->field_exists(TABLE_ORDERS_STATUS_HISTORY, 'updated_by') === false) {
        $db->Execute("ALTER TABLE " . TABLE_ORDERS_STATUS_HISTORY . " ADD updated_by VARCHAR(45) NOT NULL DEFAULT ''");
    }
}

if (!function_exists('zen_updated_by_admin')) {
    /** @since ZC v1.5.6 */
    function zen_updated_by_admin($admin_id = null)
    {
        if (empty($admin_id) && empty($_SESSION['admin_id'])) {
            return '';
        }
        if (empty($admin_id)) {
            $admin_id = $_SESSION['admin_id'];
        }
        $name = zen_get_admin_name($admin_id);
        return ($name ?? 'Unknown Name') . " [$admin_id]";
    }
}

if (!function_exists('zen_get_orders_status_name')) {
    function zen_get_orders_status_name($orders_status_id, $language_id = '')
    {
        global $db;

        if (!$language_id) {
            $language_id = $_SESSION['languages_id'];
        }
        $orders_status = $db->Execute("select orders_status_name
                                       from " . TABLE_ORDERS_STATUS . "
                                       where orders_status_id = '" . (int)$orders_status_id . "'
                                       and language_id = '" . (int)$language_id . "' LIMIT 1");
        if ($orders_status->EOF) {
            return '';
        }
        return $orders_status->fields['orders_status_name'];
    }
}

if (!function_exists('zen_catalog_href_link') && function_exists('zen_href_link')) {
    function zen_catalog_href_link ($page = '', $parameters = '', $connection = 'NONSSL')
    {
        return zen_href_link($page, $parameters, $connection, false);
    }
}

if (!function_exists('zen_update_orders_history')) {
    function zen_update_orders_history($orders_id, $message = '', $updated_by = null, $orders_new_status = -1, $notify_customer = -1, $email_include_message = true, $email_subject = '', $send_extra_emails_to = '', $filename = ''): int
    {
        global $osh_sql, $osh_additional_comments;

        // -----
        // Initialize return value to indicate no change and sanitize various inputs.
        //
        $osh_id = -1;
        $orders_id = (int)$orders_id;
        $message = (string)$message;
        $email_subject = (string)$email_subject;
        $send_extra_emails_to = (string)$send_extra_emails_to;
    
        $sql = "SELECT customers_name, customers_email_address, orders_status, date_purchased
               FROM " . TABLE_ORDERS . "
              WHERE orders_id = $orders_id
              LIMIT 1";
        
        global $db;
        if (method_exists($db, 'ExecuteNoCache')) {
            $osh_info = $db->ExecuteNoCache($sql);
        } else {
            $osh_info = $db->Execute($sql);
        }
        if ($osh_info->EOF) {
            $osh_id = -2;
        } else {
            // -----
            // Determine the message to be included in any email(s) sent.  If an observer supplies an additional
            // message, that text is appended to the message supplied on the function's call.
            //
            $message = stripslashes($message);
            $email_message = '';
            if ($email_include_message === true) {
                $email_message = $message;
                if (empty($osh_additional_comments)) {
                    $osh_additional_comments = '';
                }
                $GLOBALS['zco_notifier']->notify('ZEN_UPDATE_ORDERS_HISTORY_PRE_EMAIL', ['message' => $message], $osh_additional_comments);
                if (!empty($osh_additional_comments)) {
                    if (!empty($email_message)) {
                        $email_message .= "\n\n";
                    }
                    $email_message .= (string)$osh_additional_comments;
                }
                if (!empty($email_message)) {
                    $email_message = OSH_EMAIL_TEXT_COMMENTS_UPDATE . $email_message . "\n\n";
                }
            }
    
            $orders_current_status = $osh_info->fields['orders_status'];
            $orders_new_status = (int)$orders_new_status;
            if (($orders_new_status != -1 && $orders_current_status != $orders_new_status) || !empty($email_message)) {
                if ($orders_new_status == -1) {
                    $orders_new_status = $orders_current_status;
                }
                $GLOBALS['zco_notifier']->notify('ZEN_UPDATE_ORDERS_HISTORY_STATUS_VALUES', ['orders_id' => $orders_id, 'new' => $orders_new_status, 'old' => $orders_current_status]);
    
                $GLOBALS['db']->Execute(
                    "UPDATE " . TABLE_ORDERS . "
                        SET orders_status = $orders_new_status,
                            last_modified = now()
                      WHERE orders_id = $orders_id
                      LIMIT 1"
                );
    
                // PayPal Trans ID, if any
                $paypalLookup = $GLOBALS['db']->Execute(
                    "SELECT *
                     FROM " . TABLE_PAYPAL . "
                     WHERE order_id = $orders_id
                     ORDER BY last_modified DESC, date_added DESC, parent_txn_id DESC, paypal_ipn_id DESC"
                );
                $paypal = $paypalLookup->EOF ? [] : $paypalLookup->fields;
    
                $notify_customer = ($notify_customer == 1 || $notify_customer == -1 || $notify_customer == -2) ? $notify_customer : 0;
    
                if ($notify_customer == 1 || $notify_customer == -2) {
                    $new_orders_status_name = zen_get_orders_status_name($orders_new_status);
                    if ($new_orders_status_name === '') {
                        $new_orders_status_name = 'N/A';
                    }
    
                    if ($orders_new_status != $orders_current_status) {
                        $status_text = OSH_EMAIL_TEXT_STATUS_UPDATED;
                        $status_value_text = sprintf(OSH_EMAIL_TEXT_STATUS_CHANGE, zen_get_orders_status_name($orders_current_status), $new_orders_status_name);
                    } else {
                        $status_text = OSH_EMAIL_TEXT_STATUS_NO_CHANGE;
                        $status_value_text = sprintf(OSH_EMAIL_TEXT_STATUS_LABEL, $new_orders_status_name);
                    }
    
                    //send emails
                    $email_text =
                        EMAIL_SALUTATION . ' ' . $osh_info->fields['customers_name'] . ', ' . "\n\n" .
                        STORE_NAME . ' ' . OSH_EMAIL_TEXT_ORDER_NUMBER . ' ' . $orders_id . "\n\n" .
                        OSH_EMAIL_TEXT_INVOICE_URL . ' ' . zen_catalog_href_link(FILENAME_CATALOG_ACCOUNT_HISTORY_INFO, "order_id=$orders_id", 'SSL') . "\n\n" .
                        OSH_EMAIL_TEXT_DATE_ORDERED . ' ' . zen_date_long($osh_info->fields['date_purchased']) . "\n\n" .
                        strip_tags($email_message) .
                        $status_text . $status_value_text .
                        OSH_EMAIL_TEXT_STATUS_PLEASE_REPLY;

                    // Add in store specific order message
                    $email_order_message = defined('EMAIL_ORDER_UPDATE_MESSAGE') ? constant('EMAIL_ORDER_UPDATE_MESSAGE') : '';
                    $GLOBALS['zco_notifier']->notify('ZEN_UPDATE_ORDERS_HISTORY_SET_ORDER_UPDATE_MESSAGE', $orders_id, $email_order_message);
                    if (!empty($email_order_message)) {
                     $email_text .= "\n\n" . $email_order_message . "\n\n";
                    }
                    $html_msg['EMAIL_ORDER_UPDATE_MESSAGE'] = $email_order_message;

                    $html_msg['EMAIL_SALUTATION'] = EMAIL_SALUTATION;
                    $html_msg['EMAIL_CUSTOMERS_NAME']    = $osh_info->fields['customers_name'];
                    $html_msg['EMAIL_TEXT_ORDER_NUMBER'] = OSH_EMAIL_TEXT_ORDER_NUMBER . ' ' . $orders_id;
                    $html_msg['EMAIL_TEXT_INVOICE_URL']  = '<a href="' . zen_catalog_href_link(FILENAME_CATALOG_ACCOUNT_HISTORY_INFO, "order_id=$orders_id", 'SSL') .'">' . str_replace(':', '', OSH_EMAIL_TEXT_INVOICE_URL) . '</a>';
                    $html_msg['EMAIL_TEXT_DATE_ORDERED'] = OSH_EMAIL_TEXT_DATE_ORDERED . ' ' . zen_date_long($osh_info->fields['date_purchased']);
                    $html_msg['EMAIL_TEXT_STATUS_COMMENTS'] = nl2br($email_message);
                    $html_msg['EMAIL_TEXT_STATUS_UPDATED'] = str_replace("\n", '', $status_text);
                    $html_msg['EMAIL_TEXT_STATUS_LABEL'] = str_replace("\n", '', $status_value_text);
                    $html_msg['EMAIL_TEXT_NEW_STATUS'] = $new_orders_status_name;
                    $html_msg['EMAIL_TEXT_STATUS_PLEASE_REPLY'] = str_replace("\n", '', OSH_EMAIL_TEXT_STATUS_PLEASE_REPLY);
                    $html_msg['EMAIL_PAYPAL_TRANSID'] = '';

                    if (empty($email_subject)) {
                        $email_subject = OSH_EMAIL_TEXT_SUBJECT . ' #' . $orders_id;
                    }

                    $GLOBALS['zco_notifier']->notify('ZEN_UPDATE_ORDERS_HISTORY_BEFORE_SENDING_CUSTOMER_EMAIL', $orders_id, $email_subject, $email_text, $html_msg, $notify_customer);

                    if ($notify_customer == 1) {
                        zen_mail($osh_info->fields['customers_name'], $osh_info->fields['customers_email_address'], $email_subject, $email_text, STORE_NAME, EMAIL_FROM, $html_msg, 'order_status', $filename);
                    }

                    if (!empty($paypal['txn_id'])) {
                        $email_text .= "\n\n" . ' PayPal Trans ID: ' . $paypal['txn_id'];
                        $html_msg['EMAIL_PAYPAL_TRANSID'] = $paypal['txn_id'];
                    }

                    //send extra emails
                    if (empty($send_extra_emails_to) && (int)SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO_STATUS === 1) {
                        $send_extra_emails_to = (string)SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO;
                    }
                    if (!empty($send_extra_emails_to)) {
                        zen_mail('', $send_extra_emails_to, SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO_SUBJECT . ' ' . $email_subject, $email_text, STORE_NAME, EMAIL_FROM, $html_msg, 'order_status_extra', $filename);
                    }
                }

                if (empty($updated_by)) {
                    if (IS_ADMIN_FLAG === true && isset($_SESSION['admin_id'])) {
                        $updated_by = zen_updated_by_admin();
                    } else if (isset($_SESSION['emp_admin_id'])) {
                       $updated_by = zen_updated_by_admin($_SESSION['emp_admin_id']);
                    } elseif (IS_ADMIN_FLAG === false && isset($_SESSION['customer_id'])) {
                        $updated_by = '';
                    } else {
                        $updated_by = 'N/A';
                    }
                }

                $osh_sql = [
                    'orders_id' => $orders_id,
                    'orders_status_id' => $orders_new_status,
                    'date_added' => 'now()',
                    'customer_notified' => $notify_customer,
                    'comments' => $message,
                    'updated_by' => $updated_by
                ];

                $GLOBALS['zco_notifier']->notify('ZEN_UPDATE_ORDERS_HISTORY_BEFORE_INSERT', [], $osh_sql);
    
                zen_db_perform (TABLE_ORDERS_STATUS_HISTORY, $osh_sql);
                $osh_id = $GLOBALS['db']->Insert_ID();

                $GLOBALS['zco_notifier']->notify('ZEN_UPDATE_ORDERS_HISTORY_AFTER_INSERT', $osh_id, $osh_sql, $paypalLookup);
            }
        }
        return $osh_id;
    }
}
