<?php
/**
 * BannerStatistics Dashboard Widget
 *
 * @package   ZenCart\Admin\DashboardWidget
 * @copyright Copyright 2003-2016 Zen Cart Development Team
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

        $settings = $this->widgetInfo['config_settings'];
        $bannerId = isset($settings['banner-id']['setting_value']) ? $settings['banner-id']['setting_value'] :
            $settings['banner-id']['initial_value'];
        $bannerDateRange = isset($settings['banner-date-range']['setting_value']) ? $settings['banner-date-range']['setting_value'] :
            $settings['banner-date-range']['initial_value'];
        $bannerShowlines = isset($settings['banner-show-lines']['setting_value']) ? $settings['banner-show-lines']['setting_value'] : $settings['banner-show-lines']['initial_value'];
        $bannerShowlines = ($bannerShowlines == 'on') ? true : false;

        $opts = array('series' => array('lines'  => array('show' => $bannerShowlines),
                                        'points' => array('show' => true),
        ),
                      'yaxis'  => array('tickDecimals' => 0),
                      'colors' => array('blue', 'red'),
        );
        switch ($bannerDateRange) {
            case 'yearly':
                $stats = zen_get_banner_data_yearly($bannerId);
                $tplVars['graphTitle'] = sprintf(
                    TEXT_BANNERS_YEARLY_STATISTICS, '(' . $bannerId . ')', date('Y'));
                break;
            case 'monthly':
                $stats = zen_get_banner_data_monthly($bannerId, date('Y'));
                $tplVars['graphTitle'] = sprintf(
                    TEXT_BANNERS_MONTHLY_STATISTICS, '(' . $bannerId . ')', date('Y'));
                break;
            case 'daily':
                $stats = zen_get_banner_data_daily($bannerId, date('Y'), date('m'));
                $tplVars['graphTitle'] = sprintf(
                    TEXT_BANNERS_DAILY_STATISTICS, '(' . $bannerId . ')', date('Y'), date('m'));
                break;
            case 'recent':
                $stats = zen_get_banner_data_recent($bannerId, 14);
                $tplVars['graphTitle'] = sprintf(TEXT_BANNERS_RECENT_STATISTICS, '(' . $bannerId . ')', 14);
                break;
        }
        $tplVars['graphDatasets'] = array(array('label' => TEXT_BANNERS_BANNER_VIEWS, 'data' => $stats[0]),
                                          array('label' => TEXT_BANNERS_BANNER_CLICKS, 'data' => $stats[1]));
        $tplVars['graphTicks'] = $stats[3];
        $tplVars['graphOptions'] = $opts;
        return $tplVars;
    }
}
