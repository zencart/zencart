<?php
/**
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */
namespace App\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;
use App\Model\DashboardWidgets;

/**
 * Class Admin
 * @package ZenCart\Model
 */
class DashboardWidgetsToUsers extends Eloquent
{
    protected $table = TABLE_DASHBOARD_WIDGETS_TO_USERS;
    protected $primaryKey = 'widget_key';
    public $incrementing = false;
    public $timestamps = false;
    protected $guarded = [];

    public function DashboardWidget()
    {
        return $this->hasOne('App\Model\DashboardWidgets', 'widget_key', 'widget_key');
    }

    public function DashboardWidgetSettings()
    {
        return $this->hasMany('App\Model\DashboardWidgetsSettingsToWidget', 'widget_key', 'widget_key');
    }

    public function getWidgetInfoForUser($adminId)
    {
        $result = $this->with('DashboardWidget')->with('DashboardWidgetSettings')->where('admin_id', '=', $adminId);
        return $result->get();
    }
}
