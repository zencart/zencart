<?php
/**
 * zcAbstractListingBoxBase
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
/**
 * class zcAbstractListingBoxBase
 *
 * @package classes
 */
abstract class zcAbstractListingBoxBase extends base
{
  /**
   *
   * @var object
   */
  protected $layoutFormatter;
  /**
   *
   * @var string
   */
  protected $className;
  /**
   *
   * @var boolean
   */
  protected $hasContent;
  /**
   *
   * @var integer
   */
  protected $itemCount;
  /**
   *
   * @var array
   */
  protected $items;
  /**
   *
   * @var array
   */
  protected $formattedItems;
  /**
   *
   * @var string
   */
  protected $mainTemplate;
  /**
   *
   * @var array
   */
  protected $templateVariables;
  /**
   *
   * @var array
   */
  protected $productQuery = array();
  /**
   *
   * @var array
   */
  protected $outputLayout = array();
  /**
   *
   * @var array
   */
  protected $title;
  /**
   * constructor
   *
   * @param object $formatter
   */
  public function __construct()
  {
    $this->layoutFormatter = $formatter;
    $this->notify('NOTIFY_LISTING_BOX_CONSTRUCT_END');
  }
  /**
   * main method to process listing box
   */
  public function init()
  {
    $this->notify('NOTIFY_LISTING_BOX_INIT_START');
    $this->notify('NOTIFY_LISTING_BOX_INITPRODUCTQUERY_BEFORE');
    $this->initProductQueryAndOutputLayout();
    $this->layoutFormatter = new $this->outputLayout['formatter']();
    $this->notify('NOTIFY_LISTING_BOX_INITPRODUCTQUERY_AFTER');
    $this->className = str_replace('zcListingBox', '', get_class($this));
    $m = zcQueryBuilderManager::getInstance();
    $q = $m->buildNewQuery($this->className, $this->productQuery);
    $this->filterOutputVariables = $q->getFilterOutputVariables();
    $this->mainQuery = $q->getMainQuery();
    $q->executeQuery();
    $this->itemCount = $q->getResultCount();
    $this->items = $q->getResultItems();
    $this->paginator = $q->getPaginator();
    $this->templateVariables ['filter'] = $this->filterOutputVariables ;
    $this->notify('NOTIFY_LISTING_BOX_INITTITLE_BEFORE');
    $this->title = $this->initTitle();
    $this->notify('NOTIFY_LISTING_BOX_INITTITLE_AFTER');
    $this->formattedItems = $this->layoutFormatter->format($this);
    $this->hasContent = (count($this->formattedItems) > 0) ? TRUE : FALSE;
    $this->initMainTemplate();
    $this->initTemplateVariables();
    $this->notify('NOTIFY_LISTING_BOX_INIT_END');
  }
  /**
   * abstract method initProductQuery
   *
   * This method is used to set the productQuery array
   */
  abstract public function initProductQueryAndOutputLayout();
  /**
   * method to choose which template we are using
   */
  public function initMainTemplate()
  {
    $this->mainTemplate = $this->layoutFormatter->getDefaultTemplate();
    $this->notify('NOTIFY_LISTING_BOX_INITMAINTEMPLATE_END');
  }
  /**
   * set template variables for view
   */
  public function initTemplateVariables()
  {
    global $categoryError;
    $this->notify('NOTIFY_LISTING_BOX_INITTEMPLATEVARIABLES_START');
    $this->templateVariables ['title'] = $this->title;
    $this->templateVariables ['items'] = $this->items;
    $this->templateVariables ['hasFormattedItems'] = $this->hasContent;
    $this->templateVariables ['formattedItems'] = $this->formattedItems;
    $this->templateVariables ['template'] = $this->mainTemplate;
    $this->templateVariables ['paginatorScrollerTemplate'] = isset($this->productQuery['paginatorScrollerTemplate']) ? $this->productQuery['paginatorScrollerTemplate'] : 'tpl_paginator_standard.php';
    $this->templateVariables ['className'] = $this->className;
    if (isset($this->paginator)) {
      $this->templateVariables ['pagination']['scroller'] = $this->paginator->getScroller()->getParameters();
      $this->templateVariables ['pagination']['show'] = $this->paginator->showPaginator();
      $this->templateVariables ['pagination']['totalItems'] = $this->paginator->getAdapter()->getTotalItems();
      $this->templateVariables ['pagination']['navLinkText'] = TEXT_DISPLAY_NUMBER_OF_PRODUCTS;
      $this->templateVariables ['pagination']['showPaginatorTop'] = ((PREV_NEXT_BAR_LOCATION == '1') || (PREV_NEXT_BAR_LOCATION == '3'));
      $this->templateVariables ['pagination']['showPaginatorBottom'] = ((PREV_NEXT_BAR_LOCATION == '2') || (PREV_NEXT_BAR_LOCATION == '3'));
    }
    $this->templateVariables ['showFiltersForm'] = (isset($this->templateVariables['filter']['doFilterList']) && $this->templateVariables['filter']['doFilterList']) || ($this->hasContent && PRODUCT_LIST_ALPHA_SORTER == 'true') || (!$this->hasContent && $categoryError == false) ? TRUE : FALSE;
    $this->notify('NOTIFY_LISTING_BOX_INITTEMPLATEVARIABLES_END');
  }
  /**
   * abstract method to set title used for listingbox
   */
  abstract public function initTitle();
  /**
   * method to get the column count for the listingbox
   */
  public function getColumnCount()
  {
    return NULL;
  }
  /**
   * getter
   */
  public function getTemplateVariables()
  {
    $this->notify('NOTIFY_LISTING_BOX_GETTEMPLATEVARIABLES_START');
    return $this->templateVariables;
  }
  /**
   * getter
   */
  public function getItems()
  {
    $this->notify('NOTIFY_LISTING_BOX_GETITEMS_START');
    return $this->items;
  }
  /**
   * getter
   */
  public function getHasContent()
  {
    $this->notify('NOTIFY_LISTING_BOX_GETHASCONTENT_START');
    return $this->hasContent;
  }
  /**
   * getter
   */
  public function getProductQuery()
  {
    $this->notify('NOTIFY_LISTING_BOX_GETPRODUCTQUERY_START');
    return $this->productQuery;
  }
  /**
   * getter
   */
  public function getOutputLayout()
  {
    $this->notify('NOTIFY_LISTING_BOX_GETOUTPUTLAYOUT_START');
    return $this->outputLayout;
  }
  /**
   * getter
   */
  public function getMainQuery()
  {
    $this->notify('NOTIFY_LISTING_BOX_GETMAINQUERY_START');
    return $this->mainQuery;
  }
  /**
   * getter
   */
  public function getIsRandom()
  {
    $this->notify('NOTIFY_LISTING_BOX_GETISRANDOM_START');
    return $this->isRandom;
  }
  /**
   * getter
   */
  public function getFilterOutputVariables()
  {
    $this->notify('NOTIFY_LISTING_BOX_GETFILTEROUTPUTVARIABLES_START');
    return $this->filterOutputVariables;
  }
  /**
   * getter
   */
  public function getFormattedItemsCount()
  {
    $this->notify('NOTIFY_LISTING_BOX_GETFORMATTEDITEMSCOUNT_START');
    return count($this->formattedItems);
  }
  /**
   * setter
   *
   * @param string $key
   * @param mixed $value
   */
  public function setTemplateVariable($key, $value)
  {
    $this->templateVariables [$key] = $value;
  }
}
