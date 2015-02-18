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
abstract class AbstractScroller
{
    /**
     * scrollerResults
     *
     * @var array
     */
    protected $scrollerResults = array();
    /**
     * data
     *
     * @var
     */
    protected $data;
    /**
     * params
     *
     * @var
     */
    protected $params;

    /**
     * init method
     *
     * @param array $params
     */
    public function init(array $params)
    {
        $this->params = $params;
        $this->params['scrollerLinkParams'] = isset($this->params['scrollerLinkParams']) ? $this->params['scrollerLinkParams'] : '';
        $this->params['maxPageLinks'] = isset($this->params['maxPageLinks']) ? $this->params['maxPageLinks'] : 5;
    }

    /**
     * getter scrollerResults
     *
     * @return array
     */
    public function getScrollerResults()
    {
        return $this->scrollerResults;
    }

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
    public function getCurrentLinkParams($params)
    {
        $linkParams = zen_get_all_get_params($params['exclude'], $params['linkParams']);
        return $linkParams;
    }
}
