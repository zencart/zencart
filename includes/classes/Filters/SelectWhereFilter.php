<?php declare(strict_types=1);
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */

namespace Zencart\Filters;

use Illuminate\Database\Eloquent\Builder;
use Zencart\Request\Request;

class SelectWhereFilter extends baseFilter implements RequestFilter
{
    private $default;

    public function make(array $filterDefinition) : void
    {
        $this->filterDefinition = $filterDefinition;
        $this->default = $filterDefinition['default'] ?? '';
        $this->options = $this->getOptionsForSelect($filterDefinition);
        $this->parameters = $this->setParameters($filterDefinition);
    }

    public function output() : string
    {
        $select = $this->makeSelect($this->options, $this->default, $this->parameters);
        return $select;
    }

    public function processRequest(Request $request, Builder $query) : Builder
    {
        $this->default = $request->input($this->filterDefinition['selectName'], '*');
        if ((string)$this->default == '*') {
            return $query;
        }
        $query = $query->where($this->filterDefinition['field'], $this->default);
        return $query;
    }

    private function getOptionsForSelect(array $filterDefinition) : array
    {
        return $filterDefinition['options'];
    }

    private function setParameters($filterDefinition) : array
    {
        $parameters['label'] = $filterDefinition['label'];
        $parameters['name'] = $filterDefinition['selectName'];
        $parameters['class'] = $filterDefinition['class'] ?? '';
        $parameters['id'] = $filterDefinition['id'] ?? $parameters['name'];
        $parameters['auto'] = $filterDefinition['auto'] ?? false;
        return $parameters;
    }
}
