<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Support;

use PHPUnit\Framework\TestCase;
use Tests\Support\InProcess\InProcessDatabaseBootstrapper;
use Tests\Support\Traits\ConfigurationSettingsConcerns;
use Tests\Support\Traits\DatabaseConcerns;
use Tests\Support\Traits\GeneralConcerns;

require_once __DIR__ . '/configs/runtime_config.php';

abstract class zcInProcessFeatureTestCase extends TestCase
{
    use ConfigurationSettingsConcerns;
    use DatabaseConcerns;
    use GeneralConcerns;

    public static function setUpBeforeClass(): void
    {
        self::defineInProcessFeatureConstants();
        self::removeLogFiles();
    }

    protected static function setUpInProcessFeatureContext(string $context): void
    {
        self::loadConfigureFile('main');
        self::loadConfigureFile($context);

        if (!defined('TABLE_ADDRESS_BOOK')) {
            require ROOTCWD . 'includes/database_tables.php';
        }

        self::databaseSetup();
        (new InProcessDatabaseBootstrapper())->bootstrap($context);
    }

    protected function tearDown(): void
    {
        $this->cleanupManagedPlugins();
        self::removeProgressFile();
        parent::tearDown();
    }

    protected static function defineInProcessFeatureConstants(): void
    {
        self::defineConstantIfMissing('ZENCART_TESTFRAMEWORK_RUNNING', true);
        self::defineConstantIfMissing('TESTCWD', realpath(__DIR__ . '/../') . '/');
        self::defineConstantIfMissing('ROOTCWD', realpath(__DIR__ . '/../../../') . '/');
        self::defineConstantIfMissing('TEXT_PROGRESS_FINISHED', '');
    }

    protected static function removeProgressFile(): void
    {
        $progressFile = zc_test_config_progress_file(ROOTCWD);
        if (file_exists($progressFile)) {
            unlink($progressFile);
        }
    }

    protected static function removeLogFiles(): void
    {
        $files = glob(zc_test_config_log_directory(ROOTCWD) . '/myDEBUG*') ?: [];
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    protected static function defineConstantIfMissing(string $name, mixed $value): void
    {
        if (!defined($name)) {
            define($name, $value);
        }
    }
}
