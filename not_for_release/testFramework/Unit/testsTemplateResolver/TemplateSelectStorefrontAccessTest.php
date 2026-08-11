<?php

declare(strict_types=1);

namespace Tests\Unit\testsTemplateResolver;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\Support\zcUnitTestCase;
use Zencart\Templates\TemplateSelect;

/**
 * getSelectableTemplates() and templateIsSelectable() lazily trigger resolveTemplates(),
 * which scans the filesystem and can INSERT 'base' records - work that belongs to the admin
 * and must never be reachable from a storefront page load. Both methods are therefore gated
 * on IS_ADMIN_FLAG.
 *
 * The unit bootstrap defines IS_ADMIN_FLAG as false, so no constant juggling is needed here;
 * the admin-side behaviour is covered by TemplateSelectInheritedSettingsTest.
 */
#[AllowMockObjectsWithoutExpectations]
#[RunTestsInSeparateProcesses]
class TemplateSelectStorefrontAccessTest extends zcUnitTestCase
{
    private int $executeCallCount = 0;

    public function setUp(): void
    {
        parent::setUp();

        require_once DIR_FS_CATALOG . 'includes/classes/class.base.php';
        require_once DIR_FS_CATALOG . 'includes/classes/db/mysql/query_factory.php';
        require_once DIR_FS_CATALOG . 'includes/classes/TemplateDto.php';
        require_once DIR_FS_CATALOG . 'includes/classes/ResourceLoaders/TemplateResolver.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_templates.php';
        require_once DIR_FS_CATALOG . 'includes/classes/TemplateSelect.php';

        $GLOBALS['installedPlugins'] = [];
        $_SESSION['languages_id'] = 0;

        $GLOBALS['db'] = $this->makeMockDb();

        self::assertFalse(IS_ADMIN_FLAG, 'Precondition: these assertions describe the storefront context.');
    }

    public function testGetSelectableTemplatesIsEmptyOutsideTheAdmin(): void
    {
        $templateSelect = new TemplateSelect();

        $this->assertSame([], $templateSelect->getSelectableTemplates());
    }

    public function testTemplateIsSelectableIsFalseOutsideTheAdmin(): void
    {
        $templateSelect = new TemplateSelect();

        $this->assertFalse($templateSelect->templateIsSelectable('responsive_classic'));
    }

    /**
     * The point of the gate is not the return value but the work it avoids: resolveTemplates()
     * reads plugin_control and can INSERT base records. Only the constructor's initial
     * SELECT may reach the database here.
     */
    public function testNeitherAccessorIssuesFurtherQueriesOutsideTheAdmin(): void
    {
        $templateSelect = new TemplateSelect();
        $queriesAfterConstruction = $this->executeCallCount;

        $templateSelect->getSelectableTemplates();
        $templateSelect->templateIsSelectable('responsive_classic');

        $this->assertSame(
            $queriesAfterConstruction,
            $this->executeCallCount,
            'A storefront page load must not trigger the template-resolution queries.'
        );
    }

    private function makeMockDb(): \queryFactory
    {
        $db = $this->getMockBuilder(\queryFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $db->method('bindVars')->willReturnCallback(
            static fn (string $sql, string $token, $value, string $rule): string => str_replace($token, (string)$value, $sql)
        );

        $db->method('Execute')->willReturnCallback(
            function (): \queryFactoryResult {
                $this->executeCallCount++;

                $result = new \queryFactoryResult(null);
                $result->result = [];
                $result->is_cached = true;
                $result->cursor = 0;
                $result->fields = [];
                $result->EOF = true;

                return $result;
            }
        );

        return $db;
    }
}
