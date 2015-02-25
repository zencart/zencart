<?php
/**
 * Class QueryFactory
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
namespace ZenCart\Platform\Paginator\adapters;

use ZenCart\Platform\Paginator\AdapterInterface;
use ZenCart\Platform\Paginator\AbstractAdapter;

/**
 * Class QueryFactory
 * @package ZenCart\Platform\Paginator\adapters
 */
class QueryFactory extends AbstractAdapter implements AdapterInterface
{
    /**
     * getResultList method
     *
     * @return array|mixed
     */
    public function getResultList($data, $params)
    {
        $limit = $params['currentItem'] - 1 . ',' . $params['itemsPerPage'];
        $results = $data['dbConn']->execute($data['sqlQueries']['main'], $limit);
        $resultList = array();
        foreach ($results as $result) {
            $resultList[] = $result;
        }
        return $resultList;
    }

    /**
     * getTotalItemCount method
     *
     * @return integer
     */
    public function getTotalItemCount($data)
    {
        $result = $data['dbConn']->execute($data['sqlQueries']['count']);
        return $result->fields['total'];
    }
} 
