<?php

namespace Tests\Services;

use Closure;
use ReflectionClass;
use Tests\Support\Database\TestDb;

require_once dirname(__DIR__) . '/Support/configs/runtime_config.php';

class DatabaseBootstrapper
{
    public function __construct(
        private readonly SeederRunner $seederRunner = new SeederRunner(),
        private readonly ?Closure $installerFactory = null,
    ) {
    }

    public function run(array $mainConfigs = []): void
    {
        $this->loadInstallerDependencies();

        $options = $this->buildInstallerOptions();
        $installer = $this->createInstaller($options);

        if (!$installer->getConnection()) {
            throw new TestFrameworkBootstrapException('Unable to connect to the test database.');
        }

        foreach ($this->sqlFiles() as [$file, $message]) {
            $this->runSqlFile($installer, $file, $message);
        }

        TestDb::resetConnection();
        $this->seederRunner->run('InitialSetupSeeder', $mainConfigs);
    }

    private function loadInstallerDependencies(): void
    {
        require_once ROOTCWD . 'zc_install/includes/classes/class.zcDatabaseInstaller.php';
        require_once ROOTCWD . 'zc_install/includes/functions/general.php';
        require_once ROOTCWD . 'zc_install/includes/functions/password_funcs.php';
    }

    private function buildInstallerOptions(): array
    {
        return [
            'db_host' => DB_SERVER,
            'db_user' => DB_SERVER_USERNAME,
            'db_password' => DB_SERVER_PASSWORD,
            'db_name' => DB_DATABASE,
            'db_charset' => DB_CHARSET,
            'db_prefix' => '',
            'db_type' => DB_TYPE,
        ];
    }

    private function createInstaller(array $options): object
    {
        if ($this->installerFactory !== null) {
            return ($this->installerFactory)($options);
        }

        return new \zcDatabaseInstaller($options);
    }

    private function sqlFiles(): array
    {
        return [
            [ROOTCWD . 'zc_install/sql/install/mysql_zencart.sql', 'Running mysql_zencart.sql'],
            [ROOTCWD . 'zc_install/sql/install/mysql_utf8.sql', 'Running mysql_utf8.sql'],
            [ROOTCWD . 'zc_install/sql/demo/mysql_demo.sql', 'Running mysql_demo.sql'],
        ];
    }

    private function runSqlFile(object $installer, string $file, string $message): void
    {
        if (!file_exists($file)) {
            throw new TestFrameworkBootstrapException(sprintf('SQL bootstrap file not found: %s', $file));
        }

        echo $message . PHP_EOL;

        $result = $installer->parseSqlFile($file, [
            'doJsonProgressLogging' => false,
            'doJsonProgressLoggingFileName' => $this->progressMeterFilename(),
            'id' => 'main',
            'message' => '',
        ]);

        if ($result) {
            throw new TestFrameworkBootstrapException(sprintf('Database bootstrap failed while processing %s.', $file));
        }

        $exceptions = $this->readUpgradeExceptions($installer);
        if ($exceptions !== []) {
            throw new TestFrameworkBootstrapException(
                sprintf('Database bootstrap reported upgrade exceptions while processing %s: %s', $file, implode('; ', $exceptions))
            );
        }
    }

    private function readUpgradeExceptions(object $installer): array
    {
        $reflection = new ReflectionClass($installer);
        if (!$reflection->hasProperty('upgradeExceptions')) {
            return [];
        }

        $property = $reflection->getProperty('upgradeExceptions');
        $property->setAccessible(true);
        $value = $property->getValue($installer);

        return is_array($value) ? array_values(array_filter($value, static fn ($item) => $item !== '')) : [];
    }

    protected function progressMeterFilename(): string
    {
        $filename = zc_test_config_progress_file(ROOTCWD);
        $directory = dirname($filename);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        return $filename;
    }
}
