<?php
/**
 * Class Standard
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
namespace ZenCart\Platform\Paginator\scrollers;

use ZenCart\Platform\Paginator\ScrollerInterface;
use ZenCart\Platform\Paginator\AbstractScroller;

/**
 * Class Standard
 * @package ZenCart\Platform\Paginator\scrollers
 */
class Standard extends AbstractScroller
{
    protected $scrollerTemplate = 'tpl_paginator_standard.php';

    /**
     * process method
     *
     * @return mixed|void
     */
    protected function process(array $data, array $params)
    {
        $results = array();
        $linkParams = $params['scrollerLinkParams'];
        $maxPageLinks = $params['maxPageLinks'];
        $pageVarName = $params['pagingVarName'];
        $cmd = $params['cmd'];

        if (!isset ($params['disableZenGetAllGetParams'])) {
            $linkParams = $this->getRequestParams(array('exclude' => array($pageVarName, 'action'),
                                                        'linkParams' => $linkParams));
        }
        $pageNumber = intval($params['currentPage'] / $maxPageLinks);
        if ($params['currentPage'] % $maxPageLinks) {
            $pageNumber++;
        }
        $linkList = $this->buildLinkList($pageNumber, $linkParams, $data, $params);
        $results['linkList'] = $linkList;
        $results['hasItems'] = (count($linkList) > 0);
        $results['nextPage'] = $params['currentPage'] + 1;
        $results['totalItems'] = $data['totalItemCount'];
        $results['prevPage'] = $params['currentPage'] - 1;
        $results['fromItem'] = $data['currentItem'];
        $results['toItem'] = $data['currentItem'] + count($data['resultList']) - 1;
        $results['flagHasPrevious'] = $params['currentPage'] > 1 ? true : false;
        $results['flagHasNext'] = $params['currentPage'] < $data['totalPages'] ? true : false;

        $buildLinkParams = array('cmd' => $cmd,
                                 'linkParams' => $linkParams . $pageVarName . '=' . ($params['currentPage'] - 1));
        $results['previousLink'] = $this->buildLink($buildLinkParams);
        $buildLinkParams = array('cmd' => $cmd,
                                 'linkParams' => $linkParams . $pageVarName . '=' . ($params['currentPage'] + 1));
        $results['nextLink'] = $this->buildLink($buildLinkParams);
        $this->results = $results;

    }

    /**
     * buildItemList method
     *
     * @param $pageNumber
     * @param $linkParams
     * @return array
     */
    protected function buildLinkList($pageNumber, $linkParams, $data, $params)
    {
        $pageCount = $data['totalPages'];
        $maxPageLinks = $params['maxPageLinks'];
        $pageVarName = $params['pagingVarName'];
        $cmd = $params['cmd'];
        $currentPage = $params['currentPage'];

        $linkList = array();
        if ($pageCount <= 1) {
            return $linkList;
        }
        for ($i = 1 + (($pageNumber - 1) * $maxPageLinks); ($i <= ($pageNumber * $maxPageLinks)) && ($i <= $pageCount); $i++) {
            $buildLinkParams = array('cmd' => $cmd, 'linkParams' => $linkParams . $pageVarName . '=' . $i);
            $linkList[] = array(
                'itemNumber' => $i,
                'itemLink' => $this->buildLink($buildLinkParams),
                'isCurrent' => ($i == $currentPage) ? true : false
            );
        }
        return $linkList;
    }
}
