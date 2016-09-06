<?php
/**
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: wilt  New in v1.6.0 $
 */

namespace ZenCart\ListingQueryAndOutput\definitions;

use ZenCart\Request\Request;

/**
 * Class AbstractDefinition
 * @package ZenCart\ListingQueryAndOutput\definitions
 */
abstract class AbstractDefinition extends \base
{
    /**
     * @var array
     */
    protected $listingQuery;
    /**
     * @var array
     */
    protected $outputLayout;
    /**
     * @var array
     */
    protected $tplVars = [];

    /**
     * AbstractDefinition constructor.
     * @param Request $request
     * @param $db
     */
    public function __construct(Request $request, $db)
    {
        $this->request = $request;
        $this->dbConn = $db;
        $this->initQueryAndOutput();
        $this->notify('NOTIFY_LISTINGBOX_CONSTRUCT_END');
    }

    /**
     * @param $queryBuilder
     * @param $db
     * @param $derivedItemsManager
     * @param mixed $paginator
     * @throws \Exception
     */
    public function buildResults($queryBuilder, $db, $derivedItemsManager, $paginator = null)
    {
        $this->tplVars['filter'] = $this->doFilters($db);
        $queryBuilder->processQuery($this->getListingQuery());
        $query = $queryBuilder->getQuery();
        $query['dbConn'] = $db;
        $resultItems = $this->processPaginatorResults($paginator, $query, $db);
        $resultItems = $this->transformPaginationItems($resultItems, $paginator);
        $finalItems = $this->processDerivedItems($resultItems, $derivedItemsManager);
        $this->formatResults($finalItems, $db, $paginator);
    }


    public function formatResults($finalItems, $db, $paginator)
    {
        $formatter = $this->doFormatter($finalItems, $db);
        $this->tplVars['formatter'] = $formatter->getTplVars();
        $this->tplVars['formattedItems'] = $formatter->getFormattedResults();
        $this->doMultiFormSubmit($finalItems);
        $this->normalizeTplVars($paginator);
        $this->notify('NOTIFY_LISTINGBOX_BUILDRESULTS_END');
    }

    /**
     * @param $paginator
     * @param $query
     * @param $db
     * @return array
     */
    protected function processPaginatorResults($paginator, $query, $db)
    {
        if (isset($paginator)) {
            $resultItems = $this->getPaginatedResultItems($query, $db, $paginator);
            $this->tplVars['paginator'] = $resultItems;
            $resultItems = $resultItems['resultList'];
        }
        if (!isset($paginator)) {
            $resultItems = $this->getResultItems($query, $db);
        }

        return $resultItems;
    }

    /**
     * @param $resultItems
     * @param $derivedItemsManager
     * @return array
     */
    protected function processDerivedItems($resultItems, $derivedItemsManager)
    {
        $finalItems = [];
        $derivedItems = issetorArray($this->listingQuery, 'derivedItems', array());
        foreach ($resultItems as $resultItem) {
            $resultItem = $derivedItemsManager->manageDerivedItems($derivedItems, $resultItem, $this->pageDefinition['action']);
            $finalItems [] = $resultItem;
        }

        return $finalItems;
    }

    /**
     * @param $paginator
     */
    protected function normalizeTplVars($paginator = null)
    {
        $showFilterForm = false;
        if (isset($this->tplVars['filter'])) {
            $showFilterForm = count($this->tplVars['filter'] > 0);
        }
        $this->tplVars ['showFilterForm'] = $showFilterForm;
        $this->tplVars ['title'] = issetorArray($this->outputLayout, 'boxTitle', '');
        $classname = (new \ReflectionClass($this))->getShortName();
        $this->tplVars ['cssElement'] = issetorArray($this->outputLayout, 'cssElement', $classname);
        $this->tplVars ['formattedItemsCount'] = count($this->tplVars['formattedItems']);
        $this->tplVars ['hasFormattedItems'] = (count($this->tplVars['formattedItems']) > 0) ? true : false;
        $this->tplVars ['paginator']['show'] = false;
        if (isset($paginator)) {
            $this->tplVars['paginator'] = $paginator->getScroller()->getResults();
            $this->tplVars ['paginator']['show'] = ($this->tplVars['paginator']['totalPages'] > 0);
            $this->tplVars ['paginator']['showTop'] = ((PREV_NEXT_BAR_LOCATION == '1') || (PREV_NEXT_BAR_LOCATION == '3'));
            $this->tplVars ['paginator']['showBottom'] = ((PREV_NEXT_BAR_LOCATION == '2') || (PREV_NEXT_BAR_LOCATION == '3'));
        }
        $this->notify('NOTIFY_LISTINGBOX_NORMALIZETPLVARS_END');
    }

