<?php
/**
 * Class Standard
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
namespace ZenCart\Paginator\scrollers;
use ZenCart\Paginator\ScrollerInterface;
use ZenCart\Paginator\AbstractScroller;
/**
 * Class Standard
 * @package ZenCart\Paginator\scrollers
 */
class Standard extends AbstractScroller implements ScrollerInterface
{
    /**
     * process method
     *
     * @return mixed|void
     */
    public function process()
    {
        $linkParams = $this->params['scrollerLinkParams'];
        $maxPageLinks = $this->params['maxPageLinks'];
        $pageVarName = $this->params['pagingVarName'];
        $cmd = $this->params['cmd'];

        if (!isset ($this->params['disableZenGetAllGetParams'])) {
            $linkParams = $this->getCurrentLinkParams(array('exclude' => array($pageVarName, 'action'),
                                                            'linkParams' => $linkParams));
        }
        $pageNumber = intval($this->params['currentPage'] / $maxPageLinks);
        if ($this->params['currentPage'] % $maxPageLinks) {
            $pageNumber++;
        }
        $itemList = $this->buildItemList($pageNumber, $linkParams);
        $this->scrollerResults['linkList'] = $itemList;
        $this->scrollerResults['hasItems'] = (count($itemList) > 0);
        $this->scrollerResults['nextPage'] = $this->params['currentPage'] + 1;
        $this->scrollerResults['prevPage'] = $this->params['currentPage'] - 1;
        $this->scrollerResults['fromItem'] = $this->params['currentItem'];
        $this->scrollerResults['toItem'] = $this->params['currentItem'] + $this->params['itemsPerPage'] - 1;
        $this->scrollerResults['flagHasPrevious'] = $this->params['currentItem'] > 1 ? true : false;
        $this->scrollerResults['flagHasNext'] = $this->params['currentPage'] < $this->params['totalPages'] ? true : false;

        $buildLinkParams = array('cmd' => $cmd,
                                 'linkParams' => $linkParams . $pageVarName . '=' . ($this->params['currentPage'] - 1));
        $this->scrollerResults['previousLink'] = $this->buildLink($buildLinkParams);
        $buildLinkParams = array('cmd' => $cmd,
                                 'linkParams' => $linkParams . $pageVarName . '=' . ($this->params['currentPage'] + 1));
        $this->scrollerResults['nextLink'] = $this->buildLink($buildLinkParams);
    }

    /**
     * buildItemList method
     *
     * @param $pageNumber
     * @param $linkParams
     * @return array
     */
    protected function buildItemList($pageNumber, $linkParams)
    {
        $pageCount = $this->params['totalPages'];
        $maxPageLinks = $this->params['maxPageLinks'];
        $pageVarName = $this->params['pagingVarName'];
        $cmd = $this->params['cmd'];
        $currentPage = $this->params['currentPage'];

        $itemList = array();
        if ($pageCount <= 1) {
            return $itemList;
        }
        for ($i = 1 + (($pageNumber - 1) * $maxPageLinks); ($i <= ($pageNumber * $maxPageLinks)) && ($i <= $pageCount); $i++) {
            $buildLinkParams = array('cmd' => $cmd, 'linkParams' => $linkParams . $pageVarName . '=' . $i);
            $itemList[] = array(
                'itemNumber' => $i,
                'itemLink' => $this->buildLink($buildLinkParams),
                'isCurrent' => ($i == $currentPage) ? true : false
            );
        }
        return $itemList;
    }
}
