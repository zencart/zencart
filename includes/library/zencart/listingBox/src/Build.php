<?php
/**
 * Class Build
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
namespace ZenCart\ListingBox;
use Aura\Di\Container;
use Aura\Di\Factory;
/**
 * Class Build
 * @package ZenCart\ListingBox
 */
class Build
{
    /**
     * @var Container
     */
    protected $diContainer;

    /**
     * @param $zcDiContainer
     * @param $listingBox
     * @throws \Aura\Di\Exception\ContainerLocked
     * @throws \Aura\Di\Exception\ServiceNotFound
     * @throws \Aura\Di\Exception\ServiceNotObject
     * @throws \Exception
     */
    public function __construct($zcDiContainer, $listingBox)
    {
        $this->diContainer = $this->buildLocalDiContainer($zcDiContainer, $listingBox);
        $this->instantiateFormatter();
        $listingBox->setFilterVars($this->instantiateFilters());
        $this->diContainer->set('paginator', $this->instantiatePaginator());
        $this->diContainer->get('queryBuilder')->init($this->diContainer);
        $this->diContainer->get('queryBuilder')->processQuery();
        $this->diContainer->get('queryBuilder')->executeQuery();
        $this->diContainer->get('listingBox')->init($this->diContainer);
    }

    /**
     * @param $zcDiContainer
     * @param $listingBox
     * @return Container
     * @throws \Aura\Di\Exception\ContainerLocked
     * @throws \Aura\Di\Exception\ServiceNotObject
     */
    protected function buildLocalDiContainer($zcDiContainer, $listingBox)
    {
        $buildDiContainer = new Container(new Factory);
        $buildDiContainer->set('queryBuilder', new \ZenCart\Platform\QueryBuilder());
        $buildDiContainer->set('request', $zcDiContainer->get('request'));
        $buildDiContainer->set('dbConn', $zcDiContainer->get('dbConn'));
        $buildDiContainer->set('globalRegistry', $zcDiContainer->get('globalRegistry'));
        $buildDiContainer->set('listingBox', $listingBox);
        $buildDiContainer->set('derivedItemManager', new \ZenCart\ListingBox\DerivedItemManager());
        return $buildDiContainer;
    }
    /**
     * @throws \Aura\Di\Exception\ContainerLocked
     * @throws \Aura\Di\Exception\ServiceNotFound
     * @throws \Aura\Di\Exception\ServiceNotObject
     * @throws \Exception
     */
    protected function instantiateFormatter()
    {
        $outputLayout = $this->diContainer->get('listingBox')->getOutputLayout();
        if (!isset($outputLayout['formatter'])) {
            throw new \Exception();
        }
        $formatter = $outputLayout['formatter']['class'];
        $formatter = '\\ZenCart\\ListingBox\\formatter\\' . $formatter;
        $this->diContainer->set('formatter', new $formatter($this->diContainer));
    }

    /**
     * @return array
     * @throws \Aura\Di\Exception\ServiceNotFound
     */
    public function instantiateFilters()
    {
        $productQuery = $this->diContainer->get('listingBox')->getProductQuery();
        $filterVars = [];
        if (!isset($productQuery['filters'])) {
            return $filterVars;
        }

        foreach ($productQuery['filters'] as $filter) {
            $params = issetorArray($filter, 'parameters', array());
            $filter = '\\ZenCart\\ListingBox\\Filter\\' . $filter['name'];
            $filter = new $filter($this->diContainer, $params);
            $filter->init();
            $filterVars = array_merge($filterVars, $filter->getFilterVars());
        }
        return $filterVars;
    }

    /**
     * @return mixed
     * @throws \Aura\Di\Exception\ServiceNotFound
     */
    public function instantiatePaginator()
    {
        $productQuery = $this->diContainer->get('listingBox')->getProductQuery();
        $outputLayout = $this->diContainer->get('listingBox')->getOutputLayout();
        $ds = issetorArray($productQuery, 'paginatorDataSource', 'Mysqli');
        $po = issetorArray($outputLayout, 'paginatorOptions', array());
        $st = issetorArray($po, 'scroller', 'standard');
        $paginator = '\\ZenCart\\Platform\\Paginator\\Paginator';
        return new $paginator($this->diContainer->get('request'), null, $po, $ds, $st);
    }

    /**
     * @return mixed
     * @throws \Aura\Di\Exception\ServiceNotFound
     */
    public function getTemplateVariables()
    {
        return $this->diContainer->get('listingBox')->getTemplateVariables();
    }

    /**
     * @return mixed
     * @throws \Aura\Di\Exception\ServiceNotFound
     */
    public function getFormattedItemsCount()
    {
        return $this->diContainer->get('listingBox')->getFormattedItemsCount();
    }

    /**
     * @return mixed
     * @throws \Aura\Di\Exception\ServiceNotFound
     */
    public function getItemCount()
    {
        return $this->diContainer->get('listingBox')->getItemCount();
    }
}
