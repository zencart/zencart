<?php
/**
 * File contains just the zcQueryBuilderFilterAlphaFilter class
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
/**
 * class zcQueryBuilderFilterAlphaFilter
 *
 * @package classes
 */
class zcQueryBuilderFilterAlphaFilter extends zcAbstractQueryBuilderFilterBase
{
  public function filterItem()
  {
    if (zcRequest::hasGet('alpha_filter_id') && (int)zcRequest::readGet('alpha_filter_id') > 0) {
      $alpha_sort_list_search = explode(';', '0:reset_placeholder;' . PRODUCT_LIST_ALPHA_SORTER_LIST);
      for($j = 0, $n = sizeof($alpha_sort_list_search); $j < $n; $j ++) {
        if ((int)zcRequest::readGet('alpha_filter_id') == $j) {
          $elements = explode(':', $alpha_sort_list_search [$j]);
          $pattern = str_replace(',', '', $elements [1]);
          $this->parts ['whereClauses'] [] = array(
              'custom' => " AND pd.products_name REGEXP '^[" . $pattern . "]' "
          );
          break;
        }
      }
    }
  }
}