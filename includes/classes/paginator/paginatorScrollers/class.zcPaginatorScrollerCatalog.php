<?php
/**
 * zcPaginatorScrollerStandard Class.
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
/**
 * zcPaginatorScrollerStandard Class
 *
 * @package classes
 */
class zcPaginatorScrollerCatalog extends zcPaginatorScroller
{
  public function run ()
  {
    $paginatorParameters = $this->paginator->getParameters ();
    $pagingVariableName = (isset ( $paginatorParameters['pagingVariableName'] )) ? $paginatorParameters['pagingVariableName'] : 'page';
    $maximumPageLinks = (isset ( $paginatorParameters['maxPageLinks'] )) ? $paginatorParameters['maxPageLinks'] : 10;
    $scrollerLinkParameters = (isset ( $paginatorParameters['scrollerLinkParameters'] )) ? $paginatorParameters['scrollerLinkParameters'] . '&' : '';
    if (! isset ( $paginatorParameters['disableZenGetAllGetParams'] ))
    {
      $scrollerLinkParameters = zen_get_all_get_params ( array(
          $pagingVariableName
      ) ) . $scrollerLinkParameters;
    }
    $pageWindowNumber = intval ( $this->paginatorAdapter->getCurrentPage () / $maximumPageLinks );
    if ($this->paginatorAdapter->getCurrentPage () % $maximumPageLinks)
      $pageWindowNumber ++;
    $this->parameters['fromItem'] = $this->normalizeItem ( $this->paginatorAdapter->getCurrentItem () );
    $this->parameters['toItem'] = $this->normalizeItem ( ($this->paginatorAdapter->getCurrentItem () + $this->paginatorAdapter->getItemsPerPage () - 1) );
    $this->parameters['totalItems'] = $this->paginatorAdapter->getTotalItems ();
    $this->parameters['flagHasPrevious'] = ($this->paginatorAdapter->getCurrentPage () > 1) ? TRUE : FALSE;
    $this->parameters['flagHasNext'] = ($this->paginatorAdapter->getCurrentPage () < $this->paginatorAdapter->getTotalPages ()) ? TRUE : FALSE;
    $this->parameters['previousLink'] = zen_href_link ( zcRequest::readGet('main_page'), $scrollerLinkParameters . $pagingVariableName . '=' . ($this->paginatorAdapter->getCurrentPage () - 1) );
    $this->parameters['nextLink'] = zen_href_link ( zcRequest::readGet('main_page'), $scrollerLinkParameters . $pagingVariableName . '=' . ($this->paginatorAdapter->getCurrentPage () + 1) );
    // $this->parameters['previousLink'] = zcLinkHelper::getLink(zcUtilGeneral::getPageBase(), $pagingVariableName . '=' . ($this->paginatorAdapter->getCurrentPage()-1) . $scrollerLinkParameters, array('connectionType'=>zcUtilGeneral::getRequestType()));
    // $this->parameters['nextLink'] = zcLinkHelper::getLink(zcUtilGeneral::getPageBase(), $pagingVariableName . '=' . ($this->paginatorAdapter->getCurrentPage()+1) . $scrollerLinkParameters, array('connectionType'=>zcUtilGeneral::getRequestType()));
    $itemList = array();
    if ($this->paginatorAdapter->getTotalPages () > 1)
    {
      for($i = 1 + (($pageWindowNumber - 1) * $maximumPageLinks); ($i <= ($pageWindowNumber * $maximumPageLinks)) && ($i <= $this->paginatorAdapter->getTotalPages ()); $i ++)
      {

        $itemList[] = array(
            'itemNumber' => $i,
            'itemLink' => zen_href_link ( zcRequest::readGet('main_page'), $scrollerLinkParameters . $pagingVariableName . '=' . $i ),
            'isCurrent' => ($i == $this->paginatorAdapter->getCurrentPage ()) ? TRUE : FALSE
        );
        // $itemList[] = array('itemNumber'=>$i, 'itemLink'=>zcLinkHelper::getLink(zcUtilGeneral::getPageBase(), $pagingVariableName . '=' . $i . $scrollerLinkParameters, array('connectionType'=>zcUtilGeneral::getRequestType())), 'isCurrent'=>($i == $this->paginatorAdapter->getCurrentPage()) ? TRUE : FALSE);
      }
    }
    $this->parameters['linkList'] = $itemList;
  }
}