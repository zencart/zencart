<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsTemplateResolver;

use Tests\Support\zcUnitTestCase;

class AdminInitTemplatesTest extends zcUnitTestCase
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    private string $repoRoot;

    public function setUp(): void
    {
        parent::setUp();

        $this->repoRoot = realpath(__DIR__ . '/../../../../') . '/';

        require_once $this->repoRoot . 'includes/functions/zen_define_default.php';
        require_once $this->repoRoot . 'includes/classes/class.base.php';
        require_once $this->repoRoot . 'includes/classes/db/mysql/query_factory.php';
        require_once $this->repoRoot . 'includes/classes/TemplateDto.php';
        require_once $this->repoRoot . 'includes/classes/TemplateSelect.php';
        require_once $this->repoRoot . 'includes/classes/ResourceLoaders/TemplateResolver.php';
    }

    public function testAdminInitTemplatesLoadsActiveTemplateWhenTemplateDirStartsEmpty(): void
    {
        if (!defined('CHARSET')) {
            define('CHARSET', 'utf-8');
        }
        if (!defined('HEADER_TITLE_TOP')) {
            define('HEADER_TITLE_TOP', 'Admin Home');
        }
        if (!defined('TEXT_ADMIN_TAB_PREFIX')) {
            define('TEXT_ADMIN_TAB_PREFIX', 'Admin');
        }
        if (!defined('STORE_NAME')) {
            define('STORE_NAME', 'Zen Cart');
        }

        $_SESSION['languages_id'] = 0;
        $_GET = [];

        $db = $this->getMockBuilder(\queryFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $db->method('Execute')
            ->willReturnCallback(function (string $sql): \queryFactoryResult {
                if (stripos($sql, 'FROM ' . TABLE_TEMPLATE_SELECT) !== false) {
                    return $this->makeQueryResult([[
                        'template_id' => 1,
                        'template_dir' => 'responsive_classic',
                        'template_language' => 0,
                        'template_settings' => null,
                    ]]);
                }

                if (stripos($sql, 'plugin_control') !== false) {
                    return $this->makeQueryResult([]);
                }

                return $this->makeQueryResult([]);
            });
        $GLOBALS['db'] = $db;

        $PHP_SELF = 'index.php';
        $template_dir = '';

        include $this->repoRoot . 'admin/includes/init_includes/init_templates.php';

        $this->assertSame(
            'responsive_classic',
            $template_dir,
            'Expected admin init_templates to resolve the active template when $template_dir starts empty.'
        );
        $this->assertSame('includes/templates/responsive_classic/', DIR_WS_TEMPLATE);
    }

    private function makeQueryResult(array $rows): \queryFactoryResult
    {
        $result = new \queryFactoryResult(null);
        $result->result = $rows;
        $result->is_cached = true;
        $result->cursor = 0;
        $result->fields = $rows[0] ?? [];
        $result->EOF = ($rows === []);

        return $result;
    }
}
