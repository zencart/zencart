<?php
/**
 * Class PaginatorBuilder
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
namespace ZenCart\Platform\listingBox;
use ZenCart\Platform\Paginator\Paginator as Paginator;

/**
 * Class PaginatorBuilder
 * @package ZenCart\Platform\listingBox
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
        $this->setDefaultParams($request, $paginator);
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

    protected function setDefaultParams($request, Paginator $paginator)
    {
       $paginator->setScrollerParams(array('cmd'=>$request->readGet('main_page')));
    }

    /**
     * @return Paginator
     */
    public function getPaginator()
    {
        return $this->paginator;
    }
}
