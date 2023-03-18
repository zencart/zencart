<?php declare(strict_types=1);
/**
 * @copyright Copyright 2003-2023 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2022 Oct 16 Modified in v1.5.8a $
 */

namespace Zencart\Filters;

use Illuminate\Database\Eloquent\Builder;
use Zencart\Request\Request;

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

    public function processRequest(Request $request, Builder $query) : Builder
    {
        if (!$this->hasFilters()) {
            return $query;
        }
        foreach ($this->filters as $filter) {
            $query = $filter->processRequest($request, $query);
        }
        return $query;
    }

    public function hasFilters() : bool
    {
        if (!count($this->filterDefinitions)) {
            return false;
        }
        return true;
    }

    public function getFilters() : array
    {
        return $this->filters;
    }
}
