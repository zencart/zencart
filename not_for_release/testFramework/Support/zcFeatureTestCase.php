<?php
/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Support;

use Tests\Support\Traits\ConfigurationSettingsConcerns;
use Tests\Support\Traits\DiscountCouponConcerns;
use Tests\Support\Traits\LowOrderFeeConcerns;
use Tests\Support\Traits\CustomerAccountConcerns;
use Tests\Support\Traits\DatabaseConcerns;
use Tests\Support\Traits\GeneralConcerns;
use Tests\Support\Traits\LogFileConcerns;

require_once __DIR__ . '/configs/runtime_config.php';

/**
 *
 */
abstract class zcFeatureTestCase extends zcInProcessFeatureTestCase
{
    use DatabaseConcerns, GeneralConcerns, CustomerAccountConcerns, ConfigurationSettingsConcerns, LogFileConcerns, LowOrderFeeConcerns, DiscountCouponConcerns;

    /**
     * @return void
     *
     * set some defines where necessary
     */
    public static function setUpBeforeClass(): void
    {
        self::defineFeatureTestConstants();
        self::removeLogFiles();
    }

    public function tearDown(): void
    {
        self::removeProgressFile();
        parent::tearDown();
    }

    protected static function setUpFeatureTestContext(string $context): void
    {
        $mainConfigs = self::loadConfigureFile('main');
        self::loadConfigureFile($context);

        if (!defined('TABLE_ADDRESS_BOOK')) {
            require ROOTCWD . 'includes/database_tables.php';
        }

        self::loadMigrationAndSeeders($mainConfigs);
    }

    protected static function defineFeatureTestConstants(): void
    {
        self::defineInProcessFeatureConstants();
    }

    protected static function removeLogFiles(): void
    {
        $files = glob(zc_test_config_log_directory(ROOTCWD) . '/myDEBUG*') ?: [];
        foreach ($files as $file) {
            if (is_file($file)) {
                self::moveLogFileForArtifacts($file);
                unlink($file);
            }
        }
    }

    protected static function moveLogFileForArtifacts(string $file): void
    {
        $context = 'store';
        if (str_starts_with(basename($file), 'myDEBUG-adm')) {
            $context = 'admin';
        }

        $artifactDirectory = zc_test_config_artifact_directory(ROOTCWD, $context);
        if (!is_dir($artifactDirectory)) {
            mkdir($artifactDirectory, 0777, true);
        }

        copy($file, $artifactDirectory . basename($file));
    }

}
