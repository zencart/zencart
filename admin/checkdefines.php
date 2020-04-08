<?php
require("includes/application_top.php"); 

// Pull in all language files here 
if (IS_ADMIN_FLAG) { 
  // Pull in all admin files 
  $files= getfiles("includes/languages"); 
  $skip_list = array("includes/languages/english.php"); 
} else {
  $skip_list = array("includes/languages/english.php"); 
  // Pull in all catalog files 
}

foreach ($files as $file) {
  if (in_array($file, $skip_list)) continue; 
  require($file); 
}

$list = get_defined_constants(true) ;

if (IS_ADMIN_FLAG) { 
   echo "Run catalog_find_define on all of these to determine which should be removed."; 
} else {
   echo "All these should be removed."; 
}
echo "<br />";

$db_keys = get_db_keys(); 
foreach ($list['user'] as $key => $value) {
  if (in_array($key, $db_keys)) continue; 
  if (in_array($key, $known_good_list)) continue; 
  if (strpos($key, 'TEXT_MAX_ADMIN_') === 0) continue; 
  if (strpos($key, 'TEXT_MIN_ADMIN_') === 0) continue; 
  $parts = explode("_", $key); 
  if (!empty($parts[0])) {
    if ($parts[0] == "FILENAME") continue; 
    if ($parts[0] == "TABLE") continue; 
  }
  $rc = 0;
  system('grep -l ' . $key . ' `find . -type f` | grep -s -v "includes/languages/" 1>/dev/null 2>/dev/null', $rc); 
  if ($rc != 0) {
     echo $key . "<br />"; 
  }
}
echo "Complete!";

function get_db_keys() {
  global $db; 
  $keys = array(); 

  $query = $db->Execute("SELECT configuration_key FROM " . TABLE_CONFIGURATION); 
  $config_keys = get_all_keys($query, 'configuration_key'); 
  $keys = array_merge($keys, $config_keys); 

  $query = $db->Execute("SELECT configuration_key FROM " . TABLE_PRODUCT_TYPE_LAYOUT); 
  $layout_keys = get_all_keys($query, 'configuration_key'); 
  $keys = array_merge($keys, $layout_keys); 

  $query = $db->Execute("SELECT language_key FROM " . TABLE_ADMIN_PAGES); 
  $admin_keys = get_all_keys($query, 'language_key'); 
  $keys = array_merge($keys, $admin_keys); 


  return $keys; 
}

function get_all_keys($query, $key) {
  $keys = array(); 

  while (!$query->EOF) {
    $keys[] = $query->fields[$key];
    $query->MoveNext(); 
  }
  return $keys; 
}

