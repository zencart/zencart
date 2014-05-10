<?php
/**
 * File contains just the zcQueryBuilderFilterTypeFilter class
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
/**
 * class zcQueryBuilderFilterTypeFilter
 *
 * @package classes
 */
class zcQueryBuilderFilterTypeFilter extends zcAbstractQueryBuilderFilterBase
{
  /**
   * loads actual typeFilter based on get params.
   * replaces old index_filter code
   *
   * @see zcAbstractQueryBuilderFilterBase::filterItem()
   */
  public function filterItem()
  {
    $typeFilter = 'default';
    if (zcRequest::hasGet('typefilter') && ! zcRequest::hasGet('keyword'))
      $typeFilter = zcRequest::readGet('typefilter');
    $typeFilterClassName = 'zcTypeFilter' . ucfirst(base::camelize($typeFilter));
    $typeFilterClassFile = DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.' . $typeFilterClassName . '.php';
    if (file_exists($typeFilterClassFile)) {
      require_once $typeFilterClassFile;
      $typeFilterClass = new $typeFilterClassName($this->parts);
      $this->parts = $typeFilterClass->getParts();
      $this->outputVariables ['columnList'] = $typeFilterClass->getColumnList();
      $this->outputVariables ['filterOptions'] = $typeFilterClass->getFilterOptions();
      $this->outputVariables ['getOptionSet'] = $typeFilterClass->getGetOptionsSet();
      $this->outputVariables ['getOptionVariable'] = $typeFilterClass->getGetOptionVariable();
      $this->outputVariables ['doFilterList'] = $typeFilterClass->getDoFilterList();
    }
  }
}