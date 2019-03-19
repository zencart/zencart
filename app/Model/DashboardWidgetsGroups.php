<?php
/**
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */
namespace App\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * Class Admin
 * @package ZenCart\Model
 */
class DashboardWidgetsGroups extends Eloquent
{
    protected $table = TABLE_DASHBOARD_WIDGETS_GROUPS;
    protected $primaryKey = 'widget_group';
    public $incrementing = false;

}
