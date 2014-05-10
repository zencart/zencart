<?php
/**
 * zcPaginatorAdapterQuery Class.
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
/**
 * zcPaginatorAdapterQuery Class
 *
 * @package classes
 */
class zcPaginatorAdapterQuery extends zcPaginatorAdapter
{
  public function run ()
  {
    global $db;
    $sqlQueries = $this->paginator->getData ();
    $sql = $sqlQueries['mainQuery'];
    $limit = ($this->currentItem - 1) . ',' . $this->itemsPerPage;
    $result = $db->execute ( $sql, $limit );
    $this->totalItems = $this->getItemCount ( $sqlQueries );
    $this->totalPages = ceil ( $this->totalItems / $this->itemsPerPage );
    $resultList = array();
    while (! $result->EOF)
    {
      $resultList[] = $result->fields;
      $result->moveNext ();
    }
    $this->currentItemList = $resultList;
  }
  public function getAdapterCounterResult ($sqlQueries)
  {
    global $db;
    $result = $db->execute ( $sqlQueries['countQuery'] );
    $retVal = $result->fields['total'];
    return $retVal;
  }
  public function getItemCount ($sqlQueries)
  {
    return $this->getAdapterCounterResult ( $sqlQueries );
  }
}