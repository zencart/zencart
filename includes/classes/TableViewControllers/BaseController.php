<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt 2020 May 23 New in v1.5.7 $
 */

namespace Zencart\TableViewControllers;

use Zencart\Traits\EventManager;
use Zencart\Paginator\Paginator;

class BaseController implements TableViewController
{
    use EventManager;

    protected $filters = [];
    protected $queryParts = [];
    protected $queryBuilder;
    protected $dbConn;
    protected $listResults;
    protected $tableData;
    protected $tableDefinition;
    protected $tableObjInfo;
    protected $splitPage;
    protected $messageStack;
    protected $action;

    public function __construct($dbConn, $messageStack, $queryBuilder, $tableDefinition)
    {
        $this->queryBuilder = $queryBuilder;
        $this->dbConn = $dbConn;
        $this->messageStack = $messageStack;
        $this->tableDefinition = $tableDefinition;
        $this->tableObjInfo = null;
        $this->tableDefinition['configBox'] = ['header' => [], 'content' => []];
        $this->setTableDefinitionDefaults();
    }

    public function processRequest()
    {
        $this->action = $this->getAction();
        $this->page = (isset($_GET['page'])) ? $_GET['page'] : 1;
        $this->queryParts = $this->buildListQuery();
        $this->queryBuilder->processQuery($this->queryParts);
        $listingSql = $this->queryBuilder->getQuery()['mainSql'];
        $this->splitPage = $this->getSplitPageListingSql($listingSql);
        $results = $this->dbConn->execute($this->splitPage->getSqlQuery());
        $this->tableData = $this->processTableData($results);
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
        if (!isset($this->tableObjInfo) || !is_object($this->tableObjInfo)) {
            return false;
        }
        if ($tableData['row'][$this->tableDefinition['colKey']] !=
            $this->tableObjInfo->{$this->tableDefinition['colKey']}) {
            return false;
        }
        return true;
    }

    public function getSelectedRowLink($tableData)
    {
        $fn = $_GET['cmd'];
        $params = 'page=' . $this->page;
        $params .= "&" . $this->getColKeyGetParamName() . "=" . $tableData['row'][$this->tableDefinition['colKey']];
        if ($this->getDefaultRowAction() != '') {
            $params .= "&action=" . $this->getDefaultRowAction();
        }
        return zen_href_link($fn, $params);

    }

    public function getNotSelectedRowLink($tableData)
    {
        $fn = $_GET['cmd'];
        $params = 'page=' . $this->page;
        $params .= "&" . $this->getColKeyGetParamName() . "=" . $tableData['row'][$this->tableDefinition['colKey']];
        return zen_href_link($fn, $params);

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
            $this->tableObjInfo = $this->setTableObjInfo($listResult);
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

    protected function setTableObjInfo($listResult)
    {
        if (isset($this->tableObjInfo)) {
            return $this->tableObjInfo;
        }
        if (substr($this->getAction(), 0, 3) == 'new') {
            return null;
        }
        $listKeyValue = $listResult[$this->tableDefinition['colKey']];
        $colKeyFromGet = $this->getColKeyFromGet();
        if (!isset($colKeyFromGet) || (isset($colKeyFromGet) && $colKeyFromGet == $listKeyValue)) {
            return new \objectInfo($listResult);
        }
        return null;

    }

    protected function getColKeyFromGet()
    {

        $colKeyGetParamName = $this->getColKeyGetParamName();
        if (!isset($_GET[$colKeyGetParamName])) {
            return null;
        }
        return $_GET[$colKeyGetParamName];

    }

    protected function getColKeyGetParamName()
    {
        if (isset($this->tableDefinition['colKeyGetParamName'])) {
            return $this->tableDefinition['colKeyGetParamName'];
        }
        return 'colKey';
    }

    protected function getAction()
    {
        $action = (isset($_GET['action']) ? $_GET['action'] : '');
        $this->notify('ADMIN_VIEW_CONTROLLER_GET_ACTION', $action);
        return $action;
    }

    public function getPage()
    {
        return $this->page;
    }

    protected function getSplitPageListingSql($listingSql)
    {
        $splitSql = new Paginator($listingSql, 10);
        return $splitSql;
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

    public function getTableObjInfo()
    {
        return $this->tableObjInfo;

    }

    public function getTableConfigBoxHeader()
    {
        return $this->tableDefinition['header'];
    }

    public function getTableConfigBoxContent()
    {
        return $this->tableDefinition['content'];
    }

    public function getSplitPage()
    {
        return $this->splitPage;
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
}