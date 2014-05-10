<?php
/**
 * zcPaginatorAdapter Class.
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
/**
 * zcPaginatorAdapter Class
 *
 * @package classes
 */
class zcPaginatorAdapter extends base
{
  protected $paginator;
  protected $parameters;
  protected $pagingVariableName;
  protected $pagingVariableSource;
  protected $itemsPerPage;
  protected $countQuery;
  protected $currentItem;
  protected $currentItemList;
  protected $totalItems;
  protected $totalPages;
  static public function factory ($adapterType)
  {
    $options = array(
        'classDirectory' => 'paginator/paginatorAdapters/',
        'classFile' => 'class.zcPaginatorAdapter' . ucfirst ( $adapterType ) . '.php',
        'className' => 'zcPaginatorAdapter' . ucfirst ( $adapterType )
    );
    $paginatorAdapter = base::classFactory ( DIR_FS_CATALOG . DIR_WS_CLASSES, $options );
    return $paginatorAdapter;
  }
  public function init ($paginator)
  {
    $this->paginator = $paginator;
    $this->parameters = $this->paginator->getParameters ();
    $this->pagingVariableName = (isset ( $this->parameters['pagingVariableName'] )) ? $this->parameters['pagingVariableName'] : 'page';
    $this->pagingVariableSource = (isset ( $this->parameters['pagingVariableSource'] )) ? $this->parameters['pagingVariableSource'] : 'get';
    $this->itemsPerPage = (isset ( $this->parameters['itemsPerPage'] )) ? $this->parameters['itemsPerPage'] : 15;
    $this->currentPage = ($this->pagingVariableSource == 'get') ? zcRequest::readGet($this->pagingVariableName, 1) : zcRequest::readPost($this->pagingVariableName, 1);
    if ($this->currentPage <= 0)
      $this->currentPage = 1;
    $this->countQuery = NULL;
    $this->currentItem = ($this->currentPage - 1) * $this->itemsPerPage + 1;
    $this->currentItemList = array();
  }
  public function getPaginator ()
  {
    return $this->paginator;
  }
  public function getItemsPerPage ()
  {
    return $this->itemsPerPage;
  }
  public function getTotalItems ()
  {
    return $this->totalItems;
  }
  public function getCurrentItemList ()
  {
    return $this->currentItemList;
  }
  public function getCurrentItem ()
  {
    return $this->currentItem;
  }
  public function getCurrentPage ()
  {
    return $this->currentPage;
  }
  public function getTotalPages ()
  {
    return $this->totalPages;
  }
  public function getPagingVariableName ()
  {
    return $this->pagingVariableName;
  }
}