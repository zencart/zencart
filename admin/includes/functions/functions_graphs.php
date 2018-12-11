<?php
/**
 * @package admin
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte Sat Jul 5 15:44:37 2014 -0400 New in v1.5.6 $
 */


  function zen_get_banner_data_recent($banner_id, $days) {
    global $db;
    $set1 = $set2 = $stats = array();

    $result = $db->Execute("select dayofmonth(banners_history_date) as source,
                                       banners_shown as impressions, banners_clicked as clicks
                     from " . TABLE_BANNERS_HISTORY . "
                     where banners_id = '" . (int)$banner_id . "'
                     and to_days(now()) - to_days(banners_history_date) < " . zen_db_input($days) . "
                     order by banners_history_date");

    while (!$result->EOF) {
      $set1[] = array($result->fields['source'], $result->fields['impressions']);
      $set2[] = array($result->fields['source'], $result->fields['clicks']);
      $stats[] = array($result->fields['source'], $result->fields['impressions'], $result->fields['clicks']);
      $result->MoveNext();
    }
    if (sizeof($set1) < 1) $set1 = $set2 = array(array(date('j'), 0));

    return array($set1, $set2, $stats);
  }

  function zen_get_banner_data_yearly($banner_id) {
    global $db;
    $set1 = $set2 = array(array(0, 0));
    $years = array(0=>'');
    $stats = array();
    $result = $db->Execute("select year(banners_history_date) as year,
                                       sum(banners_shown) as impressions, sum(banners_clicked) as clicks
                     from " . TABLE_BANNERS_HISTORY . "
                     where banners_id = '" . (int)$banner_id . "'
                     group by year order by year");

    while (!$result->EOF) {
      $set1[] = array((int)$result->fields['year'], (int)$result->fields['impressions']);
      $set2[] = array((int)$result->fields['year'], (int)$result->fields['clicks']);
      $stats[] = array($result->fields['year'], $result->fields['impressions'], $result->fields['clicks']);
      $years[] = (string)$result->fields['year'];
      $result->MoveNext();
    }

    return array($set1, $set2, $stats, $years);
  }

  function zen_get_banner_data_monthly($banner_id, $year = '') {
    global $db;
    if ((int)$year == 0) $year = date('Y');
    $set1 = $set2 = $stats = $months = array();
    for ($i=1; $i<13; $i++) {
      $m = strftime('%b', mktime(0,0,0,$i));
      $months[] = array((int)$i, $m);
      $set1[] = $set2[] = $stats[] = array($i, 0);
    }

    $result = $db->Execute("select month(banners_history_date) as banner_month, sum(banners_shown) as impressions,
                                sum(banners_clicked) as clicks
                  from " . TABLE_BANNERS_HISTORY . "
                  where banners_id = '" . (int)$banner_id . "'
                  and year(banners_history_date) = '" . zen_db_input($year) . "'
                  group by banner_month order by banner_month");

    while (!$result->EOF) {
      $set1[($result->fields['banner_month']-1)] = array((int)$result->fields['banner_month'], (int)$result->fields['impressions']);
      $set2[($result->fields['banner_month']-1)] = array((int)$result->fields['banner_month'], (int)$result->fields['clicks']);
      $stats[($result->fields['banner_month']-1)] = array((int)$result->fields['banner_month'], (int)$result->fields['impressions'], (int)$result->fields['clicks']);
      $result->MoveNext();
    }

    return array($set1, $set2, $stats, $months);
  }

  function zen_get_banner_data_daily($banner_id, $year = '', $month = '') {
    global $db;
    if ((int)$year == 0) $year = date('Y');
    if ((int)$month == 0) $month = date('n');

    $set1 = $set2 = array();

    $days = (date('t', mktime(0,0,0,$month))+1);
    for ($i=1; $i<$days; $i++) {
      $set1[] = $set2[] = $stats[] = array($i, 0);
    }

    $result = $db->Execute("select dayofmonth(banners_history_date) as banner_day,
                                       banners_shown as impressions, banners_clicked as clicks
                     from " . TABLE_BANNERS_HISTORY . "
                     where banners_id = '" . (int)$banner_id . "'
                     and month(banners_history_date) = '" . zen_db_input($month) . "'
                     and year(banners_history_date) = '" . zen_db_input($year) . "' order by banner_day");

    while (!$result->EOF) {
      $set1[($result->fields['banner_day']-1)] = array((int)$result->fields['banner_day'], (int)$result->fields['impressions']);
      $set2[($result->fields['banner_day']-1)] = array((int)$result->fields['banner_day'], (int)$result->fields['clicks']);
      $stats[($result->fields['banner_day']-1)] = array((int)$result->fields['banner_day'], (int)$result->fields['impressions'], (int)$result->fields['clicks']);
      $result->MoveNext();
    }

    return array($set1, $set2, $stats);
  }


