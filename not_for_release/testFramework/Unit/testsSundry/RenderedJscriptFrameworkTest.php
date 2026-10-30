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

        $this->assertStringContainsString('zcJS.hasSession = false;', $output);
        $this->assertStringContainsString('if (!hasSession) {', $output);
        $this->assertStringContainsString("d.reject(noSessionXHR, 'nosession', 'No session');", $output);

        /**
         * The guard has to sit ahead of the request, not after it, so assert on the
         * order of the two rather than on the exact text between them. Both offsets
         * are checked first: strpos() returns false for a missing needle, and false
         * compares as 0, which would make the ordering assertion pass vacuously.
         */
        $guardPosition = strpos($output, 'if (!hasSession) {');
        $requestPosition = strpos($output, 'jQuery.ajax(jqXHRSettings);');
        $this->assertNotFalse($guardPosition, 'The no-session guard is missing.');
        $this->assertNotFalse($requestPosition, 'The jQuery.ajax() call is missing.');
        $this->assertLessThan(
            $requestPosition,
            $guardPosition,
            'The no-session guard must come before the jQuery.ajax() call.'
        );
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
        $this->assertStringContainsString('zcJS.hasSession = true;', $output);
        $this->assertStringContainsString('jQuery.ajax(jqXHRSettings);', $output);
    }

    public function testRenderedJscriptFrameworkExposesHasSessionBeforeDefiningAjax(): void
    {
        $_SESSION = ['securityToken' => 'ordering-token'];

        ob_start();
        require dirname(__DIR__) . '/fixtures/render_jscript_framework.php';
        $output = (string) ob_get_clean();

        /**
         * Callers read zcJS.hasSession to skip page-load work, so it has to be set on
         * the zcJS object itself, and set before zcJS.ajax reads it. Both offsets are
         * checked first: strpos() returns false for a missing needle, and false
         * compares as 0, which would make the ordering assertion pass vacuously.
         */
        $flagPosition = strpos($output, 'zcJS.hasSession = ');
        $ajaxPosition = strpos($output, 'zcJS.ajax = function (options) {');
        $this->assertNotFalse($flagPosition, 'zcJS.hasSession is never assigned.');
        $this->assertNotFalse($ajaxPosition, 'zcJS.ajax is never defined.');
        $this->assertLessThan(
            $ajaxPosition,
            $flagPosition,
            'zcJS.hasSession must be assigned before zcJS.ajax is defined.'
        );
        $this->assertStringContainsString('const hasSession = zcJS.hasSession;', $output);
    }
}
