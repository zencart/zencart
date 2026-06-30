<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsSundry;

use Tests\Support\zcUnitTestCase;
use Zencart\Request\Request;

class RequestSecurityTest extends zcUnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        require_once DIR_FS_CATALOG . 'includes/classes/Request.php';

        $_SERVER = [];
    }

    public function testPlainHttpRequestIsNotSecure(): void
    {
        $_SERVER['HTTPS'] = 'off';
        $_SERVER['SERVER_PORT'] = '80';

        $this->assertFalse(Request::isSecure());
    }

    public function testNativeHttpsRequestIsSecure(): void
    {
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['SERVER_PORT'] = '443';

        $this->assertTrue(Request::isSecure());
    }

    public function testForwardedHttpsRequestIsNotSecureWithoutTrustedProxy(): void
    {
        $_SERVER['HTTPS'] = 'off';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';

        $this->assertFalse(Request::isSecure());
    }

    public function testForwardedSslHeaderRequestIsNotSecureWithoutTrustedProxy(): void
    {
        $_SERVER['HTTPS'] = 'off';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['HTTP_X_FORWARDED_SSL'] = 'on';

        $this->assertFalse(Request::isSecure());
    }

    public function testForwardedPortRequestIsNotSecureWithoutTrustedProxy(): void
    {
        $_SERVER['HTTPS'] = 'off';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['HTTP_X_FORWARDED_PORT'] = '443';

        $this->assertFalse(Request::isSecure());
    }

    public function testForwardedHostContainingSslIsNotSecureWithoutTrustedProxy(): void
    {
        $_SERVER['HTTPS'] = 'off';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'ssl-proxy.example.test';

        $this->assertFalse(Request::isSecure());
    }

    public function testForwardedServerDoesNotMatchHttpServerByAccident(): void
    {
        $_SERVER['HTTPS'] = 'off';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['HTTP_X_FORWARDED_SERVER'] = 'different-host.local';

        $this->assertFalse(Request::isSecure());
    }

    public function testForwardedHttpsRequestIsSecureWhenFromTrustedProxy(): void
    {
        if (!defined('TRUSTED_PROXIES')) {
            define('TRUSTED_PROXIES', '10.0.0.1');
        }

        $_SERVER['HTTPS'] = 'off';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
        $_SERVER['REMOTE_ADDR'] = '10.0.0.1';

        $this->assertTrue(Request::isSecure());
    }
}
