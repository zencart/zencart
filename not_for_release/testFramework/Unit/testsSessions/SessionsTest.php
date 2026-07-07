<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */

namespace Tests\Unit\testsSessions;

use PHPUnit\Framework\TestCase;

class SessionsTest extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testHostAddressLookupDisabledReturnsBlankWhenAdminLabelIsNotDefined(): void
    {
        $this->loadInitSessionsWithHostLookupDisabled(false, false);

        $this->assertSame('', $_SESSION['customers_host_address']);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testHostAddressLookupDisabledReturnsAdminLabelWhenDefined(): void
    {
        $this->loadInitSessionsWithHostLookupDisabled(true, true);

        $this->assertSame('Disabled', $_SESSION['customers_host_address']);
    }

    private function loadInitSessionsWithHostLookupDisabled(bool $isAdmin, bool $defineOfficeLabel): void
    {
        if (!defined('IS_ADMIN_FLAG')) {
            define('IS_ADMIN_FLAG', $isAdmin);
        }
        if ($isAdmin && !defined('SESSION_TIMEOUT_ADMIN')) {
            define('SESSION_TIMEOUT_ADMIN', 900);
        }
        if ($isAdmin && !defined('PADSS_ADMIN_SESSION_TIMEOUT_ENFORCED')) {
            define('PADSS_ADMIN_SESSION_TIMEOUT_ENFORCED', 0);
        }
        if (!defined('DIR_WS_FUNCTIONS')) {
            define('DIR_WS_FUNCTIONS', dirname(__DIR__, 4) . '/includes/functions/');
        }
        if (!defined('SESSION_WRITE_DIRECTORY')) {
            define('SESSION_WRITE_DIRECTORY', sys_get_temp_dir());
        }
        if (!defined('SESSION_FORCE_COOKIE_USE')) {
            define('SESSION_FORCE_COOKIE_USE', 'True');
        }
        if (!defined('SESSION_USE_ROOT_COOKIE_PATH')) {
            define('SESSION_USE_ROOT_COOKIE_PATH', 'False');
        }
        if (!defined('SESSION_ADD_PERIOD_PREFIX')) {
            define('SESSION_ADD_PERIOD_PREFIX', 'False');
        }
        if (!defined('ENABLE_SSL')) {
            define('ENABLE_SSL', 'false');
        }
        if (!defined('HTTP_SERVER')) {
            define('HTTP_SERVER', 'http://example.com');
        }
        if (!defined('HTTPS_SERVER')) {
            define('HTTPS_SERVER', 'https://example.com');
        }
        if (!defined('SESSION_BLOCK_SPIDERS')) {
            define('SESSION_BLOCK_SPIDERS', 'False');
        }
        if (!defined('SESSION_IP_TO_HOST_ADDRESS')) {
            define('SESSION_IP_TO_HOST_ADDRESS', 'false');
        }
        if (!defined('SESSION_CHECK_USER_AGENT')) {
            define('SESSION_CHECK_USER_AGENT', 'False');
        }
        if (!defined('SESSION_CHECK_IP_ADDRESS')) {
            define('SESSION_CHECK_IP_ADDRESS', 'False');
        }
        if ($defineOfficeLabel && !defined('OFFICE_IP_TO_HOST_ADDRESS')) {
            define('OFFICE_IP_TO_HOST_ADDRESS', 'Disabled');
        }
        if (!function_exists('zen_get_ip_address')) {
            eval('function zen_get_ip_address() { return $_SERVER[\'REMOTE_ADDR\']; }');
        }

        $zenSessionId = 'zenid';
        $cookieDomain = '';
        $request_type = 'NONSSL';
        $_GET = [];
        $_POST = [];
        $_COOKIE = [];
        $_SESSION = [];
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'Unit test';

        require dirname(__DIR__, 4) . '/includes/init_includes/init_sessions.php';
    }
}
