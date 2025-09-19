<?php declare(strict_types=1);
/**
 * @copyright Copyright 2003-2023 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2022 Oct 16 Modified in v1.5.8a $
 */

namespace Zencart\Filters;

use Illuminate\Database\Eloquent\Builder;
use Zencart\Request\Request;

/**
 * @since ZC v1.5.8
 */
class SelectWhereFilter extends baseFilter implements RequestFilter
{
    private $default;
    protected $filterDefinition = [];
    protected $options = [];
    protected $parameters =[];
    
    /**
     * @since ZC v1.5.8
     */
    public function make(array $filterDefinition) : void
    {
        $this->filterDefinition = $filterDefinition;
        $this->default = $filterDefinition['default'] ?? '';
        $this->options = $this->getOptionsForSelect($filterDefinition);
        $this->parameters = $this->setParameters($filterDefinition);
    }

    /**
     * @since ZC v1.5.8
     */
    public function output() : string
    {
        $select = $this->makeSelect($this->options, $this->default, $this->parameters);
        return $select;
    }

    /**
     * @since ZC v1.5.8
     */
    public function processRequest(Request $request, Builder $query) : Builder
    {
        $this->default = $request->input($this->filterDefinition['selectName'], '*');
        if ((string)$this->default == '*') {
            return $query;
        }
        $query = $query->where($this->filterDefinition['field'], $this->default);
        return $query;
    }

    /**
     * @since ZC v1.5.8
     */
    private function getOptionsForSelect(array $filterDefinition) : array
    {
        return $filterDefinition['options'];
    }

    /**
     * @since ZC v1.5.8
     */
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
