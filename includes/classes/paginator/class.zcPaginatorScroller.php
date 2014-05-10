<?php
/**
 * zcPaginatorScroller Class.
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
/**
 * zcPaginatorScroller Class
 *
 * @package classes
 */
class zcPaginatorScroller extends base
{
  protected $paginator;
  protected $paginatorAdapter;
  protected $context;
  protected $parameters;
  static public function factory ($scrollerType)
  {
    $options = array(
        'classDirectory' => 'paginator/paginatorScrollers/',
        'classFile' => 'class.zcPaginatorScroller' . ucfirst ( $scrollerType ) . '.php',
        'className' => 'zcPaginatorScroller' . ucfirst ( $scrollerType )
    );
    $paginatorScroller = base::classFactory ( DIR_FS_CATALOG . DIR_WS_CLASSES, $options );
    return $paginatorScroller;
  }
  public function init ($paginator)
  {
    $this->parameters = array();
    $this->paginator = $paginator;
    $this->paginatorAdapter = $this->paginator->getAdapter ();
  }
  public function normalizeItem ($itemCount)
  {
    if ($itemCount < 1)
      $itemCount = 1;
    if ($itemCount > $this->paginatorAdapter->getTotalItems ())
      $itemCount = $this->paginatorAdapter->getTotalItems ();
    return $itemCount;
  }
  public function getParameters ()
  {
    return $this->parameters;
  }
  public function getParameter ($parameterName)
  {
    return $this->parameters[$parameterName];
  }
}