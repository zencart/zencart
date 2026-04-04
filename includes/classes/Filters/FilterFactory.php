<?php declare(strict_types=1);
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 */

namespace Zencart\Filters;

/**
 * @since ZC v1.5.8
 */
class FilterFactory
{
    /**
     * @since ZC v1.5.8
     */
    public function make(array $filterDefinition) : RequestFilter
    {
        $filterName = ucfirst($filterDefinition['type']) . 'Filter';
        $className = __NAMESPACE__ . '\\'.  $filterName;
        $filter = new $className;
        return $filter;
    }
}
