<?php
/**
 * Abstract Dashboard Widget
 *
 * @package   ZenCart\Admin\DashboardWidget
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license   http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version   GIT: $Id: $
 */

namespace ZenCart\DashboardWidget;

use base;

/**
 * Class AbstractWidget
 * @package ZenCart\DashboardWidget
 */
abstract class AbstractWidget
{
    public $widgetInfoChanged = false;
    public $widgetInfo;
    public $widgetKey;

    public function __construct($widgetKey, $widgetInfo = null)
    {
        $this->widgetInfo = $widgetInfo;
        $this->widgetKey = $widgetKey;
    }

    abstract public function prepareContent();

    public function updatewidgetInfo(&$info)
    {
    }

    public function getTemplateFile()
    {
        $tplFile = base::camelize(strtolower($this->widgetKey), false) . 'Content';
        return $tplFile;
    }

    public function getWidgetTitle()
    {
        $name = $this->widgetInfo['widget_name'];
        if (defined($name)) $name = constant($name);
        return $name;
    }

    public function getWidgetBaseId()
    {
        return strtolower(str_replace('_', '-', $this->widgetKey));
    }

    public function getWidgetInfo()
    {
        return $this->widgetInfo;
    }

}
