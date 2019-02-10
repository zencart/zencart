<?php
/**
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version  $Id: New in v1.6.0 $
 */
namespace ZenCart\View;

/**
 * Class AdminView
 * @package ZenCart\View
 */
class AdminView extends AbstractView
{
    /**
     *
     */
    protected function initView()
    {
        $this->prepareDefaultCSS();
        $this->buildMainMenu();
    }

    /**
     *
     */
    public function prepareDefaultCSS()
    {
        $cssList [] = array(
            'href' => 'includes/template/css/bootstrap.min.css',
            'id' => 'bootstrapCSS'
        );
        $cssList [] = array(
            'href' => 'includes/template/AdminLTE2/dist/css/AdminLTE.css',
            'id' => 'adminlteCSS'
        );
        $cssList [] = array(
            'href' => 'includes/template/css/stylesheet.css',
            'id' => 'stylesheetCSS'
        );
        $this->tplVarManager->set('cssList', $cssList);
    }


    /**
     *
     */
    protected function buildMainMenu()
    {
        $this->tplVarManager->set('menuTitles', zen_get_menu_titles());
        $this->tplVarManager->set('adminMenuForUser', zen_get_admin_menu_for_user());
    }

}
