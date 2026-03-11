<?php
/**
 * Part of the paypalr (PayPal Restful Api) payment module.
 * This observer class handles the JS SDK integration logic.
 * It also watches for notifications from the 'order_total' class,
 * introduced in this (https://github.com/zencart/zencart/pull/6090) Zen Cart PR,
 * to determine an order's overall value and what amounts each order-total
 * module has added/subtracted to the order's overall value.
 *
 * Last updated: v1.3.1
 */

use PayPalRestful\Api\Data\CountryCodes;
use PayPalRestful\Api\PayPalRestfulApi;
use PayPalRestful\Zc2Pp\Amount;

require_once DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/paypal/pprAutoload.php';

class zcObserverPaypalrestful extends base
{
    protected $lastOrderValues = [];
    protected $orderTotalChanges = [];
    protected $freeShippingCoupon = false;
    protected $headerAssetsSent = false;

    public function __construct()
    {
        // -----
        // If loaded via ppr_webhook.php, ensure that the $spider_flag is set so
        // that application_top.php doesn't try to load the counter.php module which,
        // depending on the zc version, might choke for the absence of the zcDate class.
        //
        global $loaderPrefix, $spider_flag;
        if ($loaderPrefix === 'webhook') {
            $spider_flag = true;
        }

        // -----
        // If the paypalr payment-module isn't installed or isn't configured to be
        // enabled, nothing further to do here.
        //
        if (!defined('MODULE_PAYMENT_PAYPALR_STATUS') || MODULE_PAYMENT_PAYPALR_STATUS !== 'True') {
            return;
        }

        // -----
        // If currently on either the 3-page or OPC checkout-confirmation pages, need to monitor
        // calls to the order-totals' pre_confirmation_check method. That method is run on that
        // page prior to paypalr's pre_confirmation_check method.
        //
        // NOTE: The page that's set during the AJAX checkout-payment class is 'index'!
        //
        global $current_page_base;
        $pages_to_watch = [
            FILENAME_CHECKOUT_CONFIRMATION,
            FILENAME_DEFAULT,
        ];
        if (defined('FILENAME_CHECKOUT_ONE_CONFIRMATION')) {
            $pages_to_watch[] = FILENAME_CHECKOUT_ONE_CONFIRMATION;
        }
        if (in_array($current_page_base, $pages_to_watch)) {
            $this->attach($this, [
                'NOTIFY_ORDER_TOTAL_PRE_CONFIRMATION_CHECK_STARTS',
                'NOTIFY_ORDER_TOTAL_PRE_CONFIRMATION_CHECK_NEXT',
                'NOTIFY_OT_COUPON_CALCS_FINISHED',
            ]);
        // -----
        // If currently on the checkout_process page, need to monitor calls to the
        // order-totals' process method.  That method's run on that page prior to
        // paypalr's before_process method.
        //
        } elseif ($current_page_base === FILENAME_CHECKOUT_PROCESS) {
            $this->attach($this, [
                'NOTIFY_ORDER_TOTAL_PROCESS_STARTS',
                'NOTIFY_ORDER_TOTAL_PROCESS_NEXT',
                'NOTIFY_OT_COUPON_CALCS_FINISHED',
            ]);
        }

        // -----
        // Attach to header to render JS SDK assets and the footer to load
        // the JS.
        //
        $this->attach($this, [
            'NOTIFY_HTML_HEAD_JS_BEGIN', // NOTE: this might come too early to detect pageType properly
            'NOTIFY_HTML_HEAD_END',
            'NOTIFY_FOOTER_END',
        ]);
    }

    // -----
    // Notification 'update' handlers for the notifications from order-totals' pre_confirmation_check method.
    //
    public function updateNotifyOrderTotalPreConfirmationCheckStarts(&$class, $eventID, array $starting_order_info)
    {
        $this->setLastOrderValues($starting_order_info['order_info']);
    }
    public function updateNotifyOrderTotalPreConfirmationCheckNext(&$class, $eventID, array $ot_updates)
    {
        $this->setOrderTotalUpdate($ot_updates);
    }

    // -----
    // Notification 'update' handlers for the notifications from order-totals' process method.
    //
    public function updateNotifyOrderTotalProcessStarts(&$class, $eventID, array $starting_order_info)
    {
        $this->setLastOrderValues($starting_order_info['order_info']);
    }
    public function updateNotifyOrderTotalProcessNext(&$class, $eventID, array $ot_updates)
    {
        $this->setOrderTotalUpdate($ot_updates);
    }

