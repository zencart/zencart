<?php
/**
 * Class SqlQuery
 *
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
namespace ZenCart\Paginator\adapters;

use ZenCart\Paginator\AdapterInterface;
use ZenCart\Paginator\AbstractAdapter;

/**
 * Class SqlQuery
 * @package ZenCart\Paginator\adapters
 */
class SqlQuery extends AbstractAdapter implements AdapterInterface
{

    /**
     * @param $data
     * @param array $params
     * @return array
     */
    public function getResultList($data, array $params)
    {
        $limit = $params['currentItem'] - 1 . ',' . $params['itemsPerPage'];
        $results = $data['dbConn']->execute($data['mainSql'], $limit);
        $resultList = array();
        foreach ($results as $result) {
            $resultList[] = $result;
        }
        return $resultList;
    }

    /**
     * @param $data
     * @return mixed
     */
    public function getTotalItemCount($data)
    {
        $result = $data['dbConn']->execute($data['countSql']);
        return $result->fields['total'];
    }
} 
