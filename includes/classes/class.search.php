<?php
/**
 * Product search SQL operations.
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: neekfenwick 2023 Dec 09 New in v2.0.0-alpha1 $
 */

namespace Zencart\Search;

use Zencart\Exceptions\SearchException;

/**
 * Search Options for when searching the product catalogue.
 * Constructor attempts to initialise from $_GET query parameters, or
 * alternatively the fields may be set directly.
 */
class SearchOptions
{
    public string $keywords;
    public string $dfrom;
    public string $dto;
    public float $pfrom;
    public float $pto;
    public int $categories_id;
    public bool $inc_subcat;
    public bool $search_in_description;
    public ?int $manufacturers_id;
    public int $alpha_filter_id;
    public string $sort;

    /**
     * Attempt to initialise from $_GET.
     * May throw SearchException if fields fail to parse.
     */
    public function __construct()
    {
        $this->keywords = $_GET['keyword'] ?? '';
        $this->dfrom = $_GET['dfrom'] ?? '';
        $this->dto = $_GET['dto'] ?? '';
        $this->categories_id = (int)($_GET['categories_id'] ?? 0);
        $this->inc_subcat = ($_GET['inc_subcat'] ?? '0') === '1';
        $this->search_in_description = ($_GET['search_in_description'] ?? '0') === '1';
        $this->manufacturers_id = (int)($_GET['manufacturers_id'] ?? 0);
        $this->alpha_filter_id = (int)($_GET['alpha_filter_id'] ?? 0);
        $this->sort = $_GET['sort'] ?? '';

        // Parse inputs that might fail due to syntax.
        if (!empty($_GET['pfrom']) && !is_numeric($_GET['pfrom'])) {
            throw new SearchException(ERROR_PRICE_FROM_MUST_BE_NUM);
        }
        $this->pfrom = (float)($_GET['pfrom'] ?? '');

        if (!empty($_GET['pto']) && !is_numeric($_GET['pto'])) {
            throw new SearchException(ERROR_PRICE_TO_MUST_BE_NUM);
        }
        $this->pto = (float)($_GET['pto'] ?? '');
    }
}

/**
 * Helper class to perform searches of the product catalogue.
 */
class Search extends \base
{
    /** Options used for our building operations. */
    protected SearchOptions $searchOptions;

    /**
     * Return the current SearchOptions, if any.
     *
     * @return SearchOptions
     */
    public function getSearchOptions(): SearchOptions
    {
        return $this->searchOptions;
    }

    /**
     * Set the SearchOptions to be used in operations.
     *
     * @param SearchOptions $searchOptions
     * @return void
     */
    public function setSearchOptions(SearchOptions $searchOptions)
    {
        $this->searchOptions = $searchOptions;
    }

