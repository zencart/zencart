<?php
/**
 * Class PaginatorBuilder
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
namespace ZenCart\Platform\listingBox;
use ZenCart\Platform\listingBox\boxes\AbstractListingBox as AbstractListingBox;
use ZenCart\Platform\Paginator\Paginator as Paginator;

/**
 * Class PaginatorBuilder
 * @package ZenCart\Platform\listingBox
 */
class PaginatorBuilder
{
    protected $paginator;

    /**
     * @param AbstractListingBox $listingBox
     * @param Paginator $paginator
     */
    public function __construct($request, AbstractListingBox $listingBox, Paginator $paginator)
    {
        $this->paginator = $paginator;
        $productQuery = $listingBox->getProductQuery();
        if (!issetorArray($productQuery, 'isPaginated', false)) {
            $this->paginator = null;
            return;
        }
        $this->buildPaginator($request, $paginator, $productQuery);
    }

    /**
     * @param Paginator $paginator
     * @param array $productQuery
     */
    protected function buildPaginator($request, Paginator $paginator, array $productQuery)
    {
        $this->setDefaultParams($request, $paginator);
        if (!isset($productQuery['pagination'])) {
            return;
        }
        if (isset($productQuery['pagination']['scrollerParams'])) {
            $paginator->setScrollerParams($productQuery['pagination']['scrollerParams']);
        }
        if (isset($productQuery['pagination']['adapterParams'])) {
            $paginator->setAdapterParams($productQuery['pagination']['adapterParams']);
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