function known_good_list() {
  // Defined on storefront side 
  return array(
    'TEXT_GV_NAME', 
    'TEXT_GV_NAMES', 
    'EMAIL_LOGO_FILENAME',
    'EMAIL_LOGO_WIDTH',
    'EMAIL_LOGO_HEIGHT',
    'EMAIL_LOGO_ALT_TITLE_TEXT',

    'OFFICE_FROM',
    'OFFICE_EMAIL',
    'OFFICE_SENT_TO',
    'OFFICE_EMAIL_TO',
    'OFFICE_USE',
    'OFFICE_LOGIN_NAME',
    'OFFICE_LOGIN_EMAIL',
    'OFFICE_LOGIN_PHONE',
    'OFFICE_IP_ADDRESS',
    'OFFICE_HOST_ADDRESS',
    'OFFICE_DATE_TIME',

    'BOX_HEADING_CONFIGURATION',
    'BOX_HEADING_MODULES',
    'BOX_HEADING_CUSTOMERS',
    'BOX_HEADING_LOCATION_AND_TAXES',
    'BOX_HEADING_REPORTS',
    'BOX_HEADING_EXTRAS',
    'BOX_HEADING_LOCALIZATION',
    'BOX_HEADING_GV_ADMIN',
    'BOX_HEADING_ADMIN_ACCESS',

    'OTHER_IMAGE_REVIEWS_RATING_STARS_FIVE',
    'OTHER_IMAGE_REVIEWS_RATING_STARS_FOUR',
    'OTHER_IMAGE_REVIEWS_RATING_STARS_THREE',
    'OTHER_IMAGE_REVIEWS_RATING_STARS_TWO',
    'OTHER_IMAGE_REVIEWS_RATING_STARS_ONE',
    'OTHER_IMAGE_BLACK_SEPARATOR', 
    'OTHER_IMAGE_BOX_NOTIFY_REMOVE', 
    'OTHER_IMAGE_BOX_NOTIFY_YES', 
    'OTHER_IMAGE_BOX_WRITE_REVIEW', 
    'OTHER_IMAGE_CALL_FOR_PRICE', 
    'OTHER_IMAGE_DOWN_FOR_MAINTENANCE', 
    'OTHER_IMAGE_PRICE_IS_FREE',
    'OTHER_IMAGE_TRANPARENT', 
    'OTHER_IMAGE_CUSTOMERS_AUTHORIZATION', 
    
    'ICON_ERROR',
    'ICON_SUCCESS',
    'ICON_WARNING',

    'ERROR_FILE_TOO_BIG',
    'ERROR_FILETYPE_NOT_ALLOWED', 
    'ERROR_FILE_NOT_SAVED',
    'ERROR_DESTINATION_NOT_WRITEABLE',
    'ERROR_DESTINATION_DOES_NOT_EXIST', 
    'WARNING_NO_FILE_UPLOADED', 
    'SUCCESS_FILE_SAVED_SUCCESSFULLY',
    'UPLOAD_FILENAME_EXTENSIONS_LIST',
    'EMAIL_SYSTEM_DEBUG',
    'EMAIL_ATTACH_EMBEDDED_IMAGES',
    'SMTPAUTH_EMAIL_PROTOCOL',
    'ENABLE_PLUGIN_VERSION_CHECKING',
    'LOG_PLUGIN_VERSIONCHECK_FAILURES',
    'TEXT_DOCUMENT_AVAILABLE',
    'OFFICE_IP_TO_HOST_ADDRESS', 
    'EMAIL_SEND_FAILED', 
    'TEXT_IMAGE_OVERWRITE_WARNING',
    'EMAIL_EXTRA_HEADER_INFO', 
    'EMAIL_FOOTER_COPYRIGHT',
    'SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO_SUBJECT', 
    'TEXT_UNSUBSCRIBE',
    'OTHER_BOX_NOTIFY_REMOVE_ALT',
    'OTHER_BOX_NOTIFY_YES_ALT',
    'OTHER_BOX_WRITE_REVIEW_ALT',
    'OTHER_CALL_FOR_PRICE_ALT',
    'OTHER_DOWN_FOR_MAINTENANCE_ALT', 
    'WARNING_COULD_NOT_LOCATE_LANG_FILE', 
    'ERROR_MODULE_REMOVAL_PROHIBITED', 
    'NEW_VERSION_CHECKUP_URL', 
    'CONNECTION_TYPE_UNKNOWN',

    'OSH_EMAIL_SEPARATOR', 
    'OSH_EMAIL_TEXT_SUBJECT',
    'OSH_EMAIL_TEXT_ORDER_NUMBER',
    'OSH_EMAIL_TEXT_INVOICE_URL',
    'OSH_EMAIL_TEXT_DATE_ORDERED',
    'OSH_EMAIL_TEXT_COMMENTS_UPDATE',
    'OSH_EMAIL_TEXT_STATUS_UPDATED',
    'OSH_EMAIL_TEXT_STATUS_NO_CHANGE',
    'OSH_EMAIL_TEXT_STATUS_LABEL',
    'OSH_EMAIL_TEXT_STATUS_CHANGE',
    'OSH_EMAIL_TEXT_STATUS_PLEASE_REPLY',
  ); 
}

function getfiles($dir_name) { 
     $subdirectories = array();
     $files = array();
     if (is_dir($dir_name) && is_readable($dir_name)) {
        $d = dir($dir_name); 
        while (false != ($f = $d->read())) {
           if ( ("." == $f) || (".." == $f) ) continue;
           if (is_dir("$dir_name/$f")) {
             array_push($subdirectories, "$dir_name/$f");
           } else {
             $extension = end(explode(".", $f));
             if (!empty($extension) && $extension == "php") { 
                array_push($files, "$dir_name/$f");
             }
           }
        }
        $d->close(); 
        foreach ($subdirectories as $subdirectory) {
           $files = array_merge($files, getfiles($subdirectory));
        }
     }
     return $files;
}
