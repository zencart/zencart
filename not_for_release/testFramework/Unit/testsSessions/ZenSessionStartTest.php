<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */

declare(strict_types=1);

namespace Tests\Unit\testsSessions;

use PHPUnit\Framework\TestCase;

class ZenSessionStartTest extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testZenSessionStartEnablesStrictMode(): void
    {
        $rootPath = dirname(__DIR__, 4) . '/';
        define('IS_ADMIN_FLAG', false);
        define('TABLE_SESSIONS', 'sessions');

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

        ini_set('session.use_cookies', '0');
        session_cache_limiter('');
        require $rootPath . 'includes/functions/sessions.php';

        $this->assertTrue(zen_session_start());
        $this->assertSame('1', ini_get('session.use_strict_mode'));

        session_write_close();
    }
}
