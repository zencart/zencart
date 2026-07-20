<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureAdmin\TemplateTests;

use PHPUnit\Framework\Attributes\Group;
use Tests\Support\zcInProcessFeatureTestCaseAdmin;

#[Group('parallel-candidate')]
#[Group('custom-seeder')]
class TemplateSelectTest extends zcInProcessFeatureTestCaseAdmin
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testEditFormPreselectsTemplateAssignedToEditedLanguageRow(): void
    {
        $this->runCustomSeeder('StoreWizardSeeder');

        $this->submitAdminLogin([
            'admin_name' => 'Admin',
            'admin_pass' => 'password',
        ])->assertOk()
            ->assertSee('Admin Home');

        $db = $this->bootstrapLegacyDbConnection();
        $db->Execute("DELETE FROM " . TABLE_TEMPLATE_SELECT);
        $db->Execute(
            "INSERT INTO " . TABLE_TEMPLATE_SELECT . "
                (template_dir, template_language)
             VALUES
                ('template_default', 0)"
        );
        $db->Execute(
            "INSERT INTO " . TABLE_TEMPLATE_SELECT . "
                (template_dir, template_language)
             VALUES
                ('responsive_classic', 999)"
        );
        $templateId = (int)$db->insert_ID();

        $page = $this->visitAdminCommand('template_select&tID=' . $templateId . '&action=edit')
            ->assertOk()
            ->assertSee('responsive_classic');

        $this->assertSame(
            'responsive_classic',
            $page->formDefaults('templateselect')['ln'] ?? null,
            'Expected the edit form to preselect the template assigned to the edited language row.'
        );
    }

    /**
     * TemplateSelect::resolveTemplates() (which scans the filesystem and can INSERT
     * new 'base' (-1) template_select records) is only meant to run when the admin's
     * "Template Selection" tool itself is in use (admin/template_select.php calls it explicitly).
     * Visiting an unrelated admin page must not trigger it.
     */
    public function testVisitingAnUnrelatedAdminPageDoesNotSynchronizeTemplateSelectTable(): void
    {
        $this->runCustomSeeder('StoreWizardSeeder');

        $this->submitAdminLogin([
            'admin_name' => 'Admin',
            'admin_pass' => 'password',
        ])->assertOk()
            ->assertSee('Admin Home');

        $db = $this->bootstrapLegacyDbConnection();
        $db->Execute("DELETE FROM " . TABLE_TEMPLATE_SELECT);
        $db->Execute(
            "INSERT INTO " . TABLE_TEMPLATE_SELECT . "
                (template_dir, template_language)
             VALUES
                ('template_default', 0)"
        );

        // Revisit Admin Home - unrelated to the Template Selection tool.
        $this->visitAdminHome()->assertOk();

        $result = $db->Execute(
            "SELECT COUNT(*) AS the_count
               FROM " . TABLE_TEMPLATE_SELECT . "
              WHERE template_language = -1"
        );
        $this->assertSame(
            0,
            (int)$result->fields['the_count'],
            'Visiting an unrelated admin page must not synchronize/insert base (-1) template_select records.'
        );
    }

    /**
     * Complements the prior test: visiting the Template Selection tool itself
     * IS expected to lazily create a 'base' (template_language = -1) record
     * for an on-disk template not yet represented in the table.
     * This is the only place that record ever gets created.
     * A fresh install only seeds the default (template_language = 0) row,
     * so relying on a -1 row existing anywhere else would be a mistake.
     */
    public function testVisitingTheTemplateSelectionToolLazilyCreatesBaseRecords(): void
    {
        $this->runCustomSeeder('StoreWizardSeeder');

        $this->submitAdminLogin([
            'admin_name' => 'Admin',
            'admin_pass' => 'password',
        ])->assertOk()
            ->assertSee('Admin Home');

        $db = $this->bootstrapLegacyDbConnection();
        $db->Execute("DELETE FROM " . TABLE_TEMPLATE_SELECT);
        $db->Execute(
            "INSERT INTO " . TABLE_TEMPLATE_SELECT . "
                (template_dir, template_language)
             VALUES
                ('template_default', 0)"
        );

        $before = $db->Execute(
            "SELECT COUNT(*) AS the_count
               FROM " . TABLE_TEMPLATE_SELECT . "
              WHERE template_language = -1"
        );
        $this->assertSame(
            0,
            (int)$before->fields['the_count'],
            'A fresh install only seeds the template_language = 0 row; no base (-1) record should exist yet.'
        );

        $this->visitAdminCommand('template_select')->assertOk();

        $result = $db->Execute(
            "SELECT COUNT(*) AS the_count
               FROM " . TABLE_TEMPLATE_SELECT . "
              WHERE template_dir = 'responsive_classic'
                AND template_language = -1"
        );
        $this->assertSame(
            1,
            (int)$result->fields['the_count'],
            'Visiting the Template Selection tool must lazily create a base (-1) record for an on-disk template.'
        );
    }

    public function testDetailsPanelRendersMissingTemplateRecordWithoutSettingsWarning(): void
    {
        $this->runCustomSeeder('StoreWizardSeeder');

        $this->submitAdminLogin([
            'admin_name' => 'Admin',
            'admin_pass' => 'password',
        ])->assertOk()
            ->assertSee('Admin Home');

        $db = $this->bootstrapLegacyDbConnection();
        $db->Execute("DELETE FROM " . TABLE_TEMPLATE_SELECT);
        $db->Execute(
            "INSERT INTO " . TABLE_TEMPLATE_SELECT . "
                (template_dir, template_language)
             VALUES
                ('template_default', 0)"
        );
        $db->Execute(
            "INSERT INTO " . TABLE_TEMPLATE_SELECT . "
                (template_dir, template_language)
             VALUES
                ('missing_template_for_test', 999)"
        );
        $templateId = (int)$db->insert_ID();

        $this->visitAdminCommand('template_select&tID=' . $templateId)
            ->assertOk()
            ->assertSee('MISSING DIRECTORY: missing_template_for_test');
    }

    private function bootstrapLegacyDbConnection(): \queryFactory
    {
        if (!class_exists('queryFactory')) {
            require_once ROOTCWD . 'includes/classes/class.base.php';
            require_once ROOTCWD . 'includes/classes/db/' . DB_TYPE . '/query_factory.php';
        }

        $db = new \queryFactory();
        if (!defined('USE_PCONNECT')) {
            define('USE_PCONNECT', 'false');
        }

        $db->connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE, USE_PCONNECT, false);

        return $db;
    }
}
