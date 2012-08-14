<?php
/**
 * @package admin
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: init_admin_history.php 18962 2011-06-21 20:40:48Z drbyte $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

  // log page visit into admin activity history
  if (basename($PHP_SELF) != FILENAME_DEFAULT . '.php') {
    $sql = "SELECT ip_address from " . TABLE_ADMIN_ACTIVITY_LOG . " LIMIT 1";
    $result = $db->Execute($sql);
    if ($result->RecordCount() < 1) {
      $sql_data_array = array( 'access_date' => 'now()',
                               'admin_id' => (isset($_SESSION['admin_id'])) ? (int)$_SESSION['admin_id'] : 0,
                               'page_accessed' =>  'Log found to be empty. Logging started.',
                               'page_parameters' => '',
                               'gzpost' => '',
                               'ip_address' => substr($_SERVER['REMOTE_ADDR'],0,45)
                               );
      zen_db_perform(TABLE_ADMIN_ACTIVITY_LOG, $sql_data_array);
    }
    $flagged = 0;
    $notes = $gzpostdata = $postdata = '';
    if (isset($_POST) && sizeof($_POST) > 0) {
      $postdata = $_POST;
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
                             'page_accessed' =>  basename($PHP_SELF) . (!isset($_SESSION['admin_id']) || (int)$_SESSION['admin_id'] == 0 ? ' ' . (isset($_POST['admin_name']) ? $_POST['admin_name'] : (isset($_POST['admin_email']) ? $_POST['admin_email'] : '') ) : ''),
                             'page_parameters' => zen_get_all_get_params(),
                             'ip_address' => substr($_SERVER['REMOTE_ADDR'],0,45),
                             'gzpost' => $gzpostdata,
                             'flagged' => (int)$flagged,
                             'attention' => ($notes === FALSE ? '' : $notes),
                             );
    zen_db_perform(TABLE_ADMIN_ACTIVITY_LOG, $sql_data_array);
    unset($flagged, $postdata, $notes, $gzpostdata, $sql_data_array, $key, $nul);
  }

  function zen_parse_for_rogue_post($string) {
    $matches = '';
    if (preg_match_all('~(file://|<iframe|<frame|<embed|<script|<meta)~i', $string, $matches)) {
      return htmlspecialchars(WARNING_REVIEW_ROGUE_ACTIVITY . "\n" . implode(' and ', $matches[1]), ENT_COMPAT, CHARSET, TRUE);
    } else {
      return FALSE;
    }
  }