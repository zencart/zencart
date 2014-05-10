<?php
/**
 * File contains just the zcAbstractQueryBuilderFilterBase class
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
/**
 * class zcAbstractQueryBuilderFilterBase
 *
 * @package classes
 */
abstract class zcAbstractQueryBuilderFilterBase extends base
{
  public function __construct($queryBuilder, $parameters = array())
  {
    $this->queryBuilder = $queryBuilder;
    $this->outputVariables = array();
    $this->parameters = $parameters;
    $this->parts = $this->queryBuilder->getParts();
    $this->filterItem();
    $this->queryBuilder->setParts($this->parts);
  }
  abstract public function filterItem();
  public function getOutputVariables()
  {
    return $this->outputVariables;
  }
}