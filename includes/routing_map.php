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
 * shopping_cart is matched explicitly. Every checkout-flow page including core
 * (checkout_shipping, checkout_payment, ...) and known third-party addons like
 * (checkout_one, checkout_one_confirmation, etc) is matched by the 'checkout_' prefix
 * instead of an enumerated list. Accepted trade-off: a plugin that names an unrelated
 * page starting with 'checkout_' would also be denied here, judged unlikely enough
 * in practice to be worth the reduced maintenance burden versus enumerating every
 * checkout-flow page by hand.
 *
 * Deliberately narrow, by design:
 *  - This is NOT an exhaustive deny-list of every non-catalog page in core,
 *    let alone every plugin page. It only targets the pages actually confirmed as
 *    real bot targets: the shopping cart and the checkout flow.
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
 *  - pID / pid: look like catalog candidates but are used by shopping_cart.php's
 *    own remove/update links, plus ask_a_question and the popup_image pages.
 *    Restricting them here would break the shopping cart's own UI ... notably,
 *    shopping_cart is itself denied below, so this isn't hypothetical.
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
        'music_genre_id', 'record_company_id',
        'disp_order', 'typefilter', 'filter_id', 'alpha_filter_id',
        'keyword', 'dfrom', 'pfrom', 'dto', 'pto', 'search_in_description', 'inc_subcat',
        'categories_id', 'sale_category', 'reviews_id',
    ];

    // Mirrors init_sanitize.php's own fallback:
    // an empty/missing main_page defaults to the index page (FILENAME_DEFAULT)
    // e.g. index.php?manufacturers_id=8 is a legitimate URL shape that renders as the index/manufacturer listing
    $page = $get['main_page'] ?? '';
    if ($page === '') {
        $page = 'index';
    }

    if ($page !== 'shopping_cart' && !str_starts_with($page, 'checkout_')) {
        return false;
    }

    foreach ($catalogFilterKeys as $key) {
        if (isset($get[$key])) {
            return true;
        }
    }

    return false;
}
