<?php declare(strict_types=1);
/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt 2021 Jan 29 New in v1.5.8-alpha $
 */

namespace Zencart\Filters;

use Illuminate\Database\Eloquent\Builder;
use Zencart\Request\Request;

/**
 * @since ZC v1.5.8
 */
interface RequestFilter
{
    /**
     * @since ZC v1.5.8
     */
    public function make(array $filterDefinition) : void;
    /**
     * @since ZC v1.5.8
     */
    public function processRequest(Request $request, Builder $query);
    /**
     * @since ZC v1.5.8
     */
    public function output() : string;
}
