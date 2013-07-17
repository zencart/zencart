<?php
/**
 * @package admin
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: functions_prices.php 18695 2011-05-04 05:24:19Z drbyte $
 */

/**
 *  check zen cart version number
 */
function zen_get_zcversioninfo($response='number') {
  global $db;
  switch($response) {
    case 'number':
      return PROJECT_VERSION_NAME . ' v' . PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR;
      break;
    case 'footer':
      $current_sinfo = PROJECT_VERSION_NAME . ' v' . PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR . '/';
      $sql = "SELECT * from " . TABLE_PROJECT_VERSION . " WHERE project_version_key = 'Zen-Cart Database' ORDER BY project_version_date_applied DESC LIMIT 1";
      $result = $db->Execute($sql);
      if (!$result->EOF) {
        $current_sinfo .=  'v' . $result->fields['project_version_major'] . '.' . $result->fields['project_version_minor'];
        if (zen_not_null($result->fields['project_version_patch1'])) $current_sinfo .= '&nbsp;&nbsp;Patch: ' . $result->fields['project_version_patch1'];
      }
      return $current_sinfo;
      break;
  }
}