<?php declare(strict_types=1);
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 */

namespace Zencart\Filters;

use Zencart\Request\Request;

/**
 * @since ZC v1.5.8
 */
class FilterManager
{

    protected $filterDefinitions = [];
    protected $filterFactory;
    protected $filters = [];
    
    public function __construct(array $filterDefinitions, FilterFactory $filterFactory)
    {
        $this->filterDefinitions = $filterDefinitions;
        $this->filterFactory = $filterFactory;
    }

    /**
     * @since ZC v1.5.8
     */
    public function build() : void
    {
        $this->filters = [];
        if (!$this->hasFilters()) {
            return;
        }
        foreach ($this->filterDefinitions as $filterDefinition) {
            $filter = $this->filterFactory->make($filterDefinition);
            $this->filters[] = $filter;
            $filter->make($filterDefinition);
        }
    }

    /**
     * @since ZC v1.5.8
     */
    public function processRequest(Request $request, $query)
    {
        if (!$this->hasFilters()) {
            return $query;
        }
        foreach ($this->filters as $filter) {
            $query = $filter->processRequest($request, $query);
        }
        return $query;
    }

    /**
     * @since ZC v1.5.8
     */
    public function hasFilters() : bool
    {
        if (!count($this->filterDefinitions)) {
            return false;
        }
        return true;
    }

    /**
     * @since ZC v1.5.8
     */
    public function getFilters() : array
    {
        return $this->filters;
    }
}
