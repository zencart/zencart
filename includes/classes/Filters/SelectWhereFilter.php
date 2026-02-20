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
    public function processRequest(Request $request, $query)
    {
        $this->default = $request->input($this->filterDefinition['selectName'], '*');
        if ((string)$this->default == '*') {
            return $query;
        }

        if (is_array($query)) {
            return array_values(array_filter($query, function ($row) {
                $field = $this->filterDefinition['field'];
                return (string)($row[$field] ?? '') === (string)$this->default;
            }));
        }

        if (is_object($query) && method_exists($query, 'where')) {
            return $query->where($this->filterDefinition['field'], $this->default);
        }

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
