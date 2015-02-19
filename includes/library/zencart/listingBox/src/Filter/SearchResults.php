<?php
/**
 * Class SearchResults
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: currencies.php 15880 2010-04-11 16:24:30Z wilt $
 */
namespace ZenCart\ListingBox\Filter;
/**
 * Class SearchResults
 * @package ZenCart\ListingBox\Filter
 */
class SearchResults extends AbstractFilter
{
    /**
     * @var
     */
    protected $productQuery;
    /**
     * @var
     */
    protected $request;

    /**
     * @param array $productQuery
     * @return array
     */
    public function filterItem(array $productQuery)
    {
        $this->productQuery = $productQuery;
        $this->request = $this->diContainer->get('request');
        $this->handleTaxRates();
        $this->startWhereClauses();
        $this->handleCategories();
        $this->handleManufacturers();
        $this->handleKeywords();
        $this->handleDates();
        $this->handleTaxWhereClauses();
        return $this->productQuery;
    }

    /**
     *
     */
    public function handleTaxRates()
    {
        if (DISPLAY_PRICE_WITH_TAX == 'false') {
            return;
        }
        $priceFrom = $this->request->readGet('pfrom');
        $priceTo = $this->request->readGet('pto');

        if (!((zen_not_null($priceFrom)) || (zen_not_null($priceTo)))) {
            return;
        }
        if (!isset($_SESSION ['customer_country_id'])) {
            $_SESSION ['customer_country_id'] = STORE_COUNTRY;
            $_SESSION ['customer_zone_id'] = STORE_ZONE;
        }
        $this->productQuery['joinTables'] ['TABLE_TAX_RATES'] = array(
            'table' => TABLE_TAX_RATES,
            'alias' => 'tr',
            'type' => 'left',
            'fkeyFieldLeft' => 'products_tax_class_id',
            'fkeyFieldRight' => 'tax_class_id',
            'addColumns' => FALSE
        );
        $this->productQuery['joinTables'] ['TABLE_ZONES_TO_GEO_ZONES'] = array(
            'table' => TABLE_ZONES_TO_GEO_ZONES,
            'alias' => 'gz',
            'type' => 'left',
            'fkeyFieldLeft' => 'tax_zone_id',
            'fkeyFieldRight' => 'geo_zone_id',
            'fkeyTable' => 'TABLE_TAX_RATES',
            'customAnd' => 'AND (gz.zone_country_id IS null OR gz.zone_country_id = 0 OR gz.zone_country_id = :zoneCountryId:) AND (gz.zone_id IS null OR gz.zone_id = 0 OR gz.zone_id = :zoneId:)',
            'addColumns' => FALSE
        );

        $this->productQuery['bindVars'] [] = array(
            ':zoneCountryId:',
            $_SESSION ['customer_country_id'],
            'integer'
        );
        $this->productQuery['bindVars'] [] = array(
            ':zoneId:',
            $_SESSION ['customer_zone_id'],
            'integer'
        );
    }

    /**
     *
     */
    public function startWhereClauses()
    {
        $this->productQuery['whereClauses'] [] = array(
            'custom' => ' AND (p.products_status = 1 '
        );
        $this->productQuery ['whereClauses'] [] = array(
            'custom' => ' AND pd.language_id = :languageId: '
        );
        $this->productQuery['bindVars'] [] = array(
            ':languageId:',
            $_SESSION ['languages_id'],
            'integer'
        );
    }

    /**
     *
     */
    public function handleCategories()
    {
        $categoryId = $this->request->readGet('categories_id');
        $incSubCat = $this->request->readGet('inc_subcat');

        if (!zen_not_null($categoryId)) {
            return;
        }
        $whereClause = array(
            'table' => TABLE_PRODUCTS_TO_CATEGORIES,
            'field' => 'categories_id',
            'value' => ':categoryId:',
            'type' => 'AND'
        );

        $bindVars = array(
            ':categoryId:',
            $categoryId,
            'integer'
        );
        if ($incSubCat == '1') {
            $categories = zenGetCategoryArrayWithChildren($categoryId);
            $categoryList = implode(',', $categories);

            $whereClause = array(
                'table' => TABLE_PRODUCTS_TO_CATEGORIES,
                'field' => 'categories_id',
                'value' => $categoryList,
                'type' => 'AND',
                'test' => 'IN'
            );
            unset($bindVars);
        }
        $this->productQuery['whereClauses'][] = $whereClause;
        if (isset($bindVars)) {
            $this->productQuery['bindVars'][] = $bindVars;
        }
    }

    /**
     *
     */
    public function handleManufacturers()
    {
        $manufacturersId = $this->request->readGet('manufacturers_id');
        if (!zen_not_null($manufacturersId)) {
            return;
        }
        $this->productQuery ['whereClauses'] [] = array(
            'table' => 'TABLE_MANUFACTURERS',
            'field' => 'manufacturers_id',
            'value' => ':manufacturersId:',
            'type' => 'AND'
        );
        $this->productQuery ['bindVars'] [] = array(
            ':manufacturersId:',
            $manufacturersId,
            'integer'
        );
    }

