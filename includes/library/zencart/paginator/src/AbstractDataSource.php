<?php
/**
 * Class AbstractDataSource
 *
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
namespace ZenCart\Paginator;
/**
 * Class AbstractDataSource
 * @package ZenCart\Paginator
 */
abstract class AbstractDataSource
{
    /**
     * datasource results
     *
     * @var array
     */
    protected $dsResults = array();
    /**
     * incoming data
     *
     * @var array
     */
    protected $data;
    /**
     * parameters
     *
     * @var array
     */
    protected $params;

    /**
     * init method
     *
     * @param array $data
     * @param array $params
     */
    public function init(array $data, array $params)
    {
        $this->data = $data;
        $this->params = $params;
    }

    /**
     * process method
     *
     */
    public function process()
    {
        $this->params['itemCount'] = $this->dsResults['itemCount'] = $this->getTotalItemCount();
        $this->dsResults['resultList'] = $this->getResultList();
        $this->dsResults['totalPages'] = ceil($this->params['itemCount'] / $this->params['itemsPerPage']);
    }

    /**
     * getter dsResults
     *
     * @return array
     */
    public function getDsResults()
    {
        return $this->dsResults;
    }
}
