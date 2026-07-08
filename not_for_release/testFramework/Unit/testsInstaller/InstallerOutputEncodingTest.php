<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsInstaller;

use Tests\Support\zcUnitTestCase;

class InstallerOutputEncodingTest extends zcUnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        require_once DIR_FS_CATALOG . 'zc_install/includes/functions/general.php';
    }

    public function testInstallerHtmlEscapeEncodesAttributeBreakingPayload(): void
    {
        $payload = '"><script>alert(1)</script>\'';

        $escaped = zc_install_escape_html($payload);

        $this->assertSame('&quot;&gt;&lt;script&gt;alert(1)&lt;/script&gt;&#039;', $escaped);
        $this->assertStringNotContainsString('<script>', $escaped);
        $this->assertStringNotContainsString('"', $escaped);
        $this->assertStringNotContainsString("'", $escaped);
    }

    public function testAdminDirectoryNormalizationKeepsOnlySafeApexDirectoryNames(): void
    {
        $this->assertSame('admin', zc_install_normalize_admin_directory(' admin '));
        $this->assertSame('my-admin_123', zc_install_normalize_admin_directory('my-admin_123'));
        $this->assertSame('my-admin_123', zc_install_normalize_admin_directory('my\-admin_123'));

        $this->assertNull(zc_install_normalize_admin_directory(''));
        $this->assertNull(zc_install_normalize_admin_directory('.admin'));
        $this->assertNull(zc_install_normalize_admin_directory('../admin'));
        $this->assertNull(zc_install_normalize_admin_directory('admin/includes'));
        $this->assertNull(zc_install_normalize_admin_directory('admin"><script>alert(1)</script>'));
        $this->assertNull(zc_install_normalize_admin_directory('admin&amp;copy'));
        $this->assertNull(zc_install_normalize_admin_directory(['admin']));
    }

    public function testHiddenPostRendererAllowlistsFieldsAndEscapesValues(): void
    {
        $hiddenFields = zc_install_render_hidden_post_fields([
            'action' => 'process',
            'db_host' => '"><script>alert(1)</script>',
            'adminDir' => 'admin',
            'bad" autofocus="autofocus' => 'bad',
            'unexpected' => '"><img src=x onerror=alert(1)>',
            'db_user' => ['not scalar'],
        ], ['action']);

        $this->assertStringContainsString(
            '<input type="hidden" name="db_host" value="&quot;&gt;&lt;script&gt;alert(1)&lt;/script&gt;">',
            $hiddenFields
        );
        $this->assertStringContainsString('<input type="hidden" name="adminDir" value="admin">', $hiddenFields);
        $this->assertStringNotContainsString('<script>', $hiddenFields);
        $this->assertStringNotContainsString('bad" autofocus', $hiddenFields);
        $this->assertStringNotContainsString('unexpected', $hiddenFields);
        $this->assertStringNotContainsString('db_user', $hiddenFields);
    }

    public function testSelectOptionsEscapeOptionValuesAndLabels(): void
    {
        $options = zen_get_select_options([
            [
                'id' => 'en"><script>alert(1)</script>',
                'text' => '<b>English</b>',
            ],
        ], '');

        $this->assertStringContainsString(
            '<option value="en&quot;&gt;&lt;script&gt;alert(1)&lt;/script&gt;">&lt;b&gt;English&lt;/b&gt;</option>',
            $options
        );
        $this->assertStringNotContainsString('<script>', $options);
        $this->assertStringNotContainsString('<b>English</b>', $options);
    }

    public function testDatabaseTemplateEscapesPostedHiddenFieldsAndInputValues(): void
    {
        $this->defineInstallerTemplateConstants();

        $_POST = [
            'action' => 'process',
            'db_host' => '"><script>alert(1)</script>',
            'adminDir' => 'admin',
            'bad" autofocus="autofocus' => 'bad',
            'unexpected' => '"><img src=x onerror=alert(1)>',
        ];
        $installer_lng = 'en_us';
        $db_host = $_POST['db_host'];
        $db_user = 'user"><img src=x onerror=alert(1)>';
        $db_password = 'pass"><script>alert(2)</script>';
        $db_name = 'store"><script>alert(3)</script>';
        $db_prefix = 'zen_"><script>alert(4)</script>';
        $install_demo_data = false;
        $sqlCacheTypeOptions = '<option value="none">No SQL Caching</option>';

        ob_start();
        include DIR_FS_CATALOG . 'zc_install/includes/template/templates/database_default.php';
        $html = ob_get_clean();

        $this->assertStringContainsString('&quot;&gt;&lt;script&gt;alert(1)&lt;/script&gt;', $html);
        $this->assertStringContainsString('&quot;&gt;&lt;img src=x onerror=alert(1)&gt;', $html);
        $this->assertStringNotContainsString('value=""><script>alert(1)</script>"', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringNotContainsString('<script>alert(2)</script>', $html);
        $this->assertStringNotContainsString('<script>alert(3)</script>', $html);
        $this->assertStringNotContainsString('<script>alert(4)</script>', $html);
        $this->assertStringNotContainsString('bad" autofocus="autofocus', $html);
        $this->assertStringNotContainsString('unexpected', $html);
    }

    private function defineInstallerTemplateConstants(): void
    {
        if (!defined('DIR_FS_INSTALL')) {
            define('DIR_FS_INSTALL', DIR_FS_CATALOG . 'zc_install/');
        }
        if (!defined('DIR_WS_INSTALL_TEMPLATE')) {
            define('DIR_WS_INSTALL_TEMPLATE', 'includes/template/');
        }

        $constants = [
            'TEXT_CONTINUE' => 'Continue',
            'TEXT_DATABASE_SETUP_CONNECTION_ERROR_DIALOG_TITLE' => 'There are some problems',
            'TEXT_DATABASE_SETUP_SETTINGS' => 'Basic Settings',
            'TEXT_DATABASE_SETUP_DB_HOST' => 'Database Host',
            'TEXT_DATABASE_SETUP_DB_USER' => 'Database User',
            'TEXT_DATABASE_SETUP_DB_PASSWORD' => 'Database Password',
            'TEXT_DATABASE_SETUP_DB_NAME' => 'Database Name',
            'TEXT_DATABASE_SETUP_DEMO_SETTINGS' => 'Demo Data',
            'TEXT_DATABASE_SETUP_LOAD_DEMO' => 'Load Demo Data',
            'TEXT_DATABASE_SETUP_LOAD_DEMO_DESCRIPTION' => 'Load demo data?',
            'TEXT_DATABASE_SETUP_ADVANCED_SETTINGS' => 'Advanced Settings',
            'TEXT_DATABASE_SETUP_DB_PREFIX' => 'Store Prefix',
            'TEXT_DATABASE_SETUP_SQL_CACHE_METHOD' => 'SQL Cache Method',
            'TEXT_DATABASE_SETUP_JSCRIPT_SQL_ERRORS1' => 'Some errors occurred when running the SQL install file ',
            'TEXT_DATABASE_SETUP_JSCRIPT_SQL_ERRORS2' => '<br>Please see error logs for more details',
            'TEXT_HELP_CONTENT_DBHOST' => 'DB host help',
            'TEXT_HELP_CONTENT_DBUSER' => 'DB user help',
            'TEXT_HELP_CONTENT_DBPASSWORD' => 'DB password help',
            'TEXT_HELP_CONTENT_DBNAME' => 'DB name help',
            'TEXT_EXAMPLE_DB_HOST' => 'localhost',
            'TEXT_EXAMPLE_DB_USER' => 'dbuser',
            'TEXT_EXAMPLE_DB_PWD' => 'password',
            'TEXT_EXAMPLE_DB_NAME' => 'zencart',
            'TEXT_EXAMPLE_DB_PREFIX' => 'zen_',
        ];

        foreach ($constants as $name => $value) {
            if (!defined($name)) {
                define($name, $value);
            }
        }
    }
}
