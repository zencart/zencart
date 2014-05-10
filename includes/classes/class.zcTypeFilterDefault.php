<?php
/**
 * File contains just the zcTypeFilterDefault class
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
/**
 * class zcTypeFilterDefault
 *
 * @package classes
 */
class zcTypeFilterDefault extends zcAbstractTypeFilter
{
  public function handleParameterFilters()
  {
    global $current_category_id;

    $this->parts ['selectList'] [] = "m.manufacturers_name";

    $this->parts ['joinTables'] ['TABLE_MANUFACTURERS'] = array(
        'table' => TABLE_MANUFACTURERS,
        'alias' => 'm',
        'type' => 'LEFT',
        'fkeyFieldLeft' => 'manufacturers_id'
    );
    if (zcRequest::hasGet('manufacturers_id') && zcRequest::readGet('manufacturers_id') != '') {
      $this->parts ['whereClauses'] [] = array(
          'table' => TABLE_MANUFACTURERS,
          'field' => 'manufacturers_id',
          'value' => (int)zcRequest::readGet('manufacturers_id'),
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
            'table' => TABLE_MANUFACTURERS,
            'field' => 'manufacturers_id',
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
    global $db, $current_category_id;

    if (PRODUCT_LIST_FILTER > 0) {
      if (zcRequest::hasGet('manufacturers_id') && zcRequest::readGet('manufacturers_id') != '') {
        $filterlist_sql = "select distinct c.categories_id as id, cd.categories_name as name
      from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
      where p.products_status = 1
        and p.products_id = p2c.products_id
        and p2c.categories_id = c.categories_id
        and p2c.categories_id = cd.categories_id
        and cd.language_id = '" . (int)$_SESSION ['languages_id'] . "'
        and p.manufacturers_id = '" . (int)zcRequest::readGet('manufacturers_id') . "'
      order by cd.categories_name";
      } else {
        $filterlist_sql = "select distinct m.manufacturers_id as id, m.manufacturers_name as name
      from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_MANUFACTURERS . " m
      where p.products_status = 1
        and p.manufacturers_id = m.manufacturers_id
        and p.products_id = p2c.products_id
        and p2c.categories_id = '" . (int)$current_category_id . "'
      order by m.manufacturers_name";
      }
      $this->filterOptions = array();
      $this->doFilterList = false;
      $this->getOptionSet = false;
      if (PRODUCT_LIST_ALPHA_SORTER == 'true')
        $this->doFilterList = true;
      $filterlist = $db->Execute($filterlist_sql);
      if ($filterlist->RecordCount() > 1) {
        $this->doFilterList = true;
        if (zcRequest::hasGet('manufacturers_id')) {
          $this->getOptionSet = true;
          $this->getOptionVariable = 'manufacturers_id';
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
                  'text' => TEXT_ALL_MANUFACTURERS
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