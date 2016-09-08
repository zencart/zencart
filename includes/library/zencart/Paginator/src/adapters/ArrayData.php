<?php
/**
 * Class ArrayData
 *
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
namespace ZenCart\Paginator\adapters;

use ZenCart\Paginator\AdapterInterface;
use ZenCart\Paginator\AbstractAdapter;

/**
 * Class ArrayData
 * @package ZenCart\Paginator\adapters
 */
class ArrayData extends AbstractAdapter implements AdapterInterface
{

    /**
     * @param $data
     * @param array $params
     * @return array
     */
    public function getResultList($data, array $params)
    {
        $start = $params['currentItem'] - 1; 
        $len = $params['itemsPerPage']; 
        $resultList = array_slice($data, $start, $len); 
        return $resultList;
    }

    /**
     * @param $data
     * @return mixed
     */
    public function getTotalItemCount($data)
    {
        $totalsize = count($data); 
        return $totalsize; 
    }
} 
