<?php
/**
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version  $Id: New in v1.6.0 $
 */
namespace ZenCart\View;

/**
 * Class StoreView
 * @package ZenCart\View
 */
class StoreView extends AbstractView
{

    /**
     * @var \messageStack
     */
    protected $messageStack;
    /**
     * @var \BreadCrumb
     */
    protected $breadCrumb;

    /**
     * @param \messageStack $messageStack
     */
    public function setMessageStack(\messageStack $messageStack)
    {
        $this->messageStack = $messageStack;

    }

    /**
     * @param \BreadCrumb $breadCrumb
     */
    public function setBreadCrumb(\BreadCrumb $breadCrumb)
    {
        $this->breadCrumb = $breadCrumb;
    }

    /**
     * @return \messageStack
     */
    public function getMessageStack()
    {
        return $this->messageStack;
    }

    /**
     * @return \BreadCrumb
     */
    public function getBreadCrumb()
    {
        return $this->breadCrumb;
    }
}