    // -----
    // Notification 'update' handler for ot_coupon, letting us know if the associated
    // coupon provides free shipping.
    //
    public function updateNotifyOtCouponCalcsFinished(&$class, $eventID, array $parameters)
    {
        $coupon_type = $parameters['coupon']['coupon_type'];
        $this->freeShippingCoupon = in_array($coupon_type, ['S', 'E', 'O']);
    }

    public function updateNotifyHtmlHeadEnd(&$class, $eventID, $current_page_base)
    {
        // This is a fallback for older versions, to ensure we only output the header JS once.
        if ($this->headerAssetsSent) {
            return;
        }
        $this->headerAssetsSent = $this->outputJsSdkHeaderAssets($current_page_base);
    }
    public function updateNotifyHtmlHeadJsBegin(&$class, $eventID, $current_page_base)
    {
        $this->headerAssetsSent = $this->outputJsSdkHeaderAssets($current_page_base);
    }
    public function updateNotifyFooterEnd(&$class, $eventID, $current_page_base)
    {
        if ($this->headerAssetsSent === false) {
            return;
        }
        $this->outputJsFooter($current_page_base);
    }

    // -----
    // Set the last order-values seen, based on the associated 'start' notification.
    //
    protected function setLastOrderValues(array $order_info)
    {
        $this->lastOrderValues = [
            'total' => $order_info['total'],
            'tax' => $order_info['tax'],
            'subtotal' => $order_info['subtotal'],
            'shipping_cost' => $order_info['shipping_cost'],
            'shipping_tax' => $order_info['shipping_tax'],
            'tax_groups' => $order_info['tax_groups'],
        ];
    }

    // -----
    // Determine the difference to the current order's values for the current
    // order-total module.
    //
    // The $ot_updates is an associative array containing these keys:
    //
    // - class ........ The name of the order-total module currently being processed.
    // - order_info ... Contains the $order->info array *after* the order-total has been processed.
    // - output ....... The 'output' provided by the order-total currently being processed.
    //
    // Note: Fuzzy comparisons are used on values throughout this method, since we're dealing
    // with floating-point values.
    //
    protected function setOrderTotalUpdate(array $ot_updates)
    {
        $updated_order = $ot_updates['order_info'];

        // -----
        // Loop through each of the 'pertinent' elements of the $order->info array, to
        // see what (if any) changes have been provided by the current order-total module.
        //
        $diff = [];
        foreach ($this->lastOrderValues as $key => $value) {
            // -----
            // All elements _other than_ the tax_groups are scalar values, just
            // check if the current order-total has made changes to the value.
            //
            if ($key !== 'tax_groups') {
                $value_difference = $updated_order[$key] - $value;
                if ($value_difference != 0) {
                    $diff[$key] = $value_difference;
                }
                continue;
            }

            // -----
            // Loop through each of the tax-groups *last seen* in the order, determining
            // whether the current order-total has make changes.
            //
            // Once processed, remove the tax-group from the updates so that any
            // *additions* can be handled.
            //
            foreach ($this->lastOrderValues['tax_groups'] as $tax_group_name => $tax_value) {
                $value_difference = $updated_order['tax_groups'][$tax_group_name] - $tax_value;
                if ($value_difference != 0) {
                    $diff['tax_groups'][$tax_group_name] = $value_difference;
                }
                unset($updated_order['tax_groups'][$tax_group_name]);
            }

            // -----
            // If any tax-groups remain in the updated order-info, then the current
            // order-total has *added* that tax-group element to the order.
            //
            foreach ($updated_order['tax_groups'] as $tax_group_name => $tax_value) {
                if ($tax_value != 0) {
                    $diff['tax_groups'][$tax_group_name] = $tax_value;
                }
            }
        }

        // -----
        // If the current order-total has made changes to the order-info, record
        // that information for use by the paypalr payment-module's processing.
        //
        if (count($diff) !== 0) {
            $this->orderTotalChanges[$ot_updates['class']] = [
                'diff' => $diff,
                'output' => $ot_updates['ot_output'],
            ];
        }

        // -----
        // Register the order-info after the current order-total has run.  These
        // values are used when checking the next order-total's changes; the
        // final result seen will be the order-info that's associated with
        // the order itself.
        //
        $this->setLastOrderValues($ot_updates['order_info']);
    }

    // -----
    // Public methods (used by the paypalr payment-module) to retrieve the results
    // of the notifications' processing.
    //
    // Note: If getLastOrderValues returns an empty array, the implication is that
    // the required notifications have not been added to the order_total.php class.
    //
    public function getLastOrderValues(): array
    {
        return $this->lastOrderValues;
    }
    public function getOrderTotalChanges(): array
    {
        return $this->orderTotalChanges;
    }
    public function orderHasFreeShippingCoupon(): bool
    {
        return $this->freeShippingCoupon;
    }