    /**
     * @param $listBoxContents
     */
    public function doMultiFormSubmit($listBoxContents)
    {
        $showSubmit = zen_run_normal();
        $showBottomSubmit = false;
        $showForm = $this->showTopBottomSubmit($showSubmit, $listBoxContents);
        $showTopSubmit = $showForm;
        if ($showForm) {
            $showTopSubmit = (PRODUCT_LISTING_MULTIPLE_ADD_TO_CART == 1 || PRODUCT_LISTING_MULTIPLE_ADD_TO_CART == 3) ? true : false;
            $showBottomSubmit = (PRODUCT_LISTING_MULTIPLE_ADD_TO_CART >= 2) ? true : false;
        }
        $showForm = ($showTopSubmit || $showBottomSubmit);
        $this->tplVars['showMultiTopSubmit'] = $showTopSubmit;
        $this->tplVars['showMultiBottomSubmit'] = $showBottomSubmit;
        $this->tplVars['showMultiForm'] = $showForm;
        $this->notify('NOTIFY_LISTINGBOX_DOMULTIFORMSUBMIT_END');
    }

    /**
     * @param $showSubmit
     * @param $listBoxContents
     * @return bool
     */
    protected function showTopBottomSubmit($showSubmit, $listBoxContents)
    {
        $retVal = false;
        $countQtyBoxItems = isset($this->tplVars['formatter']['countQtyBoxItems']) ? $this->tplVars['formatter']['countQtyBoxItems'] : 0;
        if ($countQtyBoxItems > 0 and $showSubmit == true and count($listBoxContents) > 0) {
            $retVal = true;
        }

        return $retVal;
    }

    /**
     * @param $query
     * @param $db
     * @param $paginator
     * @return mixed
     */
    protected function getPaginatedResultItems($query, $db, $paginator)
    {
        $paginator->doPagination($query);
        $resultItems = $paginator->getScroller()->getResults();

        return $resultItems;

    }

    /**
     * @param $query
     * @param $db
     * @return array
     */
    protected function getResultItems($query, $db)
    {
        $resultItems = [];
        $queryLimit = issetorArray($this->listingQuery, 'queryLimit', '');
        $results = $db->execute($query['mainSql'], $queryLimit);
        foreach ($results as $result) {
            $resultItems [] = $result;
        }

        return $resultItems;
    }

    /**
     * @param $resultItems
     * @param $db
     * @return mixed
     * @throws \Exception
     */
    protected function doFormatter($resultItems, $db)
    {
        if (!isset($this->outputLayout['formatter'])) {
            throw new \Exception();
        }
        $formatter = $this->outputLayout['formatter']['class'];
        $formatter = '\\ZenCart\\ListingQueryAndOutput\\formatters\\' . $formatter;
        $f = new $formatter($resultItems, $this->outputLayout);
        $f->setDbConnection($db);
        $f->setRequest($this->request);
        $f->format();

        return $f;
    }

    /**
     * @param $db
     * @return array
     */
    protected function doFilters($db)
    {
        $filterVars = [];
        if (!isset($this->listingQuery['filters'])) {
            return $filterVars;
        }

        foreach ($this->listingQuery['filters'] as $filter) {
            $params = issetorArray($filter, 'parameters', array());
            $filter = '\\ZenCart\\ListingQueryAndOutput\\filters\\' . $filter['name'];
            $filter = new $filter($this->request, $params);
            $filter->setDBConnection($db);
            $this->listingQuery = $filter->filterItem($this->listingQuery);
            $filterVars = array_merge($filterVars, $filter->getTplVars());
        }

        return $filterVars;
    }

    public function transformPaginationItems($items, $usePagination)
    {
        return $items;
    }

    /**
     * @return array
     */
    public function getTplVars()
    {
        $this->notify('NOTIFY_LISTINGBOX_GETTEMPLATEVARIABLES_START');

        return $this->tplVars;
    }

    /**
     * @return int
     */
    public function getTotalItemCount()
    {
        return (int)issetorArray($this->tplVars['paginator'], 'totalItemCount', 0);
    }

    /**
     * @return int
     */
    public function getFormattedItemsCount()
    {
        return (int)issetorArray($this->tplVars, 'formattedItemsCount', 0);
    }

    /**
     * @return array
     */
    public function getListingQuery($type = null)
    {
        $result = $this->listingQuery;
        if (!isset($type) && isset($this->listingQuery['main'])) {
            $result = $this->listingQuery['main'];
        }
        if (isset($type)) {
            $result = $this->listingQuery[$type];
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getOutputLayout($type = null)
    {
        $result = $this->outputLayout;
        if (!isset($type) && isset($this->outputLayout['main'])) {
            $result = $this->outputLayout['main'];
        }
        if (isset($type)) {
            $result = $this->outputLayout[$type];
        }
        return $result;
    }

    /**
     * @param array $listingQuery
     */
    public function setListingQuery(array $listingQuery)
    {
        $this->listingQuery = $listingQuery;
    }
}
