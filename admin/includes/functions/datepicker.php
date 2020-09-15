<?php

/*
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jun 16 Modified in v1.5.7 $
 */

/**
 * converts mm-dd-yy to MM-DD-YYYY
 * @return string
 */
function zen_datepicker_format_full()
{
  return str_replace("YY", "YYYY", strtoupper(DATE_FORMAT_DATE_PICKER));
}
/**
 * converts mm-dd-yy to m-d-Y
 * @return string
 */
function zen_datepicker_format_fordate()
{
  $date = DATE_FORMAT_DATE_PICKER;
  $date = str_replace('mm', 'm', $date);
  $date = str_replace('dd', 'd', $date);
  $date = str_replace('yy', 'Y', $date);
  return $date;
}
/**
 * converts mm-dd-yy to %m-%d-%Y
 * @return string
 */
function zen_datepicker_format_forsql()
{
  $date = DATE_FORMAT_DATE_PICKER;
  $date = str_replace('mm', '%m', $date);
  $date = str_replace('dd', '%d', $date);
  $date = str_replace('yy', '%Y', $date);
  return $date;
}
/**
 * 
 * @param string $raw_date
 * @return string
 */
function zen_prepare_date($raw_date)
{
  $date = zen_db_prepare_input($raw_date);
  if (DATE_FORMAT_DATE_PICKER != 'yy-mm-dd' && !empty($date)) {
    $local_fmt = zen_datepicker_format_fordate();
    $dt = DateTime::createFromFormat($local_fmt, $date);
    $date = 'null';
    if (!empty($dt)) {
      $date = $dt->format('Y-m-d');
    }
  }
  $date = (date('Y-m-d') < $date) ? $date : 'null';
  return $date;
}
