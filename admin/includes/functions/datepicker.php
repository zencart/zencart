<?php

/*
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Erik Kerkhoven 2020 Oct 21 New in v1.5.8-alpha $
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
 * Convet Date to the corect farmat
 * @param string $raw_date Date value
 * @param boolean $past_date_null Set the date value to null is the dat is in the past
 * @return string
 */
function zen_prepare_date($raw_date, $past_date_null = false)
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
  if ($past_date_null == true) {
    $date = (date('Y-m-d') < $date) ? $date : 'null';
  }
  return $date;
}
