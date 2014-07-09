<?php
/**
 * zcDashboardWidgetBannerStatistics Class.
 *
 * @package classes
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson  Fri Aug 17 17:42:37 2012 +0100 New in v1.5.1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
require_once('includes/functions/functions_graphs.php');
include_once('includes/languages/english/banner_statistics.php');

/**
 * zcDashboardWidgetBannerStatistics Class
 *
 * @package classes
 */
class zcDashboardWidgetBannerStatistics extends zcDashboardWidgetBase
{
  public function prepareContent()
  {
    $tplVars = array();

    // which banner to display
    // @TODO - make this configurable by the user, in the widget's settings
    $banner_id = 9;

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