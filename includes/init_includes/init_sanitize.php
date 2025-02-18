<?php
/**
 * sanitize the GET parameters
 * see  {@link  https://docs.zen-cart.com/dev/code/init_system/} for more details.
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Oct 16 Modified in v2.1.0 $
 */

use Zencart\PageLoader\PageLoader;
use Zencart\FileSystem\FileSystem;
use Zencart\Request\Request;

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

$zco_notifier->notify('NOTIFY_INIT_SANITIZE_STARTS');

foreach ($_GET as $varname => $varvalue) {
    if (is_array($varvalue)) {
        $get_var_override = false;
        $zco_notifier->notify('NOTIFY_INIT_SANITIZE_GET_VAR_CHECK', ['name' => $varname, 'value' => $varvalue,], $get_var_override);
        if ($get_var_override === false) {
            zen_redirect(zen_href_link(FILENAME_DEFAULT));
        }
    }
}

$csrfBlackListLocal = [];
$csrfBlackList = (isset($csrfBlackListCustom)) ? array_merge($csrfBlackListLocal, $csrfBlackListCustom) : $csrfBlackListLocal;
if (!isset($_SESSION ['securityToken'])) {
    $_SESSION ['securityToken'] = \bin2hex(\random_bytes(16));
}

if (zen_is_hmac_login()) {
    if (!zen_validate_hmac_login()) {
        unset($_GET['action']);
    } else {
        $_POST['securityToken'] = $_SESSION['securityToken'];
    }
}

if ((isset($_GET['action']) || isset($_POST['action'])) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $mainPage = $_GET['main_page'] ?? FILENAME_DEFAULT;
    if (!in_array($mainPage, $csrfBlackList)) {
        if ((!isset($_SESSION ['securityToken']) || !isset($_POST ['securityToken'])) || ($_SESSION ['securityToken'] !== $_POST ['securityToken'])) {
            zen_redirect(zen_href_link(FILENAME_TIME_OUT, '', $request_type));
        }
    }
}

// -----
// Check products_id values (and variants) as a uprid.  That's either an integer
// value or a uprid (dddd:xxxx), where xxxx is the 32-hexadecimal character md5 hash of the currently-selected
// attributes.
//
// Noting that if an id-value is found to be invalid, there's no sense
// in taking up further resources on the server; simply redirect to
// the home page.
//
$saniGroup1 = [
    'products_id',  //- 'Normal', multi-use
    'product_id',   //- shopping_cart, when removing a product from the cart
    'pid',          //- order_history sidebox and ask_a_question page
    'pID',          //- main/additional images' pop-ups
];
foreach ($saniGroup1 as $key) {
    if (isset($_GET[$key]) && !preg_match('/^\d+(:[0-9a-f]{32})?$/', (string)$_GET[$key])) {
        zen_redirect(zen_href_link(FILENAME_DEFAULT));
    }
}

// -----
// Various variables that are expected to contain **only** digits [0-9].
//
// Note: Special-case for 'page'; if set to an invalid value, it's 'reset' to '1'.
//
$saniGroup2 = [
    'alpha_filter_id',          //- Set by /includes/modules/product_listing_alpha_sorter.php
    'chapter',                  //- EZ-Pages, a 'toc_chapter'
    'cID',                      //- A "coupon_id"
    'categories_id',            //- A categories_id
    'delete',                   //- address_book_process, the id to delete
    'disp_order',               //- /includes/modules/listing_display_order
    'edit',                     //- address_book_process, the id to edit
    'faq_item',                 //- gv_faq
    'filter_id',                //- Various index_filters
    'goto',                     //- redirect, a banners_id
    'id',                       //- An EZ-page or download id
    'inc_subcat',               //- Searches (0/1)
    'manufacturers_id',         //- A manufacturers_id
    'markflow',                 //- Paypal processing
    'music_genre_id',           //- Music products
    'number_of_uploads',        //- Various
    'order_id',                 //- Various, an order_id
    'order',                    //- download page, an order_id
    'page',                     //- Various, a page's number (defaults to '1' if invalid)
    'record_company_id',        //- Music products
    'reviews_id',               //- Various, a reviews_id
    'sale_category',            //- A category_id for filtering Specials listings
    'search_in_description',    //- Searches indicator
];
foreach ($saniGroup2 as $key) {
    if (isset($_GET[$key]) && !ctype_digit((string)$_GET[$key])) {
        $_GET[$key] = ($key === 'page') ? '1' : '';
        if (isset($_REQUEST[$key])) {
            $_REQUEST[$key] = $_GET[$key];
        }
    }
}

// -----
// Various variables that are expected to be a monetary format, e.g. 10 or 10. or 10.12.
//
$saniGroup3 = [
    'pfrom', //- Searches, price-from (float)
    'pto',   //- Searches, price-to (float)
];
foreach ($saniGroup3 as $key) {
    if (isset($_GET[$key]) && !preg_match('/^\d+(\.\d+)?/', $_GET[$key])) {
        $_GET[$key] = '';
        if (isset($_REQUEST[$key])) {
            $_REQUEST[$key] = '';
        }
    }
}

// -----
// The cPath value is of the form "nnnn[_nnnn]...", e.g. 2454 or 2544_0284.
//
if (isset($_GET['cPath']) && !preg_match('/^\d+(_\d+)*/', (string)$_GET['cPath'])) {
    $_GET['cPath'] = '';
}

