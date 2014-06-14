<?php
/**
 * @package admin
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Tue Jun 3 2014 -0500 Added in v1.5.3 $
 */

function zen_record_admin_activity($data = '', $specific_message = '', $severity = '')
{
  global $db, $zco_notifier;
  if ($data == '' && $specific_message == '') $data = $_POST;
  // initialize (add new entry) if log is blank
  $sql = "SELECT ip_address from " . TABLE_ADMIN_ACTIVITY_LOG . " LIMIT 1";
  $result = $db->Execute($sql);
  if ($result->RecordCount() < 1) {
    $sql_data_array = array( 'access_date' => 'now()',
            'admin_id' => (isset($_SESSION['admin_id'])) ? (int)$_SESSION['admin_id'] : 0,
            'page_accessed' =>  'Log found to be empty. Logging started.',
            'page_parameters' => '',
            'ip_address' => substr($_SERVER['REMOTE_ADDR'],0,45),
            'gzpost' => '',
            'flagged' => '',
            'attention' => '',
    );
    zen_db_perform(TABLE_ADMIN_ACTIVITY_LOG, $sql_data_array);
  }

  // do normal logging
  $flagged = 0;
  $notes = $gzpostdata = $postdata = '';
  if (isset($data) && sizeof($data) > 0) {
    $postdata = $data;
    foreach ($postdata as $key=>$nul) {
      if (in_array($key, array('x','y','secur'.'ityTo'.'ken','admi'.'n_p'.'ass','pass'.'word','confirm', 'newpwd-'.$_SESSION['securityToken'],'oldpwd-'.$_SESSION['securityToken'],'confpwd-'.$_SESSION['securityToken']))) unset($postdata[$key]);
      if (strtolower(CHARSET) != 'utf-8') {
        if (is_string($nul)) $postdata[$key] = utf8_encode($nul);
        if (is_array($nul)) {
          foreach ($nul as $key2=>$val) {
            if (is_string($val)) $postdata[$key][$key2] = utf8_encode($val);
            if (is_array($val)) $postdata[$key][$key2] = utf8_encode(print_r($val, TRUE));
          }
        }
      }
    }
    $notes = zen_parse_for_rogue_post(print_r($postdata, true));
    $postdata = json_encode($postdata);
    $gzpostdata = gzdeflate($postdata, 7);
    $flagged = ($notes === FALSE) ? 0 : 1;
  }
  $sql_data_array = array( 'access_date' => 'now()',
          'admin_id' => (isset($_SESSION['admin_id'])) ? (int)$_SESSION['admin_id'] : 0,
          'page_accessed' =>  zcRequest::readGet('cmd') . (!isset($_SESSION['admin_id']) || (int)$_SESSION['admin_id'] == 0 ? ' ' . (isset($data['admin_name']) ? $data['admin_name'] : (isset($data['admin_email']) ? $data['admin_email'] : '') ) : ''),
          'page_parameters' => implode("\n", array(preg_replace('/(&amp;|&)$/', '', zen_get_all_get_params()), $specific_message)),
          'ip_address' => substr($_SERVER['REMOTE_ADDR'],0,45),
          'gzpost' => $gzpostdata,
          'flagged' => (int)$flagged,
          'attention' => ($notes === FALSE ? '' : $notes),
  );
  zen_db_perform(TABLE_ADMIN_ACTIVITY_LOG, $sql_data_array);

  /**
   * hook to 3rd party logging service
   */
  $zco_notifier->notify('NOTIFY_ADMIN_ACTIVITY_LOG_ADD_RECORD', $sql_data_array, $postdata);

  unset($flagged, $postdata, $notes, $gzpostdata, $sql_data_array, $key, $nul);
}

function zen_parse_for_rogue_post($string)
{
  $matches = '';
  if (preg_match_all('~(file://|<iframe|<frame|<embed|<script|<meta)~i', $string, $matches)) {
    return htmlspecialchars(WARNING_REVIEW_ROGUE_ACTIVITY . "\n" . implode(' and ', $matches[1]), ENT_COMPAT, CHARSET, TRUE);
  } else {
    return FALSE;
  }
}


