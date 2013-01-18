<?php
/**
 * zcActionAjaxBase Class.
 *
 * @package classes
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson  Fri Aug 17 17:42:37 2012 +0100 New in v1.5.1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * zcActionAjaxBase Class
 *
 * @package classes
 */
class zcActionAjaxBase extends base 
{
  public static $templateVariables;
  public static $response;
  
  public function __construct()
  {
    $this->templateVariables = array();
    $this->response = array('data'=>NULL);
    if (isset($_POST['form']))
    {
      $formBreak =  explode('&', $_POST['form']);
      foreach ($formBreak as $piece)
      {
        $piecePost = explode('=', $piece);
        $_POST[$piecePost[0]] = $piecePost[1];
      }
    }
  }
  public function dispatch()
  {
    $method = (isset($_GET['method'])) ? $_GET['method'] : 'default';
    $method = $method . 'Execute';
    if (method_exists($this, $method))
    {
      $this->$method();      
    } else 
    {
      header("Status: 403 Forbidden", TRUE, 403);
      echo json_encode(array('error'=>TRUE, 'errorType'=>"MISSING_DISPATCHER_METHOD"));
      exit(1);
    }
  }
  public function getResponse()
  {
    return $this->response;
  }
  public function loadTemplateAsString($template, $tplVars)
  {
    ob_start();
    require_once($template);
    $result = ob_get_clean();
    ob_flush();
    return $result;
  }
}