    /** Internal methods **/

    protected function outputJsSdkHeaderAssets($current_page)
    {
        global $current_page_base, $order, $paypalSandboxBuyerCountryCodeOverride, $paypalSandboxLocaleOverride;
        if (empty($current_page)) {
            $current_page = $current_page_base;
        }

        $js_url = 'https://www.paypal.com/sdk/js';
        $js_fields = [];
        $js_scriptparams = [];

        $js_fields['client-id'] = MODULE_PAYMENT_PAYPALR_SERVER === 'live' ? MODULE_PAYMENT_PAYPALR_CLIENTID_L : MODULE_PAYMENT_PAYPALR_CLIENTID_S;

        if (MODULE_PAYMENT_PAYPALR_SERVER === 'sandbox') {
            $js_fields['client-id'] = 'sb'; // 'sb' for sandbox
            $js_fields['debug'] = 'true'; // sandbox only, un-minifies the JS
            $buyerCountry = CountryCodes::ConvertCountryCode($order->delivery['country']['iso_code_2'] ?? 'US');
            $js_fields['buyer-country'] = $paypalSandboxBuyerCountryCodeOverride ?? $buyerCountry; // sandbox only
            $js_fields['locale'] = $paypalSandboxLocaleOverride ?? 'en_US'; // only passing this in sandbox to allow override testing; otherwise just letting it default to customer's browser
        }

        if (!empty($order->info['currency'])) {
            $amount = new Amount($order->info['currency']);
            $js_fields['currency'] = $amount->getDefaultCurrencyCode();
        }

        // possible components for future SDK integration: buttons,marks,messages,funding-eligibility,hosted-fields,card-fields,applepay
        $js_fields['components'] = 'messages';

        $js_page_type = $this->getMessagesPageType();
        if (empty($js_page_type) || in_array($js_page_type, ['home', 'other', 'None'], true)) {
            return false;
        }

        $js_scriptparams[] = 'data-page-type="' . $js_page_type . '"';
        $js_fields['integration-date'] = '2025-08-01';
        $js_scriptparams[] = 'data-partner-attribution-id="ZenCart_SP_PPCP"';
        $js_scriptparams[] = 'data-namespace="PayPalSDK"';

        // -----
        // Set the 'locale' for the "Pay Later" messaging, so long as the current locale isn't
        // a 2-character version, e.g. 'en' vs. 'en_US' or 'en_GB'.
        //
        $current_locale = setlocale(LC_TIME, '0');
        if ($current_locale !== false && strlen($current_locale) > 2) {
            $js_fields['locale'] = $current_locale;
        }
?>

<script title="PayPalSDK" id="PayPalJSSDK" src="<?= $js_url . '?'. str_replace('%2C', ',', http_build_query($js_fields)) ?>" <?= implode(' ', $js_scriptparams) ?> defer></script>

<?php
        return true;
    }

