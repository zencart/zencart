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
class AdminZonesLifecycleTest extends zcInProcessFeatureTestCaseAdmin
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testAdminCanCreateEditAndDeleteZone(): void
    {
        $this->completeInitialAdminSetup();

        $countryId = 223;

        $newPage = $this->getAdmin('/admin/index.php?cmd=zones&zone_page=1&action=new')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Zones')
            ->assertSee('New Zone');

        $createResponse = $this->submitAdminForm($newPage, 'zones', [
            'zone_name' => 'Lifecycle Zone',
            'zone_code' => 'LCZ',
            'zone_country_id' => (string) $countryId,
        ]);

        $createPage = $createResponse->isRedirect() ? $this->followAdminRedirect($createResponse) : $createResponse;

        $createPage->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Lifecycle Zone')
            ->assertSee('LCZ');

        $zoneId = (int) TestDb::selectValue(
            'SELECT zone_id FROM zones WHERE zone_code = :code ORDER BY zone_id DESC LIMIT 1',
            [':code' => 'LCZ']
        );

        $this->assertGreaterThan(0, $zoneId);

        $createdZone = TestDb::selectOne(
            'SELECT zone_country_id, zone_code, zone_name FROM zones WHERE zone_id = :zone_id LIMIT 1',
            [':zone_id' => $zoneId]
        );

        $this->assertNotNull($createdZone);
        $this->assertSame((string) $countryId, (string) $createdZone['zone_country_id']);
        $this->assertSame('LCZ', $createdZone['zone_code']);
        $this->assertSame('Lifecycle Zone', $createdZone['zone_name']);

        $editPage = $this->getAdmin('/admin/index.php?cmd=zones&zone_page=1&cID=' . $zoneId . '&action=edit')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Edit Zone')
            ->assertSee('Lifecycle Zone');

        $editResponse = $this->submitAdminForm($editPage, 'zones', [
            'zone_name' => 'Lifecycle Zone Updated',
            'zone_code' => 'LCU',
            'zone_country_id' => (string) $countryId,
        ]);

        $editPage = $editResponse->isRedirect() ? $this->followAdminRedirect($editResponse) : $editResponse;

        $editPage->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Lifecycle Zone Updated')
            ->assertSee('LCU');

        $updatedZone = TestDb::selectOne(
            'SELECT zone_country_id, zone_code, zone_name FROM zones WHERE zone_id = :zone_id LIMIT 1',
            [':zone_id' => $zoneId]
        );

        $this->assertNotNull($updatedZone);
        $this->assertSame((string) $countryId, (string) $updatedZone['zone_country_id']);
        $this->assertSame('LCU', $updatedZone['zone_code']);
        $this->assertSame('Lifecycle Zone Updated', $updatedZone['zone_name']);

        $deletePage = $this->getAdmin('/admin/index.php?cmd=zones&zone_page=1&cID=' . $zoneId . '&action=delete')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Delete Zone')
            ->assertSee('Lifecycle Zone Updated');

        $deleteResponse = $this->submitAdminForm($deletePage, 'zones', [
            'cID' => (string) $zoneId,
        ]);

        $deletePage = $deleteResponse->isRedirect() ? $this->followAdminRedirect($deleteResponse) : $deleteResponse;

        $deletePage->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Zones');

        $deletedZone = TestDb::selectValue(
            'SELECT zone_id FROM zones WHERE zone_id = :zone_id LIMIT 1',
            [':zone_id' => $zoneId]
        );

        $this->assertNull($deletedZone);
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
