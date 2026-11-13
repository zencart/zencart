<?php
/**
 * Routing-map check used by the very early request-inoculation block in
 * application_top.php, before the framework, database, session, or FILENAME_*
 * constants are available. Because of that, this file is deliberately
 * self-contained: no zen_* helper calls, no constants, just plain PHP.
 *
 * The idea: a small, fixed set of GET params only ever have meaning on catalog
 * "listing" pages (index, search results, specials, etc.). A request supplying
 * one of those params for a page that's guaranteed to never legitimately use them
 * (ie: shopping_cart?manufacturers_id=8&products_id=1) is either a stale/spoofed
 * link or a bot probing arbitrary URL/param combinations, and can be rejected
 * immediately (406) before a session starts or any other bootstrapping occurs.
 *
 * This is a deny-list of known-bad targets, not an allow-list of known-good pages.
 * That's deliberate: this check runs before plugins are loaded, so it has no way
 * to let a plugin register its own catalog-style page
 * (e.g. a custom "Deals" page reading manufacturers_id/sort/keyword).
 *
 * Only checkout-flow pages are matched here. Core pages (checkout_shipping,
 * checkout_payment, ...) and known third-party addons alike (checkout_one,
 * checkout_one_confirmation, etc), all via the 'checkout_' prefix instead of an
 * enumerated list.
 *
 * shopping_cart is deliberately NOT matched here because actionBuyNow() legitimately
 * redirects to shopping_cart carrying the query string of whatever catalog listing
 * the "Buy Now" button was clicked from. The actual protection against wasted
 * breadcrumb/category queries lives downstream instead, in functions_lookups.php
 * at zen_page_uses_catalog_breadcrumb_lookups().
 *
 * Deliberately narrow, by design:
 *  - This is NOT an exhaustive deny-list of every non-catalog page in core,
 *    let alone every plugin page. It only targets the checkout flow, the one place
 *    confirmed safe from the redirect-leakage problem above (checkout is never the
 *    target of a "Buy Now from a listing" redirect).
 *  - The restricted param list below is NOT an exhaustive allow-list of every GET
 *    key either, to avoid rejecting legitimate params this system doesn't control
 *    (such as marketing/tracking params (utm_source, gclid, fbclid) or plugin-added keys).
 *  - cPath and products_id are deliberately NOT in the restricted list below, even
 *    though they're catalog-filter params too. Unlike the others, they're ALSO
 *    legitimate on product-detail "_info" pages (product_info, etc), and that page
 *    set varies by installed product type so it can't be safely enumerated here as
 *    a static list (no DB connection exists yet at this point in the request).
 *    cPath/products_id are instead gated downstream by
 *    zen_page_uses_catalog_breadcrumb_lookups() in functions_lookups.php, which can
 *    correctly enumerate that page set from TABLE_PRODUCT_TYPES.
 *
 * Keys deliberately left OUT, documented so a future edit doesn't "helpfully" add them:
 *  - pID / pid: look like catalog candidates but are used legitimately on several pages.
 *  - page: used broadly, not catalog-specific.
 */

/**
 * @param array $get Typically $_GET.
 * @return bool true if a catalog-only filter param was supplied for a page
 *              confirmed to never legitimately read it.
 *
 * @since ZC v2.3.0
 */
function zen_request_has_disallowed_catalog_param(array $get): bool
{
    // NOTE: cPath and products_id are intentionally excluded -- see the docblock above.
    static $catalogFilterKeys = [
        'manufacturers_id',
        'sort',
        'disp_order', 'typefilter', 'filter_id', 'alpha_filter_id',
        'keyword', 'dfrom', 'pfrom', 'dto', 'pto', 'search_in_description', 'inc_subcat',
        'categories_id',
        'sale_category',
        'reviews_id',
        'music_genre_id', 'record_company_id',
    ];

    // Mirrors init_sanitize.php's own fallback:
    // an empty/missing main_page defaults to the index page (FILENAME_DEFAULT)
    // e.g. index.php?manufacturers_id=8 is a legitimate URL shape that renders as the index/manufacturer listing
    $page = $get['main_page'] ?? '';
    if ($page === '') {
        $page = 'index';
    }

    if (!str_starts_with($page, 'checkout_')) {
        return false;
    }

    foreach ($catalogFilterKeys as $key) {
        if (isset($get[$key])) {
            return true;
        }
    }

    return false;
}

/**
 * Body for the 406 response application_top.php sends when it refuses a 'Buy Now'
 * request that did not come from one of the store's own pages.
 *
 * That check relies on the Referer header, which a browser may withhold (privacy
 * settings and extensions do), so a real shopper can be refused along with the
 * crawlers. With no body the shopper is left on a blank page. This gives them a link
 * to the product, where they can add it to the cart the normal way. The request is
 * still refused and nothing is added to a cart.
 *
 * Self-contained like the rest of this file: no language files or constants are
 * loaded yet, so the wording is fixed and the link is relative to the storefront's
 * index.php.
 *
 * @param array $get Typically $_GET.
 * @return string an HTML document, or '' when the request names no usable product.
 * @since ZC v2.3.0
 */
function zen_buy_now_refusal_html(array $get): string
{
    $productsId = $get['products_id'] ?? '';
    if (!is_scalar($productsId)) {
        return '';
    }

    // An id may carry an attribute hash ("27:abc..."); only the product's number is used.
    $productId = (int)$productsId;
    if ($productId < 1) {
        return '';
    }

    return '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<meta name="robots" content="noindex, nofollow"><title>Item not added</title></head><body>'
        . '<p>This item could not be added to your cart from that link.</p>'
        . '<p><a href="index.php?main_page=product_info&amp;products_id=' . $productId . '" rel="nofollow">'
        . 'View the product</a> to add it to your cart.</p>'
        . '</body></html>';
}
