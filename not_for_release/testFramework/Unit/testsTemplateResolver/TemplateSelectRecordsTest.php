<?php

declare(strict_types=1);

namespace Tests\Unit\testsTemplateResolver;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\Support\InMemoryTemplateSelectDatabase;
use Tests\Support\zcUnitTestCase;
use Zencart\Templates\TemplateSelect;

/**
 * Covers TemplateSelect's handling of `template_select` records: how the table is split into
 * active and base rows, and the register, rename, deregister and settings-write paths.
 */
#[AllowMockObjectsWithoutExpectations]
#[RunTestsInSeparateProcesses]
class TemplateSelectRecordsTest extends zcUnitTestCase
{
    use InMemoryTemplateSelectDatabase;

    public function setUp(): void
    {
        // Must precede the bootstrap, which defaults IS_ADMIN_FLAG to false.
        if (!defined('IS_ADMIN_FLAG')) {
            define('IS_ADMIN_FLAG', true);
        }

        parent::setUp();

        require_once DIR_FS_CATALOG . 'includes/classes/class.base.php';
        require_once DIR_FS_CATALOG . 'includes/classes/db/mysql/query_factory.php';
        require_once DIR_FS_CATALOG . 'includes/classes/TemplateDto.php';
        require_once DIR_FS_CATALOG . 'includes/classes/ResourceLoaders/TemplateResolver.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_templates.php';
        require_once DIR_FS_CATALOG . 'includes/classes/TemplateSelect.php';

        $GLOBALS['installedPlugins'] = [];
        $_SESSION['languages_id'] = 0;

        $this->rows = [
            1 => ['template_id' => '1', 'template_dir' => 'responsive_classic', 'template_language' => '0', 'template_settings' => null],
            2 => ['template_id' => '2', 'template_dir' => 'responsive_classic', 'template_language' => '-1', 'template_settings' => null],
            3 => ['template_id' => '3', 'template_dir' => 'classic', 'template_language' => '3', 'template_settings' => null],
            4 => ['template_id' => '4', 'template_dir' => 'classic', 'template_language' => '-1', 'template_settings' => '{"K":"v"}'],
            5 => ['template_id' => '5', 'template_dir' => 'template_default', 'template_language' => '-1', 'template_settings' => '{"X":"dflt"}'],
        ];
        $this->nextId = 5;

        $GLOBALS['db'] = $this->makeMockDb();
    }

    public function testRowsAreSplitIntoActiveAndBaseTemplates(): void
    {
        $templateSelect = new TemplateSelect();

        $this->assertSame([0, 3], array_keys($templateSelect->getAllActiveTemplates()));
        $this->assertSame('responsive_classic', $templateSelect->getActiveTemplateDir());
        $this->assertTrue($templateSelect->isActiveTemplate('classic'));
        $this->assertTrue($templateSelect->isActiveTemplate('responsive_classic'));
        $this->assertFalse($templateSelect->isActiveTemplate('template_default'));
        $this->assertTrue(\Zencart\Templates\TemplateDto::getInstance()->getTemplate('responsive_classic')['is_active'] ?? null);
        $this->assertSame('classic', $templateSelect->getTemplateDirForLanguage(3));
        $this->assertSame('classic', $templateSelect->getTemplateDirForLanguage('3'));
        $this->assertSame('responsive_classic', $templateSelect->getTemplateDirForLanguage(99));

        $_SESSION['languages_id'] = 3;
        $this->assertSame('classic', $templateSelect->getActiveTemplateDir());
    }

    public function testRegisterNewTemplateValidatesItsInputs(): void
    {
        $templateSelect = new TemplateSelect();

        $this->assertFalse($templateSelect->registerNewTemplate('', 4));
        $this->assertFalse($templateSelect->registerNewTemplate('classic', 0));
        $this->assertFalse($templateSelect->registerNewTemplate('classic', -1));
        $this->assertFalse($templateSelect->registerNewTemplate('classic', 3), 'language 3 already has a template');
        $this->assertSame([], $this->executedInserts());
    }

    public function testRegisterNewTemplateInsertsAndTracksTheNewRow(): void
    {
        $templateSelect = new TemplateSelect();

        $id = $templateSelect->registerNewTemplate('responsive_classic', 1);

        $this->assertSame(6, $id);
        $this->assertSame(
            ['template_id' => '6', 'template_dir' => 'responsive_classic', 'template_language' => '1', 'template_settings' => null],
            $templateSelect->getAllActiveTemplates()[1]
        );
        $this->assertSame('responsive_classic', $this->rows[6]['template_dir']);
        $this->assertCount(1, $this->executedInserts());
        $this->assertStringContainsString('INSERT INTO ' . TABLE_TEMPLATE_SELECT, $this->executedInserts()[0]);
        $this->assertFalse($templateSelect->registerNewTemplate('classic', 1), 'language 1 is now taken');
    }

