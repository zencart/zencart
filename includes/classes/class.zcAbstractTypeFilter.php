<?php
/**
 * File contains just the zcAbstractTypeFilter class
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
/**
 * class zcAbstractTypeFilter
 *
 * @package classes
 */
class zcAbstractTypeFilter extends base
{
  public function __construct($parts)
  {
    $this->parts = $parts;
    $this->initDefineList();
    $this->initColumnList();
    $this->initSortParameter();
    if (! zcRequest::hasGet('keyword'))
      $this->handleParameterFilters();
    $this->handleColumnSorters();
    $this->buildOptionFilter();
  }
  public function initDefineList()
  {
    $this->defineList = array(
        'PRODUCT_LIST_MODEL' => PRODUCT_LIST_MODEL,
        'PRODUCT_LIST_NAME' => PRODUCT_LIST_NAME,
        'PRODUCT_LIST_MANUFACTURER' => PRODUCT_LIST_MANUFACTURER,
        'PRODUCT_LIST_PRICE' => PRODUCT_LIST_PRICE,
        'PRODUCT_LIST_QUANTITY' => PRODUCT_LIST_QUANTITY,
        'PRODUCT_LIST_WEIGHT' => PRODUCT_LIST_WEIGHT,
        'PRODUCT_LIST_IMAGE' => PRODUCT_LIST_IMAGE
    );

    asort($this->defineList);
    reset($this->defineList);
  }
  public function initColumnList()
  {
    $this->columnList = array();
    foreach ( $this->defineList as $key => $value ) {
      if ($value > 0)
        $this->columnList [] = $key;
    }
  }
  public function initSortParameter()
  {
    if (zcRequest::hasGet('sort') && strlen(zcRequest::readGet('sort')) > 3) {
      zcRequest::set('sort', substr(zcRequest::readGet('sort'), 0, 3), 'get');
    }
    if (! zcRequest::hasGet('sort') and PRODUCT_LISTING_DEFAULT_SORT_ORDER != '') {
      zcRequest::set('sort', PRODUCT_LISTING_DEFAULT_SORT_ORDER, 'get');
    }
  }
  public function handleParameterFilters()
  {
  }
  public function handleColumnSorters()
  {
    if (isset($this->columnList)) {
      if ((! zcRequest::hasGet('sort')) || (zcRequest::hasGet('sort') && ! preg_match('/[1-8][ad]/', zcRequest::readGet('sort'))) || (substr(zcRequest::readGet('sort'), 0, 1) > sizeof($this->columnList))) {
        for($i = 0, $n = sizeof($this->columnList); $i < $n; $i ++) {
          if (isset($this->columnList [$i]) && $this->columnList [$i] == 'PRODUCT_LIST_NAME') {
            zcRequest::set('sort', $i + 1 . 'a', 'get');
            $this->parts ['orderBys'] [] = array(
                'type' => 'custom',
                'field' => 'p.products_sort_order, pd.products_name'
            );
            break;
          } else {
            // sort by products_sort_order when PRODUCT_LISTING_DEFAULT_SORT_ORDER is left blank
            // for reverse, descending order use:
            // $listing_sql .= " order by p.products_sort_order desc, pd.products_name";
            $this->parts ['orderBys'] [] = array(
                'type' => 'custom',
                'field' => 'p.products_sort_order, pd.products_name'
            );
            break;
          }
        }
        // if set to nothing use products_sort_order and PRODUCTS_LIST_NAME is off
        if (PRODUCT_LISTING_DEFAULT_SORT_ORDER == '') {
          zcRequest::set('sort', '20a', 'get');
        }
      } else {
        $sort_col = substr(zcRequest::readGet('sort'), 0, 1);
        $sort_order = substr(zcRequest::readGet('sort'), - 1);
        switch ($column_list [$sort_col - 1]) {
          case 'PRODUCT_LIST_MODEL' :
            $listing_sql .= " order by p.products_model " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name";
            $this->parts ['orderBys'] [] = array(
                'type' => 'custom',
                'field' => "p.products_model " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name"
            );
            break;
          case 'PRODUCT_LIST_NAME' :
            $listing_sql .= " order by pd.products_name " . ($sort_order == 'd' ? 'desc' : '');
            $this->parts ['orderBys'] [] = array(
                'type' => 'custom',
                'field' => "pd.products_name " . ($sort_order == 'd' ? 'desc' : '')
            );
            break;
          case 'PRODUCT_LIST_MANUFACTURER' :
            $listing_sql .= " order by m.manufacturers_name " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name";
            $this->parts ['orderBys'] [] = array(
                'type' => 'custom',
                'field' => "m.manufacturers_name " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name"
            );
            break;
          case 'PRODUCT_LIST_QUANTITY' :
            $listing_sql .= " order by p.products_quantity " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name";
            $this->parts ['orderBys'] [] = array(
                'type' => 'custom',
                'field' => "p.products_quantity " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name"
            );
            break;
          case 'PRODUCT_LIST_IMAGE' :
            $listing_sql .= " order by pd.products_name";
            $this->parts ['orderBys'] [] = array(
                'type' => 'custom',
                'field' => 'pd.products_name'
            );
            break;
          case 'PRODUCT_LIST_WEIGHT' :
            $listing_sql .= " order by p.products_weight " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name";
            $this->parts ['orderBys'] [] = array(
                'type' => 'custom',
                'field' => "p.products_weight " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name"
            );
            break;
          case 'PRODUCT_LIST_PRICE' :
            $listing_sql .= " order by p.products_price_sorter " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name";
            $this->parts ['orderBys'] [] = array(
                'type' => 'custom',
                'field' => "p.products_price_sorter " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name"
            );
            break;
        }
      }
    }
  }
  public function buildOptionFilter()
  {
  }
  public function getColumnList()
  {
    return $this->columnList;
  }
  public function getParts()
  {
    return $this->parts;
  }
  public function getDoFilterList()
  {
    return $this->doFilterList;
  }
  public function getGetOptionsSet()
  {
    return $this->getOptionsSet;
  }
  public function getGetOptionVariable()
  {
    return $this->getOptionVariable;
  }
  public function getFilterOptions()
  {
    return $this->filterOptions;
  }
}