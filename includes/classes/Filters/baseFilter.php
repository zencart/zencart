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
class baseFilter
{
    /**
     * @since ZC v1.5.8
     */
    protected function makeSelect(array $options, string $default, array $parameters) : string
    {
        $parameters['autoJs'] = ($parameters['auto']) ? 'onChange="this.form.submit();"' : '';
        $parameters['options'] = $options;
        $parameters['default'] = $default;
        $class = isset($parameters['class']) ? ' class="' . $parameters['class'] . '"' : '';
        $parameters['class'] = $class;
//        $view = view('filters.searchWhere', ['tpl' => $parameters]);
//        return $view->render();
        return '';
    }
}
