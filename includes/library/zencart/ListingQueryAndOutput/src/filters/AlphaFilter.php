<?php
/**
 * Class AlphaFilter
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: currencies.php 15880 2010-04-11 16:24:30Z wilt $
 */
namespace ZenCart\ListingQueryAndOutput\filters;

/**
 * Class AlphaFilter
 * @package ZenCart\ListingQueryAndOutput\filters
 */
class AlphaFilter extends AbstractFilter implements FilterInterface
{
    /**
     * @param array $listingQuery
     * @return array
     */
    public function filterItem(array $listingQuery)
    {
        if ((int)$this->request->readGet('alpha_filter_id', 0) == 0) {
            return $listingQuery;
        }
        $alphaSortListSearch = explode(';', '0:reset_placeholder;' . PRODUCT_LIST_ALPHA_SORTER_LIST);
        for ($j = 0, $n = sizeof($alphaSortListSearch); $j < $n; $j++) {
            if ((int)$this->request->readGet('alpha_filter_id') == $j) {
                $elements = explode(':', $alphaSortListSearch [$j]);
                $pattern = str_replace(',', '', $elements [1]);
                $listingQuery ['whereClauses'] [] = array(
                    'custom' => " AND pd.products_name REGEXP '^[" . $pattern . "]' "
                );
                break;
            }
        }
        return $listingQuery;
    }
}