    /**
     * Builds SQL for searching the product catalogue, given SearchOptions.
     * Note: Sets the global $column_list, needed by product_listing template.
     *
     * @param SearchOptions $searchOptions The options for the search.
     * @return string The built SQL.
     */
    public function buildSearchSQL() {
        global $db, $messageStack, $currencies, $column_list;

        if (empty($this->searchOptions)) {
            throw new SearchException(ERROR_MISSING_SEARCH_OPTIONS);
        }

        // -----
        // Give an observer the chance to indicate that there's another element to the search
        // that **is** provided, enabling the search to continue.
        //
        $search_additional_clause = false;
        $this->notify('NOTIFY_ADVANCED_SEARCH_RESULTS_ADDL_CLAUSE', [], $search_additional_clause);

        if ($search_additional_clause === false &&
            empty($this->searchOptions->keywords) &&
            (
                (empty($this->searchOptions->dfrom) && empty($this->searchOptions->dto)) &&
                (empty($this->searchOptions->pfrom) || $this->searchOptions->pfrom <= 0) &&
                (empty($this->searchOptions->pto) || $this->searchOptions->pto <= 0)
            )) {
            throw new SearchException(ERROR_AT_LEAST_ONE_INPUT);
        } else {
            $dfrom_array = [];
            $dto_array = [];

            if (!empty($this->searchOptions->dfrom) &&
                !zen_checkdate($this->searchOptions->dfrom, DOB_FORMAT_STRING, $dfrom_array)) {
                throw new SearchException(ERROR_INVALID_FROM_DATE);
            }

            if (!empty($this->searchOptions->dto) &&
                !zen_checkdate($this->searchOptions->dto, DOB_FORMAT_STRING, $dto_array)) {
                throw new SearchException(ERROR_INVALID_TO_DATE);
            }

            if (!empty($this->searchOptions->dfrom) && !empty($this->searchOptions->dto)) {
                if (mktime(0, 0, 0, $dfrom_array[1], $dfrom_array[2], $dfrom_array[0]) > mktime(0, 0, 0, $dto_array[1], $dto_array[2], $dto_array[0])) {
                    throw new SearchException(ERROR_TO_DATE_LESS_THAN_FROM_DATE);
                }
            }

            if ($this->searchOptions->pfrom > $this->searchOptions->pto) {
                throw new SearchException(ERROR_PRICE_TO_LESS_THAN_PRICE_FROM);
            }

            if (!empty($this->searchOptions->keywords) &&
                !zen_parse_search_string(stripslashes($this->searchOptions->keywords), $search_keywords)) {
                throw new SearchException(ERROR_INVALID_KEYWORDS);
            }
        }

        if (empty($this->searchOptions->dfrom) && empty($this->searchOptions->dto) &&
            empty($this->searchOptions->pfrom) && empty($this->searchOptions->pto) &&
            empty($this->searchOptions->keywords) && $search_additional_clause === false) {
            throw new SearchException(ERROR_AT_LEAST_ONE_INPUT);
        }

        $define_list = [
            'PRODUCT_LIST_MODEL' => PRODUCT_LIST_MODEL,
            'PRODUCT_LIST_NAME' => PRODUCT_LIST_NAME,
            'PRODUCT_LIST_MANUFACTURER' => PRODUCT_LIST_MANUFACTURER,
            'PRODUCT_LIST_PRICE' => PRODUCT_LIST_PRICE,
            'PRODUCT_LIST_QUANTITY' => PRODUCT_LIST_QUANTITY,
            'PRODUCT_LIST_WEIGHT' => PRODUCT_LIST_WEIGHT,
            'PRODUCT_LIST_IMAGE' => PRODUCT_LIST_IMAGE
        ];

        asort($define_list);

        $column_list = [];
        foreach ($define_list as $column => $value) {
            if ($value) {
                $column_list[] = $column;
            }
        }

        $select_column_list = '';

        foreach ($column_list as $column) {
            if (in_array($column, ['PRODUCT_LIST_NAME', 'PRODUCT_LIST_PRICE'])) {
                continue;
            }

            if (!empty($select_column_list)) {
                $select_column_list .= ', ';
            }

            switch ($column) {
                case 'PRODUCT_LIST_MODEL':
                    $select_column_list .= 'p.products_model';
                    break;
                case 'PRODUCT_LIST_MANUFACTURER':
                    $select_column_list .= 'm.manufacturers_name';
                    break;
                case 'PRODUCT_LIST_QUANTITY':
                    $select_column_list .= 'p.products_quantity';
                    break;
                case 'PRODUCT_LIST_IMAGE':
                    $select_column_list .= 'p.products_image';
                    break;
                case 'PRODUCT_LIST_WEIGHT':
                    $select_column_list .= 'p.products_weight';
                    break;
            }
        }
        /*
        // always add quantity regardless of whether or not it is in the listing for add to cart buttons
        if (PRODUCT_LIST_QUANTITY < 1) {
        $select_column_list .= ', p.products_quantity ';
        }
        */

        // always add quantity regardless of whether or not it is in the listing for add to cart buttons
        if (PRODUCT_LIST_QUANTITY < 1) {
            if (empty($select_column_list)) {
                $select_column_list .= ' p.products_quantity ';
            } else {
                $select_column_list .= ', p.products_quantity ';
            }
        }

        if (!empty($select_column_list)) {
            $select_column_list .= ', ';
        }

        // Notifier Point
        $this->notify('NOTIFY_SEARCH_COLUMNLIST_STRING', $select_column_list, $select_column_list);

        $select_str = "SELECT DISTINCT " . $select_column_list .
            " p.products_sort_order, m.manufacturers_id, p.products_id, pd.products_name,
            p.products_price, p.products_tax_class_id, p.products_price_sorter,
            p.products_qty_box_status, p.master_categories_id, p.product_is_call ";

        if ((DISPLAY_PRICE_WITH_TAX == 'true') && (!empty($this->searchOptions->pfrom) || !empty($this->searchOptions->pto))) {
            $select_str .= ", SUM(tr.tax_rate) AS tax_rate ";
        }

        // Notifier Point
        $this->notify('NOTIFY_SEARCH_SELECT_STRING', $select_str, $select_str);

        $from_str = "FROM (" . TABLE_PRODUCTS . " p
                    LEFT JOIN " . TABLE_MANUFACTURERS . " m
                    USING(manufacturers_id), " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_CATEGORIES . " c, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c )";

