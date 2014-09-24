<?php
/**
 * Class AbstractTypeFilter
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: currencies.php 15880 2010-04-11 16:24:30Z wilt $
 */
namespace ZenCart\ListingBox\Filter;
/**
 * Class AbstractTypeFilter
 * @package ZenCart\ListingBox\Filter
 */
abstract class AbstractTypeFilter extends AbstractFilter
{
    /**
     * @var
     */
    protected $filterVars;

    /**
     * @param array $productQuery
     * @return array
     */
    public function filterItem(array $productQuery)
    {
        if (!$this->diContainer->get('request')->has('keyword')) {
            $productQuery = $this->handleParameterFilters($productQuery);
        }
        $this->buildOptionFilterVars();
        return $productQuery;
    }

    /**
     *
     */
    public function buildOptionFilterVars()
    {
        $request = $this->diContainer->get('request');
        $dbConn = $this->diContainer->get('dbConn');

        if (PRODUCT_LIST_FILTER == 0) {
            return;
        }
        $filterSql = $this->getDefaultFilterSql();
        if (zen_not_null($request->readGet($this->getGetTypeParam()))) {
            $filterSql = $this->getTypeFilterSql();
        }
        $this->filterVars['filterOptions'] = array();
        $this->filterVars['doFilterList'] = false;
        $this->filterVars['getOptionSet'] = false;
        if (PRODUCT_LIST_ALPHA_SORTER == 'true') {
            $this->filterVars['doFilterList'] = true;
        }
        $filterlist = $dbConn->Execute($filterSql);
        if ($filterlist->RecordCount() > 1) {
            $this->buildFilterList($filterlist);
        }
        if (count($this->filterVars['filterOptions']) == 0) {
            $this->filterVars['doFilterList'] = false;
        }
    }

    /**
     * @param $filterlist
     */
    public function buildFilterList($filterlist)
    {
        $request = $this->diContainer->get('request');
        $this->filterVars['doFilterList'] = true;
        $this->filterVars['getOptionSet'] = true;
        $this->filterVars['getOptionVariable'] = $this->getGetTypeParam();
        $this->filterVars['filterOptions'] = array(
            array(
                'id' => '',
                'text' => TEXT_ALL_MANUFACTURERS
            )
        );
        if ($request->has($this->getGetTypeParam())) {
            $this->filterVars['filterOptions'] = array(
                array(
                    'id' => '',
                    'text' => TEXT_ALL_CATEGORIES
                )
            );
        }
        foreach ($filterlist as $filterOption) {
            $this->filterVars['filterOptions'] [] = array(
                'id' => $filterOption['id'],
                'text' => $filterOption['name']
            );
        }
    }

    /**
     * @return mixed
     */
    public function getFilterVars()
    {
        return $this->filterVars;
    }

    /**
     * @param $productQuery
     * @return mixed
     */
    abstract protected function handleParameterFilters($productQuery);

    /**
     * @return mixed
     */
    abstract protected function getGetTypeParam();

    /**
     * @return mixed
     */
    abstract protected function getDefaultFilterSql();

    /**
     * @return mixed
     */
    abstract protected function getTypeFilterSql();
}
