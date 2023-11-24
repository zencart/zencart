<?php
/**
 * functions_general.php
 * General functions used throughout Zen Cart
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2023 Dec 31 Modified in v2.0.0-alpha1 $
 */



/**
 * Return table heading with sorting capabilities
 * Used in Product Listing module
 */
function zen_create_sort_heading($sortby, $colnum, $heading)
{
    $sort_prefix = '';
    $sort_suffix = '';

    if ($sortby) {
        $sort_prefix = '<a href="' . zen_href_link($_GET['main_page'], zen_get_all_get_params(array('page', 'info', 'sort')) . 'page=1&sort=' . $colnum . ($sortby == $colnum . 'a' ? 'd' : 'a')) . '" title="' . zen_output_string(TEXT_SORT_PRODUCTS . ($sortby == $colnum . 'd' || substr($sortby, 0, 1) != $colnum ? TEXT_ASCENDINGLY : TEXT_DESCENDINGLY) . TEXT_BY . $heading) . '" class="productListing-heading" rel="nofollow">';
        $sort_suffix = (substr($sortby, 0, 1) == $colnum ? (substr($sortby, 1, 1) == 'a' ? PRODUCT_LIST_SORT_ORDER_ASCENDING : PRODUCT_LIST_SORT_ORDER_DESCENDING) : '') . '</a>';
    }

    return $sort_prefix . $heading . $sort_suffix;
}

/**
 * Count number of modules of a certain type are enabled
 * @param string $modules
 * @return int
 */
function zen_count_modules($modules = '')
{
    $count = 0;

    if (empty($modules)) return $count;

    $modules_array = preg_split('/;/', $modules);

    for ($i = 0, $n = count($modules_array); $i < $n; $i++) {
        $class = substr($modules_array[$i], 0, strrpos($modules_array[$i], '.'));

        if (isset($GLOBALS[$class]) && is_object($GLOBALS[$class])) {
            if ($GLOBALS[$class]->enabled) {
                $count++;
            }
        }
    }
    return $count;
}

function zen_count_payment_modules()
{
    return zen_count_modules(MODULE_PAYMENT_INSTALLED);
}

function zen_count_shipping_modules()
{
    return zen_count_modules(MODULE_SHIPPING_INSTALLED);
}


/**
 * Checks to see if the currency code exists as a currency
 * @TODO - move into currencies class
 * @param string $code
 * @param bool $getFirstDefault
 * @return false|string
 */
function zen_currency_exists(string $code, bool $getFirstDefault = false)
{
    global $db;

    $currency_code = "SELECT code
                      FROM " . TABLE_CURRENCIES . "
                      WHERE code = '" . zen_db_input($code) . "' LIMIT 1";

    $currency_first = "SELECT code
                      FROM " . TABLE_CURRENCIES . "
                      ORDER BY value ASC LIMIT 1";

    $currency = $db->Execute(($getFirstDefault == false) ? $currency_code : $currency_first);

    if ($currency->RecordCount()) {
        return strtoupper($currency->fields['code']);
    }
    return false;
}


/**
 * Sidebox Box Builder helper to calculate an HTML id tag value
 * @param string $box_id
 * @return string
 */
function zen_get_box_id(string $box_id)
{
    $box_id = str_replace('_', '', $box_id);
    $box_id = str_replace('.php', '', $box_id);
    return $box_id;
}


/**
 * Switch buy now button based on call for price sold out etc.
 * @param int|string $product_id used for calculating whether to swap (while a hashed string is accepted, only the (int) portion is used)
 * @param string $buy_now_link the actual button link to use if "buy now" is allowed
 * @param string|bool $additional_link
 * @return string
 */
