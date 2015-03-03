<?php
/**
 * Interface AdapterInterface
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
namespace ZenCart\Platform\Paginator;
/**
 * Interface AdapterInterface
 * @package ZenCart\Platform\Paginator
 */
interface AdapterInterface
{
    /**
     * getResultList method
     * @return array
     */
    public function getResultList($data, $params);

    /**
     * getTotalItemCount method
     *
     * @return integer
     */
    public function getTotalItemCount($data);
}
