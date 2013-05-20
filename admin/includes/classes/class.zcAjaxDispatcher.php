<?php
/**
 * zcAjaxDispatcher Class.
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
 * zcAjaxDispatcher Class
 *
 * @package classes
 */
class zcAjaxDispatcher extends base
{
  public static function run($action)
  {
    $className = 'zcAction' . self::camelize($action, TRUE);
    $fileName = DIR_FS_ADMIN . DIR_WS_CLASSES . 'actions/ajax/class.' . $className . '.php';
    if (file_exists($fileName))
    {
      require(DIR_FS_ADMIN . DIR_WS_CLASSES . 'actions/class.zcActionAjaxBase.php');
      require ($fileName);
      if (class_exists($className))
      {
        $action = new $className();
        $result = $action->dispatch();
        $response = $action->getResponse();
        echo json_encode($response);
      } else 
      {
        header("Status: 403 Forbidden", TRUE, 403);
        echo json_encode(array('error'=>TRUE, 'errorType'=>"MISSING_DISPATCHER_CLASS"));
        exit(1);
      }
    } else 
    {
      header("Status: 403 Forbidden", TRUE, 403);
      echo json_encode(array('error'=>TRUE, 'errorType'=>"MISSING_DISPATCHER_FILE"));
      exit(1);
    }
  }
}