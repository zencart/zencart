<?php declare(strict_types=1);
/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt 2021 Jan 29 New in v1.5.8-alpha $
 */

namespace Zencart\ViewBuilders;

use Zencart\Request\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;

/**
 * @since ZC v1.5.8
 */
class SimpleDataFormatter
{
    protected $request;
    protected $tableDefinition;
    protected $resultSet;
    protected $derivedItems;

    public function __construct(Request $request, TableViewDefinition $tableViewDefinition, Paginator $resultSet, $derivedItems)
    {
        $this->request = $request;
        $this->tableDefinition = $tableViewDefinition;
        $this->resultSet = $resultSet;
        $this->derivedItems = $derivedItems;
    }

    /**
     * @since ZC v1.5.8
     */
    public function getTableHeaders(): Collection
    {
        $colHeaders = [];
        $columns = $this->tableDefinition->getParameter('columns');
        foreach ($columns as $column) {
            $headerClass = $this->getColHeaderMainClass($column);
            $colHeaders[] = ['headerClass' => $headerClass, 'title' => $column['title']];
        }
        return collect($colHeaders);
    }

    /**
     * @since ZC v1.5.8
     */
    public function getTableData()
    {
        $tableData = [];
        $columns = $this->tableDefinition->getParameter('columns');
        $fields = collect($columns)->keys();
        $columnData = [];
        foreach ($this->resultSet as $result) {
            foreach ($fields as $field) {
                $value = $this->derivedItems->process($result, $field, $columns[$field]);

                $class = '';
                // if column class is set as a closure, call it and pass in the value from $result->field; else assume it is a string
                $classDef = $columns[$field]['class'] ?? null;
                if ($classDef instanceof \Closure || is_callable($classDef)) {
                    $class = $classDef($result->$field);
                } elseif (is_string($classDef)) {
                    $class = $classDef;
                }

                $columnData[$field] = ['value' => $value, 'class' => $class, 'original' => $result->$field];
            }
            $tableData[] = $columnData;
        }
        return collect($tableData);
    }

    /**
     * @since ZC v1.5.8
     */
    public function isRowSelected(array $tableRow): bool
    {
        $colKeyFromRequest = $this->request->input($this->tableDefinition->colKeyName());
        $colKeyField = $this->tableDefinition->getParameter('colKey');
        $currentRow = $this->currentRowFromRequest();
        if (is_null($colKeyFromRequest) && $currentRow->$colKeyField == $tableRow[$colKeyField]['value']) {
            return true;
        }
        if ($colKeyFromRequest == $tableRow[$colKeyField]['value']) {
            return true;
        }
        return false;
    }

    /**
     * @since ZC v1.5.8
     */
    public function currentRowFromRequest()
    {
        $colKeyFromRequest = $this->request->input($this->tableDefinition->colKeyName());
        $colKeyField = $this->tableDefinition->getParameter('colKey');
        if (!is_null($colKeyFromRequest)) {
            $result = $this->resultSet->getCollection()->where($colKeyField, $colKeyFromRequest)->first();
        } else {
            $result = $this->resultSet->getCollection()->first();
        }
        return $result;
    }

    /**
     * @since ZC v1.5.8
     */
    public function getSelectedRowLink(array $tableRow): string
    {
        $pagerVar = $this->tableDefinition->getParameter('pagerVariable');
        $params = $pagerVar . '=' . $this->request->input($pagerVar, 1);
        $params .= "&" . $this->tableDefinition->colKeyName() . "=" . $tableRow[$this->tableDefinition->getParameter('colKey')]['value'];
        return zen_href_link($this->request->input('cmd'), $params);
    }

    /**
     * @since ZC v1.5.8
     */
    public function getNotSelectedRowLink(array $tableRow): string
    {
        $pagerVar = $this->tableDefinition->getParameter('pagerVariable');
        $params = $pagerVar . '=' . $this->request->input($pagerVar, 1);
        $params .= "&" . $this->tableDefinition->colKeyName() . "=" . $tableRow[$this->tableDefinition->getParameter('colKey')]['value'];
        return zen_href_link($this->request->input('cmd'), $params);

    }

