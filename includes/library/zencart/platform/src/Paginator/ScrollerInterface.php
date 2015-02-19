<?php
/**
 * Interface ScrollerInterface
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
namespace ZenCart\Platform\Paginator;
/**
 * Interface ScrollerInterface
 * @package ZenCart\Platform\Paginator
 */
interface ScrollerInterface
{
    /**
     * process method
     *
     * @return mixed
     */
    public function process();
}
