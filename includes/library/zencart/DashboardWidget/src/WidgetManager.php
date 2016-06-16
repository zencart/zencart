<?php
/**
 * Dashboard Widget Manager
 *
 * @package   ZenCart\Admin\DashboardWidget
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license   http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version   GIT: $Id: $
 */

namespace ZenCart\DashboardWidget;

if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

use base;

/**
 * Class WidgetManager
 * @package ZenCart\DashboardWidget
 */
final class WidgetManager
{
  public static function getWidgetsForUser($user)
  {
    global $db;
    $widgets = array();
    $sql = "SELECT * FROM " . TABLE_DASHBOARD_WIDGETS_TO_USERS . " as tdwtu
            LEFT JOIN " . TABLE_DASHBOARD_WIDGETS_DESCRIPTION . " as tdwd ON tdwd.widget_key = tdwtu.widget_key
            WHERE tdwtu.admin_id = :adminId:
            ORDER BY widget_column, widget_row";
    $sql = $db->bindVars($sql, ':adminId:', $user, 'integer');
    $result = $db->execute($sql);
    while (!$result->EOF)
    {
      $widgets[$result->fields['widget_key']] = $result->fields;
      $result->moveNext();
    }
    return $widgets;
  }

  public static function getWidgetsForProfile($user)
  {
    global $db;
    $widgets = array();
    if (zen_is_superuser($user))
    {
      $sql = "SELECT * FROM " . TABLE_DASHBOARD_WIDGETS . " as tdw
                 LEFT JOIN " . TABLE_DASHBOARD_WIDGETS_DESCRIPTION . " as tdwd ON tdwd.widget_key = tdw.widget_key";
      $result = $db->execute($sql);
      while (!$result->EOF)
      {
        $widgets[$result->fields['widget_key']] = $result->fields;
        $result->moveNext();
      }
    } else
    {
      $sql = "SELECT admin_profile FROM " . TABLE_ADMIN . " WHERE admin_id = :adminId:";
      $sql = $db->bindVars($sql, ':adminId:', $user, 'integer');
      $result = $db->execute($sql);
      $profileId = $result->fields['admin_profile'];
      $sql = "   SELECT * FROM " . TABLE_ADMIN_PAGES_TO_PROFILES . " as tdwtp
                 LEFT JOIN " .TABLE_DASHBOARD_WIDGETS . " as tdw ON tdw.widget_key = REPLACE(page_key, '_dashboardwidgets_', '')
                 LEFT JOIN " . TABLE_DASHBOARD_WIDGETS_DESCRIPTION . " as tdwd ON tdwd.widget_key = tdw.widget_key
                 WHERE tdwtp.profile_id = :profileId: AND page_key LIKE '_dashboardwidgets_%'";
      $sql = $db->bindVars($sql, ':profileId:', $profileId, 'integer');
      $result = $db->execute($sql);
      while (!$result->EOF)
      {
        $widgets[$result->fields['widget_key']] = $result->fields;
        $result->moveNext();
      }
    }
    return $widgets;
  }

  public static function setWidgetTitle($name) {
    if (defined($name)) $name = constant($name);
    return $name;
  }

  public static function getWidgetInfoForUser($user)
  {
    global $db;
    $sql = "SELECT * FROM " . TABLE_DASHBOARD_WIDGETS_TO_USERS . " as tdwtu
            LEFT JOIN " . TABLE_DASHBOARD_WIDGETS_DESCRIPTION . " as tdwd ON tdwd.widget_key = tdwtu.widget_key
            WHERE tdwtu.admin_id = :adminId:
            ORDER BY widget_column, widget_row";
    $sql = $db->bindVars($sql, ':adminId:', $user, 'integer');
    $result = $db->execute($sql);
    $widgets = array();
    while (!$result->EOF)
    {
      $widgets[$result->fields['widget_column']][$result->fields['widget_row']] = $result->fields;
      $result->moveNext();
    }
    return $widgets;
  }

  /**
   * @param  array $widgetList
   * @return array
   * @todo   Is this necessary or valid with the new autoloading?
   */
  public static function loadWidgetClasses(array $widgetInfoList)
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