    public function testBaseRowsAreNotTrackedAsActiveTemplates(): void
    {
        $templateSelect = $this->exposeTemplateSelect();

        $id = $this->invoke($templateSelect, 'addTemplateToDb', 'zz_new_template', -1);

        $this->assertSame(6, $id);
        $this->assertSame([0, 3], array_keys($templateSelect->getAllActiveTemplates()));
        $this->assertSame('6', $this->invoke($templateSelect, 'getBaseTemplateField', 'zz_new_template', 'template_id'));
        $this->assertNull($this->invoke($templateSelect, 'getBaseTemplateField', 'responsive_classic', 'no_such_field'));
        $this->assertNull($this->invoke($templateSelect, 'getBaseTemplateField', 'unknown_dir', 'template_id'));
    }

    public function testUpdateTemplateNameForIdRejectsBadAndUnknownRows(): void
    {
        $templateSelect = new TemplateSelect();

        $this->assertSame(TemplateSelect::SETTINGS_BAD_INPUTS, $templateSelect->updateTemplateNameForId(3, ''));
        $this->assertSame(TemplateSelect::SETTINGS_BAD_INPUTS, $templateSelect->updateTemplateNameForId(-1, 'classic'));
        $this->assertSame(TemplateSelect::SETTINGS_NO_UPDATE, $templateSelect->updateTemplateNameForId(0, 'classic'));
        $this->assertSame(TemplateSelect::SETTINGS_NO_UPDATE, $templateSelect->updateTemplateNameForId(99, 'classic'));
        $this->assertSame(TemplateSelect::SETTINGS_NO_UPDATE, $templateSelect->updateTemplateNameForId(2, 'classic'), 'base rows cannot be renamed');
        $this->assertSame([], $this->executedUpdates());
    }

    public function testUpdateTemplateNameForIdChangesOnlyTheTargetedRow(): void
    {
        $templateSelect = new TemplateSelect();

        $status = $templateSelect->updateTemplateNameForId(3, 'template_default');

        $this->assertSame(TemplateSelect::SETTINGS_OK, $status);
        $this->assertSame('template_default', $this->rows[3]['template_dir']);
        $this->assertSame('responsive_classic', $this->rows[1]['template_dir']);
        $this->assertSame('template_default', $templateSelect->getAllActiveTemplates()[3]['template_dir']);
        $this->assertSame('responsive_classic', $templateSelect->getAllActiveTemplates()[0]['template_dir']);
        $this->assertSame('template_default', $templateSelect->getTemplateDirForLanguage(3));
        $this->assertCount(1, $this->executedUpdates());
        $this->assertStringContainsString('UPDATE ' . TABLE_TEMPLATE_SELECT, $this->executedUpdates()[0]);
        $this->assertStringContainsString('template_language != -1', $this->executedUpdates()[0]);
        $this->assertSame(TemplateSelect::SETTINGS_OK, $templateSelect->updateTemplateNameForId(1, 'classic'));
        $this->assertSame('classic', $this->rows[1]['template_dir']);
    }

    public function testDeregisterTemplateIdOnlyRemovesNonDefaultActiveRows(): void
    {
        $templateSelect = new TemplateSelect();

        $this->assertFalse($templateSelect->deregisterTemplateId(0));
        $this->assertFalse($templateSelect->deregisterTemplateId(-4));
        $this->assertFalse($templateSelect->deregisterTemplateId(1), 'the default-language row is permanent');
        $this->assertFalse($templateSelect->deregisterTemplateId(2), 'base rows are permanent');
        $this->assertFalse($templateSelect->deregisterTemplateId(99));
        $this->assertSame([], $this->executedDeletes());

        $this->assertTrue($templateSelect->deregisterTemplateId(3));

        $this->assertSame([0], array_keys($templateSelect->getAllActiveTemplates()));
        $this->assertArrayNotHasKey(3, $this->rows);
        $this->assertArrayHasKey(1, $this->rows);
        $this->assertArrayHasKey(4, $this->rows);
        $this->assertCount(1, $this->executedDeletes());
        $this->assertStringContainsString('DELETE FROM ' . TABLE_TEMPLATE_SELECT, $this->executedDeletes()[0]);
        $this->assertFalse($templateSelect->deregisterTemplateId(3), 'already removed');
    }

