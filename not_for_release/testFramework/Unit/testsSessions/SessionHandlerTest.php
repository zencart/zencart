<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */

declare(strict_types=1);

namespace Tests\Unit\testsSessions;

use Tests\Support\zcUnitTestCase;
use Zencart\SessionHandler;

class SessionHandlerTest extends zcUnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        require_once DIR_FS_INCLUDES . 'functions/database.php';
        $GLOBALS['SESS_LIFE'] = 1440;
    }

    public function tearDown(): void
    {
        unset($GLOBALS['db']);
        unset($GLOBALS['SESS_LIFE']);

        parent::tearDown();
    }

    public function testOpenAndCloseSucceed(): void
    {
        $handler = new SessionHandler();

        $this->assertTrue($handler->open('/tmp', 'zenid'));
        $this->assertTrue($handler->close());
    }

    public function testReadDecodesAnUnexpiredSessionRecord(): void
    {
        $database = new SessionHandlerDatabaseDouble($this->queryResult(false, ['value' => base64_encode('session data')]));
        $GLOBALS['db'] = $database;

        $this->assertSame('session data', (new SessionHandler())->read('active-session-id'));
        $this->assertCount(1, $database->queries);
        $this->assertStringContainsString("sesskey = 'active-session-id'", $database->queries[0]);
        $this->assertMatchesRegularExpression("/expiry > '\\d+'/", $database->queries[0]);
    }

    public function testReadReturnsAnEmptyStringWhenTheSessionDoesNotExistOrHasExpired(): void
    {
        $database = new SessionHandlerDatabaseDouble($this->queryResult());
        $GLOBALS['db'] = $database;

        $this->assertSame('', (new SessionHandler())->read('missing-session-id'));
        $this->assertCount(1, $database->queries);
    }

    public function testWriteReturnsFalseWhenDatabaseIsUnavailable(): void
    {
        $this->assertFalse((new SessionHandler())->write('session-id', 'session data'));
    }

    public function testWriteStoresEncodedDataAndReturnsTheDatabaseResult(): void
    {
        $database = new SessionHandlerDatabaseDouble($this->queryResult(resource: true));
        $GLOBALS['db'] = $database;

        $this->assertTrue((new SessionHandler())->write('session-id', 'session data'));
        $this->assertCount(1, $database->queries);
        $this->assertStringContainsString('ON DUPLICATE KEY UPDATE', $database->queries[0]);
        $this->assertSame(
            [
                [':zkey', 'session-id', 'string'],
                [':zexpiry', $database->boundVariables[1][1], 'integer'],
                [':zvalue', base64_encode('session data'), 'string'],
            ],
            $database->boundVariables
        );
        $this->assertGreaterThan(time(), $database->boundVariables[1][1]);
    }

    public function testWriteReturnsFalseWhenTheDatabaseWriteFails(): void
    {
        $database = new SessionHandlerDatabaseDouble($this->queryResult());
        $GLOBALS['db'] = $database;

        $this->assertFalse((new SessionHandler())->write('session-id', 'session data'));
    }

    public function testDestroyDeletesTheSessionAndReportsSuccess(): void
    {
        $database = new SessionHandlerDatabaseDouble($this->queryResult(resource: true));
        $GLOBALS['db'] = $database;

        $this->assertTrue((new SessionHandler())->destroy("session'id"));
        $this->assertSame(
            "DELETE FROM " . TABLE_SESSIONS . " WHERE sesskey = 'session\\'id'",
            $database->queries[0]
        );
    }

    public function testGcReturnsTheNumberOfExpiredSessionsRemoved(): void
    {
        $database = new SessionHandlerDatabaseDouble($this->queryResult(resource: true));
        $database->affectedRows = 3;
        $GLOBALS['db'] = $database;

        $this->assertSame(3, (new SessionHandler())->gc(1440));
        $this->assertMatchesRegularExpression('/^DELETE FROM ' . TABLE_SESSIONS . ' WHERE expiry < \\d+$/', $database->queries[0]);
    }

    public function testGcReturnsFalseWhenTheDatabaseDoesNotReportAffectedRows(): void
    {
        $database = new SessionHandlerDatabaseDouble($this->queryResult(resource: true));
        $database->affectedRows = null;
        $GLOBALS['db'] = $database;

        $this->assertFalse((new SessionHandler())->gc(1440));
    }

    public function testValidateIdReturnsFalseWhenDatabaseIsUnavailable(): void
    {
        $this->assertFalse((new SessionHandler())->validateId('session-id'));
    }

    public function testValidateIdAcceptsAnUnexpiredSessionRecord(): void
    {
        $database = $this->setDatabaseResult(false);

        $this->assertTrue((new SessionHandler())->validateId('active-session-id'));
        $this->assertCount(1, $database->queries);
        $this->assertStringContainsString("sesskey = 'active-session-id'", $database->queries[0]);
        $this->assertMatchesRegularExpression("/expiry > '\\d+'/", $database->queries[0]);
    }

    public function testValidateIdRejectsMissingOrExpiredSessionRecords(): void
    {
        $database = $this->setDatabaseResult(true);

        $this->assertFalse((new SessionHandler())->validateId('unknown-or-expired-session-id'));
        $this->assertCount(1, $database->queries);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testStrictModeReplacesAnUnrecognizedSessionId(): void
    {
        $GLOBALS['db'] = $this->strictModeDatabase();

        ini_set('session.use_cookies', '0');
        ini_set('session.use_strict_mode', '1');
        session_cache_limiter('');
        session_id('unrecognized-session-id');
        session_set_save_handler(new SessionHandler(), true);

        $this->assertTrue(session_start());
        $this->assertNotSame('unrecognized-session-id', session_id());

        session_write_close();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testStrictModeKeepsARecognizedSessionId(): void
    {
        $GLOBALS['db'] = $this->strictModeDatabase(['recognized-session-id']);

        ini_set('session.use_cookies', '0');
        ini_set('session.use_strict_mode', '1');
        session_cache_limiter('');
        session_id('recognized-session-id');
        session_set_save_handler(new SessionHandler(), true);

        $this->assertTrue(session_start());
        $this->assertSame('recognized-session-id', session_id());

        session_write_close();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testCreateSidReturnsAValidIdWithoutTouchingTheDatabase(): void
    {
        $database = $this->strictModeDatabase();
        $GLOBALS['db'] = $database;

        ini_set('session.use_cookies', '0');
        session_cache_limiter('');
        session_set_save_handler(new SessionHandler(), true);

        $id = (new SessionHandler())->create_sid();

        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9,-]+$/', $id);
        // A record must NOT be written here: PHP validates a new id for collisions and
        // would treat a pre-existing row as one (see testRegenerateIdDoesNotCollideWithItself).
        $this->assertSame([], $database->queries);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testRegenerateIdDoesNotCollideWithItself(): void
    {
        $database = $this->strictModeDatabase();
        $GLOBALS['db'] = $database;

        $handler = new class extends SessionHandler {
            public int $createSidCalls = 0;

            public function create_sid(): string
            {
                $this->createSidCalls++;

                return parent::create_sid();
            }
        };

        ini_set('session.use_cookies', '0');
        ini_set('session.use_strict_mode', '1');
        session_cache_limiter('');
        session_set_save_handler($handler, true);

        ob_start();
        $this->assertTrue(session_start());
        $_SESSION['x'] = 1;
        $handler->createSidCalls = 0;
        $originalId = session_id();

        $this->assertTrue(session_regenerate_id(true));
        session_write_close();
        ob_end_clean();

        $this->assertNotSame($originalId, session_id());
        // Under strict mode PHP calls validateId() on the freshly created id to detect a collision
        // and retries (up to 3 times) if it appears to exist. A correct create_sid() is called once.
        $this->assertSame(1, $handler->createSidCalls);
        $inserts = array_filter($database->queries, static fn (string $query): bool => str_starts_with(ltrim($query), 'INSERT'));
        $this->assertCount(1, $inserts, 'Only the regenerated session should be written; no orphaned rows.');
    }

    private function setDatabaseResult(bool $endOfFile): SessionHandlerDatabaseDouble
    {
        $database = new SessionHandlerDatabaseDouble($this->queryResult($endOfFile));

        $GLOBALS['db'] = $database;

        return $database;
    }

    private function queryResult(bool $endOfFile = true, array $fields = [], bool $resource = false): \queryFactoryResult
    {
        $result = new \queryFactoryResult(null);
        $result->EOF = $endOfFile;
        $result->fields = $fields;
        $result->resource = $resource;

        return $result;
    }

    private function strictModeDatabase(array $validIds = []): object
    {
        return new class($validIds) {
            public array $queries = [];

            public function __construct(private array $validIds)
            {
            }

            public function prepare_input(string $input): string
            {
                return addslashes($input);
            }

            public function bindVars(string $sql, string $placeholder, string|int $value, string $type): string
            {
                // Mirror queryFactory: string values are quoted, integers are not.
                $bound = ($type === 'string') ? "'" . addslashes((string)$value) . "'" : (string)$value;

                return str_replace($placeholder, $bound, $sql);
            }

            public function affectedRows(): int
            {
                return 0;
            }

            public function Execute(string $sql): \queryFactoryResult
            {
                $this->queries[] = $sql;
                $result = new \queryFactoryResult(null);

                if (str_starts_with(ltrim($sql), 'SELECT') && preg_match("/sesskey = '([^']+)'/", $sql, $matches) === 1 && in_array($matches[1], $this->validIds, true)) {
                    $result->EOF = false;
                }

                if (!str_starts_with(ltrim($sql), 'SELECT')) {
                    $result->resource = true;
                    // Behave like the real table: a written id becomes a known id.
                    if (str_starts_with(ltrim($sql), 'INSERT') && preg_match("/VALUES \\('([^']+)'/", $sql, $matches) === 1) {
                        $this->validIds[] = $matches[1];
                    }
                }

                return $result;
            }
        };
    }
}

class SessionHandlerDatabaseDouble
{
    public array $queries = [];
    public array $boundVariables = [];
    public int|false|null $affectedRows = 0;
    private array $results;

    public function __construct(\queryFactoryResult ...$results)
    {
        $this->results = $results;
    }

    public function prepare_input(string $input): string
    {
        return addslashes($input);
    }

    public function bindVars(string $sql, string $placeholder, string|int $value, string $type): string
    {
        $this->boundVariables[] = [$placeholder, $value, $type];

        return str_replace($placeholder, (string)$value, $sql);
    }

    public function Execute(string $sql): \queryFactoryResult
    {
        $this->queries[] = $sql;

        return array_shift($this->results) ?? new \queryFactoryResult(null);
    }

    public function affectedRows(): int|false|null
    {
        return $this->affectedRows;
    }
}