        if (ADVANCED_SEARCH_INCLUDE_METATAGS == 'true') {
            $from_str .=
                " LEFT JOIN " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . " mtpd
                    ON (mtpd.products_id= p2c.products_id AND mtpd.language_id = :languagesID)";
            $from_str = $db->bindVars($from_str, ':languagesID', $_SESSION['languages_id'], 'integer');
        }

        if ((DISPLAY_PRICE_WITH_TAX == 'true') && !empty($this->searchOptions->pfrom) || !empty($this->searchOptions->pto)) {
            if (empty($_SESSION['customer_country_id'])) {
                $_SESSION['customer_country_id'] = STORE_COUNTRY;
                $_SESSION['customer_zone_id'] = STORE_ZONE;
            }
            $from_str .= " LEFT JOIN " . TABLE_TAX_RATES . " tr
                        ON p.products_tax_class_id = tr.tax_class_id
                        LEFT JOIN " . TABLE_ZONES_TO_GEO_ZONES . " gz
                        ON tr.tax_zone_id = gz.geo_zone_id
                        AND (gz.zone_country_id IS null OR gz.zone_country_id = 0 OR gz.zone_country_id = :zoneCountryID)
                        AND (gz.zone_id IS null OR gz.zone_id = 0 OR gz.zone_id = :zoneID)";

            $from_str = $db->bindVars($from_str, ':zoneCountryID', $_SESSION['customer_country_id'], 'integer');
            $from_str = $db->bindVars($from_str, ':zoneID', $_SESSION['customer_zone_id'], 'integer');
        }

        // Notifier Point
        $this->notify('NOTIFY_SEARCH_FROM_STRING', $from_str, $from_str);

        $where_str = " WHERE (p.products_status = 1
                    AND p.products_id = pd.products_id
                    AND pd.language_id = :languagesID
                    AND p.products_id = p2c.products_id
                    AND p2c.categories_id = c.categories_id ";

        $where_str = $db->bindVars($where_str, ':languagesID', $_SESSION['languages_id'], 'integer');

        // reset previous selection

