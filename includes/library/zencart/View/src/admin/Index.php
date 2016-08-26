<?php
/**
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version  $Id: New in v1.6.0 $
 */
namespace ZenCart\View\admin;

/**
 * Class Index
 * @package ZenCart\View\admin
 */
class Index extends \ZenCart\View\AdminView
{
    /**
     *
     */
    public function prepareDefaultCSS()
    {
        parent::prepareDefaultCSS();
        $cssList = $this->tplVarManager->get('cssList');
        $cssList [] = array(
            'href' => 'includes/template/css/index.css',
            'id' => 'indexCSS'
        );
        $this->tplVarManager->set('cssList', $cssList);
    }
}
