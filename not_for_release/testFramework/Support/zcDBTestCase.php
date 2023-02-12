<?php
/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Support;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestResult;
use Tests\Support\Traits\CrossConcerns;

/**
 *
 */
abstract class zcDBTestCase extends TestCase
{

    use CrossConcerns;
    /**
     * @param TestResult|null $result
     * @return TestResult
     *
     * This allows us to run in full isolation mode including
     * classes, functions, and defined statements
     */
    public function run(TestResult $result = null): TestResult
    {
        $this->setPreserveGlobalState(false);
        return parent::run($result);
    }

    /**
     * @return void
     *
     * set some defines where necessary
     */
    public function setUp(): void
    {
        if (!defined('IS_ADMIN_FLAG')) {
            define('IS_ADMIN_FLAG', false);
        }

        if (!defined('DIR_FS_CATALOG')) {
            define('DIR_FS_CATALOG', realpath(__DIR__ . '/../../../') . '/');
        }
        if (!defined('DIR_FS_INCLUDES')) {
            define('DIR_FS_INCLUDES', DIR_FS_CATALOG . 'includes/');
        }
        if (!defined('DIR_WS_CATALOG')) {
            define('DIR_WS_CATALOG', '/');
        }

        if (!defined('DIR_WS_ADMIN')) {
            define('DIR_WS_ADMIN', '/admin/');
        }
        if (!defined('DIR_FS_ADMIN')) {
            define('DIR_FS_ADMIN', DIR_FS_CATALOG . 'admin/');
        }
        if (!defined('DIR_WS_HTTPS_CATALOG')) {
            define('DIR_WS_HTTPS_CATALOG', '/ssl/');
        }

       require_once(DIR_FS_INCLUDES . 'database_tables.php');
    }

 }