        if (!empty($this->searchOptions->categories_id)) {
            if ($this->searchOptions->inc_subcat) {
                $subcategories_array = [];
                zen_get_subcategories($subcategories_array, $this->searchOptions->categories_id);
                $where_str .= " AND p2c.products_id = p.products_id
                                AND p2c.products_id = pd.products_id
                                AND (p2c.categories_id = :categoriesID";

                $where_str = $db->bindVars($where_str, ':categoriesID', $this->searchOptions->categories_id, 'integer');

                if (count($subcategories_array) > 0) {
                    $where_str .= " OR p2c.categories_id in (";
                    for ($i = 0, $n = count($subcategories_array); $i < $n; $i++) {
                        $where_str .= " :categoriesID";
                        if ($i + 1 < $n) {
                            $where_str .= ",";
                        }
                        $where_str = $db->bindVars($where_str, ':categoriesID', $subcategories_array[$i], 'integer');
                    }
                    $where_str .= ")";
                }
                $where_str .= ")";
            } else {
                $where_str .= " AND p2c.products_id = p.products_id
                                AND p2c.products_id = pd.products_id
                                AND pd.language_id = :languagesID
                                AND p2c.categories_id = :categoriesID";

                $where_str = $db->bindVars($where_str, ':categoriesID', $this->searchOptions->categories_id, 'integer');
                $where_str = $db->bindVars($where_str, ':languagesID', $_SESSION['languages_id'], 'integer');
            }
        }

        if (!empty($this->searchOptions->manufacturers_id)) {
            $where_str .= " AND m.manufacturers_id = :manufacturersID";
            $where_str = $db->bindVars($where_str, ':manufacturersID', $this->searchOptions->manufacturers_id, 'integer');
        }

        if (!empty($this->searchOptions->keywords)) {
            $keyword_search_fields = [
                'pd.products_name',
                'p.products_model',
                'm.manufacturers_name',
            ];

            if (ADVANCED_SEARCH_INCLUDE_METATAGS == 'true') {
                $keyword_search_fields[] = 'mtpd.metatags_keywords';
                $keyword_search_fields[] = 'mtpd.metatags_description';
            }

            if ($this->searchOptions->search_in_description) {
                $keyword_search_fields[] = 'pd.products_description';
            }

            $this->notify('NOTIFY_SEARCH_MATCHING_KEYWORD_FIELDS', '', $keyword_search_fields);

            $where_str .= zen_build_keyword_where_clause($keyword_search_fields, trim($this->searchOptions->keywords));
        }
        $where_str .= ')';
        if (!empty($this->searchOptions->alpha_filter_id)) {
            $alpha_sort = " and (pd.products_name LIKE '" . chr($this->searchOptions->alpha_filter_id) . "%') ";
            $where_str .= $alpha_sort;
        } else {
            $alpha_sort = '';
            $where_str .= $alpha_sort;
        }

        if (!empty($this->searchOptions->dfrom)) {
            $where_str .= " AND p.products_date_added >= :dateAdded";
            $where_str = $db->bindVars($where_str, ':dateAdded', zen_date_raw($this->searchOptions->dfrom), 'date');
        }

        if (!empty($this->searchOptions->dto)) {
            $where_str .= " and p.products_date_added <= :dateAdded";
            $where_str = $db->bindVars($where_str, ':dateAdded', zen_date_raw($this->searchOptions->dto), 'date');
        }

        $rate = $currencies->get_value($_SESSION['currency']);

        if ($rate) {
            if (!empty($this->searchOptions->pfrom)) {
                $this->searchOptions->pfrom = $this->searchOptions->pfrom / $rate;
            }
            if (!empty($this->searchOptions->pto)) {
                $this->searchOptions->pto = $this->searchOptions->pto / $rate;
            }
        }

        if (DISPLAY_PRICE_WITH_TAX == 'true') {
            if (!empty($this->searchOptions->pfrom)) {
                $where_str .= " AND (p.products_price_sorter * IF(gz.geo_zone_id IS null, 1, 1 + (tr.tax_rate / 100)) >= :price)";
                $where_str = $db->bindVars($where_str, ':price', $this->searchOptions->pfrom, 'float');
            }
            if (!empty($this->searchOptions->pto)) {
                $where_str .= " AND (p.products_price_sorter * IF(gz.geo_zone_id IS null, 1, 1 + (tr.tax_rate / 100)) <= :price)";
                $where_str = $db->bindVars($where_str, ':price', $this->searchOptions->pto, 'float');
            }
        } else {
            if (!empty($this->searchOptions->pfrom)) {
                $where_str .= " and (p.products_price_sorter >= :price)";
                $where_str = $db->bindVars($where_str, ':price', $this->searchOptions->pfrom, 'float');
            }
            if (!empty($this->searchOptions->pto)) {
                $where_str .= " and (p.products_price_sorter <= :price)";
                $where_str = $db->bindVars($where_str, ':price', $this->searchOptions->pto, 'float');
            }
        }


        $order_str = '';

        // Notifier Point
        $this->notify('NOTIFY_SEARCH_WHERE_STRING', $this->searchOptions->keywords, $where_str, $keyword_search_fields);


        if ((DISPLAY_PRICE_WITH_TAX == 'true') && (!empty($this->searchOptions->pfrom)) || !empty($this->searchOptions->pto)) {
            $where_str .= " group by p.products_id, tr.tax_priority";
        }

        // set the default sort order setting from the Admin when not defined by customer
        if (empty($this->searchOptions->sort) and PRODUCT_LISTING_DEFAULT_SORT_ORDER != '') {
            $this->searchOptions->sort = PRODUCT_LISTING_DEFAULT_SORT_ORDER;
        }
        if (empty($this->searchOptions->sort) ||
            (!preg_match('/[1-8][ad]/', $this->searchOptions->sort)) ||
            (substr($this->searchOptions->sort, 0, 1) > count($column_list))) {
            for ($col = 0, $n = sizeof($column_list); $col < $n; $col++) {
                if ($column_list[$col] == 'PRODUCT_LIST_NAME') {
                    $this->searchOptions->sort = $col + 1 . 'a';
                    $order_str .= ' ORDER BY pd.products_name';
                    break;
                } else {
                    // sort by products_sort_order when PRODUCT_LISTING_DEFAULT_SORT_ORDER ia left blank
                    // for reverse, descending order use:
                    //       $listing_sql .= " order by p.products_sort_order desc, pd.products_name";
                    $order_str .= " order by p.products_sort_order, pd.products_name";
                    break;
                }
            }
            // if set to nothing use products_sort_order and PRODUCTS_LIST_NAME is off
            if (PRODUCT_LISTING_DEFAULT_SORT_ORDER == '') {
                $this->searchOptions->sort = '20a';
            }
        } else {
            $sort_col = substr($this->searchOptions->sort, 0, 1);
            $sort_order = substr($this->searchOptions->sort, -1);
            $order_str = ' order by ';
            switch ($column_list[$sort_col - 1]) {
                case 'PRODUCT_LIST_MODEL':
                    $order_str .= "p.products_model " . ($sort_order == 'd' ? "desc" : "") . ", pd.products_name";
                    break;
                case 'PRODUCT_LIST_NAME':
                    $order_str .= "pd.products_name " . ($sort_order == 'd' ? "desc" : "");
                    break;
                case 'PRODUCT_LIST_MANUFACTURER':
                    $order_str .= "m.manufacturers_name " . ($sort_order == 'd' ? "desc" : "") . ", pd.products_name";
                    break;
                case 'PRODUCT_LIST_QUANTITY':
                    $order_str .= "p.products_quantity " . ($sort_order == 'd' ? "desc" : "") . ", pd.products_name";
                    break;
                case 'PRODUCT_LIST_IMAGE':
                    $order_str .= "pd.products_name";
                    break;
                case 'PRODUCT_LIST_WEIGHT':
                    $order_str .= "p.products_weight " . ($sort_order == 'd' ? "desc" : "") . ", pd.products_name";
                    break;
                case 'PRODUCT_LIST_PRICE':
                    //        $order_str .= "final_price " . ($sort_order == 'd' ? "desc" : "") . ", pd.products_name";
                    $order_str .= "p.products_price_sorter " . ($sort_order == 'd' ? "desc" : "") . ", pd.products_name";
                    break;
            }
        }

        $this->notify('NOTIFY_SEARCH_REAL_ORDERBY_STRING', $order_str, $order_str);

        $listing_sql = $select_str . $from_str . $where_str . $order_str;
        // Notifier Point
        $this->notify('NOTIFY_SEARCH_ORDERBY_STRING', $listing_sql);

        return $listing_sql;
    }
}
