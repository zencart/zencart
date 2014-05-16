<?php
/**
 * File contains just the zcTypeFilterRecordCompany class
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
/**
 * class zcTypeFilterRecordCompany
 *
 * @package classes
 */
class zcTypeFilterRecordCompany extends zcAbstractTypeFilter
{
  public function handleParameterFilters()
  {
    global $current_category_id;

    $this->parts ['selectList'] [] = "r.record_company_name as manufacturers_name";

    $this->parts ['joinTables'] ['TABLE_PRODUCT_MUSIC_EXTRA'] = array(
        'table' => TABLE_PRODUCT_MUSIC_EXTRA,
        'alias' => 'pme',
        'type' => 'LEFT',
        'fkeyFieldLeft' => 'products_id'
    );
    $this->parts ['joinTables'] ['TABLE_RECORD_COMPANY'] = array(
        'table' => TABLE_RECORD_COMPANY,
        'alias' => 'r',
        'type' => 'LEFT',
        'fkeyFieldLeft' => 'record_company_id',
        'fkeyTable' => 'TABLE_PRODUCT_MUSIC_EXTRA'
    );
    if (zcRequest::hasGet('record_company_id') && zcRequest::readGet('record_company_id') != '') {
      $this->parts ['whereClauses'] [] = array(
          'table' => TABLE_RECORD_COMPANY,
          'field' => 'record_company_id',
          'value' => (int)zcRequest::readGet('record_company_id'),
          'type' => 'AND'
      );
      if (zcRequest::hasGet('filter_id') && zen_not_null(zcRequest::readGet('filter_id'))) {
        $this->parts ['joinTables'] ['TABLE_PRODUCTS_TO_CATEGORIES'] = array(
            'table' => TABLE_PRODUCTS_TO_CATEGORIES,
            'alias' => 'p2c',
            'type' => 'LEFT',
            'fkeyFieldLeft' => 'products_id'
        );
        $this->parts ['whereClauses'] [] = array(
            'table' => TABLE_PRODUCTS_TO_CATEGORIES,
            'field' => 'categories_id',
            'value' => (int)zcRequest::readGet('filter_id'),
            'type' => 'AND'
        );
      }
    } else {
      $this->parts ['joinTables'] ['TABLE_PRODUCTS_TO_CATEGORIES'] = array(
          'table' => TABLE_PRODUCTS_TO_CATEGORIES,
          'alias' => 'p2c',
          'type' => 'LEFT',
          'fkeyFieldLeft' => 'products_id'
      );
      $this->parts ['whereClauses'] [] = array(
          'table' => TABLE_PRODUCTS_TO_CATEGORIES,
          'field' => 'categories_id',
          'value' => (int)$current_category_id,
          'type' => 'AND'
      );
      if (zcRequest::hasGet('filter_id') && zen_not_null(zcRequest::readGet('filter_id'))) {
        $this->parts ['whereClauses'] [] = array(
            'table' => TABLE_RECORD_COMPANY,
            'field' => 'record_company_id',
            'value' => (int)zcRequest::readGet('filter_id'),
            'type' => 'AND'
        );
      }
    }
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
            $this->parts ['orderBys'] [] = array(
                'type' => 'custom',
                'field' => "p.products_model " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name"
            );
            break;
          case 'PRODUCT_LIST_NAME' :
            $this->parts ['orderBys'] [] = array(
                'type' => 'custom',
                'field' => "pd.products_name " . ($sort_order == 'd' ? 'desc' : '')
            );
            break;
          case 'PRODUCT_LIST_MANUFACTURER' :
            $this->parts ['orderBys'] [] = array(
                'type' => 'custom',
                'field' => "r.record_company_name " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name"
            );
            break;
          case 'PRODUCT_LIST_QUANTITY' :
            $this->parts ['orderBys'] [] = array(
                'type' => 'custom',
                'field' => "p.products_quantity " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name"
            );
            break;
          case 'PRODUCT_LIST_IMAGE' :
            $this->parts ['orderBys'] [] = array(
                'type' => 'custom',
                'field' => 'pd.products_name'
            );
            break;
          case 'PRODUCT_LIST_WEIGHT' :
            $this->parts ['orderBys'] [] = array(
                'type' => 'custom',
                'field' => "p.products_weight " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name"
            );
            break;
          case 'PRODUCT_LIST_PRICE' :
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
    global $db, $current_category_id;

    if (PRODUCT_LIST_FILTER > 0) {
      if (zcRequest::hasGet('record_company_id') && zcRequest::readGet('record_company_id') != '') {
        $filterlist_sql = "select distinct c.categories_id as id, cd.categories_name as name
      from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd, " .  TABLE_PRODUCT_MUSIC_EXTRA . " pme, " . TABLE_RECORD_COMPANY . " r
      where p.products_status = 1
          and p.products_id = pme.products_id
          and pme.products_id = p2c.products_id
         and pme.record_company_id = r.record_company_id
         and p2c.categories_id = c.categories_id
        and p2c.categories_id = cd.categories_id
        and cd.language_id = '" . (int)$_SESSION ['languages_id'] . "'
        and r.record_company_id = '" . (int)zcRequest::readGet('record_company_id') . "'
      order by cd.categories_name";
      } else {
        $filterlist_sql = "select distinct r.record_company_id as id, r.record_company_name as name
        from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_PRODUCT_MUSIC_EXTRA . " pme, " . TABLE_RECORD_COMPANY . " r
        where p.products_status = 1
          and pme.record_company_id = r.record_company_id
          and p.products_id = p2c.products_id
          and pme.products_id = p.products_id
          and p2c.categories_id = '" . (int)$current_category_id . "'
        order by r.record_company_name";
      }
      $this->filterOptions = array();
      $this->doFilterList = false;
      $this->getOptionSet = false;
      if (PRODUCT_LIST_ALPHA_SORTER == 'true')
        $this->doFilterList = true;
      $filterlist = $db->Execute($filterlist_sql);
      if ($filterlist->RecordCount() > 1) {
        $this->doFilterList = true;
        if (zcRequest::hasGet('record_company_id')) {
          $this->getOptionSet = true;
          $this->getOptionVariable = 'record_company_id';
          $this->filterOptions = array(
              array(
                  'id' => '',
                  'text' => TEXT_ALL_CATEGORIES
              )
          );
        } else {
          $this->filterOptions = array(
              array(
                  'id' => '',
                  'text' => TEXT_ALL_RECORD_COMPANIES
              )
          );
        }
        while ( ! $filterlist->EOF ) {
          $this->filterOptions [] = array(
              'id' => $filterlist->fields ['id'],
              'text' => $filterlist->fields ['name']
          );
          $filterlist->MoveNext();
        }
      }
      // print_r($this->filterOptions);
      if (count($this->filterOptions) == 0)
        $this->doFilterList = false;
    }
  }
}