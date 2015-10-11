<?php
/**
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version  $Id: New in v1.6.0 $
 */

namespace ZenCart\View;

/**
 * Class View
 * @package ZenCart\View
 */
class View extends \base
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
     * @var TplVarManager
     */
    protected $tplVarManager;

    /**
     * @param TplVarManager $tplVarManager
     * @param \messageStack $messageStack
     * @param \BreadCrumb $breadCrumb
     */
    public function __construct(\ZenCart\View\TplVarManager $tplVarManager, \messageStack $messageStack, \BreadCrumb $breadCrumb)
    {
        $this->messageStack = $messageStack;
        $this->breadCrumb = $breadCrumb;
        $this->tplVarManager = $tplVarManager;
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

    /**
     * @return TplVarManager
     */
    public function getTplVarManager()
    {
        return $this->tplVarManager;
    }
}
