<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt 2020 May 23 New in v1.5.7 $
 */

namespace Zencart\TableViewControllers;

use Zencart\Traits\NotifierManager;
use Zencart\Paginator\Paginator;

class BaseController implements TableViewController
{
    use NotifierManager;

    protected $filters = [];
    protected $tableData;
    protected $tableDefinition;
    protected $messageStack;
    protected $action;

    public function __construct($request, $messageStack, $tableDefinition, $filterFactory)
    {
        $this->request = $request;
        $this->messageStack = $messageStack;
        $this->filterFactory = $filterFactory;
        $this->tableDefinition = $tableDefinition;
        $this->tableDefinition['configBox'] = ['header' => [], 'content' => []];
    }

    public function processRequest()
    {
        $this->setTableDefinitionDefaults();
        $this->buildFilters();
        $this->action = $this->getAction();
        $this->page = $this->request->input($this->tableDefinition['pagerVariable'], 1);
        $this->query = $this->buildInitialQuery();
        $this->query = $this->processFilters($this->request, $this->query);
        $this->paginatedResults = $this->query->paginate($this->tableDefinition['maxRowCount']);
        $this->tableData = $this->processTableData($this->paginatedResults);
        $method = ($this->action == '') ? 'processDefaultAction' : 'processAction' . ucfirst($this->action);
        if (method_exists($this, $method)) {
            $result = $this->$method();
        }
        return $this;
    }

    public function getTableData($section = null)
    {
        if (isset($section) && isset($this->tableData[$section])) {
            return $this->tableData[$section];
        }
        return $this->tableData;
    }

    public function tableRowSelected($tableData)
    {
        if (!isset($this->currentRow) || !is_object($this->currentRow)) {
            return false;
        }
        if ($tableData['row'][$this->tableDefinition['colKey']] !=
            $this->currentRow->{$this->tableDefinition['colKey']}) {
            return false;
        }
        return true;
    }

    public function getSelectedRowLink($tableData)
    {
        $params = 'page=' . $this->page;
        $params .= "&" . $this->colKeyName() . "=" . $tableData['row'][$this->tableDefinition['colKey']];
        if ($this->getDefaultRowAction() != '') {
            $params .= "&action=" . $this->getDefaultRowAction();
        }
        return zen_href_link($this->request->input('cmd'), $params);

    }

    public function getNotSelectedRowLink($tableData)
    {
        $params = 'page=' . $this->page;
        $params .= "&" . $this->colKeyName() . "=" . $tableData['row'][$this->tableDefinition['colKey']];
        return zen_href_link($this->request->input('cmd'), $params);

    }

    protected function processTableData($listingData)
    {
        $tableData = [];
        $tableData['headerInfo'] = $this->getTableHeaderInfo();
        $tableData['contentInfo'] = $this->getTableDataInfo($listingData);
        return $tableData;
    }

    protected function getTableHeaderInfo()
    {
        $colHeaders = [];
        foreach ($this->tableDefinition['columns'] as $column) {
            $headerClass = $this->getColHeaderMainClass($column);

            $colHeaders[] = ['headerClass' => $headerClass, 'title' => $column['title']];
        }
        return $colHeaders;
    }

    protected function getTableDataInfo($listingData)
    {
        $listResults = [];
        foreach ($listingData as $listResult) {
            $this->currentRow = $this->setCurrentRow($listResult);
            $colResults = [];
            foreach ($this->tableDefinition['columns'] as $colName => $column) {
                $columnClass = 'dataTableContent';
                $colResults[] = [
                    'columnClass' => $columnClass, 'value' => $this->getColumnData(
                        $listResult,
                        $colName, $column)
                ];
            }
            $listResults[] = ['row' => $listResult, 'cols' => $colResults];
        }
        return $listResults;
    }

    protected function getColumnData($listResult, $colName, $columnInfo)
    {
        if (!isset($columnInfo['derivedItem'])) {
            return $listResult[$colName];
        }
        $colData = $this->processDerivedItem($listResult, $colName, $columnInfo);
        return $colData;
    }

    protected function processDerivedItem($listResult, $colName, $columnInfo)
    {
        $type = $columnInfo['derivedItem']['type'];
        switch ($type) {
            case 'local':
                $result = $this->{$columnInfo['derivedItem']['method']}($listResult, $colName, $columnInfo);
                return $result;
                break;
        }
    }