    public function testTemplateSettingsAreWrittenOnlyToBaseRecords(): void
    {
        $templateSelect = $this->exposeTemplateSelect();

        $this->assertSame(TemplateSelect::SETTINGS_NO_UPDATE, $this->invoke($templateSelect, 'updateDbTemplateSettings', 1, ['A' => 'b']));
        $this->assertSame(TemplateSelect::SETTINGS_NO_UPDATE, $this->invoke($templateSelect, 'updateDbTemplateSettings', 99, ['A' => 'b']));
        $this->assertSame([], $this->executedUpdates());

        $this->assertSame(TemplateSelect::SETTINGS_OK, $this->invoke($templateSelect, 'updateDbTemplateSettings', 2, ['A' => 'b']));
        $this->assertSame('{"A":"b"}', stripslashes($this->rows[2]['template_settings']));
        $this->assertStringContainsString('UPDATE ' . TABLE_TEMPLATE_SELECT, $this->executedUpdates()[0]);
        $this->assertStringContainsString('template_language = -1', $this->executedUpdates()[0]);
        $this->assertSame(['A' => 'b'], $templateSelect->getTemplateSettings('responsive_classic'));
    }

    public function testEmptyOrNullSettingsAreStoredAsNull(): void
    {
        $templateSelect = new TemplateSelect();

        $this->assertSame(TemplateSelect::SETTINGS_OK, $templateSelect->setTemplateSettings('classic', []));
        $this->assertNull($this->rows[4]['template_settings']);
        $this->assertNull($templateSelect->getTemplateSettings('classic'));

        $templateSelect->setTemplateSettings('classic', ['K' => 'again']);
        $this->assertSame('{"K":"again"}', stripslashes($this->rows[4]['template_settings']));
        $this->assertSame(TemplateSelect::SETTINGS_OK, $templateSelect->setTemplateSettings('classic', null));
        $this->assertNull($this->rows[4]['template_settings']);
        $this->assertNull($templateSelect->getTemplateSettings('classic'));
    }

    public function testUndecodableSettingsAreTreatedAsNoSettings(): void
    {
        $this->rows[4]['template_settings'] = '{not json';
        $this->rows[2]['template_settings'] = '"a string"';
        $templateSelect = new TemplateSelect();

        $this->assertNull($templateSelect->getTemplateSettings('classic'));
        $this->assertNull($templateSelect->getTemplateSettings('responsive_classic'));
        $this->assertNull($templateSelect->getTemplateSettings('unknown_dir'));
    }

    public function testInheritedSettingsIgnoreTemplateDefault(): void
    {
        $templateSelect = new TemplateSelect();

        $this->assertSame('fallback', $templateSelect->getInheritedSetting('responsive_classic', 'X', 'fallback'));
    }

    public function testResolvingTemplatesAddsBaseRecordsForEverySelectableTemplateOnce(): void
    {
        $templateSelect = new TemplateSelect();
        $templatesWithBaseRow = array_column(
            array_filter($this->rows, static fn(array $row): bool => $row['template_language'] === '-1'),
            'template_dir'
        );

        $selectable = $templateSelect->getSelectableTemplates();

        $this->assertArrayHasKey('responsive_classic', $selectable);
        $this->assertArrayHasKey('classic', $selectable);
        $this->assertTrue($templateSelect->templateIsSelectable('classic'));
        $this->assertFalse($templateSelect->templateIsSelectable('not_a_template'));

        foreach (array_keys($selectable) as $templateDir) {
            $baseRows = array_filter(
                $this->rows,
                static fn(array $row): bool => $row['template_dir'] === $templateDir && $row['template_language'] === '-1'
            );
            $this->assertCount(1, $baseRows, $templateDir);
        }
        $this->assertCount(
            count(array_diff(array_keys($selectable), $templatesWithBaseRow)),
            $this->executedInserts(),
            'only the missing base rows are inserted'
        );
    }

    private function exposeTemplateSelect(): TemplateSelect
    {
        return new class extends TemplateSelect {
        };
    }

    private function invoke(TemplateSelect $templateSelect, string $method, mixed ...$arguments): mixed
    {
        $call = \Closure::bind(
            fn() => $this->$method(...$arguments),
            $templateSelect,
            get_class($templateSelect)
        );

        return $call();
    }

    /**
     * @return string[]
     */
    private function executedInserts(): array
    {
        return array_values(array_filter($this->executedSql, static fn(string $sql): bool => str_contains($sql, 'INSERT INTO')));
    }

    /**
     * @return string[]
     */
    private function executedUpdates(): array
    {
        return array_values(array_filter($this->executedSql, static fn(string $sql): bool => str_contains($sql, 'UPDATE ')));
    }

    /**
     * @return string[]
     */
    private function executedDeletes(): array
    {
        return array_values(array_filter($this->executedSql, static fn(string $sql): bool => str_contains($sql, 'DELETE FROM')));
    }
}
