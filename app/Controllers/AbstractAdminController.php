<?php
/**
 * Class AbstractAdminController
 *
 * @copyright Copyright 2003-2017 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  Modified in v1.6.0 $
 */
namespace App\Controllers;

use ZenCart\Request\Request as Request;
use ZenCart\AdminUser\AdminUser as User;
use ZenCart\View\ViewFactory as View;
use App\Model\ModelFactory;
use Zencart\PluginManager\PluginManager;

/**
 * Class AbstractAdminController
 * @package App\Controllers
 */
abstract class AbstractAdminController extends \base
{
    /**
     * @var array
     */
    protected $tplVars;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var User
     */
    protected $currentUser;

    /**
     * @var
     */
    protected $dbConn;

    /**
     * @var mixed
     */
    protected $response;

    /**
     * @var mixed
     */
    protected $view;

    /**
     * @var string
     */
    protected $mainTemplate = null;

    public function __construct(Request $request, ModelFactory $modelFactory, User $user, View $view,
                                PluginManager $pluginManager)
    {
        $this->request = $request;
        $this->currentUser = $user;
        $this->dbConn = $modelFactory->getConnection();
        $this->modelFactory = $modelFactory;
        $this->pluginManager = $pluginManager;
        //$this->modelLanguages = $modelFactory->factory('languages');
        $this->tplVars = array();
        $this->response = null;
        $c = (new \ReflectionClass($this))->getShortName();
        $this->view = $view->factory($c);
        $this->view->setMainTemplate($this->mainTemplate);
        $this->tplVarManager = $this->view->getTplVarManager();
        $this->prepareCommonTplVars();
        $this->pluginManager->buildPluginListToDb();
    }

    /**
     *
     */
    public function dispatch()
    {
        $controllerAction = 'main';
        $tmp = $this->request->get('action', $this->request->get('action', 'main', 'post'), 'get');
        if ($tmp = preg_replace('/[^a-zA-Z0-9_-]/', '', $tmp)) {
            $controllerAction = $tmp;
        }
        $controllerAction .= 'Execute';
        $controllerAction = (method_exists($this, $controllerAction)) ? $controllerAction : 'mainExecute';
        if (!method_exists($this, $controllerAction)) {
            die ('No Dispatch Method ' . $controllerAction);
        }
        $this->{$controllerAction}();
        return $this->response;
    }


    /**
     * @param $response
     */
    public function handleResponse($response)
    {
        $this->view->getTplVarManager()->addTplVars($this->tplVars);
        $this->view->doOutput($response);
    }
    /**
     *
     */
    protected function prepareCommonTplVars()
    {
        $this->tplVarManager->set('cmd', $this->request->readGet('cmd'));
        $this->tplVarManager->set('hide_languages', $GLOBALS['hide_languages']);
        $this->tplVarManager->set('languages', $GLOBALS['languages']);
        $this->tplVarManager->set('languages_array', $GLOBALS['languages_array']);
        $this->tplVarManager->set('languages_selected', $GLOBALS['languages_selected']);
        $this->tplVarManager->set('user', $this->currentUser->getCurrentUser());
        $this->tplVarManager->set('messageStack', $GLOBALS['messageStack']);
        $this->tplVarManager->set('notifications', $this->currentUser->getNotifications()->getNotificationList());
        $this->tplVarManager->set('jscriptVars', ['securityToken' => $this->request->getSession()->get('securityToken')]);
        $this->tplVarManager->set('csrfToken', $this->request->getSession()->get('securityToken'));
    }
}