function zen_get_buy_now_button($product_id, string $buy_now_link, $additional_link = false)
{
    global $db, $zco_notifier, $current_page_base;

// show case only supercedes all other settings
    if (STORE_STATUS != '0') {
        return '<a href="' . zen_href_link(FILENAME_CONTACT_US, '', 'SSL') . '">' . TEXT_SHOWCASE_ONLY . '</a>';
    }

// 0 = normal shopping
// 1 = Login to shop
// 2 = Can browse but no prices
    // verify display of prices
    switch (true) {
        case (CUSTOMERS_APPROVAL == '1' && !zen_is_logged_in()):
            // customer must be logged in to browse
            $login_for_price = '<a href="' . zen_href_link(FILENAME_LOGIN, '', 'SSL') . '">' . TEXT_LOGIN_FOR_PRICE_BUTTON_REPLACE . '</a>';
            return $login_for_price;
            break;
        case (CUSTOMERS_APPROVAL == '2' && !zen_is_logged_in()):
            if (TEXT_LOGIN_FOR_PRICE_PRICE == '') {
                // show room only
                return TEXT_LOGIN_FOR_PRICE_BUTTON_REPLACE;
            } else {
                // customer may browse but no prices
                $login_for_price = '<a href="' . zen_href_link(FILENAME_LOGIN, '', 'SSL') . '">' . TEXT_LOGIN_FOR_PRICE_BUTTON_REPLACE . '</a>';
            }
            return $login_for_price;
            break;
        // show room only
        case (CUSTOMERS_APPROVAL == '3'):
            $login_for_price = TEXT_LOGIN_FOR_PRICE_BUTTON_REPLACE_SHOWROOM;
            return $login_for_price;
            break;
        case (CUSTOMERS_APPROVAL_AUTHORIZATION != '0' && CUSTOMERS_APPROVAL_AUTHORIZATION != '3' && !zen_is_logged_in()):
            // customer must be logged in to browse
            $login_for_price = TEXT_AUTHORIZATION_PENDING_BUTTON_REPLACE;
            return $login_for_price;
            break;
        case (CUSTOMERS_APPROVAL_AUTHORIZATION == '3' && !zen_is_logged_in()):
            // customer must be logged in and approved to add to cart
            $login_for_price = '<a href="' . zen_href_link(FILENAME_LOGIN, '', 'SSL') . '">' . TEXT_LOGIN_TO_SHOP_BUTTON_REPLACE . '</a>';
            return $login_for_price;
            break;
        case (CUSTOMERS_APPROVAL_AUTHORIZATION != '0' && isset($_SESSION['customers_authorization']) && (int)$_SESSION['customers_authorization'] > 0):
            // customer must be logged in to browse
            $login_for_price = TEXT_AUTHORIZATION_PENDING_BUTTON_REPLACE;
            return $login_for_price;
            break;
        case (isset($_SESSION['customers_authorization']) && (int)$_SESSION['customers_authorization'] >= 2):
            // customer is logged in and was changed to must be approved to buy
            $login_for_price = TEXT_AUTHORIZATION_PENDING_BUTTON_REPLACE;
            return $login_for_price;
            break;
        default:
            // proceed normally
            break;
    }

    $button_check = $db->Execute("SELECT product_is_call, products_quantity FROM " . TABLE_PRODUCTS . " WHERE products_id = " . (int)$product_id);
    switch (true) {
// cannot be added to the cart
        case (zen_get_products_allow_add_to_cart($product_id) == 'N'):
            return $additional_link;
            break;
        case ($button_check->fields['product_is_call'] == '1'):
            $return_button = '<a href="' . zen_href_link(FILENAME_ASK_A_QUESTION, 'pid=' . (int)$product_id . '&cfp=true', 'SSL') . '">' . TEXT_CALL_FOR_PRICE . '</a>';
            break;
        case ($button_check->fields['products_quantity'] <= 0 and SHOW_PRODUCTS_SOLD_OUT_IMAGE == '1'):
            global $template;
            $image = BUTTON_IMAGE_SOLD_OUT; 
            $alt = BUTTON_SOLD_OUT_ALT; 
            if (strtolower(IMAGE_USE_CSS_BUTTONS) === 'yes') {
                $return_button = zen_image_button($image, $alt);
            } else {
                $return_button = '<span class="text-center">' . zen_image($template->get_template_dir($image, DIR_WS_TEMPLATE, $current_page_base, 'buttons/' . $_SESSION['language'] . '/') . $image, $alt, '', '', '') . '</span>'; 
            }
            $zco_notifier->notify('NOTIFY_ZEN_SOLD_OUT_IMAGE', array_merge($button_check->fields, ['products_id' => (int)$product_id]), $return_button);
                
            break;
        default:
            $return_button = $buy_now_link;
            break;
    }

    // -----
    // Given an observer the opportunity to modify the to-be-returned button's contents.
    //
    $zco_notifier->notify('NOTIFY_ZEN_GET_BUY_NOW_BUTTON_RETURN', array_merge($button_check->fields, ['products_id' => (int)$product_id]), $return_button);

    if ($return_button != $buy_now_link && $additional_link != false) {
        return $additional_link . '<br>' . $return_button;
    }

    return $return_button;
}

/**
 * Returns the string content of the named define page, processed either:
 * - by an observer running its own processing/substitution/templating engine
 * or
 * - by the PHP engine (this is the classic default behaviour)
 *
 * @param string $define_page_name Name of the define page e.g. 'define_contact_us'
 * @param array|null $context An object of extra data that may be used by the observer.
 * @return string The final content from the define page.
 */
function zen_get_define_page_content(string $define_page_name, ?array $context = null) : string {
    global $zco_notifier;

    $define_page = zen_get_file_directory(DIR_WS_LANGUAGES . $_SESSION['language'] . '/html_includes/', $define_page_name, false);
    if (!file_exists($define_page)) {
        trigger_error("Define Page file '$define_page' does not exist!");
        return ''; // no action
    }

    // Give an observer a chance to process the define page
    $processed_content = null;
    $zco_notifier->notify('NOTIFY_ZEN_PROCESS_DEFINE_PAGE', $define_page, $processed_content, $context);

    if (!empty($processed_content)) {
        return $processed_content;
    }

    ob_start();
    require($define_page);
    $output = ob_get_contents();
    ob_end_clean();

    return $output;
}