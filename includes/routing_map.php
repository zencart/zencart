<?php
/**
 * Routing-map check used by the very early request-inoculation block in
 * application_top.php, before the framework, database, session, or FILENAME_*
 * constants are available. Because of that, this file is deliberately
 * self-contained: no zen_* helper calls, no constants, just plain PHP.
 *
 * The idea: a small, fixed set of GET params only ever have meaning on a small,
 * fixed set of catalog "listing" pages (index, search results, specials, etc.).
 * A request supplying one of those params for any OTHER page
 * (ie: shopping_cart?manufacturers_id=8&products_id=1) is either a stale/spoofed link
 * or a bot probing arbitrary URL/param combinations, and can be rejected immediately (406)
 * before a session starts or any other bootstrapping occurs.
 *
 * Deliberately narrow, by design:
 *  - This is NOT an exhaustive per-page allow-list of every GET key,
 *    to avoid rejecting legitimate params this system doesn't control
 *    (such as marketing/tracking params (utm_source, gclid, fbclid) or plugin-added keys).
 *  - cPath and products_id are deliberately NOT in the restricted list below,
 *    even though they're catalog-filter params too. Unlike the others, they're
 *    ALSO legitimate on product-detail "_info" pages (product_info, etc),
 *    and that page set varies by installed product type so it can't be safely enumerated
 *    here as a static list (no DB connection exists yet at this point in the
 *    request). cPath/products_id are instead gated downstream by
 *    zen_page_uses_catalog_breadcrumb_lookups() in functions_lookups.php,
 *    which can correctly enumerate that page set from TABLE_PRODUCT_TYPES.
 *  - Checkout/Account pages are NOT included either. They normally require an
 *    authenticated session (absent a guest-checkout plugin), and are also
 *    already covered by the same downstream function.
 *
 * Keys deliberately left OUT, documented so a future edit doesn't "helpfully" add them:
 *  - pID / pid: look like catalog candidates but are used by shopping_cart.php's
 *    own remove/update links, plus ask_a_question and the popup_image pages.
 *    Restricting them here would break the shopping cart's own UI.
 *  - page: used broadly, not catalog-specific.
 *
 * Pages in $catalogPages beyond the obvious "listing" ones, and why:
 *  - redirect: legacy-URL/click-through redirects (action= params).
 *    Rejecting these would break real manufacturer-click tracking and legacy bookmarks.
 *  - advanced_search / advanced_search_result: these are pure 301-redirect stubs.
 *  - brands: reads $_GET['typefilter'] (but functionally inert). Included anyway: the page
 *    reads that key today, so a request carrying it currently renders 200.
 *    Excluding the page would turn that into a 406 for no functional gain.
 *  - featured_categories: $_GET['typefilter'] is dead code, but kept to avoid risk.
 */

/**
 * @param array $get Typically $_GET.
 * @return bool true if a catalog-only filter param was supplied for a page that
 *              never legitimately reads it.
 *
 * @since ZC v2.3.0
 */
function zen_request_has_disallowed_catalog_param(array $get): bool
{
    static $catalogPages = [
        'index', 'search', 'search_result', 'advanced_search', 'advanced_search_result',
        'specials', 'products_new', 'products_all', 'featured_products',
        'product_reviews', 'product_reviews_info', 'product_reviews_write',
        'redirect', 'brands', 'featured_categories',
    ];

    // NOTE: cPath and products_id are intentionally excluded -- see the docblock above.
    static $catalogFilterKeys = [
        'manufacturers_id', 'music_genre_id', 'record_company_id',
        'disp_order', 'sort', 'typefilter', 'filter_id', 'alpha_filter_id',
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

    if (in_array($page, $catalogPages, true)) {
        return false;
    }

    foreach ($catalogFilterKeys as $key) {
        if (isset($get[$key])) {
            return true;
        }
    }

    return false;
}