    // -----
    // Outputs the javascript support for the PayPal PayLater messaging
    // into the page's footer.
    //
    protected function outputJsFooter($current_page_base)
    {
        $containingElement = null;
        $priceSelector = null;
        $outputElement = null;
        $messageStyles = [
            "layout" => "text",
            "logo" => [
                "type" => "inline",
                "position" => "top"
            ],
            "text" => [
                "align" => "center"
            ],
        ];
        $pageType = $this->getMessagesPageType();
        $this->notify('NOTIFY_PAYPAL_PAYLATER_SELECTORS', ['current_page_base' => $current_page_base, 'pageType' => $pageType], $containingElement, $priceSelector, $outputElement, $messageStyles);

        $override = null;
        if (!empty($containingElement) && !empty($priceSelector) && !empty($outputElement)) {
            $override = [
                'pageType' => $pageType,
                'container' => $containingElement,
                'price' => $priceSelector,
                'outputElement' => $outputElement,
                'styleAlign' => $messageStyles['text']['align'] ?? 'center',
            ];
        }

        $messagableObjects = [
            [   //- product_info, bootstrap, position override, fallback price selectors
                'pageType' => 'product-details',
                'container' => '#productsPriceBottom-card',
                'price' => ['.productSalePrice', '.productSpecialPriceSale', '.productSpecialPrice', '.productBasePrice', '.normalPrice'],
                'outputElement' => '#paypal-message-container',
                'styleAlign' => '',
            ],
            [   //- product_info, bootstrap, fallback price selectors
                'pageType' => 'product-details',
                'container' => '#productsPriceBottom-card',
                'price' => ['.productSalePrice', '.productSpecialPriceSale', '.productSpecialPrice', '.productBasePrice', '.normalPrice'],
                'outputElement' => '.productPriceBottomPrice',
                'styleAlign' => '',
            ],
            [   //- product_info, responsive_classic, position override, fallback price selectors
                'pageType' => 'product-details',
                'container' => '.add-to-cart-Y',
                'price' => ['.productSalePrice', '.productSpecialPriceSale', '.productSpecialPrice', '.productBasePrice', '.normalPrice'],
                'outputElement' => '#paypal-message-container',
                'styleAlign' => '',
            ],
            [   //- product_info, responsive_classic, fallback price selectors
                'pageType' => 'product-details',
                'container' => '.add-to-cart-Y',
                'price' => ['.productSalePrice', '.productSpecialPriceSale', '.productSpecialPrice', '.productBasePrice', '.normalPrice'],
                'outputElement' => '#productPrices',
                'styleAlign' => '',
            ],
            [   //- listing pages, bootstrap
                'pageType' => 'product-listing',
                'container' => '.pl-dp',
                'price' => ['.productSalePrice', '.productSpecialPriceSale', '.productSpecialPrice', '.productBasePrice', '.normalPrice'],
                'outputElement' => '.pl-dp',
                'styleAlign' => '',
            ],
            [   //- listing pages, responsive classic
                'pageType' => 'product-listing',
                'container' => '.list-price',
                'price' => ['.productSalePrice', '.productSpecialPriceSale', '.productSpecialPrice', '.productBasePrice', '.normalPrice'],
                'outputElement' => '.list-price',
                'styleAlign' => '',
            ],
            [   //- search results, bootstrap
                'pageType' => 'search-results',
                'container' => '.pl-dp',
                'price' => ['.productSalePrice', '.productSpecialPriceSale', '.productSpecialPrice', '.productBasePrice', '.normalPrice'],
                'outputElement' => '.pl-dp',
                'styleAlign' => '',
            ],
            [   //- search results, responsive classic
                'pageType' => 'search-results',
                'container' => '.list-price',
                'price' => ['.productSalePrice', '.productSpecialPriceSale', '.productSpecialPrice', '.productBasePrice', '.normalPrice'],
                'outputElement' => '.list-price',
                'styleAlign' => '',
            ],
            [   //- shopping-cart, position override
                'pageType' => 'cart',
                'container' => '#shoppingCartDefault',
                'price' => '#cart-total',
                'outputElement' => '#paypal-message-container',
                'styleAlign' => 'right',
            ],
            [   //- shopping-cart, bootstrap
                'pageType' => 'cart',
                'container' => '#shoppingCartDefault-cartTableDisplay',
                'price' => '#cartTotal',
                'outputElement' => '#cartTotal',
                'styleAlign' => 'right',
            ],
            [   //- shopping-cart, responsive classic
                'pageType' => 'cart',
                'container' => '#shoppingCartDefault',
                'price' => '#cartSubTotal',
                'outputElement' => '#cartSubTotal',
                'styleAlign' => 'right',
            ],
            [   //- OPC checkout, bootstrap
                'pageType' => 'checkout',
                'container' => '#checkout_payment',
                'price' => '#ottotal > .ot-text',
                'outputElement' => '#ottotal > .ot-title',
                'styleAlign' => 'right',
            ],
            [   //- checkout-payment, bootstrap
                'pageType' => 'checkout',
                'container' => '#checkoutPayment',
                'price' => '#ottotal > .ot-text',
                'outputElement' => '#ottotal > .ot-title',
                'styleAlign' => 'right',
            ],
            [   //- OPC checkout, responsive classic
                'pageType' => 'checkout',
                'container' => '#checkoutOrderTotals',
                'price' => '#ottotal > .totalBox',
                'outputElement' => '#ottotal > .lineTitle',
                'styleAlign' => 'right',
            ],
            [   //- standard checkout, responsive classic
                'pageType' => 'checkout',
                'container' => '#cartOrderTotals',
                'price' => '#ottotal > .totalBox',
                'outputElement' => '#ottotal > .lineTitle',
                'styleAlign' => 'right',
            ],
        ];

        // -----
        // Enable an observer to add/modify the messagable objects' locations.  First match
        // in the array is where the message is output!
        //
        $this->notify('NOTIFY_PAYPAL_PAYLATER_MESSAGE_OBJECTS', $messagableObjects, $messagableObjects);
?>
<script title="PayPal Pay Later Messaging">
    // PayPal PayLater messaging set up
    let paypalMessagesPageType = '<?= $pageType ?>';
    let paypalMessageableOverride = <?= $override ? json_encode($override) : '{}' ?>;
    let paypalMessageableStyles = <?= !empty($messageStyles) ? json_encode($messageStyles) : '{}' ?>;
    let $messagableObjects = <?= json_encode($messagableObjects) ?>;
    let paypalPayLaterCurrency = '<?= $_SESSION['currency'] ?>';
    <?= file_get_contents(DIR_WS_MODULES . 'payment/paypal/PayPalRestful/jquery.paypalr.jssdk_messages.js'); ?>
</script>
<?php
        return;
    }

