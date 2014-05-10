<?php
/**
 * File contains just the zcQueryBuilderManager class
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
/**
 * class zcQueryBuilderManager
 *
 * @package classes
 */
class zcQueryBuilderManager extends base
{
  /**
   *
   * @var array
   */
  protected $queryRegistry;
  /**
   *
   * @var array
   */
  protected $productRegistry;
  /**
   *
   * @var array
   */
  protected $categoryRegistry;
  /**
   *
   * @var object
   */
  private static $instance = NULL;
  /**
   * getInstance
   *
   * enforces singleton on class
   */
  public static function getInstance()
  {
    if (! self::$instance) {
      $class = __CLASS__;
      self::$instance = new $class();
    }
    return self::$instance;
  }
  /**
   * constructor
   *
   * Just set some properties
   */
  public function __construct()
  {
    $this->queryRegistry = array();
    $this->productRegistry = array();
    $this->notify('NOTIFY_QUERY_BUILDER_MANAGER_CONSTRUCTOR_END');
  }
  /**
   * Method creates a product query object
   *
   * @param string $name
   * @param array $parameters
   * @return string
   */
  public function buildNewQuery($name, array $parameters = array())
  {
    $this->notify('NOTIFY_QUERY_BUILDER_MANAGER_BUILDNEWQUERY_START');
    if (isset($queryRegistry [$name])) {
      return $queryRegistry [$name];
    }
    $this->queryRegistry [$name] = new zcQueryBuilder($parameters);
    $this->notify('NOTIFY_QUERY_BUILDER_MANAGER_BUILDNEWQUERY_END');
    return $this->queryRegistry [$name];
  }
  /**
   * method to register product info
   *
   * @param array $product
   */
  public function registerProduct($product)
  {
    $this->notify('NOTIFY_QUERY_BUILDER_MANAGER_REGISTERPRODUCT_START', NULL, $product);
    $this->productRegistry [$product ['products_id']] = $product;
    $this->notify('NOTIFY_QUERY_BUILDER_MANAGER_REGISTERPRODUCT_END');
  }
}