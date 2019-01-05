<?php
/**
 * Dashboard Widget Manager
 *
 * @package   ZenCart\Admin\DashboardWidget
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license   http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version   GIT: $Id: $
 */

namespace ZenCart\DashboardWidget;

use App\Model\ModelFactory;
use base;
use ZenCart\AdminUser\AdminUser;
use ZenCart\ConfigSettings\ConfigSettingsFactory;

/**
 * Class WidgetManager
 * @package ZenCart\DashboardWidget
 */
class WidgetManager
{

    public function __construct(
        ModelFactory $modelFactory, AdminUser $adminUser, ConfigSettingsFactory $configSettingsFactory
    ) {
        $this->adminUser = $adminUser->getModel();
        $this->modelFactory = $modelFactory;
        $this->configSettingsFactory = $configSettingsFactory;
    }

    public function getWidgetInfoForUser()
    {
        $model = $this->modelFactory->make('DashboardWidgetsToUsers');
        $widgets = $model->getWidgetInfoForUser($this->adminUser->admin_id)->toArray();
        $widgetinfoList = [];
        foreach ($widgets as $widget) {
            $widget['config_settings'] =  $this->getCombinedWidgetInfo($widget['widget_key']);
            $widget['widget_name'] = $widget['dashboard_widget']['widget_name'];
            $widget['has_settings'] = (count($widget['dashboard_widget_settings']) > 0);
            $widgetinfoList[$widget['widget_column']][$widget['widget_row']] = $widget;
        }
        return $widgetinfoList;
    }

    public function loadWidgetClasses(array $widgetInfoList)
    {
        $widgetList = array();
        foreach ($widgetInfoList as $widgets) {
            foreach ($widgets as $widget) {
                $classNameSpace = __NAMESPACE__ . '\\';
                $className = base::camelize(strtolower(str_replace('-', '_', $widget['widget_key'])), TRUE);
                $classFile = __DIR__ . DIRECTORY_SEPARATOR . $className . '.php';
                $className = $classNameSpace . $className;

                if (!class_exists($className, true) && is_readable($classFile)) {
                    require_once($classFile);
                }
                if (!class_exists($className)) continue;

                $widgetClass = new $className($widget['widget_key'], $widget);
                $widgetList[$widget['widget_key']] = $widgetClass;
            }
        }
        return $widgetList;
    }

    public function prepareTemplateVariables(array $widgetList)
    {
        $tplVars = array();
        foreach ($widgetList as $widgetkey => $widget) {
            $tplVars[$widgetkey] = $widget->prepareContent();
            $tplVars[$widgetkey]['templateFile'] = $widget->getTemplateFile();
            $tplVars[$widgetkey]['widgetTitle'] = $widget->getWidgetTitle();
            $tplVars[$widgetkey]['widgetBaseId'] = $widget->getWidgetBaseId();
            $tplVars[$widgetkey]['widgetInfo'] = $widget->getWidgetInfo();
            $widget->updatewidgetInfo($widgetList[$widgetkey]->widgetInfo);
        }
        return $tplVars;
    }

    public function applyPositionSettings(array $items)
    {
        //@todo transaction
        $model = $this->modelFactory->make('DashboardWidgetsToUsers');
        foreach ($items as $detail) {
            $model->where('admin_id', '=', $this->adminUser->admin_id)->where('widget_key', '=', $detail['id'])
                  ->update(
                      ['widget_column' => $detail['x'],
                       'widget_row'    => $detail['y'],
                       'widget_width'  => $detail['width'],
                       'widget_height' => $detail['height'],
                      ]);
        }

    }

    public function removeWidget($item)
    {
        $model = $this->modelFactory->make('DashboardWidgetsToUsers');
        $model->where('widget_key', '=', $item)->where('admin_id', '=', $this->adminUser->admin_id)->delete();
    }


    public function getWidgetsForProfile()
    {
        global $db;
        $widgetList = array();
        if ($this->adminUser->isSuperUser()) {
            $dashboardWidgetsModel = $this->modelFactory->make('DashboardWidgets');
            $widgets = $dashboardWidgetsModel->all()->toArray();
        }
        if (!$this->adminUser->isSuperUser()) {
            $profileId = $this->adminUser->admin_profile;
            // @todo convert to eloquent
            $sql = "   SELECT * FROM " . TABLE_ADMIN_PAGES_TO_PROFILES . " as tdwtp
                 LEFT JOIN " .TABLE_DASHBOARD_WIDGETS . " as tdw ON tdw.widget_key = REPLACE(page_key, '_dashboardwidgets_', '')
                 LEFT JOIN " . TABLE_DASHBOARD_WIDGETS_DESCRIPTION . " as tdwd ON tdwd.widget_key = tdw.widget_key
                 WHERE tdwtp.profile_id = :profileId: AND page_key LIKE '_dashboardwidgets_%'";
            $sql = $db->bindVars($sql, ':profileId:', $profileId, 'integer');
            $results = $db->execute($sql);
            foreach ($results as $result) {
                $widgets[$result['widget_key']] = $result;
            }
        }
        foreach ($widgets as $widget) {
            $widgetList[$widget['widget_key']] = $widget;
        }

        return $widgetList;
    }


    public function getWidgetsForUser()
    {
        $model = $this->modelFactory->make('DashboardWidgetsToUsers');
        $widgets = $model->getWidgetInfoForUser($this->adminUser->admin_id)->toArray();
        $widgetList = array();
        foreach ($widgets as $widget) {
            $widget['widget_name'] = $widget['dashboard_widget']['widget_name'];
            $widgetList[$widget['widget_key']] = $widget;
        }
        return $widgetList;
    }


    public function getWidgetTitle($name)
    {
        if (defined($name)) $name = constant($name);
        return $name;
    }

