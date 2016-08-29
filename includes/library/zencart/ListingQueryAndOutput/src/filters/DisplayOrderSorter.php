<?php
/**
 * Class DisplayOrderSorter
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: currencies.php 15880 2010-04-11 16:24:30Z wilt $
 */
namespace ZenCart\ListingQueryAndOutput\filters;

/**
 * Class DisplayOrderSorter
 * @package ZenCart\ListingQueryAndOutput\filters
 */
class DisplayOrderSorter extends AbstractFilter implements FilterInterface
{
    /**
     * @param array $listingQuery
     * @return array
     */
    public function filterItem(array $listingQuery)
    {
        $dispOrder = $this->request->readGet('disp_order', 0);
        $this->tplVars  ['displayOrderDefault'] = $this->params ['defaultSortOrder'];
        $this->tplVars  ['displayOrder'] = $dispOrder;
        if (!$this->request->has('disp_order')) {
            $dispOrder = $this->tplVars  ['displayOrderDefault'];
            $this->tplVars ['displayOrder'] = $this->tplVars ['displayOrderDefault'];
        }
        $map = $this->buildMap();
        $orderBy = " p.products_sort_order";
        if ($dispOrder == 0) {
            $dispOrder = $this->tplVars  ['displayOrderDefault'];
            $this->tplVars ['displayOrder'] = $this->tplVars ['displayOrderDefault'];
        }
        if (isset($map[$dispOrder])) {
            $orderBy = $map[$dispOrder];
        }
        $listingQuery['orderBys'] [] = array(
            'type' => 'custom',
            'field' => $orderBy
        );
        return $listingQuery;
    }

    /**
     * @return array
     */
    protected function buildMap()
    {
        $map = array();
        $map[1] = " pd.products_name";
        $map[2] = " pd.products_name DESC";
        $map[3] = " p.products_price_sorter, pd.products_name";
        $map[4] = " p.products_price_sorter DESC, pd.products_name";
        $map[5] = " p.products_model";
        $map[6] = " p.products_date_added DESC, pd.products_name";
        $map[7] = " p.products_date_added, pd.products_name";
        return $map;
    }
} 
