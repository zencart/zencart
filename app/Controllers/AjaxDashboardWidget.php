<?php
/**
 * Class AjaxDashboardWidget
 *
 * @copyright Copyright 2003-2017 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson  Fri Aug 17 17:42:37 2012 +0100 New in v1.5.1 $
 */
namespace App\Controllers;

use ZenCart\AdminUser\AdminUser;
use ZenCart\DashboardWidget\WidgetManager;
use ZenCart\Request\Request;

/**
 * Class AjaxDashboardWidget
 * @package App\Controllers
 */
class AjaxDashboardWidget extends AbstractAjaxController
{

    public function __construct(Request $request, WidgetManager $widgetManager)
    {
        parent::__construct($request);
        $this->widgetManager = $widgetManager;
    }

    /**
     *
     */
    public function updateWidgetPositionsExecute()
    {
        if ($this->request->has('items', 'post')) {
            $items = json_decode($this->request->readPost('items'), true);
            $this->widgetManager->applyPositionSettings($items);
        }
    }

    /**
     *
     */
    public function removeWidgetExecute()
    {
        if ($this->request->has('item', 'post')) {
            $this->widgetManager->removeWidget($this->request->readPost('item'));
        }
    }


    /**
     * @param bool $addUpdateDiv
     */
    public function rebuildWidgetExecute($addUpdateDiv = FALSE)
    {
        if (!$this->request->has('id', 'post')) {
            return;
        }

        $key = str_replace('widget-edit-dismiss-', '', $this->request->readPost('id'));
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
    public function getInstallableWidgetsExecute()
    {
        $widgets = $this->widgetManager->getInstallableWidgets();
        $this->tplVars['widgets'] = $widgets;
        $this->tplVars['flagHasWidgets'] = (count($widgets) > 0) ? TRUE : FALSE;
        $html = $this->loadTemplateAsString(DIR_FS_ADMIN . DIR_WS_INCLUDES . 'template/partials/tplWidgetInstallableList.php', $this->tplVars);
        $this->response = array('html' => $html);
    }

    /**
     * @todo
     */
    public function addWidgetExecute()
    {
        $id = str_replace('add-widget-', '', $this->request->readPost('id'));
        $this->widgetManager->addWidgetForUser($id);
        $widgetInfoList = $this->widgetManager->getWidgetInfoForUser();
        $widgetList = $this->widgetManager->loadWidgetClasses($widgetInfoList);
        $tplVars = $this->widgetManager->prepareTemplateVariables($widgetList);
        $tplVars['widgetInfoList'] = $widgetInfoList;
        $tplVars['widgetList'] = $this->widgetManager->loadWidgetClasses($widgetInfoList);
        $tplVars['widgets'] = $this->widgetManager->prepareTemplateVariables($tplVars['widgetList']);
        $this->response = array();
    }


    public function getWidgetSettingsFieldsExecute()
    {
        $widget = $this->widgetManager->getWidgetInfoForEdit($this->request->readPost('widget'));
        $this->tplVars['widget'] = $widget;
        $html = $this->loadTemplateAsString(DIR_FS_ADMIN . DIR_WS_INCLUDES . 'template/partials/tplWidgetEditForm.php', $this->tplVars);
        $this->response = array('html' => $html);
    }

    public function submitWidgetEditExecute()
    {
        $result = $this->widgetManager->updateWidgetSettings($this->request);
        $this->response = array('errors' => false);

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
            $classDir = DIR_APP_LIBRARY . URL_DASHBOARDWIDGETS;
            require_once($classDir . $className . '.php');
        }
        return new $classNameSpace($id);
    }
}
