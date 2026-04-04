<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsSundry;

use Closure;
use PHPUnit\Framework\TestCase;
use Tests\Services\DatabaseBootstrapper;
use Tests\Services\SeederRunner;
use Tests\Services\TestFrameworkBootstrapException;

class DatabaseBootstrapperTest extends TestCase
{
    private static string $rootPath;
    private static bool $ownsRootPath = false;

    public static function setUpBeforeClass(): void
    {
        self::$rootPath = defined('ROOTCWD')
            ? ROOTCWD
            : sys_get_temp_dir() . '/zc-bootstrap-' . uniqid('', true) . '/';

        self::$ownsRootPath = !defined('ROOTCWD');

        foreach ([
            self::$rootPath . 'zc_install/sql/install',
            self::$rootPath . 'zc_install/sql/demo',
            self::$rootPath . 'zc_install/includes/classes',
            self::$rootPath . 'zc_install/includes/functions',
        ] as $directory) {
            if (!is_dir($directory)) {
                mkdir($directory, 0777, true);
            }
        }

        file_put_contents(self::$rootPath . 'zc_install/sql/install/mysql_zencart.sql', "-- install\n");
        file_put_contents(self::$rootPath . 'zc_install/sql/install/mysql_utf8.sql', "-- charset\n");
        file_put_contents(self::$rootPath . 'zc_install/sql/demo/mysql_demo.sql', "-- demo\n");
        file_put_contents(self::$rootPath . 'zc_install/includes/classes/class.zcDatabaseInstaller.php', "<?php\n");
        file_put_contents(self::$rootPath . 'zc_install/includes/functions/general.php', "<?php\n");
        file_put_contents(self::$rootPath . 'zc_install/includes/functions/password_funcs.php', "<?php\n");

        if (!defined('ROOTCWD')) {
            define('ROOTCWD', self::$rootPath);
        }
        if (!defined('DB_SERVER')) {
            define('DB_SERVER', '127.0.0.1');
        }
        if (!defined('DB_SERVER_USERNAME')) {
            define('DB_SERVER_USERNAME', 'root');
        }
        if (!defined('DB_SERVER_PASSWORD')) {
            define('DB_SERVER_PASSWORD', 'root');
        }
        if (!defined('DB_DATABASE')) {
            define('DB_DATABASE', 'db');
        }
        if (!defined('DB_CHARSET')) {
            define('DB_CHARSET', 'utf8mb4');
        }
        if (!defined('DB_TYPE')) {
            define('DB_TYPE', 'mysql');
        }
    }

    public static function tearDownAfterClass(): void
    {
        if (!self::$ownsRootPath) {
            return;
        }

        foreach ([
            'zc_install/includes/classes/class.zcDatabaseInstaller.php',
            'zc_install/includes/functions/general.php',
            'zc_install/includes/functions/password_funcs.php',
            'zc_install/sql/install/mysql_zencart.sql',
            'zc_install/sql/install/mysql_utf8.sql',
            'zc_install/sql/demo/mysql_demo.sql',
        ] as $file) {
            $path = self::$rootPath . $file;
            if (file_exists($path)) {
                unlink($path);
            }
        }

        foreach ([
            'zc_install/includes/classes',
            'zc_install/includes/functions',
            'zc_install/sql/install',
            'zc_install/sql/demo',
            'zc_install/includes',
            'zc_install/sql',
            'zc_install',
        ] as $directory) {
            $path = self::$rootPath . $directory;
            if (is_dir($path)) {
                rmdir($path);
            }
        }

        if (is_dir(rtrim(self::$rootPath, '/'))) {
            rmdir(rtrim(self::$rootPath, '/'));
        }
    }

    protected function tearDown(): void
    {
        putenv('ZC_TEST_WORKER');
        putenv('TEST_TOKEN');

        parent::tearDown();
    }

    public function testRunExecutesSqlFilesAndSeedsInitialSetup(): void
    {
        $installer = new class {
            public array $files = [];
            public array $options = [];
            protected array $upgradeExceptions = [];

            public function getConnection(): bool
            {
                return true;
            }

            public function parseSqlFile($fileName, ?array $options = null): bool
            {
                $this->files[] = $fileName;
                $this->options[] = $options ?? [];
                return false;
            }
        };

        $seederRunner = $this->createMock(SeederRunner::class);
        $seederRunner->expects($this->once())
            ->method('run')
            ->with('InitialSetupSeeder', ['mailserver-host' => 'localhost']);

        $bootstrapper = new DatabaseBootstrapper(
            $seederRunner,
            static fn (array $options) => $installer
        );

        $bootstrapper->run(['mailserver-host' => 'localhost']);

        $this->assertCount(3, $installer->files);
        $this->assertSame(self::$rootPath . 'zc_install/sql/install/mysql_zencart.sql', $installer->files[0]);
        $this->assertSame(self::$rootPath . 'zc_install/sql/install/mysql_utf8.sql', $installer->files[1]);
        $this->assertSame(self::$rootPath . 'zc_install/sql/demo/mysql_demo.sql', $installer->files[2]);
        $this->assertSame(ROOTCWD . 'progress.json', $installer->options[0]['doJsonProgressLoggingFileName']);
    }