    /**
     * @since ZC v1.5.8
     */
    public function getResultSet()
    {
        return $this->resultSet;
    }

    /**
     * @since ZC v1.5.8
     */
    public function hasRowActions()
    {
        return $this->tableDefinition->hasRowActions();
    }

    /**
     * @since ZC v1.5.8
     */
    public function getRowActions($tableRow)
    {
        $rowActions = $this->tableDefinition->getRowActions();
        $processed = [];
        foreach ($rowActions as $rowAction) {
            $processed[] = $this->processRowAction($rowAction, $tableRow);
        }
        return $processed;
    }

    /**
     * @since ZC v1.5.8
     */
    public function hasButtonActions()
    {
         $buttonActions = $this->getRawButtonActions();
         if (count($buttonActions) == 0) {
             return false;
         }
         return (count($buttonActions) > 0);
    }

    /**
     * @since ZC v1.5.8
     */
    public function getButtonActions()
    {
        $buttonActions = $this->getRawButtonActions();
        $processed = [];
        foreach ($buttonActions as $buttonAction) {
            $buttonAction['hrefLink'] = $this->processButtonActionLink($buttonAction);
            $processed[] = $buttonAction;
        }
        return $processed;
    }

    /**
     * @since ZC v1.5.8
     */
    protected function getRawButtonActions()
    {
        $buttonActions = $this->tableDefinition->getButtonActions();
        if (count($buttonActions) == 0) {
            return [];
        }
        $processed = [];
        foreach ($buttonActions as $buttonAction) {
            if ($this->buttonPassesWhiteList($buttonAction) && $this->buttonPassesBlackList($buttonAction)) {
                $processed[] = $buttonAction;
            }
        }
        return $processed;
    }

    /**
     * @since ZC v1.5.8
     */
    protected function processButtonActionLink($buttonAction)
    {
        $link = 'action=' . $buttonAction['action'];
        return $link;
    }

    /**
     * @since ZC v1.5.8
     */
    protected function buttonPassesWhiteList($buttonAction)
    {
        $action = $this->request->input('action');
        if (!isset($buttonAction['whitelist'])) {
            return true;
        }
        if (in_array($action, $buttonAction['whitelist'])) {
            return true;
        }
        return false;
    }

    /**
     * @since ZC v1.5.8
     */
    protected function buttonPassesBlackList($buttonAction)
    {
        $action = $this->request->input('action');
        if (!isset($buttonAction['blacklist'])) {
            return true;
        }
        if (in_array($action, $buttonAction['blacklist'])) {
            return false;
        }
        return true;
    }

    /**
     * @since ZC v1.5.8
     */
    protected function processRowAction($rowAction, $tableRow)
    {
        $processed = $rowAction;
        $link = $this->buildRowActionLink($rowAction, $tableRow);
        $processed['hrefLink'] = $link;
        return $processed;
    }

    /**
     * @since ZC v1.5.8
     */
    protected function buildRowActionLink($rowAction, $tableRow)
    {
        $pagerVar = $this->tableDefinition->getParameter('pagerVariable');
        $link = $pagerVar . '=' . $this->request->input($pagerVar, 1);
        $link .= '&action='  . $rowAction['action'];
        $tableRowLink = $this->processRowActionTableRowLink($rowAction, $tableRow);
        $tableRowLink = rtrim($tableRowLink, '&');
        $link .= '&' . $tableRowLink;
        return $link;
    }

    /**
     * @since ZC v1.5.8
     */
    protected function processRowActionTableRowLink($rowAction, $tableRow)
    {
        $link = '';
        if (!isset($rowAction['linkParams'])) {
            return $link;
        }
        foreach ($rowAction['linkParams'] as $linkParams) {
            if ($linkParams['source'] !== 'tableRow') continue;
            $link .= $linkParams['param'] . '=' . $tableRow[$linkParams['field']]['original'] . '&';
        }
        return $link;
    }

    /**
     * @since ZC v1.5.8
     */
    protected function getColHeaderMainClass($colDef)
    {
        $mainClass = "dataTableHeadingContent";
        return $mainClass;
    }
}