    protected function setCurrentRow($listResult)
    {
        if (isset($this->currentRow)) {
            return $this->currentRow;
        }
        if (substr($this->getAction(), 0, 3) == 'new') {
            return null;
        }
        $listKeyValue = $listResult[$this->tableDefinition['colKey']];
        $colKeyFromGet = $this->getColKeyFromGet();
        if (!isset($colKeyFromGet) || (isset($colKeyFromGet) && $colKeyFromGet == $listKeyValue)) {
            return $listResult;
        }
        return null;

    }

    protected function getColKeyFromGet()
    {
        return $this->request->input($this->colKeyName(), null);
    }

    protected function colKeyName()
    {
        return $this->tableDefinition['colKeyName'];
    }

    protected function getAction()
    {
        $action = $this->request->input('action', '');
        $this->notify('ADMIN_VIEW_CONTROLLER_GET_ACTION', $action);
        return $action;
    }

    public function getPage()
    {
        return $this->page;
    }

    protected function getColHeaderMainClass($colDef)
    {
        $mainClass = "dataTableHeadingContent";
        return $mainClass;
    }

    protected function getDefaultRowAction()
    {
        if (!isset($this->tableDefinition['defaultRowAction'])) {
            return 'edit';
        }
        return $this->tableDefinition['defaultRowAction'];
    }

    public function getTableConfigBoxHeader()
    {
        return $this->tableDefinition['header'];
    }

    public function getTableConfigBoxContent()
    {
        return $this->tableDefinition['content'];
    }

    public function outputMessageList($errorList, $errorType)
    {
        if (!count($errorList)) {
            return;
        }
        foreach ($errorList as $error) {
            $this->messageStack->add_session($error, $errorType);
        }
    }
    protected function setTableDefinitionDefaults()
    {
        $this->tableDefinition['maxRowCount'] = $this->tableDefinition['maxRowCount'] ?? 10;
        $this->tableDefinition['colKeyName'] = $this->tableDefinition['colKeyName'] ?? 'colKey';
        $this->tableDefinition['pagerVariable'] = $this->tableDefinition['pagerVariable'] ?? 'page';
        $this->notify('TABLE_CONTROLLER_SET_TABLE_DESC_DEFAULTS');
    }

    protected function booleanReplace($listResult, $colName, $columnInfo)
    {
        $params = $columnInfo['derivedItem']['params'];
        $listValue = $listResult[$colName];
        $result = $params['false'];
        if ($listValue) $result = $params['true'];
        return $result;
    }

    protected function arrayReplace($listResult, $colName, $columnInfo)
    {
        $params = $columnInfo['derivedItem']['params'];
        $listValue = $listResult[$colName];
        $result = $params[$listValue];
        return $result;
    }

    public function setBoxHeader($content, $parameters = [])
    {
        $this->tableDefinition['header'][] = $this->processBoxContent($content, $parameters);
    }

    public function setBoxContent($content, $parameters = [])
    {
        $this->tableDefinition['content'][] = $this->processBoxContent($content, $parameters);
    }

    public function setBoxForm($content)
    {
        $this->tableDefinition['content']['form'] = $content;
    }


    public function processBoxContent($content, $parameters)
    {
        $align = $parameters['align'] ?? 'text-center';
        $params = $parameters['params'] ?? '';
        $boxContent = ['align' => $align, 'params' => $params, 'text' => $content];
        return $boxContent;
    }

    public function pageLink()
    {
        return $this->tableDefinition['pagerVariable'] . '=' . $this->page;
    }

    public function colKeyLink()
    {
        return $this->colKeyName() . '=' . $this->currentRow->{$this->tableDefinition['colKey']};
    }

    public function getPaginatedResults()
    {
        return $this->paginatedResults;
    }

    public function hasFilters()
    {
        if (!isset($this->tableDefinition['filters'])) {
            return false;
        }
        if (!is_array($this->tableDefinition['filters'])) {
            return false;
        }
        return true;
    }
    private function processFilters($request, $query)
    {
        if (!$this->hasFilters()) {
            return $query;
        }
        foreach ($this->filters as $filter) {


            $query = $filter->processRequest($request, $query);
        }
        return $query;
    }

    public function getFilters()
    {
        return $this->filters;
    }

    private function buildFilters()
    {
        $this->filters = [];
        if (!$this->hasFilters()) {
            return;
        }
        foreach ($this->tableDefinition['filters'] as $filterDefinition) {
            $filter = $this->filterFactory->make($filterDefinition);
            $this->filters[] = $filter;
            $filter->make($filterDefinition);
        }
    }
}
