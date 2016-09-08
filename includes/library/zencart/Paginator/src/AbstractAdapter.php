<?php
/**
 * Class AbstractAdapter
 *
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
namespace ZenCart\Paginator;

/**
 * Class AbstractAdapter
 * @package ZenCart\Paginator
 */
abstract class AbstractAdapter extends \base
{
    /**
     * Results
     *
     * @var array
     */
    protected $results = array();

    /**
     * constructor
     *
     * @param array $data
     * @param array $params
     */
    public function __construct(array $data, array $params = array())
    {
        $this->notify('NOTIFY_PAGINATOR_ADAPTER_CONSTRUCT_START', $data, $params);
        $params['currentPage'] = (isset($params['currentPage'])) ? $params['currentPage'] : 1;
        $params['itemsPerPage'] = (isset($params['itemsPerPage'])) ? $params['itemsPerPage'] : 10;
        $params['currentItem'] = ($params['currentPage'] - 1) * $params['itemsPerPage'] + 1;
        $this->notify('NOTIFY_PAGINATOR_ADAPTER_CONSTRUCT_END', $data, $params);
        $this->process($data, $params);
    }

    /**
     * process method
     *
     * @return array
     */
    public function process($data, $params)
    {
        $results = array();
        $this->notify('NOTIFY_PAGINATOR_ADAPTER_PROCESS_START', $data, $params);
        $results['currentItem'] = $params['currentItem'];
        $results['itemsPerPage'] = $params['itemsPerPage'];
        $results['totalItemCount'] = $this->getTotalItemCount($data);
        $results['resultList'] = $this->getResultList($data, $params);
        $results['totalPages'] = ceil($results['totalItemCount'] / $params['itemsPerPage']);
        $this->results = $results;
        $this->notify('NOTIFY_PAGINATOR_ADAPTER_PROCESS_END');
    }

    /**
     * @return array
     */
    public function getResults()
    {
        $this->notify('NOTIFY_PAGINATOR_ADAPTER_GETRESULTS_START');
        return $this->results;
    }
}
