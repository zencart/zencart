<?php
/**
 * File contains just the zcQueryBuilderFilterSearchResultsFilter class
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
/**
 * class zcQueryBuilderFilterSearchResultsFilter
 *
 * @package classes
 */
class zcQueryBuilderFilterSearchResultsFilter extends zcAbstractQueryBuilderFilterBase
{
  /**
   * query filter to manage advanced search
   *
   * @see zcAbstractQueryBuilderFilterBase::filterItem()
   */
  public function filterItem()
  {
    $this->handleTaxRates();
    $this->startWhereClauses();
    $this->handleCategories();
    $this->handleManufacturers();
    $this->handleKeywords();
    $this->handleDates();
    $this->handleTaxWhereClauses();
  }
  /**
   * create joins for tax stuff where prices are used in advanced search
   */
  public function handleTaxRates()
  {
    if ((DISPLAY_PRICE_WITH_TAX == 'true') && ((zcRequest::hasGet('pfrom') && zen_not_null(zcRequest::readGet('pfrom'))) || (zcRequest::hasGet('pto') && zen_not_null(zcRequest::readGet('pto'))))) {
      if (! $_SESSION ['customer_country_id']) {
        $_SESSION ['customer_country_id'] = STORE_COUNTRY;
        $_SESSION ['customer_zone_id'] = STORE_ZONE;
      }
      $this->parts ['joinTables'] ['TABLE_TAX_RATES'] = array(
          'table' => TABLE_TAX_RATES,
          'alias' => 'tr',
          'type' => 'left',
          'fkeyFieldLeft' => 'products_tax_class_id',
          'fkeyFieldRight' => 'tax_class_id',
          'addColumns' => FALSE
      );
      $this->parts ['joinTables'] ['TABLE_ZONES_TO_GEO_ZONES'] = array(
          'table' => TABLE_ZONES_TO_GEO_ZONES,
          'alias' => 'gz',
          'type' => 'left',
          'fkeyFieldLeft' => 'tax_zone_id',
          'fkeyFieldRight' => 'geo_zone_id',
          'fkeyTable' => 'TABLE_TAX_RATES',
          'customAnd' => 'AND (gz.zone_country_id IS null OR gz.zone_country_id = 0 OR gz.zone_country_id = :zoneCountryId:) AND (gz.zone_id IS null OR gz.zone_id = 0 OR gz.zone_id = :zoneId:)',
          'addColumns' => FALSE
      );

      $this->parts ['bindVars'] [] = array(
          ':zoneCountryId:',
          $_SESSION ['customer_country_id'],
          'integer'
      );
      $this->parts ['bindVars'] [] = array(
          ':zoneId:',
          $_SESSION ['customer_zone_id'],
          'integer'
      );
    }
  }
  /*
   * initial where clauses
   */
  public function startWhereClauses()
  {
    $this->parts ['whereClauses'] [] = array(
        'custom' => ' AND (p.products_status = 1 '
    );
    $this->parts ['whereClauses'] [] = array(
        'custom' => ' AND pd.language_id = :languageId: '
    );
    $this->parts ['bindVars'] [] = array(
        ':languageId:',
        $_SESSION ['languages_id'],
        'integer'
    );
  }
  /**
   * search by categories
   */
  public function handleCategories()
  {
    if (zcRequest::hasGet('categories_id') && zen_not_null(zcRequest::readGet('categories_id'))) {
      if (zcRequest::readGet('inc_subcat', '') == '1') {
        $categories = zenGetCategoryArrayWithChildren(zcRequest::readGet('categories_id'));
        $categoryList = implode(',', $categories);
        $this->parts ['whereClauses'] [] = array(
            'table' => TABLE_PRODUCTS_TO_CATEGORIES,
            'field' => 'categories_id',
            'value' => $categoryList,
            'type' => 'AND',
            'test' => 'IN'
        );
      } else {
        $this->parts ['whereClauses'] [] = array(
            'table' => TABLE_PRODUCTS_TO_CATEGORIES,
            'field' => 'categories_id',
            'value' => ':categoryId:',
            'type' => 'AND'
        );
        $this->parts ['bindVars'] [] = array(
            ':categoryId:',
            zcRequest::readGet('categories_id'),
            'integer'
        );
      }
    }
  }
  /**
   * search by manufacturers
   */
  public function handleManufacturers()
  {
    if (zcRequest::hasGet('manufacturers_id') && zen_not_null(zcRequest::readGet('manufacturers_id'))) {
      $this->parts ['whereClauses'] [] = array(
          'table' => 'TABLE_MANUFACTURERS',
          'field' => 'manufacturers_id',
          'value' => ':manufacturersId:',
          'type' => 'AND'
      );
      $this->parts ['bindVars'] [] = array(
          ':manufacturersId:',
          zcRequest::readGet('manufacturers_id'),
          'integer'
      );
    }
  }
  /**
   * keyword parsing/handling
   */
  public function handleKeyWords()
  {
    global $keywords;
    if (isset($keywords) && zen_not_null($keywords)) {
      if (zen_parse_search_string(stripslashes(zcRequest::readGet('keyword', '')), $search_keywords)) {
        $this->parts ['whereClauses'] [] = array(
            'custom' => ' AND ('
        );
        for($i = 0, $n = sizeof($search_keywords); $i < $n; $i ++) {
          switch ($search_keywords [$i]) {
            case '(' :
            case ')' :
            case 'and' :
            case 'or' :
              $this->parts ['whereClauses'] [] = array(
                  'custom' => $search_keywords [$i]
              );
              break;
            default :
              $this->parts ['whereClauses'] [] = array(
                  'custom' => "(pd.products_name LIKE '%:keywords" . $i . ":%' OR p.products_model LIKE '%:keywords" . $i . ":%' OR m.manufacturers_name LIKE '%:keywords" . $i . ":%'"
              );
              $this->parts ['bindVars'] [] = array(
                  ':keywords' . $i . ':',
                  $search_keywords [$i],
                  'noquotestring'
              );
              $this->parts ['whereClauses'] [] = array(
                  'custom' => " OR (mtpd.metatags_keywords LIKE '%:keywords" . $i . ":%' AND mtpd.metatags_keywords !='')"
              );
              $this->parts ['whereClauses'] [] = array(
                  'custom' => " OR (mtpd.metatags_description LIKE '%:keywords" . $i . ":%' AND mtpd.metatags_description !='')"
              );
              if (zcRequest::hasGet('search_in_description') && (zcRequest::readGet('search_in_description') == '1')) {
                $this->parts ['whereClauses'] [] = array(
                    'custom' => " OR pd.products_description LIKE '%:keywords" . $i . ":%'"
                );
              }
              $this->parts ['whereClauses'] [] = array(
                  'custom' => ")"
              );
              break;
          }
        }
        $this->parts ['whereClauses'] [] = array(
            'custom' => " ))"
        );
      }
    }
    if (! isset($keywords) || $keywords == "") {
      $this->parts ['whereClauses'] [] = array(
          'custom' => ")"
      );
    }
  }
  /**
   * search by date
   */
  public function handleDates()
  {
    global $dfrom, $dto;

    if (zcRequest::hasGet('dfrom') && zen_not_null(zcRequest::readGet('dfrom')) && (zcRequest::readGet('dfrom') != DOB_FORMAT_STRING)) {
      $this->parts ['whereClauses'] [] = array(
          'table' => TABLE_PRODUCTS,
          'field' => 'products_date_added',
          'value' => ':dateAddedFrom:',
          'type' => 'AND',
          'test' => '>='
      );
      $this->parts ['bindVars'] [] = array(
          ':dateAddedFrom:',
          zen_date_raw($dfrom),
          'date'
      );
    }
    if (zcRequest::hasGet('dto') && zen_not_null(zcRequest::readGet('dto')) && (zcRequest::readGet('dto') != DOB_FORMAT_STRING)) {
      $this->parts ['whereClauses'] [] = array(
          'table' => TABLE_PRODUCTS,
          'field' => 'products_date_added',
          'value' => ':dateAddedTo:',
          'type' => 'AND',
          'test' => '<='
      );
      $this->parts ['bindVars'] [] = array(
          ':dateAddedTo:',
          zen_date_raw($dto),
          'date'
      );
    }
  }
  /**
   * search by price
   */
  public function handleTaxWhereClauses()
  {
    global $currencies, $pfrom, $pto;

    if (zcRequest::hasGet('pfrom') && zcRequest::hasGet('pto')) {
      $rate = $currencies->get_value($_SESSION ['currency']);
      if ($rate) {
        $pfrom = zcRequest::readGet('pfrom') / $rate;
        $pto = zcRequest::readGet('pto') / $rate;
      }

      if (DISPLAY_PRICE_WITH_TAX == 'true') {
        if ($pfrom) {
          $this->parts ['whereClauses'] [] = array(
              'custom' => " AND (p.products_price_sorter * IF(gz.geo_zone_id IS null, 1, 1 + (tr.tax_rate / 100)) >= :priceFrom:)"
          );
          $this->parts ['bindVars'] [] = array(
              ':priceFrom:',
              $pfrom,
              'float'
          );
        }
        if ($pto) {
          $this->parts ['whereClauses'] [] = array(
              'custom' => " AND (p.products_price_sorter * IF(gz.geo_zone_id IS null, 1, 1 + (tr.tax_rate / 100)) <= :priceTo:)"
          );
          $this->parts ['bindVars'] [] = array(
              ':priceTo:',
              $pto,
              'float'
          );
        }
      } else {
        if ($pfrom) {
          $this->parts ['whereClauses'] [] = array(
              'custom' => "  and (p.products_price_sorter >= :priceFrom:)"
          );
          $this->parts ['bindVars'] [] = array(
              ':priceFrom:',
              $pfrom,
              'float'
          );
        }
        if ($pto) {
          $this->parts ['whereClauses'] [] = array(
              'custom' => "  and (p.products_price_sorter <= :priceTo:)"
          );
          $this->parts ['bindVars'] [] = array(
              ':priceTo:',
              $pto,
              'float'
          );
        }
      }
      if ((DISPLAY_PRICE_WITH_TAX == 'true') && ((zcRequest::hasGet('pfrom') && zen_not_null(zcRequest::readGet('pfrom'))) || (zcRequest::hasGet('pto') && zen_not_null(zcRequest::readGet('pto'))))) {
        $this->parts ['whereClauses'] [] = array(
            'custom' => "   GROUP BY p.products_id, tr.tax_priority"
        );
      }
    }
  }
}