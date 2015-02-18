<?php
/**
 * Interface DataSourceInterface
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
namespace ZenCart\Platform\Paginator;
/**
 * Interface DataSourceInterface
 * @package ZenCart\Platform\Paginator
 */
interface DataSourceInterface
{
    /**
     * getResultList method
     * @return mixed
     */
    public function getResultList();

    /**
     * getTotalItemCount method
     *
     * @return mixed
     */
    public function getTotalItemCount();
} 
