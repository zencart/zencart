<?php
/**
 * Class Mysqli
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
namespace ZenCart\Platform\Paginator\dataSources;
use ZenCart\Platform\Paginator\DataSourceInterface;
use ZenCart\Platform\Paginator\AbstractDataSource;

/**
 * Class Mysqli
 * @package ZenCart\Platform\Paginator\dataSources
 */
class Mysqli extends AbstractDataSource implements DataSourceInterface
{
    /**
     * getResultList method
     *
     * @return array|mixed
     */
    public function getResultList()
    {
        $limit = $this->params['currentItem'] - 1 . ',' . $this->params['itemsPerPage'];

        $results = $this->data['dbConn']->execute($this->data['sqlQueries']['main'], $limit);
        $resultList = array();
        foreach ($results as $result) {
            $resultList[] = $result;
        }
        return $resultList;
    }

    /**
     * getTotalItemCount method
     *
     * @return mixed
     */
    public function getTotalItemCount()
    {
        $result = $this->data['dbConn']->execute($this->data['sqlQueries']['count']);
        return $result->fields['total'];
    }
} 