    public function getSettingsTitle(string $widgetName, string $settingsName)
    {
        if (defined($widgetName . '_' . $settingsName)) {
            $name = constant($widgetName . '_' . $settingsName);
            return $name;
        }
        if (defined($settingsName)) {
            $name = constant($settingsName);
            return $name;
        }
        return 'UNDEFINED';
    }


    public function getWidgetDescription($name)
    {
        if (defined($name . "_DESCRIPTION")) {
            $desc = constant($name . "_DESCRIPTION");
        } else {
            $desc = "";
        }
        return $desc;
    }


    public function getInstallableWidgets()
    {
        $installableWidgets = $this->getInstallableWidgetsList();
        foreach ($installableWidgets as &$w) {
            $w['widget_description'] = $this->getWidgetDescription($w['widget_name']);
            $w['widget_name'] = $this->getWidgetTitle($w['widget_name']);
        }
        return $installableWidgets;
    }

    public function getInstallableWidgetsList()
    {
        $profileWidgets = $this->getWidgetsForProfile();
        $installedWidgets = $this->getWidgetsForUser();
        $installableWidgets = array_diff_key($profileWidgets, $installedWidgets);
        return $installableWidgets;
    }

    public function getWidgetGroups()
    {
        $dashboardWidgetsGroups = $this->modelFactory->make('DashboardWidgetsGroups');
        $groups = $dashboardWidgetsGroups->all()->toArray();
        return $groups;
    }

    public function addWidgetForUser($widgetKey)
    {
        $dashboardWidgets = $this->modelFactory->make('DashboardWidgets');
        $widgetDetail = $dashboardWidgets->where('widget_key', '=', $widgetKey)->first()->toArray();
        $widgetIcon = $widgetDetail['widget_icon'];
        $widgetTheme = $widgetDetail['widget_theme'];
        $dashboardWidgetsToUser = $this->modelFactory->make('DashboardWidgetsToUsers');
        $max = $dashboardWidgetsToUser->max('widget_row') + 1;
        $dashboardWidgetsToUser->create(
            ['widget_key'    => $widgetKey, 'admin_id' => $this->adminUser->admin_id, 'widget_row' => $max,
             'widget_column' => 0, 'widget_icon' => $widgetIcon, 'widget_theme' => $widgetTheme]);
    }

    public function getWidgetInfoForEdit($widgetKey)
    {
        $dashboardWidgets = $this->modelFactory->make('DashboardWidgets');
        $widgetInfo = $dashboardWidgets->where('widget_key', '=', $widgetKey)->first()->toArray();
        $widgetSettings = $this->getCombinedWidgetInfo($widgetKey);
        $widget['info'] = $widgetInfo;
        $widget['settings'] = $widgetSettings;
        return $widget;
    }

    public function updateWidgetSettings($request)
    {
        $widgetKey = $request->readPost('widget_key');
        $widget = $this->getCombinedWidgetInfo($widgetKey);
        $updateList = [];
        foreach ($widget as $setting) {
            $configValue = $this->configSettingsFactory->make($setting['setting_type'])
                                                       ->getValueFromRequest($request, $setting);
            if (!isset($configValue)) {
                continue;
            }
            $updateList[] = ['widget_key'    => $widgetKey, 'setting_key' => $setting['setting_key'],
                             'admin_id'      => $this->adminUser->admin_id,
                             'setting_value' => $configValue];
        }
        $this->commitUpdatedWidgetSettings($updateList);
    }

    protected function mapToNewKey(array $input, string $key)
    {
        $output = [];
        foreach ($input as $element) {
            $output[$element[$key]] = $element;
        }
        return $output;
    }

    protected function getCombinedWidgetInfo($widgetKey)
    {
        $model = $this->modelFactory->make('DashboardWidgets');
        $widget = $model->with('dashboardWidgetSettings')->where('widget_key', '=', $widgetKey)->first()->toArray();
        $widget['title'] = $this->getWidgetTitle($widget['widget_name']);
        $settingKeys = array_column($widget['dashboard_widget_settings'], 'setting_key');
        $dwt = $this->mapToNewKey($widget['dashboard_widget_settings'], 'setting_key');
        $model = $this->modelFactory->make('DashboardWidgetsSettings');
        $settings = $model->whereIn('setting_key', $settingKeys)->get()->toArray();
        $settingsList = [];
        foreach ($settings as $setting) {
            $settingClass = $this->configSettingsFactory->make($setting['setting_type']);
//            $setting['class'] = $settingClass;
            $setting = $settingClass->transformSettingsFromDefinition($setting);
            $setting['title'] = $this->getSettingsTitle($widget['widget_name'], $setting['setting_name']);
            $settingsList[$setting['setting_key']] = $setting;

        }
        $model = $this->modelFactory->make('DashboardWidgetsSettingsToUser');
        $userSettings = $model->where('admin_id', '=', $this->adminUser->admin_id)->where(
            'widget_key', '=',
            $widgetKey)->get()->toArray();
        $userSettings = $this->mapToNewKey($userSettings, 'setting_key');

        $settingsList = array_replace_recursive($userSettings, $dwt, $settingsList);
        unset($widget['dashboard_widget_settings']);
        return $settingsList;
    }

    protected function commitUpdatedWidgetSettings(array $updateList)
    {
        //@ todo db transaction
        $model = $this->modelFactory->make('DashboardWidgetsSettingsToUser');
        foreach ($updateList as $update) {
            $model->updateOrCreate(
                ['setting_key' => $update['setting_key'], 'widget_key' => $update['widget_key'],
                 'admin_id'    => $update['admin_id']], ['setting_value' => $update['setting_value']]);
        }
    }
}
