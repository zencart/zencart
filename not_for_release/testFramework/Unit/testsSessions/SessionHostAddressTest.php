<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */

declare(strict_types=1);

namespace Tests\Unit\testsSessions;

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

/**
 * Covers the $_SESSION['customers_host_address'] branch of init_sessions.php.
 *
 * Regression guard for "always resolves hostname even when configured not to"
 * (issue #7905): the earlier condition was an OR, so with
 * SESSION_IP_TO_HOST_ADDRESS switched off and no OFFICE_IP_TO_HOST_ADDRESS
 * override defined - the default install - a blocking reverse-DNS lookup still
 * ran on every new session.
 */
class SessionHostAddressTest extends TestCase
{
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testHostAddressLookupDisabledReturnsBlankWhenOfficeLabelIsNotDefined(): void
    {
        $this->loadInitSessionsWithHostLookupDisabled(false);

        $this->assertSame('', $_SESSION['customers_host_address']);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testHostAddressLookupDisabledReturnsOfficeLabelWhenDefined(): void
    {
        $this->loadInitSessionsWithHostLookupDisabled(true);

        $this->assertSame('Disabled', $_SESSION['customers_host_address']);
    }

    /**
     * Loads init_sessions.php with host lookup switched off.
     *
     * SESSION_FORCE_COOKIE_USE is 'True' with no cookie_test cookie present, so
     * no session is actually started and execution reaches the host_address
     * block without needing a session backend. zen_config() falls back to
     * constant() whenever no configuration repository is loaded, which is the
     * case here, so defining these as constants is sufficient.
     */
    private function loadInitSessionsWithHostLookupDisabled(bool $defineOfficeLabel): void
    {
        $rootPath = dirname(__DIR__, 4) . '/';

        define('IS_ADMIN_FLAG', false);
        define('DIR_WS_FUNCTIONS', $rootPath . 'includes/functions/');
        define('SESSION_WRITE_DIRECTORY', sys_get_temp_dir());
        define('SESSION_FORCE_COOKIE_USE', 'True');
        define('SESSION_USE_ROOT_COOKIE_PATH', 'False');
        define('SESSION_ADD_PERIOD_PREFIX', 'False');
        define('HTTP_SERVER', 'http://example.com');
        define('SESSION_BLOCK_SPIDERS', 'False');
        define('SESSION_IP_TO_HOST_ADDRESS', 'false');
        define('SESSION_CHECK_USER_AGENT', 'False');
        define('SESSION_CHECK_IP_ADDRESS', 'False');
        if ($defineOfficeLabel) {
            define('OFFICE_IP_TO_HOST_ADDRESS', 'Disabled');
        }

        if (!function_exists('zen_get_ip_address')) {
            eval('function zen_get_ip_address() { return $_SERVER[\'REMOTE_ADDR\']; }');
        }

        require_once $rootPath . 'includes/functions/zen_config.php';

        $zenSessionId = 'zenid';
        $cookieDomain = '';
        $_GET = [];
        $_POST = [];
        $_COOKIE = [];
        $_SESSION = [];
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'Unit test';

        require $rootPath . 'includes/init_includes/init_sessions.php';
    }
}
