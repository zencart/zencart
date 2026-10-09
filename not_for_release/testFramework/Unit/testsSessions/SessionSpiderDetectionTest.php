<?php
declare(strict_types=1);
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */

namespace Tests\Unit\testsSessions;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

/**
 * Covers the SESSION_BLOCK_SPIDERS matching loop in init_sessions.php.
 *
 * Two things are asserted together in every case, because the consequence is
 * what actually matters: whether the user agent was classified as a spider, and
 * whether a session was therefore started. A spider is deliberately denied a
 * session; a real visitor must always get one.
 *
 * Regression guard for the loop rewrite in commit fafc676e8. The previous loop
 * ran trim() on each line and fed the result straight to strpos(), so an empty
 * pattern produced strpos($user_agent, '') === 0, which is_int() accepted. A
 * single blank or whitespace-only line in includes/spiders.txt therefore matched
 * every user agent on the planet, classified every visitor as a spider, and left
 * the whole store running with no sessions at all. The current loop skips lines
 * shorter than three characters, '#' comments and the '$Id:' version stamp.
 */
class SessionSpiderDetectionTest extends TestCase
{
    /**
     * Directory holding a generated spiders.txt, removed again in tearDown().
     */
    private ?string $fixtureDir = null;

    protected function tearDown(): void
    {
        if ($this->fixtureDir !== null) {
            @unlink($this->fixtureDir . 'spiders.txt');
            @rmdir(rtrim($this->fixtureDir, '/'));
            $this->fixtureDir = null;
        }

        parent::tearDown();
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    #[DataProvider('realBrowserUserAgentProvider')]
    public function testRealBrowserIsNotFlaggedAsSpiderAndGetsASession(string $userAgent): void
    {
        $result = $this->loadInitSessions($userAgent, $this->shippedIncludesDir());

        $this->assertFalse($result['spider_flag'], 'A real browser must not be classified as a spider.');
        $this->assertTrue($result['session_started'], 'A real browser must be given a session.');
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    #[DataProvider('crawlerUserAgentProvider')]
    public function testCrawlerIsFlaggedAsSpiderAndIsDeniedASession(string $userAgent): void
    {
        $result = $this->loadInitSessions($userAgent, $this->shippedIncludesDir());

        $this->assertTrue($result['spider_flag'], 'A crawler must be classified as a spider.');
        $this->assertFalse($result['session_started'], 'A crawler must not be given a session.');
    }

    /**
     * The loop skips any pattern shorter than three characters. 'bot', 'lwp' and
     * 'mff' are the three-character entries in the shipped includes/spiders.txt,
     * so the length floor must be "< 3" and never "<= 3". Each user agent here
     * matches exactly one line of the shipped file - the three-character one -
     * so a match can only have come from that pattern.
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    #[DataProvider('threeCharacterPatternProvider')]
    public function testShortestLegitimatePatternsStillMatch(string $userAgent): void
    {
        $result = $this->loadInitSessions($userAgent, $this->shippedIncludesDir());

        $this->assertTrue($result['spider_flag'], 'A three-character spiders.txt pattern must still match.');
        $this->assertFalse($result['session_started'], 'A matched spider must not be given a session.');
    }

    /**
     * The regression guard proper: a stray line in spiders.txt must not take the
     * store down. Under the pre-fix loop a blank, whitespace-only or very short
     * line matched every user agent, so this browser was flagged and denied a
     * session.
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    #[DataProvider('straySpiderFileLineProvider')]
    public function testStrayLineInSpiderFileDoesNotFlagARealBrowser(string $strayLine): void
    {
        $result = $this->loadInitSessions(
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
            $this->makeSpiderFileFixture($strayLine)
        );

        $this->assertFalse($result['spider_flag'], 'A stray spiders.txt line must not classify a browser as a spider.');
        $this->assertTrue($result['session_started'], 'A stray spiders.txt line must not cost a browser its session.');
    }

    /**
     * The other half of the guard: skipping stray lines must not stop the loop,
     * so the real pattern that follows the stray line in the same file still
     * matches.
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    #[DataProvider('straySpiderFileLineProvider')]
    public function testStrayLineInSpiderFileStillLeavesLaterPatternsMatching(string $strayLine): void
    {
        $result = $this->loadInitSessions(
            'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
            $this->makeSpiderFileFixture($strayLine)
        );

        $this->assertTrue($result['spider_flag'], 'A pattern after a stray line must still be reached and matched.');
        $this->assertFalse($result['session_started'], 'A matched spider must not be given a session.');
    }

    public static function realBrowserUserAgentProvider(): array
    {
        return [
            'Chrome on Windows' => ['Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36'],
            'Edge on Windows' => ['Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'],
            'Safari on macOS' => ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.6 Safari/605.1.15'],
            'Firefox on Linux' => ['Mozilla/5.0 (X11; Linux x86_64; rv:133.0) Gecko/20100101 Firefox/133.0'],
            'Safari on iPhone' => ['Mozilla/5.0 (iPhone; CPU iPhone OS 18_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.0 Mobile/15E148 Safari/604.1'],
            'Chrome on Android' => ['Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.6778.81 Mobile Safari/537.36'],
        ];
    }

    public static function crawlerUserAgentProvider(): array
    {
        return [
            'Googlebot' => ['Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'],
            'GPTBot' => ['Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko); compatible; GPTBot/1.2; +https://openai.com/gptbot'],
            'ClaudeBot' => ['Mozilla/5.0 (compatible; ClaudeBot/1.0; +claudebot@anthropic.com)'],
        ];
    }

    public static function threeCharacterPatternProvider(): array
    {
        return [
            "'bot'" => ['SomeThing-bot/1.0'],
            "'lwp'" => ['LWP::Simple/6.72'],
            "'mff'" => ['mff/1.0'],
        ];
    }

    /**
     * Lines that must never be treated as a match pattern.
     *
     * The one- and two-character entries are substrings of 'mozilla', so they do
     * appear in the browser user agent used above: that is deliberate, it is
     * what makes the case fail if the length floor is removed. The blank,
     * whitespace-only, one- and two-character cases each fail against the
     * pre-fix loop. The comment case does not - the old loop searched for the
     * whole '#...' string, which no real user agent contains - so the '#' clause
     * of the guard is defense in depth, and this case is here to pin the
     * contract that spiders.txt supports comments at all.
     */
    public static function straySpiderFileLineProvider(): array
    {
        return [
            'blank line' => [''],
            'whitespace-only line' => ['   '],
            'comment line' => ['# AI crawler tokens below'],
            'one-character line' => ['m'],
            'two-character line' => ['mo'],
        ];
    }

    /**
     * Writes a spiders.txt containing one stray line followed by one real
     * pattern, and returns its directory for use as DIR_WS_INCLUDES. The stray
     * line comes first so that, under the pre-fix loop, it is the line that
     * matches and breaks before 'googlebot' is ever reached.
     */
    private function makeSpiderFileFixture(string $strayLine): string
    {
        $directory = sys_get_temp_dir() . '/zc-spiders-fixture-' . uniqid('', true);
        mkdir($directory, 0777, true);
        $this->fixtureDir = $directory . '/';

        file_put_contents(
            $directory . '/spiders.txt',
            '$Id: spiders.txt fixture $' . "\n" . $strayLine . "\n" . 'googlebot' . "\n"
        );

        return $this->fixtureDir;
    }

    private function shippedIncludesDir(): string
    {
        return dirname(__DIR__, 4) . '/includes/';
    }

    /**
     * Loads init_sessions.php with the spider-blocking branch active and returns
     * the two locals it leaves behind: ['spider_flag' => bool, 'session_started' => bool].
     *
     * SESSION_FORCE_COOKIE_USE is 'False' and SESSION_BLOCK_SPIDERS is 'True', so
     * the spider-matching loop is the branch that runs - unlike
     * SessionHostAddressTest, which forces the cookie branch to avoid starting a
     * session at all. A user agent that is not a spider therefore reaches
     * zen_session_start() and the database-backed save handler, so $GLOBALS['db']
     * is stubbed the way ZenSessionStartTest does it. zen_config() falls back to
     * constant() whenever no configuration repository is loaded, which is the
     * case here, so defining these settings as constants is sufficient.
     *
     * @return array{spider_flag: bool, session_started: bool}
     */
    private function loadInitSessions(string $userAgent, string $includesDir): array
    {
        $rootPath = dirname(__DIR__, 4) . '/';

        define('IS_ADMIN_FLAG', false);
        define('DIR_WS_FUNCTIONS', $rootPath . 'includes/functions/');
        define('DIR_WS_INCLUDES', $includesDir);
        define('TABLE_SESSIONS', 'sessions');
        define('SESSION_WRITE_DIRECTORY', sys_get_temp_dir());
        define('SESSION_FORCE_COOKIE_USE', 'False');
        define('SESSION_BLOCK_SPIDERS', 'True');
        define('SESSION_USE_ROOT_COOKIE_PATH', 'False');
        define('SESSION_ADD_PERIOD_PREFIX', 'False');
        define('HTTP_SERVER', 'http://example.com');
        define('SESSION_IP_TO_HOST_ADDRESS', 'false');
        define('SESSION_CHECK_USER_AGENT', 'False');
        define('SESSION_CHECK_IP_ADDRESS', 'False');

        if (!function_exists('zen_get_ip_address')) {
            eval('function zen_get_ip_address() { return $_SERVER[\'REMOTE_ADDR\']; }');
        }

        require_once $rootPath . 'includes/functions/zen_config.php';
        require_once $rootPath . 'includes/functions/database.php';

        $GLOBALS['db'] = new class {
            public function prepare_input(string $input): string
            {
                return addslashes($input);
            }

            public function bindVars(string $sql, string $placeholder, string|int $value, string $type): string
            {
                return str_replace($placeholder, (string)$value, $sql);
            }

            public function affectedRows(): int
            {
                return 0;
            }

            public function Execute(string $sql): object
            {
                $result = new \stdClass();
                $result->EOF = true;
                $result->fields = [];
                $result->resource = true;

                return $result;
            }
        };

        /**
         * Keep PHP from trying to emit session headers from the CLI SAPI; the
         * cookie parameters themselves are still exercised by init_sessions.php.
         */
        session_cache_limiter('');

        $zenSessionId = 'zenid';
        $cookieDomain = '';
        $_GET = [];
        $_POST = [];
        $_COOKIE = [];
        $_SESSION = [];
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = $userAgent;

        require $rootPath . 'includes/init_includes/init_sessions.php';

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        return ['spider_flag' => $spider_flag, 'session_started' => $session_started];
    }
}
