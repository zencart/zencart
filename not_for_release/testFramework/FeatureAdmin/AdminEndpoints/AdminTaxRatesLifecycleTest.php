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
class AdminTaxRatesLifecycleTest extends zcInProcessFeatureTestCaseAdmin
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testAdminCanCreateEditAndDeleteTaxRate(): void
    {
        $this->completeInitialAdminSetup();

        $taxClassId = (int) TestDb::insert('tax_class', [
            'tax_class_title' => 'Lifecycle Rate Class',
            'tax_class_description' => 'Used by tax rate lifecycle test',
            'date_added' => date('Y-m-d H:i:s'),
        ]);

        $geoZoneId = (int) TestDb::selectValue(
            'SELECT geo_zone_id FROM geo_zones ORDER BY geo_zone_id ASC LIMIT 1'
        );

        $this->assertGreaterThan(0, $taxClassId);
        $this->assertGreaterThan(0, $geoZoneId);

        $newPage = $this->getAdmin('/admin/index.php?cmd=tax_rates&page=1&action=new')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Tax Rates')
            ->assertSee('New Tax Rate');

        $createResponse = $this->submitAdminForm($newPage, 'rates', [
            'tax_class_id' => (string) $taxClassId,
            'tax_zone_id' => (string) $geoZoneId,
            'tax_rate' => '7.25',
            'tax_priority' => '1',
            'tax_description' => [1 => 'Lifecycle Tax Rate'],
        ]);

        $createPage = $createResponse->isRedirect() ? $this->followAdminRedirect($createResponse) : $createResponse;

        $createPage->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Tax Rates')
            ->assertSee('Lifecycle Rate Class')
            ->assertSee('Lifecycle Tax Rate');

        $taxRateId = (int) TestDb::selectValue(
            'SELECT tax_rates_id FROM tax_rates WHERE tax_class_id = :tax_class_id ORDER BY tax_rates_id DESC LIMIT 1',
            [':tax_class_id' => $taxClassId]
        );

        $this->assertGreaterThan(0, $taxRateId);

        $createdRate = TestDb::selectOne(
            'SELECT tax_zone_id, tax_class_id, tax_rate, tax_priority FROM tax_rates WHERE tax_rates_id = :tax_rates_id LIMIT 1',
            [':tax_rates_id' => $taxRateId]
        );
        $createdDescription = TestDb::selectValue(
            'SELECT tax_description FROM tax_rates_description WHERE tax_rates_id = :tax_rates_id AND language_id = 1 LIMIT 1',
            [':tax_rates_id' => $taxRateId]
        );

        $this->assertNotNull($createdRate);
        $this->assertSame((string) $geoZoneId, (string) $createdRate['tax_zone_id']);
        $this->assertSame((string) $taxClassId, (string) $createdRate['tax_class_id']);
        $this->assertSame('7.2500', (string) $createdRate['tax_rate']);
        $this->assertSame('1', (string) $createdRate['tax_priority']);
        $this->assertSame('Lifecycle Tax Rate', (string) $createdDescription);

        $editPage = $this->getAdmin('/admin/index.php?cmd=tax_rates&page=1&tID=' . $taxRateId . '&action=edit')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Edit Tax Rate')
            ->assertSee('Lifecycle Rate Class');

        $editResponse = $this->submitAdminForm($editPage, 'rates', [
            'tax_class_id' => (string) $taxClassId,
            'tax_zone_id' => (string) $geoZoneId,
            'tax_rate' => '8.5',
            'tax_priority' => '2',
            'tax_description' => [1 => 'Lifecycle Tax Rate Updated'],
        ]);

        $editPage = $editResponse->isRedirect() ? $this->followAdminRedirect($editResponse) : $editResponse;

        $editPage->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Tax Rates')
            ->assertSee('Lifecycle Tax Rate Updated');

        $updatedRate = TestDb::selectOne(
            'SELECT tax_rate, tax_priority FROM tax_rates WHERE tax_rates_id = :tax_rates_id LIMIT 1',
            [':tax_rates_id' => $taxRateId]
        );
        $updatedDescription = TestDb::selectValue(
            'SELECT tax_description FROM tax_rates_description WHERE tax_rates_id = :tax_rates_id AND language_id = 1 LIMIT 1',
            [':tax_rates_id' => $taxRateId]
        );

        $this->assertNotNull($updatedRate);
        $this->assertSame('8.5000', (string) $updatedRate['tax_rate']);
        $this->assertSame('2', (string) $updatedRate['tax_priority']);
        $this->assertSame('Lifecycle Tax Rate Updated', (string) $updatedDescription);

        $deletePage = $this->getAdmin('/admin/index.php?cmd=tax_rates&page=1&tID=' . $taxRateId . '&action=delete')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Delete Tax Rate')
            ->assertSee('Lifecycle Rate Class');

        $deleteResponse = $this->submitAdminForm($deletePage, 'rates', [
            'tID' => (string) $taxRateId,
        ]);

        $deletePage = $deleteResponse->isRedirect() ? $this->followAdminRedirect($deleteResponse) : $deleteResponse;

        $deletePage->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Tax Rates');

        $deletedRate = TestDb::selectValue(
            'SELECT tax_rates_id FROM tax_rates WHERE tax_rates_id = :tax_rates_id LIMIT 1',
            [':tax_rates_id' => $taxRateId]
        );
        $deletedDescription = TestDb::selectValue(
            'SELECT tax_rates_id FROM tax_rates_description WHERE tax_rates_id = :tax_rates_id LIMIT 1',
            [':tax_rates_id' => $taxRateId]
        );

        $this->assertNull($deletedRate);
        $this->assertNull($deletedDescription);
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
