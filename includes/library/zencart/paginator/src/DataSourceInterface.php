<?php
/**
 * Interface DataSourceInterface
 *
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
namespace ZenCart\Paginator;
use ZenCart\Paginator\AbstractDataSource;
/**
 * Interface DataSourceInterface
 * @package ZenCart\Paginator
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
