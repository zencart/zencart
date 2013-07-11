<?php
/**
 * @package admin
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */


// look in your $PATH_LOCALE/locale directory for available locales..
// recommended to list all values for your language in this array. And include at least the 3 versions: 'en_US', 'en_US.utf8', 'en'. These help support multiple server configurations (since IIS and Windows are less reliably configured)
$locales = array('en_US', 'en_US.utf8', 'en');

// For the most part, LC_TIME is fine here. On rare occasions you might need to change this to LC_ALL.
setlocale(LC_TIME, $locales);

define('DATE_FORMAT_SHORT', '%m/%d/%Y');  // this is used for strftime()
define('DATE_FORMAT_LONG', '%A %d %B, %Y'); // this is used for strftime()
define('ADMIN_NAV_DATE_TIME_FORMAT', '%A %d %b %Y %X'); // this is used for strftime()
define('DATE_FORMAT', 'd/m/Y'); // this is used for date()
define('DATE_TIME_FORMAT', DATE_FORMAT_SHORT . ' %H:%M:%S');
define('DATE_FORMAT_DATEPICKER_ADMIN', zen_date_datepicker(DATE_FORMAT));  //Use only 'dd', 'mm' and 'yy' here in any order

////
// Return date in raw format - DEPRECATED
// $date should be in format mm/dd/yyyy
// raw date is in format YYYYMMDD, or DDMMYYYY
function zen_date_raw($date, $reverse = false) {
  if ($reverse) {
    return substr($date, 3, 2) . substr($date, 0, 2) . substr($date, 6, 4);
  } else {
    return substr($date, 6, 4) . substr($date, 0, 2) . substr($date, 3, 2);
  }
}
function zen_date_datepicker($format)
{
  $date = preg_replace('/[ds]/', 'dd', $format);
  $date = preg_replace('/[mn]/', 'mm', $date);
  $date = preg_replace('/[yY]/', 'yy', $date);
  return $date;
}

// Global entries for the <html> tag
define('HTML_PARAMS','dir="ltr" lang="en"');

// charset for web pages and emails
define('CHARSET', 'utf-8');

// text for date of birth example
define('DOB_FORMAT_STRING', 'mm/dd/yyyy');

define('_JANUARY', 'January');
define('_FEBRUARY', 'February');
define('_MARCH', 'March');
define('_APRIL', 'April');
define('_MAY', 'May');
define('_JUNE', 'June');
define('_JULY', 'July');
define('_AUGUST', 'August');
define('_SEPTEMBER', 'September');
define('_OCTOBER', 'October');
define('_NOVEMBER', 'November');
define('_DECEMBER', 'December');

// weight units
define('TEXT_PRODUCT_WEIGHT_UNIT', 'lbs');
