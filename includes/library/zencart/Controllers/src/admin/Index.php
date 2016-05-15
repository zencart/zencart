<?php
/**
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  New in v1.6.0 $
 */
namespace ZenCart\Controllers;
use ZenCart\Services\IndexRoute;

/**
 * Class Index
 * @package ZenCart\Controllers
 */
class Index extends AbstractController
{
    /**
     * @var bool
     */
    public $useFoundation = true;

    /**
     * @var
     */
    protected $service;

    /**
     * @param $controllerCommand
     * @param $request
     * @param $db
     */
    public function __construct($controllerCommand, $request, $db)
    {
        parent::__construct($controllerCommand, $request, $db);
        $this->service = new IndexRoute($this, $request, $db);
    }

    /**
     *
     */
    public function preCheck()
    {
        $this->tplVars['cssList'] [] = array(
            'href' => 'includes/template/css/index.css',
            'id' => 'indexCSS'
        );
    }

    /**
     *
     */
    public function mainExecute()
    {
        $this->service->displayHomePage();
    }

    /**
     *
     */
    public function setupWizardExecute()
    {
        $result = $this->service->setupWizardExecute();
        if ($result) {
            zen_redirect(zen_href_link(FILENAME_DEFAULT));
        }
    }

    /**
     *
     */
    public function getZonesExecute()
    {
        $this->useView = false;
        $this->response = array('html'=>'');
        if ($this->request->readPost('id'))  {
            $options = zen_get_country_zones((int)$this->request->readPost('id'));
            if (count($options) == 0) {
                array_unshift($options, array('id' => 0, 'text' => TEXT_NONE));
            }
            $html = zen_draw_pull_down_menu('store_zone', $options, -1, 'id="store_zone" tabindex="5"'); // tabindex is here so it gets reinserted when ajax redraws this input field
            $this->response = array('html'=>$html);
        }
    }}
