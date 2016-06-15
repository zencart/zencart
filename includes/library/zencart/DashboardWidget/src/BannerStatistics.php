<?php
/**
 * BannerStatistics Dashboard Widget
 *
 * @package   ZenCart\Admin\DashboardWidget
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license   http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version   GIT: $Id: $
 */

namespace ZenCart\DashboardWidget;

if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

require_once('includes/functions/functions_graphs.php');
include_once('includes/languages/english/banner_statistics.php');

/**
 * Class BannerStatistics
 * @package ZenCart\DashboardWidget
 */
class BannerStatistics extends AbstractWidget
{
  public function prepareContent()
  {
    $tplVars = array();

    // which banner to display
    // @TODO - make this configurable by the user, in the widget's settings
    $banner_id = 6;

    $opts = array('series' => array('lines'=> array('show'=>'true'),
                                    'points'=> array('show'=>'true'),
                                   ),
                  'yaxis' => array('tickDecimals' => 0),
                  'colors' => array('blue', 'red'),
                  );
    $stats = zen_get_banner_data_monthly($banner_id, date('Y'));
    $tplVars['graphTitle'] = sprintf(TEXT_BANNERS_MONTHLY_STATISTICS, $banner->fields['banners_title'], date('Y'));
    $tplVars['graphDatasets'] = array(array('label'=>TEXT_BANNERS_BANNER_VIEWS, 'data'=>$stats[0]), array('label'=>TEXT_BANNERS_BANNER_CLICKS, 'data'=>$stats[1]));
    $tplVars['graphTicks'] = $stats[3];
    $tplVars['graphOptions'] = $opts;
    return $tplVars;
  }
}
