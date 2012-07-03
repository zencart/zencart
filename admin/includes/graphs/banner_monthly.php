<?php
/**
 * @package admin
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: banner_monthly.php 18695 2011-05-04 05:24:19Z drbyte $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

  include(DIR_WS_CLASSES . 'phplot.php');

  $year = (($_GET['year']) ? zen_db_input($_GET['year']) : date('Y'));

  $stats = array();
  for ($i=1; $i<13; $i++) {
    $stats[] = array(strftime('%b', mktime(0,0,0,$i)), '0', '0');
  }

  $banner_stats = $db->Execute("select month(banners_history_date) as banner_month, sum(banners_shown) as value, sum(banners_clicked) as dvalue from " . TABLE_BANNERS_HISTORY . " where banners_id = '" . (int)$banner_id . "' and year(banners_history_date) = '" . $year . "' group by banner_month");
  while (!$banner_stats->EOF) {
    $stats[($banner_stats->fields['banner_month']-1)] = array(strftime('%b', mktime(0,0,0,$banner_stats->fields['banner_month'])), (($banner_stats->fields['value']) ? $banner_stats->fields['value'] : '0'), (($banner_stats->fields['dvalue']) ? $banner_stats->fields['dvalue'] : '0'));
    $banner_stats->MoveNext();
  }

  $graph = new PHPlot(600, 350, 'images/graphs/banner_monthly-' . $banner_id . '.' . $banner_extension);

  $graph->SetFileFormat($banner_extension);
  $graph->SetIsInline(1);
  $graph->SetPrintImage(0);

  $graph->SetSkipBottomTick(1);
  $graph->SetDrawYGrid(1);
  $graph->SetPrecisionY(0);
  $graph->SetPlotType('lines');

  $graph->SetPlotBorderType('left');
  $graph->SetTitleFontSize('4');
  $graph->SetTitle(sprintf(TEXT_BANNERS_MONTHLY_STATISTICS, $banner->fields['banners_title'], $year));

  $graph->SetBackgroundColor('white');

  $graph->SetVertTickPosition('plotleft');
  $graph->SetDataValues($stats);
  $graph->SetDataColors(array('blue','red'),array('blue', 'red'));

  $graph->DrawGraph();

  $graph->PrintImage();
