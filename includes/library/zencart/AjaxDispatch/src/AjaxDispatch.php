<?php
/**
 * Class AjaxDispatch
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson  Fri Aug 17 17:42:37 2012 +0100 New in v1.5.1 $
 */
namespace ZenCart\AjaxDispatch;

/**
 * Class AjaxDispatch
 * @package ZenCart\AjaxDispatch
 */
class AjaxDispatch extends \base
{
    /**
     * @param $action
     */
    public static function run($action, $request)
    {
        $className = 'Ajax' . self::camelize($action, TRUE);
        $namespaceClassName = 'App\\Controllers\\' . $className;
        $fileName =  DIR_FS_CATALOG . URL_CONTROLLERS . $className . '.php';
        $headerResponse = array("Status: 403 Forbidden", TRUE, 403);
        $jsonResponse = array('error' => TRUE, 'errorType' => "MISSING_DISPATCHER_FILE");
        $exitResponse = 1;
        if (file_exists($fileName)) {
            $headerResponse = array("Status: 403 Forbidden", TRUE, 403);
            $jsonResponse = array('error' => TRUE, 'errorType' => "MISSING_DISPATCHER_CLASS");
            $exitResponse = 1;
            require_once($fileName);
            if (class_exists($namespaceClassName)) {
                $action = new $namespaceClassName($request);
                $action->dispatch();
                $headerResponse = null;
                $exitResponse = 0;
                $jsonResponse = $action->getResponse();
            }
        }
        header($headerResponse[0], $headerResponse[1], $headerResponse[2]);
        echo json_encode($jsonResponse);
        exit($exitResponse);
    }
}