    /**
     *
     */
    public function handleKeyWords()
    {
        $searchDescription = $this->request->readGet('search_in_description');
        $keyword = $this->request->readGet('keyword');
        if (!isset($keyword) || $keyword == "") {
            $this->productQuery['whereClauses'] [] = array(
                'custom' => ")"
            );
            return;
        }
        if (!zen_parse_search_string(stripslashes($keyword), $search_keywords)) {
            return;
        }
        $this->productQuery['whereClauses'] [] = array(
            'custom' => ' AND ('
        );
        for ($i = 0, $n = sizeof($search_keywords); $i < $n; $i++) {
            if (in_array($search_keywords [$i], array('(', ')', 'and', 'or'))) {
                $this->productQuery['whereClauses'] [] = array(
                    'custom' => $search_keywords [$i]
                );
            } else {
                $this->productQuery ['whereClauses'] [] = array(
                    'custom' => "(pd.products_name LIKE '%:keywords" . $i . ":%' OR p.products_model LIKE '%:keywords" . $i . ":%' OR m.manufacturers_name LIKE '%:keywords" . $i . ":%'"
                );
                $this->productQuery['bindVars'] [] = array(
                    ':keywords' . $i . ':',
                    $search_keywords [$i],
                    'noquotestring'
                );
                $this->productQuery['whereClauses'] [] = array(
                    'custom' => " OR (mtpd.metatags_keywords LIKE '%:keywords" . $i . ":%' AND mtpd.metatags_keywords !='')"
                );
                $this->productQuery ['whereClauses'] [] = array(
                    'custom' => " OR (mtpd.metatags_description LIKE '%:keywords" . $i . ":%' AND mtpd.metatags_description !='')"
                );
                if ($searchDescription == '1') {
                    $this->productQuery['whereClauses'] [] = array(
                        'custom' => " OR pd.products_description LIKE '%:keywords" . $i . ":%'"
                    );
                }
                $this->productQuery['whereClauses'] [] = array(
                    'custom' => ")"
                );
            }
        }
        $this->productQuery ['whereClauses'] [] = array(
            'custom' => " ))"
        );
    }

    /**
     *
     */
    public function handleDates()
    {
        $dateFrom = $this->request->readGet('dfrom', DOB_FORMAT_STRING);
        $dateTo = $this->request->readGet('dto', DOB_FORMAT_STRING);

        if ($dateFrom != DOB_FORMAT_STRING) {
            $this->productQuery['whereClauses'] [] = array(
                'table' => TABLE_PRODUCTS,
                'field' => 'products_date_added',
                'value' => ':dateAddedFrom:',
                'type' => 'AND',
                'test' => '>='
            );
            $this->productQuery ['bindVars'] [] = array(
                ':dateAddedFrom:',
                zen_date_raw($dateFrom),
                'date'
            );
        }

        if ($dateTo != DOB_FORMAT_STRING) {
            $this->productQuery['whereClauses'] [] = array(
                'table' => TABLE_PRODUCTS,
                'field' => 'products_date_added',
                'value' => ':dateAddedTo:',
                'type' => 'AND',
                'test' => '<='
            );
            $this->productQuery['bindVars'] [] = array(
                ':dateAddedTo:',
                zen_date_raw($dateTo),
                'date'
            );
        }
    }

    /**
     *
     */
    public function handleTaxWhereClauses()
    {

        $currencies = $this->diContainer->get('currencies');

        $priceFrom = $this->request->readGet('pfrom');
        $priceTo = $this->request->readGet('pto');

        if (!isset($priceFrom) || !isset($priceTo)) {
            return;
        }

        $rate = $currencies->get_value($_SESSION ['currency']);
        if ($rate) {
            $priceFrom = $priceFrom / $rate;
            $priceTo = $priceTo / $rate;
        }

        $map = array(DISPLAY_PRICE_WITH_TAX == 'true', $priceFrom, ':priceFrom:',
                     " AND (p.products_price_sorter * IF(gz.geo_zone_id IS null, 1, 1 + (tr.tax_rate / 100)) >= :priceFrom:)");
        $map[] = array(DISPLAY_PRICE_WITH_TAX == 'true', $priceTo, ':priceTo:',
                       " AND (p.products_price_sorter * IF(gz.geo_zone_id IS null, 1, 1 + (tr.tax_rate / 100)) >= :priceFrom:)");
        $map[] = array(DISPLAY_PRICE_WITH_TAX == 'false', $priceFrom, ':priceFrom:',
                       " AND (p.products_price_sorter >= :priceFrom:)");
        $map[] = array(DISPLAY_PRICE_WITH_TAX == 'false', $priceTo, ':priceTo:',
                       "  AND (p.products_price_sorter <= :priceTo:)");

        $this->handleTaxWhereClausesMap($map);

        if (DISPLAY_PRICE_WITH_TAX == 'false') {
            return;
        }
        if (((zen_not_null($priceFrom))) || (zen_not_null($priceTo))) {
            $this->productQuery ['whereClauses'] [] = array(
                'custom' => "   GROUP BY p.products_id, tr.tax_priority"
            );
        }
    }

    /**
     * @param $map
     */
    public function handleTaxWhereClausesMap($map)
    {
        foreach ($map as $mapEntry) {
            if (!($mapEntry[0] && $mapEntry[1])) {
                continue;
            }
            $whereClause = $mapEntry[3];
            $whereClause = str_replace(':insert:', $mapEntry[2], $whereClause);
            $this->productQuery ['whereClauses'] [] = array(
                'custom' => $whereClause
            );
            $this->productQuery['bindVars'] [] = array(
                $mapEntry[2],
                $mapEntry[1],
                'float'
            );
        }
    }
}
