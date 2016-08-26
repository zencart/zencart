<?php
/**
 * Class AbstractAdminController
 *
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  Modified in v1.6.0 $
 */
namespace ZenCart\Controllers;

use ZenCart\Request\Request as Request;
use ZenCart\AdminUser\AdminUser as User;
use ZenCart\View\ViewFactory as View;

/**
 * Class AbstractAdminController
 * @package ZenCart\Controllers
 */
abstract class AbstractAdminController
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

    /**
     * AbstractAdminController constructor.
     * @param Request $request
     * @param $db
     * @param User $user
     */
    public function __construct(Request $request, $db, User $user, View $view)
    {
        $this->request = $request;
        $this->currentUser = $user;
        $this->dbConn = $db;
        $this->tplVars = array();
        $this->tplVars = array('jscriptVars' => ['securityToken' => $request->getSession()->get('securityToken')]);
        $this->response = null;
        $c = (new \ReflectionClass($this))->getShortName();
        $this->view = $view->factory($c);
        $this->view->setMainTemplate($this->mainTemplate);
        $this->prepareCommonTplVars();
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
        $this->view->getTplVarManager()->set('cmd', $this->request->readGet('cmd'));
        $this->view->getTplVarManager()->set('hide_languages', $GLOBALS['hide_languages']);
        $this->view->getTplVarManager()->set('languages', $GLOBALS['languages']);
        $this->view->getTplVarManager()->set('languages_array', $GLOBALS['languages_array']);
        $this->view->getTplVarManager()->set('languages_selected', $GLOBALS['languages_selected']);
        $this->view->getTplVarManager()->set('user', $this->currentUser->getCurrentUser());
        $this->view->getTplVarManager()->set('messageStack', $GLOBALS['messageStack']);
        $this->view->getTplVarManager()->set('notifications', $this->currentUser->getNotifications()->getNotificationList());
    }


    /**
     * @param $key
     * @param $value
     */
    public function setTplVar($key, $value)
    {
        $this->tplVars[$key] = $value;
    }

    /**
     * @param $tplVars
     */
    public function setTplVars($tplVars)
    {
        $this->tplVars = $tplVars;
    }

    /**
     * @return array
     */
    public function getTplVars()
    {
        return $this->tplVars;
    }
}
