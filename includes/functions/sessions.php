<?php
/**
 * functions/sessions.php
 * Session functions
 *
 * @package functions
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: sessions.php 18697 2011-05-04 14:35:20Z wilt $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
  if (IS_ADMIN_FLAG === true) {
    if (!$SESS_LIFE = (SESSION_TIMEOUT_ADMIN > 900 ? 900 : SESSION_TIMEOUT_ADMIN)) {
      $SESS_LIFE = (SESSION_TIMEOUT_ADMIN > 900 ? 900 : SESSION_TIMEOUT_ADMIN);
    }
  } else {
    if (!$SESS_LIFE = get_cfg_var('session.gc_maxlifetime')) {
      $SESS_LIFE = 1440;
    }
  }

  function _sess_open($save_path, $session_name) {
    return true;
  }

  function _sess_close() {
    return true;
  }

  function _sess_read($key) {
    global $db;
    $qid = "select value
            from " . TABLE_SESSIONS . "
            where sesskey = '" . zen_db_input($key) . "'
            and expiry > '" . time() . "'";

    $value = $db->Execute($qid);

    if (isset($value->fields['value']) && $value->fields['value']) {
      $value->fields['value'] = base64_decode($value->fields['value']);
      return $value->fields['value'];
    }

    return ("");
  }

  function _sess_write($key, $val) {
    global $db;
    if (!is_object($db)) {
      //PHP 5.2.0 bug workaround ...
      if (!class_exists('queryFactory')) require('includes/classes/db/' .DB_TYPE . '/query_factory.php');
      $db = new queryFactory();
      $db->connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE, USE_PCONNECT, false);
    }
    $val = base64_encode($val);

    global $SESS_LIFE;

    $expiry = time() + $SESS_LIFE;

    $qid = "select count(*) as total
            from " . TABLE_SESSIONS . "
            where sesskey = '" . zen_db_input($key) . "'";

    $total = $db->Execute($qid);

    if ($total->fields['total'] > 0) {
      $sql = "update " . TABLE_SESSIONS . "
              set expiry = '" . zen_db_input($expiry) . "', value = '" . zen_db_input($val) . "'
              where sesskey = '" . zen_db_input($key) . "'";

      $result = $db->Execute($sql);

    } else {
      $sql = "insert into " . TABLE_SESSIONS . "
              values ('" . zen_db_input($key) . "', '" . zen_db_input($expiry) . "', '" .
                       zen_db_input($val) . "')";

      $result = $db->Execute($sql);

    }
  return (!empty($result) && !empty($result->resource));
  }

  function _sess_destroy($key) {
    global $db;
    $sql = "delete from " . TABLE_SESSIONS . " where sesskey = '" . zen_db_input($key) . "'";
    return $db->Execute($sql);
  }

  function _sess_gc($maxlifetime) {
    global $db;
    $sql = "delete from " . TABLE_SESSIONS . " where expiry < " . time();
    $db->Execute($sql);
    return true;
  }

  session_set_save_handler('_sess_open', '_sess_close', '_sess_read', '_sess_write', '_sess_destroy', '_sess_gc');

  function zen_session_start() {
    @ini_set('session.gc_probability', 1);
    @ini_set('session.gc_divisor', 2);
    if (IS_ADMIN_FLAG === true) {
      @ini_set('session.gc_maxlifetime', (SESSION_TIMEOUT_ADMIN > 900 ? 900 : SESSION_TIMEOUT_ADMIN));
    }
  	if (preg_replace('/[a-zA-Z0-9]/', '', session_id()) != '')
  	{
  	  zen_session_id(md5(uniqid(rand(), true)));
  	}
    $temp = session_start();
    if (!isset($_SESSION['securityToken'])) {
      $_SESSION['securityToken'] = md5(uniqid(rand(), true));
    }
    return $temp;
  }

  function zen_session_id($sessid = '') {
    if (!empty($sessid)) {
      $tempSessid = $sessid;
  	  if (preg_replace('/[a-zA-Z0-9]/', '', $tempSessid) != '')
  	  {
  	    $sessid = md5(uniqid(rand(), true));
  	  }
      return session_id($sessid);
    } else {
      return session_id();
    }
  }

  function zen_session_name($name = '') {
    if (!empty($name)) {
      $tempName = $name;
      if (preg_replace('/[a-zA-Z0-9]/', '', $tempName) == '') return session_name($name);
      return FALSE;
    } else {
      return session_name();
    }
  }

  function zen_session_close() {
    if (function_exists('session_close')) {
      return session_close();
    }
  }

  function zen_session_destroy() {
    return session_destroy();
  }

  function zen_session_save_path($path = '') {
    if (!empty($path)) {
      return session_save_path($path);
    } else {
      return session_save_path();
    }
  }

  function zen_session_recreate() {
    global $http_domain, $https_domain, $current_domain;
      if ($http_domain == $https_domain) {
      $saveSession = $_SESSION;
      $oldSessID = session_id();
      session_regenerate_id();
      $newSessID = session_id();
      session_id($oldSessID);
      session_id($newSessID);
      session_set_save_handler('_sess_open', '_sess_close', '_sess_read', '_sess_write', '_sess_destroy', '_sess_gc');
      $_SESSION = $saveSession;
      if (IS_ADMIN_FLAG !== true) {
        whos_online_session_recreate($oldSessID, $newSessID);
      }
    }
  }