    public function testRunUsesWorkerScopedProgressFileWhenWorkerIsConfigured(): void
    {
        putenv('ZC_TEST_WORKER=2');

        $installer = new class {
            public array $options = [];
            protected array $upgradeExceptions = [];

            public function getConnection(): bool
            {
                return true;
            }

            public function parseSqlFile($fileName, ?array $options = null): bool
            {
                $this->options[] = $options ?? [];
                return false;
            }
        };

        $bootstrapper = new DatabaseBootstrapper(
            $this->createMock(SeederRunner::class),
            static fn (array $options) => $installer
        );

        $bootstrapper->run([]);

        $this->assertSame(ROOTCWD . 'progress_2.json', $installer->options[0]['doJsonProgressLoggingFileName']);
    }

    public function testRunCreatesParentDirectoryForWorkerScopedProgressFile(): void
    {
        $progressDirectory = rtrim(self::$rootPath, '/') . '/nested-progress';
        $progressFile = $progressDirectory . '/progress_2.json';

        if (is_dir($progressDirectory)) {
            rmdir($progressDirectory);
        }

        putenv('ZC_TEST_WORKER=2');
        putenv('ZC_TEST_DB_DATABASE');

        $installer = new class {
            public array $options = [];
            protected array $upgradeExceptions = [];

            public function getConnection(): bool
            {
                return true;
            }

            public function parseSqlFile($fileName, ?array $options = null): bool
            {
                $this->options[] = $options ?? [];
                return false;
            }
        };

        $bootstrapper = new class($this->createMock(SeederRunner::class), static fn (array $options) => $installer, $progressFile) extends DatabaseBootstrapper {
            public function __construct(
                SeederRunner $seederRunner,
                ?Closure $installerFactory,
                private readonly string $progressFile
            ) {
                parent::__construct($seederRunner, $installerFactory);
            }

            protected function progressMeterFilename(): string
            {
                $directory = dirname($this->progressFile);
                if (!is_dir($directory)) {
                    mkdir($directory, 0777, true);
                }

                return $this->progressFile;
            }
        };

        $bootstrapper->run([]);

        $this->assertDirectoryExists($progressDirectory);
        $this->assertSame($progressFile, $installer->options[0]['doJsonProgressLoggingFileName']);
    }

    public function testRunThrowsWhenDatabaseConnectionFails(): void
    {
        $installer = new class {
            protected array $upgradeExceptions = [];

            public function getConnection(): bool
            {
                return false;
            }

            public function parseSqlFile($fileName, ?array $options = null): bool
            {
                return false;
            }
        };

        $this->expectException(TestFrameworkBootstrapException::class);
        $this->expectExceptionMessage('Unable to connect to the test database.');

        (new DatabaseBootstrapper(
            $this->createMock(SeederRunner::class),
            static fn (array $options) => $installer
        ))->run([]);
    }

    public function testRunThrowsWhenSqlFileProcessingReturnsError(): void
    {
        $installer = new class {
            protected array $upgradeExceptions = [];

            public function getConnection(): bool
            {
                return true;
            }

            public function parseSqlFile($fileName, ?array $options = null): bool
            {
                return str_ends_with($fileName, 'mysql_utf8.sql');
            }
        };

        $this->expectException(TestFrameworkBootstrapException::class);
        $this->expectExceptionMessage('Database bootstrap failed while processing');

        (new DatabaseBootstrapper(
            $this->createMock(SeederRunner::class),
            static fn (array $options) => $installer
        ))->run([]);
    }

    public function testRunThrowsWhenInstallerReportsUpgradeExceptions(): void
    {
        $installer = new class {
            protected array $upgradeExceptions = [];

            public function getConnection(): bool
            {
                return true;
            }

            public function parseSqlFile($fileName, ?array $options = null): bool
            {
                if (str_ends_with($fileName, 'mysql_demo.sql')) {
                    $this->upgradeExceptions = ['duplicate key'];
                }

                return false;
            }
        };

        $this->expectException(TestFrameworkBootstrapException::class);
        $this->expectExceptionMessage('Database bootstrap reported upgrade exceptions');

        (new DatabaseBootstrapper(
            $this->createMock(SeederRunner::class),
            static fn (array $options) => $installer
        ))->run([]);
    }
}
