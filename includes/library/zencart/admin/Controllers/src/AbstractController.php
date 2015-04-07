<?php
/**
 * Class AbstractController
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */
namespace ZenCart\Admin\Controllers;
/**
 * Class AbstractController
 * @package ZenCart\Admin\Controllers
 */
abstract class AbstractController extends \base
{
    /**
     * @var array
     */
    protected $templateVariables;
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
     * @var bool
     */
    protected $useFoundation = false;

    /**
     * @param $controllerCommand
     * @param $diContainer
     */
    public function __construct($controllerCommand, $request, $db)
    {
        $this->request = $request;
        $this->dbConn = $db;
        $this->controllerCommand = $controllerCommand;
        $this->templateVariables = array();
        $this->response = array(
            'data' => null
        );
        $this->prepareDefaultCss();
        $this->prepareCommonTemplateVariables();
        $this->preCheck();
        $this->initDefinitions();
    }

    /**
     *
     */
    public function prepareCommonTemplateVariables()
    {
        $this->templateVariables['cmd'] = $this->request->readGet('cmd');
//        $globalRegistry = $this->diContainer->get('globalRegistry');
//        foreach ($globalRegistry as $key => $value) {
//            $this->templateVariables [$key] = $value;
//        }
        $this->templateVariables ['useFoundation'] = $this->useFoundation;
    }

    /**
     *
     */
    public function prepareDefaultCSS()
    {
        if ($this->useView) {
            $cssList [] = array(
                'href' => 'includes/template/css/normalize.css',
                'id' => 'normalizeCSS'
            );
            if ($this->useFoundation) {
                $cssList [] = array(
                    'href' => 'includes/template/css/foundation.min.css',
                    'id' => 'foundationCSS'
                );
            }
            $cssList [] = array(
                'href' => 'includes/template/css/stylesheet.css',
                'id' => 'stylesheetCSS'
            );
            $cssList [] = array(
                'href' => 'includes/template/css/stylesheet_print.css',
                'media' => 'print',
                'id' => 'printCSS'
            );
            if ($this->useFoundation) {
                $cssList [] = array(
                    'href' => 'includes/template/css/zen-foundation-reset.css',
                    'id' => 'zenFoundationResetCSS'
                );
            }
        }
        $this->templateVariables ['cssList'] = $cssList;
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
    public function doOutput()
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
    public function doViewOutput()
    {
        $tplVars = $this->templateVariables;
        require('includes/template/common/tplAdminHtmlHead.php');
        echo "\n" . "</head>";
        echo "\n" . "<body>";
        require_once('includes/template/common/tplHeader.php');
        $useTemplate = $this->getMainTemplate();
        if (isset($useTemplate)) {
            require($useTemplate);
        }
        require('includes/template/common/tplFooter.php');
    }

    /**
     * @return null|string
     */
    public function getMainTemplate()
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
    public function doNonViewOutput()
    {
        echo json_encode($this->response);
    }

    /**
     * @param $template
     * @param $tplVars
     * @return string
     */
    public function loadTemplateAsString($template, $tplVars)
    {
        ob_start();
        require_once($template);
        $result = ob_get_clean();
        ob_flush();
        return $result;
    }

    /**
     *
     */
    public function preCheck()
    {
    }

    /**
     *
     */
    public function initDefinitions()
    {
    }
}
