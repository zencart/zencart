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