        $widgetClass = new $className($widget['widget_key'], $widget);
        $widgetList[$widget['widget_key']] = $widgetClass;
      }
    }

    return $widgetList;
  }

  public static function prepareTemplateVariables($widgetList)
  {
    $tplVars = array();

    foreach ($widgetList as $widgetkey => $widget) {
      $tplVars[$widgetkey] = $widget->prepareContent();
      $tplVars[$widgetkey]['templateFile'] = $widget->getTemplateFile();
      $tplVars[$widgetkey]['widgetTitle']  = $widget->getWidgetTitle();
      $tplVars[$widgetkey]['widgetBaseId'] = $widget->getWidgetBaseId();
      $widget->updatewidgetInfo($widgetList[$widgetkey]->widgetInfo);
    }

    return $tplVars;
  }

  public static function applyPositionSettings($items, $user)
  {
    global $db;
    $widgetList = self::transformPositions($items);
    foreach ($widgetList as $key => $detail)
    {
      $sql = "UPDATE " . TABLE_DASHBOARD_WIDGETS_TO_USERS . " SET widget_column = :column:, widget_row = :row:
              WHERE admin_id = :adminId: AND widget_key = :key:";
      $sql = $db->bindVars($sql, ':column:', $detail['col'], 'integer');
      $sql = $db->bindVars($sql, ':row:', $detail['row'], 'integer');
      $sql = $db->bindVars($sql, ':key:', $key, 'string');
      $sql = $db->bindVars($sql, ':adminId:', $user, 'integer');
      $db->execute($sql);
    }
  }

  public static function removeWidget($item, $user)
  {
    global $db;
    $sql = "DELETE FROM ". TABLE_DASHBOARD_WIDGETS_TO_USERS . " WHERE widget_key = :key: AND admin_id = :user:";
    $sql = $db->bindVars($sql, ':key:', $item, 'string');
    $sql = $db->bindVars($sql, ':user:', $user, 'integer');
    $db->execute($sql);
  }

  public static function transformPositions($items)
  {
    $columns = explode('|', $items);
    {
      $colC = 0;
      foreach ($columns as $rowString)
      {
        if ($rowString != '')
        {
          $rows = explode(',', $rowString);
          $rowC = 0;
          foreach ($rows as $row)
          {
            if ($row != '')
            {
              //$row = strtoupper(str_replace('-', '_', $row));
              $widgetEnum[$row] = array('col'=>$colC, 'row'=>$rowC);
            }
            $rowC ++;
          }
        }
        $colC ++;
      }
    }
    return $widgetEnum;
  }

  public static function setWidgetRefresh($widgetRefresh, $item, $user)
  {
    global $db;
    $sql = "UPDATE ". TABLE_DASHBOARD_WIDGETS_TO_USERS . " SET widget_refresh = :refresh: WHERE widget_key = :key: AND admin_id = :user:";
    $sql = $db->bindVars($sql, ':key:', $item, 'string');
    $sql = $db->bindVars($sql, ':user:', $user, 'integer');
    $sql = $db->bindVars($sql, ':refresh:', $widgetRefresh, 'integer');
    $db->execute($sql);

  }

  public static function getWidgetTimerSelect($id)
  {
    global $db;
    $optionList = array(
      array('id' => 0,   'text' => TEXT_TIMER_SELECT_NONE),
      array('id' => 60,  'text' => TEXT_TIMER_SELECT_1MIN),
      array('id' => 300, 'text' => TEXT_TIMER_SELECT_5MIN),
      array('id' => 600, 'text' => TEXT_TIMER_SELECT_10MIN),
      array('id' => 900, 'text' => TEXT_TIMER_SELECT_15MIN),
    );
    return $optionList;
  }

  public static function getWidgetRefresh($item, $user)
  {
    global $db;
    $sql = "SELECT * FROM " . TABLE_DASHBOARD_WIDGETS_TO_USERS . " WHERE admin_id = :user: and widget_key = :key:";
    $sql = $db->bindVars($sql, ':key:', $item, 'string');
    $sql = $db->bindVars($sql, ':user:', $user, 'integer');
    $result = $db->execute($sql);
    return $result->fields['widget_refresh'];
  }

  public static function getInstallableWidgets($adminId)
  {
    $groups = self::getWidgetGroups();
    $installableWidgets = self::getInstallableWidgetsList($_SESSION['admin_id']);
    foreach ($installableWidgets as &$w) {
      $w['widget_name'] = self::setWidgetTitle($w['widget_name']);
    }
    return $installableWidgets;
  }

  public static function getWidgetGroups()
  {
    global $db;
    $groups = array();
    $sql = "SELECT * FROM " . TABLE_DASHBOARD_WIDGETS_GROUPS;
    $result = $db->execute($sql);
    while (!$result->EOF)
    {
      $groups[$result->fields['widget_group']] = array('name'=>$result->fields['widget_group_name'], 'count'=>0);
      $result->moveNext();
    }
    return $groups;
  }

  public static function getInstallableWidgetsList($user)
  {
    $profileWidgets = self::getWidgetsForProfile($user);
    $installedWidgets = self::getWidgetsForUser($user);
    //print_r($profileWidgets);
    //print_r($installedWidgets);
    $installableWidgets = array_diff_key($profileWidgets, $installedWidgets);
    return $installableWidgets;
  }

  public static function addWidgetForUser($widget, $user)
  {
    global $db;
    $sql = "SELECT * FROM " . TABLE_DASHBOARD_WIDGETS . " WHERE widget_key = :widgetKey:";
    $sql = $db->bindVars($sql, ':widgetKey:', $widget, 'string');
    $widgetDetail = $db->execute($sql);
    $widgetIcon =  $widgetDetail->fields['widget_icon'];
    $widgetTheme =  $widgetDetail->fields['widget_theme'];

    $sql = "SELECT MAX(widget_row) as max FROM " . TABLE_DASHBOARD_WIDGETS_TO_USERS;
    $result = $db->execute($sql);
    $max = (int)$result->fields['max'];
    $max++;
    $sql = "INSERT INTO " . TABLE_DASHBOARD_WIDGETS_TO_USERS . "
            (widget_key, admin_id, widget_row, widget_column, widget_icon, widget_theme) VALUES (:widgetId:, :adminId:, $max, 0, :widgetIcon:, :widgetHeaderColor:) ";
    $sql = $db->bindVars($sql, ':widgetId:', $widget, 'string');
    $sql = $db->bindVars($sql, ':adminId:', $user, 'integer');
    $sql = $db->bindVars($sql, ':widgetIcon:', $widgetIcon, 'string');
    $sql = $db->bindVars($sql, ':widgetHeaderColor:', $widgetTheme, 'string');
    $db->execute($sql);
  }
}
