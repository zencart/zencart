<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Sep 23 Modified in v2.1.0-beta1 $
 */

/** @var $PHP_SELF */
/** @var $zco_notifier */

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

define('SUPERUSER_PROFILE', 1);

// -----
// Special handling for AJAX requests.  Return a 'logged_out' error if no admin session
// is active; otherwise, bypass the remainder of the authorization checks.
//
if (basename($PHP_SELF) === FILENAME_AJAX . '.php') {
    if (empty($_SESSION['admin_id'])) {
        $ajax_response = [
            'error' => 'logged_out',
            'redirect' => zen_href_link(FILENAME_LOGIN, '', 'SSL'),
        ];
        echo json_encode($ajax_response);
        exit;
    }
    return;
}

// admin folder rename required
if ((!defined('ADMIN_BLOCK_WARNING_OVERRIDE') || ADMIN_BLOCK_WARNING_OVERRIDE === '') && !defined('ZENCART_TESTFRAMEWORK_RUNNING')) {
    if (basename($PHP_SELF) !== FILENAME_ALERT_PAGE . '.php') {
        if (str_ends_with(DIR_WS_ADMIN, '/admin/') || str_ends_with(DIR_WS_HTTPS_ADMIN, '/admin/')) {
            zen_redirect(zen_href_link(FILENAME_ALERT_PAGE));
        }
        $check_path = dirname($PHP_SELF) . '/../zc_install';
        if (is_dir($check_path)) {
            zen_redirect(zen_href_link(FILENAME_ALERT_PAGE));
        }
    }
}

// Check safety of access
if (basename($PHP_SELF) !== FILENAME_ALERT_PAGE . '.php') {

    // handle malicious URLs
    if (str_contains(strtolower($PHP_SELF), FILENAME_PASSWORD_FORGOTTEN . '.php')
        && substr_count(strtolower($PHP_SELF), '.php') > 1)
    {
        zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
    }

    if (!(basename($PHP_SELF) === FILENAME_LOGIN . ".php")) {
        $page = basename($PHP_SELF, ".php");

        // must be logged in
        if (!isset($_SESSION['admin_id'])) {
            if (!(basename($PHP_SELF) == FILENAME_PASSWORD_FORGOTTEN . '.php')) {
                zen_redirect(zen_href_link(FILENAME_LOGIN, 'camefrom=' . basename($PHP_SELF) . '&' . zen_get_all_get_params(), 'SSL'));
            }
        }

        // Do MFA validation
        if (basename($PHP_SELF) !== FILENAME_MFA . '.php' && basename($PHP_SELF) !== FILENAME_LOGOFF . '.php'
            && (!empty($_SESSION['mfa']['pending']) || !empty($_SESSION['mfa']['setup_required']))
        ) {
            zen_redirect(zen_href_link(FILENAME_MFA, zen_get_all_get_params('action')));
        }

        // check page authorization access
        if (!in_array($page, ['keepalive', FILENAME_DEFAULT, FILENAME_ADMIN_ACCOUNT, FILENAME_LOGOFF, FILENAME_ALERT_PAGE, FILENAME_PASSWORD_FORGOTTEN, FILENAME_DENIED, FILENAME_ALT_NAV], true)
            && !zen_is_superuser())
        {
            if (check_page($page, $_GET) === false && check_related_page($page, $_GET) === false) {
                zen_record_admin_activity('Attempted access to unauthorized page [' . $page . ']. Redirected to DENIED page instead.', 'notice');
                zen_redirect(zen_href_link(FILENAME_DENIED, '', 'SSL'));
            }
            $zco_notifier->notify('NOTIFY_ADMIN_NONSUPERUSER_ACTION');
        }
    }

    // handle malicious URLs
    if ((basename($PHP_SELF) === FILENAME_LOGIN . '.php')
        && (substr_count(dirname($PHP_SELF), '//') > 0 || substr_count(dirname($PHP_SELF), '.php') > 0))
    {
        zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
    }
}
