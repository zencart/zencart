<?php
/**
 * Class PaginatorBuilder
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
namespace ZenCart\QueryBuilder;
use ZenCart\Paginator\Paginator as Paginator;

/**
 * Class PaginatorBuilder
 * @package ZenCart\QueryBuilder
 */
class PaginatorBuilder
{
    protected $paginator;

    /**
     * @param $request
     * @param array $listingQuery
     * @param Paginator $paginator
     */
    public function __construct($request, array $listingQuery, Paginator $paginator)
    {
        $this->paginator = $paginator;
        if (!issetorArray($listingQuery, 'isPaginated', false)) {
            $this->paginator = null;
            return;
        }
        $this->buildPaginator($request, $paginator, $listingQuery);
    }

    /**
     * @param Paginator $paginator
     * @param array $listingQuery
     */
    protected function buildPaginator($request, Paginator $paginator, array $listingQuery)
    {
        if (!isset($listingQuery['pagination'])) {
            return;
        }
        if (isset($listingQuery['pagination']['scrollerParams'])) {
            $paginator->setScrollerParams($listingQuery['pagination']['scrollerParams']);
        }
        if (isset($listingQuery['pagination']['adapterParams'])) {
            $paginator->setAdapterParams($listingQuery['pagination']['adapterParams']);
        }
    }

    /**
     * @return Paginator
     */
    public function getPaginator()
    {
        return $this->paginator;
    }
}
