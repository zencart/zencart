<?php
/**
 * Class DisplayOrderSorter
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: currencies.php 15880 2010-04-11 16:24:30Z wilt $
 */
namespace ZenCart\ListingBox\Filter;
/**
 * Class DisplayOrderSorter
 * @package ZenCart\ListingBox\Filter
 */
class DisplayOrderSorter extends AbstractFilter
{
    /**
     * @param array $productQuery
     * @return array
     */
    public function filterItem(array $productQuery)
    {
        $request = $this->diContainer->get('request');
        $this->filterVars  ['displayOrderDefault'] = $this->params ['defaultSortOrder'];
        $this->filterVars  ['displayOrder'] = $request->readGet('disp_order', 0);
        if (!$request->has('disp_order')) {
            $request->set('disp_order', $this->filterVars  ['displayOrderDefault'], 'get');
            $this->filterVars ['displayOrder'] = $this->filterVars ['displayOrderDefault'];
        }
        $map = $this->buildMap();
        $orderBy = " p.products_sort_order";
        if ($request->readGet('disp_order', 0) == 0) {
            $request->set('disp_order', $this->filterVars ['displayOrderDefault'], 'get');
            $this->filterVars ['displayOrder'] = $this->filterVars ['displayOrderDefault'];
        }
        if (isset($map[$request->readGet('disp_order', 0)])) {
            $orderBy = $map[$request->readGet('disp_order', 0)];
        }
        $productQuery['orderBys'] [] = array(
            'type' => 'custom',
            'field' => $orderBy
        );
        return $productQuery;
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
