<?php
/**
 * Interface AdapterInterface
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
namespace ZenCart\Paginator;

/**
 * Interface AdapterInterface
 * @package ZenCart\Paginator
 */
interface AdapterInterface
{

    /**
     * @param $data
     * @param array $params
     * @return mixed
     */
    public function getResultList($data, array $params);

    /**
     * @param $data
     * @return mixed
     */
    public function getTotalItemCount($data);
}
