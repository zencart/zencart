<?php
/**
 * Class AbstractScroller
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
namespace ZenCart\Platform\Paginator;
/**
 * Class AbstractScroller
 * @package ZenCart\Platform\Paginator
 */
abstract class AbstractScroller extends \base
{
    /**
     * results
     *
     * @var array
     */
    protected $results = array();

    /**
     * constructor
     *
     * @param array $params
     */
    public function __construct(AdapterInterface $adapter, array $params)
    {
        $this->notify('NOTIFY_PAGINATOR_SCROLLER_CONSTRUCT_START', $params);
        $params['pagingVarName'] = isset($params['pagingVarName']) ? $params['pagingVarName'] : 'page';
        $params['currentPage'] = isset($params['currentPage']) ? $params['currentPage'] : 1;
        $params['scrollerLinkParams'] = isset($params['scrollerLinkParams']) ? $params['scrollerLinkParams'] : '';
        $params['maxPageLinks'] = isset($params['maxPageLinks']) ? $params['maxPageLinks'] : 5;
        $this->notify('NOTIFY_PAGINATOR_SCROLLER_BEFORE_PROCESS', $params);
        $this->process($adapter->getResults(), $params);
        $this->results['resultList'] = $adapter->getResults()['resultList'];
        $this->notify('NOTIFY_PAGINATOR_SCROLLER_CONSTRUCT_END');
    }

    /**
     * @param array $data
     * @param array $params
     * @return mixed
     */
    abstract protected function process(array $data, array $params);

    /**
     * buildLink method
     *
     * @param $params
     * @return string
     */
    public function buildLink($params)
    {
        $link = zen_href_link($params['cmd'], $params['linkParams']);
        return $link;
    }

    /**
     * getCurrentLinkParams method
     *
     * @param $params
     * @return mixed|string
     */
    public function getRequestParams($params)
    {
        $linkParams = zen_get_all_get_params($params['exclude'], $params['linkParams']);
        return $linkParams;
    }

    /**
     * @return array
     */
    public function getResults()
    {
        $this->notify('NOTIFY_PAGINATOR_SCROLLER_GETRESULTS_START');
        return $this->results;
    }
}
