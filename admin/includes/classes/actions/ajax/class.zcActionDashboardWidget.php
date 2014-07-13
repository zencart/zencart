<?php
/**
 * zcActionDashboardWidget Class.
 *
 * @package classes
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson  Fri Aug 17 17:42:37 2012 +0100 New in v1.5.1 $
 */
use Zencart\DashboardWidgets\zcWidgetManager;
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * zcActionDashboardWidget Class
 *
 * @package classes
 */
class zcActionDashboardWidget extends zcActionAjaxBase
{
  public function updateWidgetPositionsExecute()
  {
    if (isset($_POST['items']))
    {
      zcWidgetManager::applyPositionSettings($_POST['items'], $_SESSION['admin_id']);
    }
  }
  public function removeWidgetExecute()
  {
    if (isset($_POST['item']))
    {
      zcWidgetManager::removeWidget($_POST['item'], $_SESSION['admin_id']);
    }
  }
  public function getWidgetEditExecute()
  {
    if (isset($_POST['id']))
    {
      $this->templateVariables['id'] = $_POST['id'];
      $this->getFormDefaults();
      $html = $this->loadTemplateAsString(DIR_FS_ADMIN . DIR_WS_INCLUDES . 'template/partials/tplWidgetEditForm.php', $this->templateVariables);
      $this->response = array('html'=>$html);
    }
  }
  public function rebuildWidgetExecute($addUpdateDiv = FALSE)
  {
    if (isset($_POST['id']))
    {
      $key = $id = str_replace('widget-edit-dismiss-', '', $_POST['id']);
      $id = self::camelize($id, TRUE);
      $className = 'zcDashboardWidget' . $id;
      $classNameSpace = '\\ZenCart\\DashboardWidgets\\dashboardWidgets\\' . $className;
      $classDir = 'vendor/zencart/dashboardWidgets/src/dashboardWidgets/';
      $fileName = DIR_FS_CATALOG . $classDir . $className . '.php';
      require_once($fileName);
      $widget = new $classNameSpace($key);
      $tplVars['widget'] = $widget->prepareContent();
      $html = "";
      if ($addUpdateDiv)
      {
        $html = '<div class="widget-update-header">' . TEXT_WIDGET_UPDATE_HEADER .  '</div>';
      }
      $template = $widget->getTemplateFile();
      $html .= $this->loadTemplateAsString($template, $tplVars);
      $this->response['html'] = $html;
    }
  }
  public function submitWidgetEditExecute()
  {
    $id = self::camelize($_POST['id'], TRUE);
    $className = 'zcDashboardWidget' . $id;
    $classNameSpace = '\\ZenCart\\DashboardWidgets\\dashboardWidgets\\' . $className;
    $classDir = 'vendor/zencart/dashboardWidgets/src/dashboardWidgets/';
    $fileName = DIR_FS_CATALOG . $classDir . $className . '.php';
    require_once($fileName);
    $widget = new $classNameSpace($_POST['id']);
    $result = $widget->validateEditForm();
    if ($result == FALSE)
    {
      $this->response = array('error'=>TRUE, 'errorType'=>'FORM_VALIDATION', 'errorList'=>$widget->getFormValidationErrors());
    } else
    {
      $widget->executeEditForm();
      $interval = $_POST['widget-refresh'];
      $this->response['timerInterval'] = $interval;
      $this->response['timerKey'] = $_POST['id'];
      $_POST['id'] = 'widget-edit-dismiss-' . $_POST['id'];
      $this->rebuildWidgetExecute(TRUE);
    }
  }
  public function getFormDefaults()
  {
    $id = self::camelize($_POST['id'], TRUE);
    $className = 'zcDashboardWidget' . $id;
    $classNameSpace = '\\ZenCart\\DashboardWidgets\\dashboardWidgets\\' . $className;
    $classDir = 'vendor/zencart/dashboardWidgets/src/dashboardWidgets/';
    $fileName = DIR_FS_CATALOG . $classDir . $className . '.php';
    require_once($fileName);
    $widget = new $classNameSpace($_POST['id']);
    $widget->getFormDefaults($_POST['id'], $this);
  }
  public function timerUpdateExecute()
  {
    $_POST['id'] = 'widget-edit-dismiss-' . $_POST['id'];
    $this->rebuildWidgetExecute();
  }
  public function getInstallableWidgetsExecute()
  {
    $widgets = zcWidgetManager::getInstallableWidgets($_SESSION['admin_id']);
    $this->templateVariables['widgets'] = $widgets;
    $this->templateVariables['flagHasWidgets'] = (count($widgets) > 0) ? TRUE : FALSE;
    $html = $this->loadTemplateAsString(DIR_FS_ADMIN . DIR_WS_INCLUDES . 'template/partials/tplWidgetInstallableList.php', $this->templateVariables);
    $this->response = array('html'=>$html);
  }
  public function addWidgetExecute()
  {
    $id = str_replace('add-widget-', '', $_POST['id']);
    zcWidgetManager::addWidgetForUser($id, $_SESSION['admin_id']);
    $widgetInfoList = zcWidgetManager::getWidgetInfoForUser($_SESSION['admin_id'], $_SESSION['languages_id']);
    //$widgetProfileList = $widgetManager->mergeProfileInfoList($widgetProfileList, $widgetInfoList);
    $widgetList = zcWidgetManager::loadWidgetClasses($widgetInfoList);
    $tplVars = zcWidgetManager::prepareTemplateVariables($widgetList);
    $tplVars['widgetInfoList'] = $widgetInfoList;
    $tplVars['widgetList'] = zcWidgetManager::loadWidgetClasses($widgetInfoList);
    $tplVars ['widgets'] = zcWidgetManager::prepareTemplateVariables($tplVars['widgetList']);
    $template = DIR_FS_ADMIN . DIR_WS_INCLUDES . 'template/partials/tplDashboardMainSortables.php';
    $html = $this->loadTemplateAsString($template, $tplVars);
    $this->response = array('html'=>$html);
  }
}