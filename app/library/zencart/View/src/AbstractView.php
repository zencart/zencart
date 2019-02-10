<?php
/**
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version  $Id: New in v1.6.0 $
 */
namespace ZenCart\View;

use Jenssegers\Blade\Blade;

/**
 * Class AbstractView
 * @package ZenCart\View
 */
Abstract class AbstractView
{
    /**
     * @var TplVarManager
     */
    protected $tplVarManager;
    /**
     * @var string
     */
    protected $templateLayout = 'default';

    protected $viewRoot = DIR_FS_CATALOG . 'app/resources/views/admin/';

    protected $templateRoot = DIR_FS_ADMIN . 'includes/template/';

    /**
     * AbstractView constructor.
     * @param TplVarManager $tplVarManager
     */
    public function __construct($command, TplVarManager $tplVarManager)
    {
        $this->command = $command;
        $this->tplVarManager = $tplVarManager;
        $this->initView();
    }

    /**
     *
     */
    protected function initView()
    {
    }

    /**
     * @return TplVarManager
     */
    public function getTplVarManager()
    {
        return $this->tplVarManager;
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
     *
     */
    protected function doViewOutput($response)
    {
        if (isset($response['redirect'])) {
            $this->notify('NOTIFIER_ADMIN_BASE_DO_VIEW_OUTPUT_REDIRECT_BEFORE');
            zen_redirect($response['redirect']);
        }
        $useTemplate = $this->getMainTemplate();
        $templateType = $this->getTemplateType($useTemplate);
        $tplVars = $this->tplVarManager->getTplVars();
        if ($templateType === 'legacy') {
            $this->tplVarManager->set('mainTemplate', $this->templateRoot . 'templates/' . $useTemplate);
            $tplVars = $this->tplVarManager->getTplVars();
            require_once($this->templateRoot . 'layouts/' . $this->templateLayout . '.php');
        }
        if ($templateType === 'blade') {
            $blade = new Blade($this->viewRoot, DIR_FS_CATALOG . 'cache');
            echo $blade->make($useTemplate, ['tplVars' => $tplVars]);
        }
    }

    /**
     * @param $response
     */
    protected function doNonViewOutput($response)
    {
        echo json_encode($response);
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
     * @return null|string
     */
    protected function getMainTemplate()
    {

        if (isset($this->mainTemplate)) {
            return $this->mainTemplate;
        }
        $tryTemplate = 'tpl' . ucfirst($this->command) . '.php';
        if (file_exists($this->templateRoot . $tryTemplate)) {
            return $tryTemplate;
        }
        $tryTemplate = lcfirst($this->command) . '.blade.php';
        if (file_exists($this->viewRoot . $tryTemplate)) {
            return lcfirst($this->command);
        }
        return null;
    }

    /**
     * @param $templateName
     */
    public function setMainTemplate($templateName)
    {
        $this->mainTemplate = $templateName;
    }

    protected function getTemplateType($template)
    {
        $bEndsWith = strrpos($template, '.php') == strlen($template)-strlen('.php');
        return $bEndsWith ? 'legacy' : 'blade';
    }
}
