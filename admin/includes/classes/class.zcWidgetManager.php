<?php
/**
 * zcWidgetManager Class.
 *
 * @package classes
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson  Fri Aug 17 17:42:37 2012 +0100 New in v1.5.1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * zcWidgetManager Class
 *
 * @package classes
 */
class zcWidgetManager extends base
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

  public static function getWidgetInfoForUser($user)
  {
    global $db;
    $sql = "SELECT * FROM " . TABLE_DASHBOARD_WIDGETS_TO_USERS . " as tdwtu
            LEFT JOIN " . TABLE_DASHBOARD_WIDGETS_DESCRIPTION . " as tdwd ON tdwd.widget_key = tdwtu.widget_key
            WHERE tdwtu.admin_id = :adminId:
            ORDER BY widget_column, widget_row";
    $sql = $db->bindVars($sql, ':adminId:', $user, 'integer');
    $result = $db->execute($sql);
    while (!$result->EOF)
    {
      $widgets[$result->fields['widget_column']][$result->fields['widget_row']] = $result->fields;
      $result->moveNext();
    }
    return $widgets;
  }
  public static function loadWidgetClasses($widgetList)
  {
    $widgetClassList = array();
    if (count($widgetList) > 0 )
    {
      foreach ($widgetList as $widgets)
      {
        foreach ($widgets as $widget)
        {
          $className = 'zcDashboardWidget' . self::camelize(strtolower(str_replace('-', '_', $widget['widget_key'])), TRUE);
          $fileName = 'class.' . $className . '.php';
          if (file_exists(DIR_FS_ADMIN . DIR_WS_CLASSES . 'dashboardWidgets/' . $fileName))
          {
            require(DIR_FS_ADMIN . DIR_WS_CLASSES . 'dashboardWidgets/' . $fileName);
            $widgetClass = new $className($widget['widget_key'], $widget);
            $widgetClassList[$widget['widget_key']] = $widgetClass;
          }
        }
      }
    }
    return $widgetClassList;
  }
  public static function prepareTemplateVariables($widgetList)
  {
    $tplVars = array();
    foreach ($widgetList as $widgetkey => $widget)
    {
      $tplVars[$widgetkey] = $widget->prepareContent();
      $template = $widget->getTemplateFile();
      $tplVars[$widgetkey]['templateFile'] = $template;
      $tplVars[$widgetkey]['widgetTitle'] = $widget->getWidgetTitle();
      $tplVars[$widgetkey]['widgetBaseId'] = $widget->getWidgetBaseId();
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
    $optionList = array(array('id'=>0, 'text'=>TEXT_TIMER_SELECT_NONE),array('id'=>60, 'text'=>TEXT_TIMER_SELECT_1MIN),array('id'=>300, 'text'=>TEXT_TIMER_SELECT_5MIN), array('id'=>600, 'text'=>TEXT_TIMER_SELECT_10MIN), array('id'=>900, 'text'=>TEXT_TIMER_SELECT_15MIN));
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
    return $installableWidgets;
  }
  public static function getWidgetGroups()
  {
    global $db;
    $sql = "SELECT * FROM " . TABLE_DASHBOARD_WIDGETS_GROUPS;
    $result = $db->execute($sql);
    while (!$result->EOF)
    {
      $result->moveNext();
      $groups[$result->fields['widget-group']] = array('name'=>$result->fields['widget_group_name'], 'count'=>0);
    }
    return $groups;
  }
  public static function getInstallableWidgetsList($user)
  {
    $profileWidgets = self::getWidgetsForProfile($user);
    $installedWidgets = self::getWidgetsForUser($user);
    $installableWidgets = array_diff_assoc($profileWidgets, $installedWidgets);
    return $installableWidgets;
  }
  public static function addWidgetForUser($widget, $user)
  {
    global $db;
    $sql = "SELECT MAX(widget_row) as max FROM " . TABLE_DASHBOARD_WIDGETS_TO_USERS;
    $result = $db->execute($sql);
    $max = (int)$result->fields['max'];
    $max++;
    $sql = "INSERT INTO " . TABLE_DASHBOARD_WIDGETS_TO_USERS . "
            (widget_key, admin_id, widget_row, widget_column) VALUES (:widgetId:, :adminId:, $max, 0) ";
    $sql = $db->bindVars($sql, ':widgetId:', $widget, 'string');
    $sql = $db->bindVars($sql, ':adminId:', $user, 'integer');
    $db->execute($sql);
  }
}
