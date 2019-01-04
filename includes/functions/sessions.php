<?php
/**
 * functions/sessions.php
 * Session functions
 *
 * @package functions
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2019 Jan 04 Modified in v1.5.6a $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

if (IS_ADMIN_FLAG === true) {
    $SESS_LIFE = (int)SESSION_TIMEOUT_ADMIN;
    // if strict is enabled, must be a max of 900
    if (PADSS_ADMIN_SESSION_TIMEOUT_ENFORCED != 0 && $SESS_LIFE > 900) {
        $SESS_LIFE = 900;
    }
} else {
    // read PHP config
    $SESS_LIFE = get_cfg_var('session.gc_maxlifetime');
    // override if set
    if (defined('SESSION_TIMEOUT_CATALOG') && (int)SESSION_TIMEOUT_CATALOG > 120) {
        $SESS_LIFE = (int)SESSION_TIMEOUT_CATALOG;
    }
    // if set toooo short, reset to default
    if ((int)$SESS_LIFE < 120) {
        $SESS_LIFE = 1440;
    }
}

function _sess_open($save_path, $session_name)
{
    return true;
}

function _sess_close()
{
    return true;
}

function _sess_read($key)
{
    global $db;
    $qid = "select value
            from " . TABLE_SESSIONS . "
            where sesskey = '" . zen_db_input($key) . "'
            and expiry > '" . time() . "'";

    $value = $db->Execute($qid);

    if (!empty($value->fields['value'])) {
        $value->fields['value'] = base64_decode($value->fields['value']);
        return $value->fields['value'];
    }
    return '';
}

function _sess_write($key, $val)
{
    global $db;
    if (!is_object($db)) return false;
    $val = base64_encode($val);

    global $SESS_LIFE;
    $expiry = time() + $SESS_LIFE;

    $sql = "insert into " . TABLE_SESSIONS . " (sesskey, expiry, `value`)
            values (:zkey, :zexpiry, :zvalue)
            ON DUPLICATE KEY UPDATE `value`=:zvalue, expiry=:zexpiry";
    $sql = $db->bindVars($sql, ':zkey', $key, 'string');
    $sql = $db->bindVars($sql, ':zexpiry', $expiry, 'integer');
    $sql = $db->bindVars($sql, ':zvalue', $val, 'string');
    $result = $db->Execute($sql);

    return (!empty($result) && !empty($result->resource));
}

function _sess_destroy($key)
{
    global $db;
    $sql = "delete from " . TABLE_SESSIONS . " where sesskey = '" . zen_db_input($key) . "'";
    $db->Execute($sql);

    return true;
}

function _sess_gc($maxlifetime)
{
    global $db;
    $sql = "delete from " . TABLE_SESSIONS . " where expiry < " . time();
    $db->Execute($sql);

    return true;
}


// Initialize session save-handler
session_set_save_handler('_sess_open', '_sess_close', '_sess_read', '_sess_write', '_sess_destroy', '_sess_gc');
// write and close session at the end of scripts, and before objects are destroyed
register_shutdown_function('session_write_close');


function zen_session_start()
{
    global $SESS_LIFE;
    @ini_set('session.gc_maxlifetime', $SESS_LIFE);
    @ini_set('session.gc_probability', 1);
    @ini_set('session.gc_divisor', 2);

    if (preg_replace('/[a-zA-Z0-9,-]/', '', session_id()) != '') {
        zen_session_id(md5(uniqid(rand(), true)));
    }
    $temp = session_start();
    if (!isset($_SESSION['securityToken'])) {
        $_SESSION['securityToken'] = md5(uniqid(rand(), true));
    }

    return $temp;
}

function zen_session_id($sessid = '')
{
    if (!empty($sessid)) {
        $tempSessid = $sessid;
        if (preg_replace('/[a-zA-Z0-9,-]/', '', $tempSessid) != '') {
            $sessid = md5(uniqid(rand(), true));
        }

        return session_id($sessid);
    }

    return session_id();
}

function zen_session_name($name = '')
{
    if (!empty($name)) {
        $tempName = $name;
        if (preg_replace('/[a-zA-Z0-9,-]/', '', $tempName) == '') return session_name($name);

        return false;
    }

    return session_name();
}

function zen_session_write_close()
{
    session_write_close();
}

function zen_session_destroy()
{
    return session_destroy();
}

function zen_session_save_path($path = '')
{
    if (!empty($path)) {
        return session_save_path($path);
    }

    return session_save_path();
}

function zen_session_recreate()
{
    global $http_domain, $https_domain;
    if ($http_domain == $https_domain) {
        $saveSession = $_SESSION;
        $oldSessID   = session_id();
        session_regenerate_id();
        $newSessID = session_id();
        $_SESSION = $saveSession;
        if (IS_ADMIN_FLAG !== true) {
            whos_online_session_recreate($oldSessID, $newSessID);
        }
    }
}
