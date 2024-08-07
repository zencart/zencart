<?php
/**
 * Header code file for the Search Results page
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Jul 08 Modified in v2.1.0-alpha1 $
 *
 * @var Zencart\Search\Search $search
 */

use Zencart\Exceptions\SearchException;
use Zencart\Search\SearchOptions;

// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_ADVANCED_SEARCH_RESULTS');

if (!defined('KEYWORD_FORMAT_STRING')) {
    define('KEYWORD_FORMAT_STRING', 'keywords');
}
if (!defined('ADVANCED_SEARCH_INCLUDE_METATAGS')) {
    define('ADVANCED_SEARCH_INCLUDE_METATAGS', 'true');
}

require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));

// set the product filters according to selected product type
$typefilter = $_GET['typefilter'] ?? 'default';
require(zen_get_index_filters_directory($typefilter . '_filter.php'));

$error = false;
$missing_one_input = false;

$keywords = $_GET['keyword'] ?? '';

$price_check_error = false;

try {
    // Perform the search using the provided parameters.
    $searchOptions = new SearchOptions();

    $search->setSearchOptions($searchOptions);
    $listing_sql = $search->buildSearchSQL();
    $keywords = $searchOptions->keywords;

    $result = new \splitPageResults($listing_sql, MAX_DISPLAY_PRODUCTS_LISTING, 'p.products_id', 'page');
    $zco_notifier->notify('NOTIFY_SEARCH_RESULTS', $listing_sql, $keywords, $result);

    // Expose changed search options in $_GET for product listing page.
    $_GET['sort'] = $searchOptions->sort;

    // if no results were found, show a customisable message.
    if ($result->number_of_rows === 0) {
        $message = TEXT_NO_PRODUCTS;
        $zco_notifier->notify('NOTIFY_SEARCH_NO_RESULTS_MESSAGE', $result, $search, $message);
        $messageStack->add_session('search', $message, 'caution');
        zen_redirect(zen_href_link(FILENAME_SEARCH, zen_get_all_get_params('action')));
    }
    // if only one product found in search results, go directly to the product page, instead of displaying a link to just one item:
    if ($result->number_of_rows === 1 && SKIP_SINGLE_PRODUCT_CATEGORIES === 'True') {
        $result = $db->Execute($result->sql_query);
        zen_redirect(zen_href_link(zen_get_info_page($result->fields['products_id']), 'cPath=' . zen_get_product_path($result->fields['products_id']) . '&products_id=' . $result->fields['products_id']));
    }
} catch (SearchException $e) {
    $messageStack->add_session('search', $e->getMessage());
    zen_redirect(zen_href_link(FILENAME_SEARCH, zen_get_all_get_params(), 'NONSSL', true, false));
}

$breadcrumb->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_SEARCH));
//$breadcrumb->add(NAVBAR_TITLE_2);
$breadcrumb->add(zen_output_string_protected($keywords));

// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_ADVANCED_SEARCH_RESULTS', $keywords);
