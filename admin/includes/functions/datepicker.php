<?php

/*
 * @copyright Copyright 2003-2023 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2022 Oct 16 Modified in v1.5.8a $
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
 * Convert Date to the correct format
 * @param string $raw_date Date value
 * @param string $past_date is the value to set the date if it is in the past or empty
 * @return string
 */
function zen_prepare_date($raw_date, $past_date = '')
{
    if (empty($raw_date)) {
        return $past_date;
    }
  $date = zen_db_prepare_input($raw_date);
  if (DATE_FORMAT_DATE_PICKER != 'yy-mm-dd' && !empty($date)) {
    $local_fmt = zen_datepicker_format_fordate();
    $dt = DateTime::createFromFormat($local_fmt, $date);
    $date = 'null';
    if (!empty($dt)) {
      $date = $dt->format('Y-m-d');
    }
  }
  if (!empty($past_date)) {
    $date = (date('Y-m-d') < $date) ? $date : $past_date;
  }
  return $date;
}
