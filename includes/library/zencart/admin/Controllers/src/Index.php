<?php
/**
 * Class Index
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id:$
 */
namespace ZenCart\Admin\Controllers;

use ZenCart\Admin\Services\IndexRoute;

/**
 * Class Index
 * @package ZenCart\Admin\Controllers
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
    public function initDefinitions()
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
}
