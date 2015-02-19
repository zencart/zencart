<?php
/**
 * Class Paginator
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
namespace ZenCart\Platform\Paginator;
/**
 * Class Paginator
 * @package ZenCart\Platform\Paginator
 */
class Paginator
{
    /**
     * dataSource
     *
     * @var mixed
     */
    protected $dataSource;
    /**
     * scroller
     *
     * @var mixed
     */
    protected $scroller;
    /**
     * params
     *
     * @var array
     */
    protected $params = array();
    /**
     * request
     *
     * @var
     */
    protected $request;
    /**
     * data
     *
     * @var array
     */
    protected $data;

    /**
     * constructor
     *
     * @param $request
     * @param null $data
     * @param null $params
     * @param null $dataSource
     * @param null $scroller
     */
    public function __construct($request, $data = null, $params = null, $dataSource = null, $scroller = null)
    {
        $this->request = $request;
        $this->setParams($params);
        $this->setData($data);
        $this->setDataSource($dataSource);
        $this->setScroller($scroller);
    }

    /**
     * setData method
     *
     * @param $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * setParams method
     *
     * @param $params
     */
    public function setParams($params)
    {
        $this->params = $params;
        $this->params['cmd'] = $this->request->get('main_page');
        $this->params['pagingVarName'] = isset($this->params['pagingVarName']) ? $this->params['pagingVarName'] : 'page';
        $this->params['pagingText'] = isset($this->params['pagingText']) ? $this->params['pagingText'] : TEXT_DISPLAY_NUMBER_OF_ENTRIES;
        $this->params['pagingVarSrc'] = isset($this->params['pagingVarSrc']) ? $this->params['pagingVarSrc'] : 'get';
        $this->params['itemsPerPage'] = isset($this->params['itemsPerPage']) ? $this->params['itemsPerPage'] : 15;
        $this->params['currentPage'] = $this->request->get($this->params['pagingVarName'], 1, $this->params['pagingVarSrc']);
        $this->params['currentItem'] = ($this->params['currentPage'] - 1) * $this->params['itemsPerPage'] + 1;
        $this->params['scrollerTemplate'] = isset($this->params['scrollerTemplate']) ? $this->params['scrollerTemplate'] : 'tpl_paginator_standard.php';
    }

    /**
     * setDataSource method
     *
     * @param $dataSource
     */
    public function setDataSource($dataSource)
    {
        if (isset($dataSource) && !is_object($dataSource)) {
            $dataSource = HelperFactory::makeDataSource($dataSource, $this->data, $this->params);
        }
        if (isset($dataSource)) {
            $this->dataSource = $dataSource;
        }
    }

    /**
     * setScroller method
     *
     * @param $scroller
     */
    public function setScroller($scroller)
    {
        if (isset($scroller) && !is_object($scroller)) {
            $scroller = HelperFactory::makeScroller($scroller, $this->data, $this->params);
        }
        if (isset($scroller)) {
            $this->scroller = $scroller;
        }
    }

    /**
     * init method
     */
    public function init()
    {
        $this->validate();
        $this->dataSource->init($this->data, $this->params);
    }

    /**
     * process method
     *
     */
    public function process()
    {
        $this->dataSource->process();
        $dataSourceResults = $this->dataSource->getDsResults();
        $this->mergeInResults($dataSourceResults);
        $this->scroller->init($this->params);
        $this->scroller->process();
        $scrollerResults = $this->scroller->getScrollerResults();
        $this->params['scroller'] = $scrollerResults;

    }

    /**
     * mergeInResults method
     *
     * @param $results
     */
    public function mergeInResults($results)
    {
        $this->params = array_merge($this->params, $results);
    }

    /**
     * validate method
     *
     */
    public function validate()
    {
        $this->validateDataSource();
        $this->validateScroller();
    }

    /**
     * validateDataSource method
     *
     * @throws \InvalidArgumentException
     */
    protected function validateDataSource()
    {
        if (!isset($this->dataSource)) {
            throw new \InvalidArgumentException();
        }
    }

    /**
     * validateScroller method
     *
     * @throws \InvalidArgumentException
     */
    protected function validateScroller()
    {
        if (!isset($this->scroller)) {
            throw new \InvalidArgumentException();
        }
    }

    /**
     * getParams method
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * getItems method
     *
     * @return mixed
     */
    public function getItems()
    {
        return $this->params['resultList'];
    }

    /**
     * showPaginator method
     *
     * @return bool
     */
    public function showPaginator()
    {
        return ($this->params['totalPages'] > 1);
    }
}
