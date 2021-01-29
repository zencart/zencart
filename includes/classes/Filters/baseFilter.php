<?php declare(strict_types=1);
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */

namespace Zencart\Filters;

class baseFilter
{
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
