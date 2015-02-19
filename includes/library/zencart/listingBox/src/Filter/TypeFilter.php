<?php
/**
 * Class TypeFilter
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: currencies.php 15880 2010-04-11 16:24:30Z wilt $
 */
namespace ZenCart\ListingBox\Filter;
/**
 * Class TypeFilter
 * @package ZenCart\ListingBox\Filter
 */
class TypeFilter extends AbstractFilter
{
    /**
     * loads actual typeFilter based on get params.
     * replaces old index_filter code
     *
     */
    public function filterItem(array $productQuery)
    {
        $request = $this->diContainer->get('request');
        $typeFilter = 'default';
        if ($request->has('typefilter') && !$request->has('keyword')) {
            $typeFilter = $request->readGet('typefilter');
        }
        $typeFilterClassName = __NAMESPACE__ . '\TypeFilter' . ucfirst(\base::camelize($typeFilter));
        $typeFilterClass = new $typeFilterClassName($this->diContainer, $this->params);
        $productQuery = $typeFilterClass->filterItem($productQuery);
        $this->filterVars = $typeFilterClass->getFilterVars();
        return $productQuery;
    }
}
