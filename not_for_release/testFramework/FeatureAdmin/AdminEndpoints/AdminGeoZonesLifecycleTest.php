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
class AdminGeoZonesLifecycleTest extends zcInProcessFeatureTestCaseAdmin
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testAdminCanCreateEditAndDeleteGeoZoneAndAssociation(): void
    {
        $this->completeInitialAdminSetup();

        $countryId = 223;
        $firstZoneId = 1;
        $secondZoneId = 2;

        $newZonePage = $this->getAdmin('/admin/index.php?cmd=geo_zones&zpage=1&action=new_zone')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Zone Definitions - Taxes, Payment and Shipping')
            ->assertSee('New Zone');

        $createZoneResponse = $this->submitAdminForm($newZonePage, 'zones', [
            'geo_zone_name' => 'Lifecycle Geo Zone',
            'geo_zone_description' => 'Created by admin feature test',
        ]);

        $createZonePage = $createZoneResponse->isRedirect() ? $this->followAdminRedirect($createZoneResponse) : $createZoneResponse;

        $createZonePage->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Lifecycle Geo Zone');

        $geoZoneId = (int) TestDb::selectValue(
            'SELECT geo_zone_id FROM geo_zones WHERE geo_zone_name = :name ORDER BY geo_zone_id DESC LIMIT 1',
            [':name' => 'Lifecycle Geo Zone']
        );

        $this->assertGreaterThan(0, $geoZoneId);

        $zoneRecord = TestDb::selectOne(
            'SELECT geo_zone_name, geo_zone_description FROM geo_zones WHERE geo_zone_id = :geo_zone_id LIMIT 1',
            [':geo_zone_id' => $geoZoneId]
        );

        $this->assertNotNull($zoneRecord);
        $this->assertSame('Lifecycle Geo Zone', $zoneRecord['geo_zone_name']);
        $this->assertSame('Created by admin feature test', $zoneRecord['geo_zone_description']);

        $listPage = $this->getAdmin('/admin/index.php?cmd=geo_zones&zpage=1&zID=' . $geoZoneId . '&action=list')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Lifecycle Geo Zone');

        $newSubZonePage = $this->getAdmin('/admin/index.php?cmd=geo_zones&zpage=1&zID=' . $geoZoneId . '&action=list&spage=1&saction=new')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('New Sub Zone');

        $createSubZoneResponse = $this->submitAdminForm($newSubZonePage, 'zones', [
            'zone_country_id' => (string) $countryId,
            'zone_id' => (string) $firstZoneId,
        ]);

        $createSubZonePage = $createSubZoneResponse->isRedirect() ? $this->followAdminRedirect($createSubZoneResponse) : $createSubZoneResponse;

        $createSubZonePage->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Alabama');

        $associationId = (int) TestDb::selectValue(
            'SELECT association_id FROM zones_to_geo_zones WHERE geo_zone_id = :geo_zone_id ORDER BY association_id DESC LIMIT 1',
            [':geo_zone_id' => $geoZoneId]
        );

        $this->assertGreaterThan(0, $associationId);

        $association = TestDb::selectOne(
            'SELECT zone_country_id, zone_id FROM zones_to_geo_zones WHERE association_id = :association_id LIMIT 1',
            [':association_id' => $associationId]
        );

        $this->assertNotNull($association);
        $this->assertSame((string) $countryId, (string) $association['zone_country_id']);
        $this->assertSame((string) $firstZoneId, (string) $association['zone_id']);

        $editSubZonePage = $this->getAdmin('/admin/index.php?cmd=geo_zones&zpage=1&zID=' . $geoZoneId . '&action=list&spage=1&sID=' . $associationId . '&saction=edit')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Edit Sub Zone')
            ->assertSee('Alabama');

        $editSubZoneResponse = $this->submitAdminForm($editSubZonePage, 'zones', [
            'zone_country_id' => (string) $countryId,
            'zone_id' => (string) $secondZoneId,
        ]);

        $editSubZonePage = $editSubZoneResponse->isRedirect() ? $this->followAdminRedirect($editSubZoneResponse) : $editSubZoneResponse;

        $editSubZonePage->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Alaska');

        $updatedAssociation = TestDb::selectOne(
            'SELECT zone_country_id, zone_id FROM zones_to_geo_zones WHERE association_id = :association_id LIMIT 1',
            [':association_id' => $associationId]
        );

        $this->assertNotNull($updatedAssociation);
        $this->assertSame((string) $countryId, (string) $updatedAssociation['zone_country_id']);
        $this->assertSame((string) $secondZoneId, (string) $updatedAssociation['zone_id']);

        $deleteSubZonePage = $this->getAdmin('/admin/index.php?cmd=geo_zones&zpage=1&zID=' . $geoZoneId . '&action=list&spage=1&sID=' . $associationId . '&saction=delete')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Delete Sub Zone');

        $deleteSubZoneResponse = $this->submitAdminForm($deleteSubZonePage, 'zones', [
            'sID' => (string) $associationId,
        ]);

        $deleteSubZonePage = $deleteSubZoneResponse->isRedirect() ? $this->followAdminRedirect($deleteSubZoneResponse) : $deleteSubZoneResponse;

        $deleteSubZonePage->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Lifecycle Geo Zone');

        $deletedAssociation = TestDb::selectValue(
            'SELECT association_id FROM zones_to_geo_zones WHERE association_id = :association_id LIMIT 1',
            [':association_id' => $associationId]
        );

        $this->assertNull($deletedAssociation);

        $deleteZonePage = $this->getAdmin('/admin/index.php?cmd=geo_zones&zpage=1&zID=' . $geoZoneId . '&action=delete_zone')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Delete Zone')
            ->assertSee('Lifecycle Geo Zone');

        $deleteZoneResponse = $this->submitAdminForm($deleteZonePage, 'zones', [
            'zID' => (string) $geoZoneId,
        ]);

        $deleteZonePage = $deleteZoneResponse->isRedirect() ? $this->followAdminRedirect($deleteZoneResponse) : $deleteZoneResponse;

        $deleteZonePage->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Zone Definitions - Taxes, Payment and Shipping');

        $deletedZone = TestDb::selectValue(
            'SELECT geo_zone_id FROM geo_zones WHERE geo_zone_id = :geo_zone_id LIMIT 1',
            [':geo_zone_id' => $geoZoneId]
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
