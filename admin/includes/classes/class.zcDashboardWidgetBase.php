<?php
/**
 * zcDashboardWidgetBase Class.
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
 * zcDashboardWidgetBase Class
 *
 * @package classes
 */
class zcDashboardWidgetBase extends base
{
  public function __construct($widgetInfo)
  {
    $this->widgetInfo = $widgetInfo;
    $this->tplVars = array();
  }
  public function prepareContent() 
  {
    return array();
  }
  public function getTemplateFile()
  {
    $tplFile = DIR_FS_ADMIN . DIR_WS_INCLUDES . 'template/dashboardWidgets/tpl' . self::camelize(strtolower($this->widgetInfo['widget_key']), TRUE) . '.php';
    if (!file_exists($tplFile))
    {
      $tplFile = DIR_FS_ADMIN . DIR_WS_INCLUDES . 'template/dashboardWidgets/tplDefault.php';
    }
    return $tplFile;
  }
  public function getWidgetTitle()
  {
    return $this->widgetInfo['widget_name'];
  }
  public function getWidgetBaseId()
  {
    return strtolower(str_replace('_', '-', $this->widgetInfo['widget_key']));
  }
  /**
   * Default form validation
   * 
   * default form only contains settings for refresh, however does need to validate the securityToken
   * @return boolean
   */
  public function validateEditForm()
  {
    return TRUE;
  }
  public function getFormValidationErrors()
  {
    return $this->formValidationErrors;
  }
  public function executeEditForm()
  {
    if (isset($_POST['widget-refresh']))
    {
      $item = $_POST['id'];
      zcWidgetManager::setWidgetRefresh($_POST['widget-refresh'], $item, $_SESSION['admin_id']);
    }
  }
  public function setFormValidationErrors($errors)
  {
    $this->formValidationErrors = $errors;
  }
  public function getWidgetInfo()
  {
    return $this->widgetInfo;
  }
  public function getFormDefaults($item, $handler)
  {
      $result = zcWidgetManager::getWidgetRefresh($item, $_SESSION['admin_id']);
      $handler->templateVariables['widget-refresh'] = $result;
  }
}