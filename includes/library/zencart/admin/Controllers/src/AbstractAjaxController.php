<?php
/**
 * Class AbstractAjaxController
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  Modified in v1.6.0 $
 */
namespace ZenCart\Admin\Controllers;
/**
 * Class AbstractAjaxController
 * @package ZenCart\Admin\Controllers
 */
class AbstractAjaxController extends \base
{
    /**
     * @var array
     */
    protected $tplVars;
    /**
     * @var array
     */
    protected $response;

    /**
     * @param \ZenCart\Platform\Request $request
     */
    public function __construct(\ZenCart\Platform\Request $request)
    {
        $this->tplVars = array();
        $this->response = array('data' => NULL);
        $this->request = $request;
    }

    /**
     *
     */
    public function dispatch()
    {
        $method = $this->request->readGet('method', 'default');
        $method = $method . 'Execute';
        if (method_exists($this, $method)) {
            $this->$method();
        } else {
            header("Status: 403 Forbidden", TRUE, 403);
            echo json_encode(array('error' => TRUE, 'errorType' => "MISSING_DISPATCHER_METHOD"));
            exit(1);
        }
    }

    /**
     * @return array
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param $template
     * @param $tplVars
     * @return string
     */
    public function loadTemplateAsString($template, $tplVars = array())
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
}
