<?php
/**
 * Class AbstractFilter
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: currencies.php 15880 2010-04-11 16:24:30Z wilt $
 */
namespace ZenCart\ListingBox\Filter;
/**
 * Class AbstractFilter
 * @package ZenCart\ListingBox\Filter
 */
abstract class AbstractFilter
{
    /**
     * @var array
     */
    protected $filterVars;
    /**
     * @var
     */
    protected $diContainer;
    /**
     * @var
     */
    protected $params;
    /**
     * @var int
     */
    protected $currentCategoryId;

    /**
     * @param $diContainer
     * @param $params
     */
    public function __construct($diContainer, $params)
    {
        $current_category_id = $diContainer->get('globalRegistry')['current_category_id'];
        $this->currentCategoryId = $current_category_id;
        $this->filterVars = array();
        $this->diContainer = $diContainer;
        $this->params = $params;
    }

    /**
     *
     */
    public function init()
    {
        $productQuery = $this->diContainer->get('listingBox')->getProductQuery();
        $productQuery = $this->filterItem($productQuery);
        $this->diContainer->get('listingBox')->setProductQuery($productQuery);
    }

    /**
     * @param array $productQuery
     * @return mixed
     */
    abstract public function filterItem(array $productQuery);

    /**
     * @return array
     */
    public function getFilterVars()
    {
        return $this->filterVars;
    }
}
