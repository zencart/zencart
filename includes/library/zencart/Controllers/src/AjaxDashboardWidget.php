<?php
/**
 * Class AjaxDashboardWidget
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson  Fri Aug 17 17:42:37 2012 +0100 New in v1.5.1 $
 */
namespace ZenCart\Controllers;
use ZenCart\DashboardWidget\WidgetManager;

/**
 * Class AjaxDashboardWidget
 * @package ZenCart\Controllers
 */
class AjaxDashboardWidget extends AbstractAjaxController
{
    /**
     *
     */
    public function updateWidgetPositionsExecute()
    {
        if (isset($_POST['items'])) {
            WidgetManager::applyPositionSettings($_POST['items'], $_SESSION['admin_id']);
        }
    }

    /**
     *
     */
    public function removeWidgetExecute()
    {
        if (isset($_POST['item'])) {
            WidgetManager::removeWidget($_POST['item'], $_SESSION['admin_id']);
        }
    }

    /**
     *
     */
    public function getWidgetEditExecute()
    {
        if (isset($_POST['id'])) {
            $this->tplVars['id'] = $_POST['id'];
            $this->getFormDefaults();
            $html = $this->loadTemplateAsString(DIR_FS_ADMIN . DIR_WS_INCLUDES . 'template/partials/tplWidgetEditForm.php', $this->tplVars);
            $this->response = array('html' => $html);
        }
    }

    /**
     * @param bool $addUpdateDiv
     */
    public function rebuildWidgetExecute($addUpdateDiv = FALSE)
    {
        if (!isset($_POST['id'])) {
            return;
        }

        $key = str_replace('widget-edit-dismiss-', '', $_POST['id']);
        $widget = $this->loadClass($key);
        $tplVars['widget'] = $widget->prepareContent();
        $html = "";

        if ($addUpdateDiv) {
            $html = '<div class="widget-update-header">' . TEXT_WIDGET_UPDATE_HEADER . '</div>';
        }

        $template = $widget->getTemplateFile();
        $html .= $this->loadTemplateAsString($template, $tplVars);
        $this->response['html'] = $html;
    }

    /**
     *
     */
    public function submitWidgetEditExecute()
    {
        $widget = $this->loadClass($_POST['id']);
        $result = $widget->validateEditForm();

        if ($result == FALSE) {
            $this->response = array(
                'error' => TRUE,
                'errorType' => 'FORM_VALIDATION',
                'errorList' => $widget->getFormValidationErrors()
            );
            return;
        }
        $widget->executeEditForm();
        $interval = $_POST['widget-refresh'];
        $this->response['timerInterval'] = $interval;
        $this->response['timerKey'] = $_POST['id'];
        $_POST['id'] = 'widget-edit-dismiss-' . $_POST['id'];
        $this->rebuildWidgetExecute(TRUE);
    }

    /**
     *
     */
    public function getFormDefaults()
    {
        $widget = $this->loadClass($_POST['id']);
        $widget->getFormDefaults($_POST['id'], $this);
    }

    public function timerUpdateExecute()
    {
        $_POST['id'] = 'widget-edit-dismiss-' . $_POST['id'];
        $this->rebuildWidgetExecute();
    }

    /**
     *
     */
    public function getInstallableWidgetsExecute()
    {
        $widgets = WidgetManager::getInstallableWidgets($_SESSION['admin_id']);
        $this->tplVars['widgets'] = $widgets;
        $this->tplVars['flagHasWidgets'] = (count($widgets) > 0) ? TRUE : FALSE;
        $html = $this->loadTemplateAsString(DIR_FS_ADMIN . DIR_WS_INCLUDES . 'template/partials/tplWidgetInstallableList.php', $this->tplVars);
        $this->response = array('html' => $html);
    }

    /**
     *
     */
    public function addWidgetExecute()
    {
        $id = str_replace('add-widget-', '', $_POST['id']);
        WidgetManager::addWidgetForUser($id, $_SESSION['admin_id']);
        $widgetInfoList = WidgetManager::getWidgetInfoForUser($_SESSION['admin_id'], $_SESSION['languages_id']);
        $widgetList = WidgetManager::loadWidgetClasses($widgetInfoList);
        $tplVars = WidgetManager::prepareTemplateVariables($widgetList);
        $tplVars['widgetInfoList'] = $widgetInfoList;
        $tplVars['widgetList'] = WidgetManager::loadWidgetClasses($widgetInfoList);
        $tplVars ['widgets'] = WidgetManager::prepareTemplateVariables($tplVars['widgetList']);
        $template = DIR_FS_ADMIN . DIR_WS_INCLUDES . 'template/partials/tplDashboardMainSortables.php';
        $html = $this->loadTemplateAsString($template, $tplVars);
        $this->response = array('html' => $html);
    }

    /**
     * @param $id
     * @return mixed
     */
    private function loadClass($id)
    {
        $className = self::camelize($id, TRUE);
        $classNameSpace = 'ZenCart\\DashboardWidget\\' . $className;
        if (!class_exists($classNameSpace, true)) {
            $classDir = DIR_CATALOG_LIBRARY . URL_DASHBOARDWIDGETS;
            require_once($classDir . $className . '.php');
        }
        return new $classNameSpace($id);
    }
}
