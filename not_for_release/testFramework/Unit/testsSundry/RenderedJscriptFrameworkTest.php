<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\Support\zcUnitTestCase;

#[RunTestsInSeparateProcesses]
class RenderedJscriptFrameworkTest extends zcUnitTestCase
{
    public function testRenderedJscriptFrameworkUsesCsrfHeaderForObjectPayloads(): void
    {
        $_SESSION = ['securityToken' => 'header-token-123'];

        ob_start();
        require dirname(__DIR__) . '/fixtures/render_jscript_framework.php';
        $output = (string) ob_get_clean();

        $this->assertStringContainsString("const csrfToken = 'header-token-123';", $output);
        $this->assertStringContainsString("'X-CSRF-Token': csrfToken", $output);
        $this->assertStringContainsString("data: options.data,", $output);
        $this->assertStringContainsString("options.data += '&securityToken=' + encodeURIComponent(csrfToken);", $output);
        $this->assertStringNotContainsString("data: jQuery.extend(true, {}, options.data, {securityToken:", $output);
    }

    public function testRenderedJscriptFrameworkRejectsWithoutRequestWhenNoSession(): void
    {
        $this->assertSame(PHP_SESSION_NONE, session_status());
        $_SESSION = ['securityToken' => 'no-session-token'];

        ob_start();
        require dirname(__DIR__) . '/fixtures/render_jscript_framework.php';
        $output = (string) ob_get_clean();

        $this->assertStringContainsString('const hasSession = false;', $output);
        $noSessionGuard = "/if \(!hasSession\) \{.*d\.reject\(noSessionXHR, 'nosession', 'No session'\);\s*return;\s*\}\s*const jqXHRSettings/s";
        $this->assertMatchesRegularExpression($noSessionGuard, $output);
    }

    public function testRenderedJscriptFrameworkSendsRequestWhenSessionActive(): void
    {
        ini_set('session.use_cookies', '0');
        ini_set('session.cache_limiter', '');
        ini_set('session.save_path', sys_get_temp_dir());
        session_start();
        $_SESSION['securityToken'] = 'active-session-token';

        ob_start();
        require dirname(__DIR__) . '/fixtures/render_jscript_framework.php';
        $output = (string) ob_get_clean();
        session_destroy();

        $this->assertStringContainsString("const csrfToken = 'active-session-token';", $output);
        $this->assertStringContainsString('const hasSession = true;', $output);
        $this->assertStringContainsString('jQuery.ajax(jqXHRSettings);', $output);
    }
}
