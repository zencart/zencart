<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */


namespace Zencart\Filters;


use Illuminate\Http\Request;

interface RequestFilter
{
    public function make(array $filterDefinition);
    public function processRequest(Request $request, $query);
    public function output();
}
