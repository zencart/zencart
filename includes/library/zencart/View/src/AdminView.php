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
     * @param $response
     */
    public function doOutput($response)
    {
        if (isset($response['header_response_code'])) {
            http_response_code($response['header_response_code']);
        }
        if (!$this->useView($response)) {
            $this->doNonViewOutput($response);
        } else {
            $this->doViewOutput($response);
        }
    }

    /**
     * @return bool
     */
    protected function useView($response)
    {
        if (!isset($response)) {
            return true;
        }
        if (isset($response['redirect'])) {
            return true;
        }
        return false;
    }

    /**
     * @param $response
     */
    protected function doViewOutput($response)
    {
        if (isset($response['redirect'])) {
            zen_redirect($response['redirect']);
        }
        $useTemplate = $this->getMainTemplate();
        $this->tplVarManager->set('mainTemplate', $useTemplate);
        $tplVars = $this->tplVarManager->getTplVars();
        require_once('includes/template/layouts/' . $this->templateLayout . '.php');
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
