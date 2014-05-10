<?php
/**
 * File contains just the zcQueryBuilder class
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
/**
 * class zcQueryBuilder
 *
 * @package classes
 */
class zcQueryBuilder extends base
{
  /**
   *
   * @var array
   */
  protected $parameters;
  /**
   *
   * @var integer
   */
  protected $paginationQueryLimit;
  /**
   *
   * @var boolean
   */
  protected $isDistinct;
  /**
   *
   * @var boolean
   */
  protected $isPaginated;
  /**
   *
   * @var boolean
   */
  protected $isRandom;
  /**
   *
   * @var array
   */
  protected $selectColumns;
  /**
   *
   * @var array
   */
  protected $query;
  /**
   *
   * @var integer
   */
  protected $resultCount;
  /**
   *
   * @var array
   */
  protected $resultItems;
  /**
   *
   * @var string
   */
  protected $tableAliases;
  protected $paginationScroller;
  protected $paginationScrollerTemplate;
  /**
   * constructor
   *
   * @param array $parameters
   */
  public function __construct(array $parameters = array())
  {
    $this->parameters = $parameters;
    $this->paginator = NULL;
    $this->tableAliases = array();
    $this->filterOutputVariables = array();
    $this->initParameters();
    $this->processQuery();
    $this->notify('NOTIFY_QUERY_BUILDER_CONSTRUCT_END');
  }
  /**
   * initialise parameters if they have been passed in or default
   */
  protected function initParameters()
  {
    $this->notify('NOTIFY_QUERY_BUILDER_INITPARAMETERS_START');
    $this->parts ['bindVars'] = (isset($this->parameters ['bindVars'])) ? $this->parameters ['bindVars'] : array();
    $this->parts ['selectList'] = (isset($this->parameters ['selectList'])) ? $this->parameters ['selectList'] : array();
    $this->parts ['orderBys'] = (isset($this->parameters ['orderBys'])) ? $this->parameters ['orderBys'] : array();
    $this->parts ['filters'] = (isset($this->parameters ['filters'])) ? $this->parameters ['filters'] : array();
    $this->parts ['derivedItems'] = (isset($this->parameters ['derivedItems'])) ? $this->parameters ['derivedItems'] : array();
    $this->paginationQueryLimit = (isset($this->parameters ['paginationQueryLimit'])) ? $this->parameters ['paginationQueryLimit'] : NULL;
    $this->queryLimit = (isset($this->parameters ['queryLimit'])) ? $this->parameters ['queryLimit'] : NULL;
    $this->parts ['joinTables'] = (isset($this->parameters ['joinTables'])) ? $this->parameters ['joinTables'] : array();
    $this->parts ['whereClauses'] = (isset($this->parameters ['whereClauses'])) ? $this->parameters ['whereClauses'] : array();
    $this->isDistinct = (isset($this->parameters ['isDistinct']) && $this->parameters ['isDistinct'] == true) ? true : false;
    $this->isPaginated = (isset($this->parameters ['isPaginated']) && $this->parameters ['isPaginated'] == true) ? true : false;
    $this->paginationScroller = (isset($this->parameters ['paginationScroller']) ? $this->parameters ['paginationScroller'] : 'catalog');
    $this->isRandom = (isset($this->parameters ['isRandom']) && $this->parameters ['isRandom'] == true) ? true : false;
    $this->parts ['mainTableName'] = TABLE_PRODUCTS;
    $this->parts ['mainTableAlias'] = 'p';
    $this->parts ['mainTableFkeyField'] = 'products_id';
    if (isset($this->parameters ['mainTable'])) {
      $this->parts ['mainTableName'] = $this->parameters ['mainTable'] ['table'];
      $this->parts ['mainTableAlias'] = $this->parameters ['mainTable'] ['alias'];
      $this->parts ['mainTableFkeyField'] = $this->parameters ['mainTable'] ['fkeyFieldLeft'];
    }
    $this->parts ['tableAliases'] [$this->parts ['mainTableName']] = $this->parts ['mainTableAlias'];
    $this->notify('NOTIFY_QUERY_BUILDER_INITPARAMETERS_END');
  }
  /**
   * assemble the parts into a query string
   */
  public function processQuery()
  {
    $this->selectColumns = array();
    $this->notify('NOTIFY_QUERY_BUILDER_ASSEMBLEPARTS_START');
    $this->query ['select'] = "SELECT " . ($this->isDistinct ? ' DISTINCT ' : '') . $this->parts ['mainTableAlias'] . ".*";
    $this->preProcessJoins();
    // print_r($this->parts);
    $this->processFilters();
    // print_r($this->parts);
    $this->preProcessJoins();
    $this->query ['joins'] = '';
    $this->query ['table'] = ' FROM ';
    $this->processJoins();
    $this->query ['table'] .= $this->parts ['mainTableName'] . " AS " . $this->parts ['mainTableAlias'] . " ";
    $this->processWhereClause();
    $this->processOrderBys();
    $this->processSelectList();
    $this->mainQuery = $this->query ['select'] . $this->query ['table'] . $this->query ['joins'] . $this->query ['where'] . $this->query ['orderBy'];
    if ($this->isPaginated) {
      if (! isset($this->countQuery)) {
        $this->countQuery = "SELECT COUNT(*) AS total " . $this->query ['table'] . $this->query ['joins'] . $this->query ['where'] . $this->query ['orderBy'];
      }
    }
    $this->notify('NOTIFY_QUERY_BUILDER_ASSEMBLEPARTS_END');
  }
  /**
   * pre-process any join tables
   */
  public function preProcessJoins()
  {
    $this->notify('NOTIFY_QUERY_BUILDER_PREPROCESSJOINS_START');
    if (count($this->parts ['joinTables']) > 0) {
      foreach ( $this->parts ['joinTables'] as $joinTable ) {
        $this->parts ['tableAliases'] [$joinTable ['table']] = $joinTable ['alias'];
      }
    }
    $this->notify('NOTIFY_QUERY_BUILDER_PREPROCESSJOINS_END');
  }
  /**
   * process any join tables
   */
  public function processJoins()
  {
    $this->notify('NOTIFY_QUERY_BUILDER_PROCESSJOINS_START');
    if (count($this->parts ['joinTables']) > 0) {
      foreach ( $this->parts ['joinTables'] as $joinTable ) {
        $this->query ['joins'] .= strtoupper($joinTable ['type']) . " JOIN " . $joinTable ['table'] . ' AS ' . $joinTable ['alias'];
        $fkeyFieldLeft = $this->parts ['mainTableAlias'] . '.' . $this->parts ['mainTableFkeyField'];
        $fkeyFieldRight = $joinTable ['alias'] . '.' . $this->parts ['mainTableFkeyField'];
        if (isset($joinTable ['fkeyFieldLeft'])) {
          $fkeyFieldLeft = $this->parts ['mainTableAlias'] . '.' . $joinTable ['fkeyFieldLeft'];
          if (isset($joinTable ['fkeyTable'])) {
            $fkeyFieldLeft = $this->parts ['tableAliases'] [constant($joinTable ['fkeyTable'])] . '.' . $joinTable ['fkeyFieldLeft'];
          }
          if (isset($joinTable ['fkeyFieldRight'])) {
            $fkeyFieldRight = $joinTable ['alias'] . '.' . $joinTable ['fkeyFieldRight'];
          } else {
            $fkeyFieldRight = $joinTable ['alias'] . '.' . $joinTable ['fkeyFieldLeft'];
          }
        }
        $this->query ['joins'] .= " ON " . $fkeyFieldLeft . " = " . $fkeyFieldRight . " ";
        if (isset($joinTable ['customAnd'])) {
          $this->query ['joins'] .= " " . $joinTable ['customAnd'] . " ";
        }
        if (isset($joinTable ['addColumns']) && $joinTable ['addColumns']) {
          $this->query ['select'] .= ", " . $joinTable ['alias'] . ".*";
        }
      }
      $this->query ['table'] .= "(";
      $this->query ['joins'] .= ")";
    }
    $this->notify('NOTIFY_QUERY_BUILDER_PROCESSJOINS_END');
  }
  /**
   * process any where clauses
   */
  public function processWhereClause()
  {
    $this->notify('NOTIFY_QUERY_BUILDER_PROCESSWHERECLAUSE_START');
    $this->query ['where'] = ' WHERE 1';
    if (is_array($this->parts ['whereClauses']) && count($this->parts ['whereClauses']) > 0) {
      foreach ( $this->parts ['whereClauses'] as $whereClause ) {
        if (isset($whereClause ['custom'])) {
          $this->query ['where'] .= " " . trim($whereClause ['custom']) . " ";
        } else {
          if (! isset($whereClause ['test']))
            $whereClause ['test'] = '=';
          switch (strtoupper($whereClause ['test'])) {
            case 'IN' :
              $this->query ['where'] .= " " . $whereClause ['type'] . " " . $this->parts ['tableAliases'] [$whereClause ['table']] . "." . $whereClause ['field'] . " IN ( " . $whereClause ['value'] . " ) ";
              break;
            case 'LIKE' :
              $this->query ['where'] .= " " . $whereClause ['type'] . " " . $this->parts ['tableAliases'] [$whereClause ['table']] . "." . $whereClause ['field'] . " LIKE " . $whereClause ['value'] . " ";
              break;
            default :
              $this->query ['where'] .= " " . $whereClause ['type'] . " " . $this->parts ['tableAliases'] [$whereClause ['table']] . "." . $whereClause ['field'] . " = " . $whereClause ['value'];
              break;
          }
        }
      }
    }
    $this->notify('NOTIFY_QUERY_BUILDER_PROCESSWHERECLAUSE_END');
  }
  /**
   * process order by clause
   */
  public function processOrderBys()
  {
    $this->notify('NOTIFY_QUERY_BUILDER_PROCESSORDERBYS_START');
    $this->query ['orderBy'] = "";
    if ($this->isRandom) {
      $this->parts ['orderBys'] [] = array(
          'type' => 'mysql',
          'field' => 'RAND()'
      );
    }
    if (count($this->parts ['orderBys']) > 0) {
      $this->query ['orderBy'] = " ORDER BY ";
      foreach ( $this->parts ['orderBys'] as $orderBy ) {
        if ($orderBy ['type'] == 'mysql') {
          $this->query ['orderBy'] .= $orderBy ['field'];
        } elseif ($orderBy ['type'] == 'custom') {
          if (isset($orderBy ['table'])) {
            $this->query ['orderBy'] .= $this->parts ['tableAliases'] [$orderBy ['table']] . ".";
          } else {
            // $this->query['orderBy'] .= "p.";
          }
          $this->query ['orderBy'] .= $orderBy ['field'] . ", ";
        }
      }
      if (substr($this->query ['orderBy'], strlen($this->query ['orderBy']) - 2) == ', ') {
        $this->query ['orderBy'] = substr($this->query ['orderBy'], 0, strlen($this->query ['orderBy']) - 2) . " ";
      }
    }
    $this->notify('NOTIFY_QUERY_BUILDER_PROCESSORDERBYS_END');
  }
  public function processFilters()
  {
    if (count($this->parts ['filters']) > 0) {
      foreach ( $this->parts ['filters'] as $filter ) {
        if (is_array($filter)) {
          require_once (DIR_WS_CLASSES . 'class.' . $filter ['requestHandler'] . '.php');
          $filter = new $filter ['requestHandler']($this, isset($filter ['parameters']) ? $filter ['parameters'] : array());
          $this->filterOutputVariables = array_merge($this->filterOutputVariables, $filter->getOutputVariables());
        }
      }
    }
  }
  public function processBindVars()
  {
    global $db;
    if (count($this->parts ['bindVars']) > 0) {
      foreach ( $this->parts ['bindVars'] as $bindVars ) {
        $this->mainQuery = $db->bindVars($this->mainQuery, $bindVars [0], $bindVars [1], $bindVars [2]);
        if (isset($this->countQuery)) {
          $this->countQuery = $db->bindVars($this->countQuery, $bindVars [0], $bindVars [1], $bindVars [2]);
        }
      }
    }
  }
  public function processSelectList()
  {
    if (count($this->parts ['selectList']) > 0) {
      foreach ( $this->parts ['selectList'] as $selectList ) {
        $this->query ['select'] .= ", " . $selectList;
      }
    }
  }
  /**
   * execute the query and get resultset
   */
  public function executeQuery()
  {
    global $db;
    $resultItems = array();
    $this->notify('NOTIFY_QUERY_BUILDER_EXECUTEQUERY_START');
    $this->processBindVars();
    // echo $this->mainQuery;
    // echo $this->countQuery;
    if ($this->isPaginated) {
      $itemsPerPage = isset($this->paginationQueryLimit) ? $this->paginationQueryLimit : 10;
      $this->paginator = new zcPaginator(array(
          'mainQuery' => $this->mainQuery,
          'countQuery' => $this->countQuery
      ), array(
          'itemsPerPage' => $itemsPerPage
      ), 'query', $this->paginationScroller);
      $resultItems = $this->paginator->getItems();
      // print_r($resultItems);
    } else {
      $result = $db->execute($this->mainQuery, $this->queryLimit);
      $this->notify('NOTIFY_QUERY_BUILDER_EXECUTEQUERY_RESULT', NULL, $result);
      $this->resultCount = $result->recordCount();
      while ( ! $result->EOF ) {
        $resultItems [] = $result->fields;
        $result->moveNext();
      }
    }
    // echo $this->mainQuery;
    foreach ( $resultItems as $resultItem ) {
      if (count($this->parts ['derivedItems']) > 0) {
        foreach ( $this->parts ['derivedItems'] as $derivedItem ) {
          if (is_string($derivedItem ['handler'])) {
            if (strpos($derivedItem ['handler'], '::') != 0) {
            } else {
              $resultItem [$derivedItem ['field']] = $this->$derivedItem ['handler']($resultItem);
            }
          } else {
            $resultItem [$derivedItem ['field']] = $derivedItem ['handler']($resultItem);
          }
        }
      }
      $this->resultItems [] = $resultItem;
    }
    $this->notify('NOTIFY_QUERY_BUILDER_EXECUTEQUERY_END');
  }
  /**
   * helper method
   *
   * @param array $queryResult
   * @return Ambigous <string, unknown, multitype:string boolean NULL Ambigous <boolean, string> Ambigous <number, unknown> Ambigous <boolean, unknown, string> >
   */
  public function displayPriceBuilder($resultItem)
  {
    $displayPrice = zen_get_products_display_price($resultItem ['products_id']);
    return $displayPrice;
  }
  /**
   *
   * @param array $queryResult
   * @return unknown
   */
  public function productCpathBuilder($resultItem)
  {
    $productCpath = zen_get_generated_category_path_rev((isset($resultItem ['categories_id']) ? $resultItem ['categories_id'] : $resultItem ['master_categories_id']));
    return $productCpath;
  }
  /**
   * getter
   */
  public function getResultItems()
  {
    $this->notify('NOTIFY_QUERY_BUILDER_GETRESULTITEMS_START');
    return $this->resultItems;
  }
  /**
   * getter
   */
  public function getResultCount()
  {
    $this->notify('NOTIFY_QUERY_BUILDER_GETRESULTCOUNT_START');
    return $this->resultCount;
  }
  /**
   * getter
   */
  public function getPaginator()
  {
    $this->notify('NOTIFY_QUERY_BUILDER_GETPAGINATOR_START');
    return $this->paginator;
  }
  /**
   * getter
   */
  public function getFilterOutputVariables()
  {
    $this->notify('NOTIFY_QUERY_BUILDER_GETFILTEROUTPUTVARIABLES_START');
    return $this->filterOutputVariables;
  }
  /**
   * getter
   */
  public function getMainQuery()
  {
    $this->notify('NOTIFY_QUERY_BUILDER_GETMAINQUERY_START');
    return $this->mainQuery;
  }
  /**
   * getter
   */
  public function getParts()
  {
    $this->notify('NOTIFY_QUERY_BUILDER_GETPARTS_START');
    return $this->parts;
  }
  /**
   * setter
   */
  public function setParts($value)
  {
    $this->notify('NOTIFY_QUERY_BUILDER_SETPARTS_START');
    $this->parts = $value;
  }
}