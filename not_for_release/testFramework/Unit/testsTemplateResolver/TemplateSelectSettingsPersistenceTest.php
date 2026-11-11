<?php

declare(strict_types=1);

namespace Tests\Unit\testsTemplateResolver;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\Support\InMemoryTemplateSelectDatabase;
use Tests\Support\zcUnitTestCase;
use Zencart\Templates\TemplateSelect;

/**
 * Regression/coverage for the `template_settings` persistence introduced alongside
 * TemplateSelect's 'base' (template_language = -1) record: settings are stored only
 * on that base record so that they survive a template being deregistered from any
 * given language, as described in the class-level documentation in TemplateSelect.php.
 */
#[AllowMockObjectsWithoutExpectations]
#[RunTestsInSeparateProcesses]
class TemplateSelectSettingsPersistenceTest extends zcUnitTestCase
{
    use InMemoryTemplateSelectDatabase;

    public function setUp(): void
    {
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

        // Avoids TemplateResolver constructing a real PluginManager
        // (which would issue further DB queries our mock $db below doesn't model);
        // an empty array of installed plugins is sufficient for resolving on-disk templates
        // such as 'responsive_classic' used throughout this test.
        $GLOBALS['installedPlugins'] = [];

        $_SESSION['languages_id'] = 0;

        $this->rows = [
            1 => [
                'template_id' => '1',
                'template_dir' => 'responsive_classic',
                'template_language' => '0',
                'template_settings' => null,
            ],
            2 => [
                'template_id' => '2',
                'template_dir' => 'responsive_classic',
                'template_language' => '-1',
                'template_settings' => null,
            ],
        ];
        $this->nextId = 2;

        $GLOBALS['db'] = $this->makeMockDb();
    }

    public function testSetTemplateSettingsPersistsAndIsRetrievable(): void
    {
        $templateSelect = new TemplateSelect();

        $status = $templateSelect->setTemplateSettings('responsive_classic', ['FOO' => 'bar']);

        $this->assertSame(TemplateSelect::SETTINGS_OK, $status);
        $this->assertSame(
            ['FOO' => 'bar'],
            $templateSelect->getTemplateSettings('responsive_classic')
        );
    }

    public function testSetTemplateSettingsPersistsJsonStringContainingNull(): void
    {
        $templateSelect = new TemplateSelect();

        $status = $templateSelect->setTemplateSettings('responsive_classic', ['LABEL' => 'contains NULL text']);

        $this->assertSame(TemplateSelect::SETTINGS_OK, $status);
        $this->assertSame(
            ['LABEL' => 'contains NULL text'],
            $templateSelect->getTemplateSettings('responsive_classic')
        );
    }

    public function testSetTemplateSettingsReturnsUnknownDirForATemplateWithNoBaseRecord(): void
    {
        $templateSelect = new TemplateSelect();

        $status = $templateSelect->setTemplateSettings('not_a_real_template', ['FOO' => 'bar']);

        $this->assertSame(TemplateSelect::SETTINGS_UNKNOWN_DIR, $status);
        $this->assertNull($templateSelect->getTemplateSettings('not_a_real_template'));
    }

    public function testUpdateTemplateSettingsMergesWithExistingSettings(): void
    {
        $templateSelect = new TemplateSelect();

        $templateSelect->setTemplateSettings('responsive_classic', ['A' => '1', 'B' => '2']);

        $status = $templateSelect->updateTemplateSettings('responsive_classic', ['B' => '20', 'C' => '3']);

        $this->assertSame(TemplateSelect::SETTINGS_OK, $status);
        $this->assertSame(
            ['A' => '1', 'B' => '20', 'C' => '3'],
            $templateSelect->getTemplateSettings('responsive_classic')
        );
    }

    /**
     * This is the core behavior added to the TemplateSelect class:
     * a template's `template_settings` live only on its 'base' (template_language = -1) record,
     * so removing an active language assignment for that template must NOT lose the settings.
     */
    public function testTemplateSettingsSurviveDeregisteringTheActiveLanguageAssignment(): void
    {
        $templateSelect = new TemplateSelect();
        $templateSelect->setTemplateSettings('responsive_classic', ['THEME' => 'dark']);

        $newLanguageId = (int)$templateSelect->registerNewTemplate('responsive_classic', 5);
        $this->assertGreaterThan(0, $newLanguageId, 'Expected registerNewTemplate() to succeed for an unused language.');

        $deregistered = $templateSelect->deregisterTemplateId($newLanguageId);

        $this->assertTrue($deregistered, 'Expected deregisterTemplateId() to succeed for the active-language row just created.');
        $this->assertArrayNotHasKey(
            5,
            $templateSelect->getAllActiveTemplates(),
            'The deregistered language assignment should no longer be active.'
        );
        $this->assertSame(
            ['THEME' => 'dark'],
            $templateSelect->getTemplateSettings('responsive_classic'),
            'template_settings must persist on the base record after an active-language row is removed.'
        );
    }

    public function testRegisterNewTemplateRejectsAnAlreadyRegisteredLanguage(): void
    {
        $templateSelect = new TemplateSelect();

        $this->assertFalse(
            $templateSelect->registerNewTemplate('responsive_classic', 0),
            'Language 0 (the default) is already registered and must not be re-registerable.'
        );
    }

    /**
     * queryFactory::affectedRows() reports mysqli_affected_rows(), which counts rows
     * whose stored value actually *changed*, not rows merely matched by the WHERE clause.
     * Re-saving a template_dir that's already assigned to a row is a legitimate no-op
     * and must not be treated as a failure.
     */
    public function testUpdatingATemplateDirToItsCurrentValueIsNotTreatedAsAFailure(): void
    {
        $templateSelect = new TemplateSelect();

        // Row id 1 (seeded in setUp()) is already 'responsive_classic' for language 0.
        $status = $templateSelect->updateTemplateNameForId(1, 'responsive_classic');

        $this->assertSame(TemplateSelect::SETTINGS_OK, $status);
    }

    /**
     * Same idempotent-save concern as above, applied to template_settings.
     */
    public function testReSavingIdenticalSettingsIsNotTreatedAsAFailure(): void
    {
        $templateSelect = new TemplateSelect();

        $templateSelect->setTemplateSettings('responsive_classic', ['FOO' => 'bar']);

        // Save the exact same settings again - a no-op from MySQL's point of view.
        $status = $templateSelect->setTemplateSettings('responsive_classic', ['FOO' => 'bar']);

        $this->assertSame(TemplateSelect::SETTINGS_OK, $status);
    }
}
