<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */


namespace Zencart\Filters;


use Illuminate\Http\Request;

class SelectWhereFilter extends baseFilter implements RequestFilter
{
    private $default;

    public function make(array $filterDefinition)
    {
        $this->filterDefinition = $filterDefinition;
        $this->default = $filterDefinition['default'] ?? '';
        $this->options = $this->getOptionsForSelect($filterDefinition);
        $this->parameters = $this->setParameters($filterDefinition);
    }

    public function output()
    {
        $select = $this->makeSelect($this->options, $this->default, $this->parameters);
        return $select;
    }

    public function processRequest(Request $request, $query)
    {
        $this->default = $request->input($this->filterDefinition['selectName'], '*');
        if ((string)$this->default == '*') {
            return $query;
        }
        $query = $query->where($this->filterDefinition['field'], $this->default);
        return $query;
    }

    private function getOptionsForSelect($filterDefinition)
    {
        return $filterDefinition['options'];
    }

    private function setParameters($filterDefinition)
    {
        $parameters['label'] = $filterDefinition['label'];
        $parameters['name'] = $filterDefinition['selectName'];
        $parameters['class'] = $filterDefinition['class'] ?? '';
        $parameters['id'] = $filterDefinition['id'] ?? $parameters['name'];
        $parameters['auto'] = $filterDefinition['auto'] ?? false;
        return $parameters;
    }
}
