<?php
/**
 * zcPaginator Class.
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
/**
 * zcPaginator Class
 *
 * A class to paginate a list of items. Uses an adapter and scroller to separate the handling
 * of the data and the output variables that are passed to the view.
 *
 * @package classes
 */
class zcPaginator extends base
{
  protected $adapter;
  protected $scroller;
  private $parameters;
  private $data;
  public function __construct ($data, $parameters = NULL, $adapter = 'query', $scroller = 'standard')
  {
    $this->parameters = $parameters;
    $this->data = $data;
    if (! $adapter instanceof zcPaginatorAdapter)
    {
      $adapter = zcPaginatorAdapter::factory ( $adapter );
    }
    if (! $scroller instanceof zcPaginatorScroller)
    {
      $scroller = zcPaginatorScroller::factory ( $scroller );
    }
    $this->adapter = $adapter;
    $this->scroller = $scroller;
    $this->adapter->init ( $this );
    $this->adapter->run ();
    $this->scroller->init ( $this );
    $this->scroller->run ();
  }
  public function getItems ()
  {
    return $this->adapter->getCurrentItemList ();
  }
  public function getAdapter ()
  {
    return $this->adapter;
  }
  public function getScroller ()
  {
    return $this->scroller;
  }
  public function getParameters ()
  {
    return $this->parameters;
  }
  public function getData ()
  {
    return $this->data;
  }
  public function getScrollerParameters ()
  {
    return $this->scroller->getParameters ();
  }
  public function getScrollerParameter ($parameterName)
  {
    return $this->scroller->getParameter ( $parameterName );
  }
  public function showPaginator ()
  {
    return ($this->adapter->getTotalPages () > 1);
  }
}