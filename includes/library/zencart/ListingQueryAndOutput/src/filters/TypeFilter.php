<?php
/**
 * Class TypeFilter
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: currencies.php 15880 2010-04-11 16:24:30Z wilt $
 */
namespace ZenCart\ListingQueryAndOutput\filters;

/**
 * Class TypeFilter
 * @package ZenCart\ListingQueryAndOutput\filters
 */
class TypeFilter extends AbstractFilter implements FilterInterface
{
    /**
     * @param array $listingQuery
     * @return array
     */
    public function filterItem(array $listingQuery)
    {
        $typeFilter = 'default';
        if ($this->request->has('typefilter') && !$this->request->has('keyword')) {
            $typeFilter = $this->request->readGet('typefilter');
        }
        $typeFilterClassName = __NAMESPACE__ . '\TypeFilter' . ucfirst(\base::camelize($typeFilter));
        $typeFilterClass = new $typeFilterClassName($this->request, $this->params);
        if (isset($this->dbConn)) {
            $typeFilterClass->setDBConnection($this->dbConn);
        }
        $listingQuery = $typeFilterClass->filterItem($listingQuery);
        $this->tplVars = $typeFilterClass->getTplVars();
        return $listingQuery;
    }
}
