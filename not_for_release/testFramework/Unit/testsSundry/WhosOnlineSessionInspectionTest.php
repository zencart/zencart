<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsSundry;

use Tests\Support\zcUnitTestCase;

$projectRoot = dirname(__DIR__, 4);
require_once $projectRoot . '/includes/classes/class.base.php';
require_once $projectRoot . '/admin/includes/classes/WhosOnline.php';

#[\PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses]
class WhosOnlineSessionInspectionTest extends zcUnitTestCase
{
    private TestableWhosOnline $subject;

    public function setUp(): void
    {
        parent::setUp();

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        ini_set('session.save_handler', 'files');
        ini_set('session.save_path', sys_get_temp_dir());
        session_id(bin2hex(random_bytes(8)));
        session_start();
        $_SESSION = ['admin_id' => 1, 'currency' => 'USD'];

        $this->subject = new TestableWhosOnline(false, true);
    }

    public function tearDown(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        parent::tearDown();
    }

    public function testInspectSessionCartDecodesBase64EncodedStoredSessions(): void
    {
        $encodedSession = $this->buildStoredSessionPayload([
            'languages_id' => 7,
            'comments' => 'Front door code',
        ]);

        $result = $this->subject->inspectSessionCartForTest('', $encodedSession);

        $this->assertSame(7, $result['language_id']);
        $this->assertSame('Front door code', $result['checkout_comments']);
        $this->assertSame(['admin_id' => 1, 'currency' => 'USD'], $_SESSION);
    }

    public function testInspectSessionCartIgnoresMalformedStoredSessionsWithoutWarnings(): void
    {
        $warnings = [];
        set_error_handler(
            static function (int $errno, string $errstr) use (&$warnings): bool {
                if ($errno === E_WARNING) {
                    $warnings[] = $errstr;
                }

                return true;
            }
        );

        try {
            $result = $this->subject->inspectSessionCartForTest('', base64_encode('not-a-valid-session-payload'));
        } finally {
            restore_error_handler();
        }

        $this->assertSame([], $result);
        $this->assertSame([], array_filter($warnings, static fn($warning) => str_starts_with($warning, 'session_decode():')));
        $this->assertSame(['admin_id' => 1, 'currency' => 'USD'], $_SESSION);
    }

    private function buildStoredSessionPayload(array $sessionData): string
    {
        $originalSession = $_SESSION;
        $_SESSION = $sessionData;
        $encodedSession = base64_encode(session_encode());
        $_SESSION = $originalSession;

        return $encodedSession;
    }
}

class TestableWhosOnline extends \WhosOnline
{
    public function inspectSessionCartForTest(string $sessionId = '', string $sessionData = ''): ?array
    {
        return $this->inspectSessionCart($sessionId, $sessionData);
    }
}
