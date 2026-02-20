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
interface RequestFilter
{
    /**
     * @since ZC v1.5.8
     */
    public function make(array $filterDefinition) : void;
    /**
     * @since ZC v1.5.8
     */
    public function processRequest(Request $request, $query);
    /**
     * @since ZC v1.5.8
     */
    public function output() : string;
}
