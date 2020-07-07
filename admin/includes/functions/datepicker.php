<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jun 16 Modified in v1.5.7 $
 */

  function zen_datepicker_format_full() {
    // converts mm-dd-yy to MM-DD-YYYY
    return str_replace("YY","YYYY", strtoupper(DATE_FORMAT_DATE_PICKER));
  }

  function zen_datepicker_format_fordate() {
    // converts mm-dd-yy to m-d-Y
    $date = DATE_FORMAT_DATE_PICKER;
    $date = str_replace('mm','m', $date);
    $date = str_replace('dd','d', $date);
    $date = str_replace('yy','Y', $date);
    return $date;
  }

  function zen_datepicker_format_forsql() {
    // converts mm-dd-yy to %m-%d-%Y
    $date = DATE_FORMAT_DATE_PICKER;
    $date = str_replace('mm','%m', $date);
    $date = str_replace('dd','%d', $date);
    $date = str_replace('yy','%Y', $date);
    return $date;
  }
