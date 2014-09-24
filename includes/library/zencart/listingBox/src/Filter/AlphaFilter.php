<?php
/**
 * Class AlphaFilter
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: currencies.php 15880 2010-04-11 16:24:30Z wilt $
 */
namespace ZenCart\ListingBox\Filter;
/**
 * Class AlphaFilter
 * @package ZenCart\ListingBox\Filter
 */
class AlphaFilter extends AbstractFilter
{
    /**
     * @param array $productQuery
     * @return array
     */
    public function filterItem(array $productQuery)
    {
        $request = $this->diContainer->get('request');
        if (!$request->has('alpha_filter_id') || (int)$request->readGet('alpha_filter_id') == 0) {
            return $productQuery;
        }
        $alphaSortListSearch = explode(';', '0:reset_placeholder;' . PRODUCT_LIST_ALPHA_SORTER_LIST);
        for ($j = 0, $n = sizeof($alphaSortListSearch); $j < $n; $j++) {
            if ((int)$request->readGet('alpha_filter_id') == $j) {
                $elements = explode(':', $alphaSortListSearch [$j]);
                $pattern = str_replace(',', '', $elements [1]);
                $productQuery ['whereClauses'] [] = array(
                    'custom' => " AND pd.products_name REGEXP '^[" . $pattern . "]' "
                );
                break;
            }
        }
        return $productQuery;
    }
}