// -----
// Other variables with special formatting.
//
if (isset($_GET['typefilter'])) {
    $_GET['typefilter'] = preg_replace('/[^0-9a-zA-Z_-]/', '', $_GET['typefilter']);
}
if (isset($_GET['main_page'])) {
    $_GET['main_page'] = preg_replace('/[^0-9a-zA-Z_]/', '', $_GET['main_page']);
}
if (isset($_GET['sort']) && !ctype_alnum($_GET['sort'])) {
    $_GET['sort'] = '';
}
if (isset($_GET['gv_no']) && !ctype_alnum($_GET['gv_no'])) {
    $_GET['gv_no'] = '';
}
if (isset($_GET['addr']) && !filter_var($_GET['addr'], FILTER_VALIDATE_EMAIL)) {
    $_GET['addr'] = '';
}

// -----
// Remaining variables, sanitized as with previous Zen Cart versions.
//
$saniGroup4 = [
    'action',                           //- Various
    'alpha_filter',                     //- Not present
    'currency',                         //- A currency definition
    'debug',                            //- Various
    'dfrom',                            //- Various, a date-formatted value
    'dto',                              //- Various, a date-formatted value
    'goback',                           //- gv_redeem, gv_faq, if set is 'true'
    'language',                         //- A language string
    'nocache',                          //- init_db_config_read and square (mixed)
    'notify',                           //- Various (mixed)
    'override',                         //- ot_total.php, remove method, admin only
    'pos',                              //- EZ-pages (page) page, either 'h' or 'v'
    'products_image_large_additional',  //- A filename
    'referer',                          //- PayPal payment method, specifically 'paypal'
    'set_session_login',                //- init_customer_auth (not set or 'true')
    'token',                            //- paypalwpp/paypaldp, [0-9A-Z.-]
    'tx',                               //- paypal/paypay_functions
    'type',                             //- Paypal
    'zenid',                            //- [a-z0-9]
    $zenSessionId                       //- [a-z0-9]
];
foreach ($saniGroup4 as $key) {
    if (isset($_GET[$key])) {
        $_GET[$key] = preg_replace('/[^\/0-9a-zA-Z_.-]/', '', $_GET[$key]);
        if (isset($_REQUEST[$key])) {
            $_REQUEST[$key] = preg_replace('/[^\/0-9a-zA-Z_.-]/', '', $_REQUEST[$key]);
        }
    }
}

/**
 * process all $_GET terms
 */
$strictReplace = ['<', '>', "'"];
$unStrictReplace = ['<', '>'];
foreach ($_GET as $key => $value) {
    if (is_array($value)) {
        foreach ($value as $key2 => $val2){
            if ($key2 === 'keyword') {
                $_GET[$key][$key2] = str_replace($unStrictReplace, '', $val2);
                if (isset($_REQUEST[$key][$key2])) {
                    $_REQUEST[$key][$key2] = str_replace($unStrictReplace, '', $val2);
                }
            } elseif (is_array($val2)) {
                foreach ($val2 as $key3 => $val3){
                    $_GET[$key][$key2][$key3] = str_replace($strictReplace, '', $val3);
                    if (isset($_REQUEST[$key][$key2][$key3])) {
                        $_REQUEST[$key][$key2][$key3] = str_replace($strictReplace, '', $val3);
                    }
                }
            } else {
                $_GET[$key][$key2] = str_replace($strictReplace, '', $val2);
                if (isset($_REQUEST[$key][$key2])) {
                    $_REQUEST[$key][$key2] = str_replace($strictReplace, '', $val2);
                }
            }
        }
    } else {
        if ($key === 'keyword') {
            $_GET[$key] = str_replace($unStrictReplace, '', $value);
            if (isset($_REQUEST[$key])) {
                $_REQUEST[$key] = str_replace($unStrictReplace, '', $value);
            }
        } else {
            $_GET[$key] = str_replace($strictReplace, '', $value);
            if (isset($_REQUEST[$key])) {
                $_REQUEST[$key] = str_replace($strictReplace, '', $value);
            }
        }
    }
}

/**
 * validate products_id for search engines and bookmarks, etc.
 */
if (isset($_GET['products_id']) && (!isset($_SESSION['check_valid_prod']) || $_SESSION['check_valid_prod'] !== false)) {
    $check_valid = zen_products_id_valid($_GET['products_id']) && !empty($_GET['main_page']);
    if (!$check_valid) {
        $_GET['main_page'] = zen_get_info_page($_GET['products_id']);
        /**
         * do not recheck redirect
         */
        $_SESSION['check_valid_prod'] = false;
        zen_redirect(zen_href_link($_GET['main_page'], 'products_id=' . $_GET['products_id']));
    }
}
$_SESSION['check_valid_prod'] = true;

/**
 * We do some checks here to ensure $_GET['main_page'] has a sane value
 */
if (empty($_GET['main_page'])) {
    $_GET['main_page'] = FILENAME_DEFAULT;
}

$pageLoader = PageLoader::getInstance();
$pageLoader->init($installedPlugins, $_GET['main_page'], new FileSystem);

$pageDir = $pageLoader->findModulePageDirectory();
if ($pageDir === false) {
    if (MISSING_PAGE_CHECK === 'On' || MISSING_PAGE_CHECK === 'true') {
        zen_redirect(zen_href_link(FILENAME_DEFAULT));
    } elseif (MISSING_PAGE_CHECK === 'Page Not Found') {
        header('HTTP/1.1 404 Not Found');
        zen_redirect(zen_href_link(FILENAME_PAGE_NOT_FOUND));
    }
}

$current_page = $_GET['main_page'];
$current_page_base = $current_page;
$code_page_directory = $pageDir;
$page_directory = $code_page_directory;

$sanitizedRequest = Request::capture();

$zco_notifier->notify('NOTIFY_INIT_SANITIZE_ENDS');
