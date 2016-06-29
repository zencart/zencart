<?php
/**
 * Class AbstractAdminController
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  Modified in v1.6.0 $
 */
namespace ZenCart\Controllers;

use ZenCart\Request\Request as Request;
use ZenCart\AdminUser\AdminUser as User;

/**
 * Class AbstractAdminController
 * @package ZenCart\Controllers
 */
abstract class AbstractAdminController extends \base
{
    /**
     * @var array
     */
    protected $tplVars;
    /**
     * @var
     */
    protected $controllerCommand;
    /**
     * @var
     */
    protected $controllerAction;
    /**
     * @var bool
     */
    protected $useView = true;
    /**
     * @var string
     */
    protected $templateLayout = 'default';

    /**
     * AbstractAdminController constructor.
     * @param Request $request
     * @param $db
     * @param User $user
     */
    public function __construct(Request $request, $db, User $user)
    {
        $this->request = $request;
        $this->currentUser = $user;
        $this->dbConn = $db;
        $this->controllerCommand = $this->request->readGet('cmd');
        $this->tplVars = array();
        $this->tplVars = array('jscriptVars' => ['securityToken' => $request->getSession()->get('securityToken')]);
        $this->response = array(
            'data' => null
        );
        $this->prepareDefaultCss();
        $this->prepareCommonTplVars();
        $this->buildMainMenu();
        $this->preCheck();
    }

    /**
     *
     */
    protected function prepareCommonTplVars()
    {
        $this->tplVars['cmd'] = $this->request->readGet('cmd');
        $this->tplVars['hide_languages'] = $GLOBALS['hide_languages'];
        $this->tplVars['languages'] = $GLOBALS['languages'];
        $this->tplVars['languages_array'] = $GLOBALS['languages_array'];
        $this->tplVars['languages_selected'] = $GLOBALS['languages_selected'];
        $this->tplVars['user'] = $this->currentUser->getCurrentUser();
        $this->tplVars['messageStack'] = $GLOBALS['messageStack'];
        $this->tplVars['notifications'] = $this->currentUser->getNotifications()->getNotificationList();
    }


    protected function buildMainMenu()
    {
        $this->tplVars['menuTitles'] = zen_get_menu_titles();
        $this->tplVars['adminMenuForUser'] = zen_get_admin_menu_for_user();
    }
    /**
     *
     */
    protected function prepareDefaultCSS()
    {
        if ($this->useView) {
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
                'id' => 'mainCSS'
            );
        }
        $this->tplVars ['cssList'] = $cssList;
    }

    /**
     *
     */
    public function invoke()
    {
        $this->controllerAction = 'main';
        $tmp = $this->request->get('action', $this->request->get('action', 'main', 'post'), 'get');
        if ($tmp = preg_replace('/[^a-zA-Z0-9_-]/', '', $tmp)) {
            $this->controllerAction = $tmp;
        }
        $this->controllerAction .= 'Execute';
        $this->controllerAction = (method_exists($this, $this->controllerAction)) ? $this->controllerAction : 'mainExecute';
        $this->{$this->controllerAction}();
        $this->doOutput();
    }

    /**
     *
     */
    protected function doOutput()
    {
        if (!$this->useView) {
            $this->doNonViewOutput();
        } else {
            $this->doViewOutput();
        }
    }

    /**
     *
     */
    protected function doViewOutput()
    {
        if (isset($this->response['redirect'])) {
            $this->notify('NOTIFIER_ADMIN_BASE_DO_VIEW_OUTPUT_REDIRECT_BEFORE');
            zen_redirect($this->response['redirect']);
        }
        $useTemplate = $this->getMainTemplate();
        $this->tplVars['mainTemplate'] = $useTemplate;
        $tplVars = $this->tplVars;
        require_once('includes/template/layouts/'. $this->templateLayout . '.php');
    }

    /**
     * @return null|string
     */
    protected function getMainTemplate()
    {
        if (isset($this->mainTemplate)) {
            return ('includes/template/templates/' . $this->mainTemplate);
        }
        $tryTemplate = 'tpl' . ucfirst($this->controllerCommand) . '.php';
        if (file_exists('includes/template/templates/' . $tryTemplate)) {
            return ('includes/template/templates/' . $tryTemplate);
        }

        return null;
    }

    /**
     *
     */
    protected function doNonViewOutput()
    {
        echo json_encode($this->response);
    }

    /**
     * @param $template
     * @param $tplVars
     * @return string
     */
    protected function loadTemplateAsString($template, $tplVars)
    {
        ob_start();
        require_once($template);
        $result = ob_get_clean();
        ob_flush();

        return $result;
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

    /**
     * @param $templateName
     */
    public function setMainTemplate($templateName)
    {
        $this->mainTemplate = $templateName;
    }

    /**
     *
     */
    protected function preCheck()
    {
    }
}
