<?php
/**
 * zcPaginatorAdapterArray Class.
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
/**
 * zcPaginatorAdapterArray Class
 *
 * @package classes
 */
class zcPaginatorAdapterArray extends zcPaginatorAdapter
{
  public function run ()
  {
    $data = $this->paginator->getData ();
    $this->totalItems = count ( $data );
    $this->totalPages = ceil ( $this->totalItems / $this->itemsPerPage );
    $resultList = array_slice ( $data, $this->currentItem - 1, $this->itemsPerPage );
    $this->currentItemList = $resultList;
  }
}