<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureAdmin\AdminEndpoints;

use Tests\Support\Database\TestDb;
use Tests\Support\zcInProcessFeatureTestCaseAdmin;

/**
 * @group parallel-candidate
 */
class AdminTaxClassesLifecycleTest extends zcInProcessFeatureTestCaseAdmin
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testAdminCanCreateEditAndDeleteTaxClass(): void
    {
        $this->completeInitialAdminSetup();

        $newPage = $this->getAdmin('/admin/index.php?cmd=tax_classes&page=1&action=new')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Tax Classes')
            ->assertSee('New Tax Class');

        $createResponse = $this->submitAdminForm($newPage, 'classes', [
            'tax_class_title' => 'Lifecycle Tax Class',
            'tax_class_description' => 'Created by admin feature test',
        ]);

        $createPage = $createResponse->isRedirect() ? $this->followAdminRedirect($createResponse) : $createResponse;

        $createPage->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Tax Classes')
            ->assertSee('Lifecycle Tax Class');

        $taxClassId = (int) TestDb::selectValue(
            'SELECT tax_class_id FROM tax_class WHERE tax_class_title = :title ORDER BY tax_class_id DESC LIMIT 1',
            [':title' => 'Lifecycle Tax Class']
        );

        $this->assertGreaterThan(0, $taxClassId);

        $createdTaxClass = TestDb::selectOne(
            'SELECT tax_class_title, tax_class_description FROM tax_class WHERE tax_class_id = :tax_class_id LIMIT 1',
            [':tax_class_id' => $taxClassId]
        );

        $this->assertNotNull($createdTaxClass);
        $this->assertSame('Lifecycle Tax Class', $createdTaxClass['tax_class_title']);
        $this->assertSame('Created by admin feature test', $createdTaxClass['tax_class_description']);

        $editPage = $this->getAdmin('/admin/index.php?cmd=tax_classes&page=1&tID=' . $taxClassId . '&action=edit')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Edit Tax Class')
            ->assertSee('Lifecycle Tax Class');

        $editResponse = $this->submitAdminForm($editPage, 'classes', [
            'tax_class_title' => 'Lifecycle Tax Class Updated',
            'tax_class_description' => 'Updated by admin feature test',
        ]);

        $editPage = $editResponse->isRedirect() ? $this->followAdminRedirect($editResponse) : $editResponse;

        $editPage->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Tax Classes')
            ->assertSee('Lifecycle Tax Class Updated');

        $updatedTaxClass = TestDb::selectOne(
            'SELECT tax_class_title, tax_class_description FROM tax_class WHERE tax_class_id = :tax_class_id LIMIT 1',
            [':tax_class_id' => $taxClassId]
        );

        $this->assertNotNull($updatedTaxClass);
        $this->assertSame('Lifecycle Tax Class Updated', $updatedTaxClass['tax_class_title']);
        $this->assertSame('Updated by admin feature test', $updatedTaxClass['tax_class_description']);

        $deletePage = $this->getAdmin('/admin/index.php?cmd=tax_classes&page=1&tID=' . $taxClassId . '&action=delete')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Delete Tax Class')
            ->assertSee('Lifecycle Tax Class Updated');

        $deleteResponse = $this->submitAdminForm($deletePage, 'classes', [
            'tID' => (string) $taxClassId,
        ]);

        $deletePage = $deleteResponse->isRedirect() ? $this->followAdminRedirect($deleteResponse) : $deleteResponse;

        $deletePage->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Tax Classes');

        $deletedTaxClass = TestDb::selectValue(
            'SELECT tax_class_id FROM tax_class WHERE tax_class_id = :tax_class_id LIMIT 1',
            [':tax_class_id' => $taxClassId]
        );

        $this->assertNull($deletedTaxClass);
    }

    protected function completeInitialAdminSetup(): void
    {
        $this->visitAdminHome()
            ->assertOk()
            ->assertSee('Admin Login');

        $this->submitAdminLogin([
            'admin_name' => 'Admin',
            'admin_pass' => 'password',
        ])->assertOk()
            ->assertSee('Initial Setup Wizard');

        $this->submitAdminSetupWizard([
            'store_name' => 'Zencart Store',
        ])->assertOk()
            ->assertSee('Initial Setup Wizard');

        $this->submitAdminSetupWizard([
            'store_name' => 'Zencart Store',
            'store_owner' => 'Store Owner',
        ])->assertOk()
            ->assertSee('Admin Home');
    }
}
