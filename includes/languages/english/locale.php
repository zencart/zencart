<?php
/**
 * @package languageDefines
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  New in v1.6.0 $
 */

// look in your $PATH_LOCALE/locale directory for available locales..
// recommended to list all values for your language in this array. And include at least the 3 versions: 'en_US', 'en_US.utf8', 'en'. These help support multiple server configurations (since IIS and Windows are less reliably configured)
  $locales = array('en_US', 'en_US.utf8', 'en', 'English_United States.1252');

// For the most part, LC_TIME is fine here. On rare occasions you might need to change this to LC_ALL.
  @setlocale(LC_TIME, $locales);

  define('DATE_FORMAT_SHORT', '%m/%d/%Y');  // this is used for strftime()
  define('DATE_FORMAT_LONG', '%A %d %B, %Y'); // this is used for strftime()
  define('DATE_FORMAT', 'm/d/Y'); // this is used for date()
  define('DATE_TIME_FORMAT', DATE_FORMAT_SHORT . ' %H:%M:%S');

  define('ORDER_EMAIL_DATE_FORMAT', 'M-d-Y h:iA'); // this is used with date() -- see http://www.php.net/manual/en/function.date.php


// Return date in raw format
// $date should be in format mm/dd/yyyy
// raw date is in format YYYYMMDD, or DDMMYYYY
  if (!function_exists('zen_date_raw')) {
    function zen_date_raw($date, $reverse = false) {
      if ($reverse) {
        return substr($date, 3, 2) . substr($date, 0, 2) . substr($date, 6, 4);
      } else {
        return substr($date, 6, 4) . substr($date, 0, 2) . substr($date, 3, 2);
      }
    }
  }

// if USE_DEFAULT_LANGUAGE_CURRENCY is true, use the following currency, instead of the applications default currency (used when changing language)
  define('LANGUAGE_CURRENCY', 'USD');

// Global entries for the <html> tag
  define('HTML_PARAMS','dir="ltr" lang="en"');

// charset for web pages and emails
  define('CHARSET', 'utf-8');

// text for date of birth example
  define('DOB_FORMAT_STRING', 'mm/dd/yyyy');

  define('ENTRY_DATE_OF_BIRTH_ERROR', 'Is your birth date correct? Our system requires the date in this format: ' . DOB_FORMAT_STRING);
  define('ENTRY_DATE_OF_BIRTH_EXAMPLE', ' (eg. ' . DOB_FORMAT_STRING . ')');


  define('TEXT_PRODUCT_WEIGHT_UNIT','lbs');

// Shipping
  define('TEXT_SHIPPING_WEIGHT','lbs');
  define('TEXT_SHIPPING_BOXES', 'Boxes');

//universal symbols
  define('TEXT_NUMBER_SYMBOL', '# ');

  define('TEXT_FILESIZE_BYTES', ' bytes');
  define('TEXT_FILESIZE_KBS', ' KB');
  define('TEXT_FILESIZE_MEGS', ' MB');
  define('TEXT_FILESIZE_UNKNOWN', 'Unknown');

// misc
  define('COLON_SPACER', ':&nbsp;&nbsp;');