    protected function getButtonsPageType(): string
    {
        global $current_page_base, $this_is_home_page, $category_depth, $tpl_page_body;

        if (!defined('MODULE_PAYMENT_PAYPALR_BUTTON_PLACEMENT')) {
            return 'None';
        }

        switch (true) {
            case str_starts_with($current_page_base, 'checkout'):
                return 'checkout';
            case str_contains(MODULE_PAYMENT_PAYPALR_BUTTON_PLACEMENT, 'Cart') && $current_page_base === 'shopping_cart':
                return 'cart';
            case str_contains(MODULE_PAYMENT_PAYPALR_BUTTON_PLACEMENT, 'Cart') && $current_page_base === 'mini-cart':
                return 'mini-cart';
            case str_contains(MODULE_PAYMENT_PAYPALR_BUTTON_PLACEMENT, 'Product') && in_array($current_page_base, zen_get_buyable_product_type_handlers(), true):
                return 'product-details';
            case str_contains(MODULE_PAYMENT_PAYPALR_BUTTON_PLACEMENT, 'Listing') && $category_depth === 'products':
                return 'product-listing';
            case str_contains(MODULE_PAYMENT_PAYPALR_BUTTON_PLACEMENT, 'Search') && str_ends_with($current_page_base, 'search_result'):
                return 'search-results';
            default:
                return 'None';
        }
    }
    protected function getMessagesPageType(): string
    {
        global $current_page_base, $this_is_home_page, $category_depth, $tpl_page_body;

        $limit = defined('MODULE_PAYMENT_PAYPALR_PAYLATER_MESSAGING') ? MODULE_PAYMENT_PAYPALR_PAYLATER_MESSAGING : 'All';
        $limit = explode(', ', $limit);

        switch (true) {
            case !empty(array_intersect($limit, ['All', 'Checkout'])) && str_starts_with($current_page_base, 'checkout'):
                return 'checkout';
            case !empty(array_intersect($limit, ['All', 'Shopping Cart'])) && $current_page_base === 'shopping_cart':
                return 'cart';
            case !empty(array_intersect($limit, ['All', 'Shopping Cart'])) && $current_page_base === 'mini-cart':
                return 'mini-cart';
            case !empty(array_intersect($limit, ['All', 'Product Pages'])) && in_array($current_page_base, zen_get_buyable_product_type_handlers(), true):
                return 'product-details';
            case !empty(array_intersect($limit, ['All', 'Product Listings and Search Results'])) && ($category_depth === 'products' || ($tpl_page_body ?? null) === 'tpl_index_product_list.php'):
                return 'product-listing';
            case !empty(array_intersect($limit, ['All', 'Product Listings and Search Results'])) && str_ends_with($current_page_base, 'search_result'):
                return 'search-results';
            case !empty($limit) && $this_is_home_page:
                return 'home';
            case !empty($limit):
                return 'other';
            default:
                return 'None';
        }
    }
}





/*****************************/
// Backward Compatibility for prior to ZC v2.2.0
if (!function_exists('zen_get_buyable_product_type_handlers')) {
    /**
     * Get a list of product page names that identify buyable products.
     * This allows us to mark a page as containing a product which can
     * be allowed to add-to-cart or buy-now with various modules.
     * @since ZC v2.2.0
     */
    function zen_get_buyable_product_type_handlers(): array
    {
        global $db;
        $sql = "SELECT type_handler from " . TABLE_PRODUCT_TYPES . " WHERE allow_add_to_cart = 'Y'";
        $results = $db->Execute($sql);
        $retVal = [];
        foreach ($results as $result) {
            $retVal[] = $result['type_handler'] . '_info';
        }
        return $retVal;
    }
}
/*****************************/
