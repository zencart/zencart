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
class AdminCurrenciesLifecycleTest extends zcInProcessFeatureTestCaseAdmin
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testAdminCanCreateEditAndDeleteCurrency(): void
    {
        $this->completeInitialAdminSetup();

        $newPage = $this->getAdmin('/admin/index.php?cmd=currencies&page=1&action=new')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Currencies')
            ->assertSee('New Currency');

        $createResponse = $this->submitAdminForm($newPage, 'currencies', [
            'title' => 'Lifecycle Currency',
            'code' => 'LCT',
            'symbol_left' => 'L$',
            'symbol_right' => '',
            'decimal_point' => '.',
            'thousands_point' => ',',
            'decimal_places' => '2',
            'value' => '1.2345',
        ]);

        $createPage = $createResponse->isRedirect() ? $this->followAdminRedirect($createResponse) : $createResponse;

        $createPage->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Currencies')
            ->assertSee('Lifecycle Currency')
            ->assertSee('LCT');

        $currencyId = (int) TestDb::selectValue(
            'SELECT currencies_id FROM currencies WHERE code = :code ORDER BY currencies_id DESC LIMIT 1',
            [':code' => 'LCT']
        );

        $this->assertGreaterThan(0, $currencyId);

        $createdCurrency = TestDb::selectOne(
            'SELECT title, code, symbol_left, decimal_places, value FROM currencies WHERE currencies_id = :currency_id LIMIT 1',
            [':currency_id' => $currencyId]
        );

        $this->assertNotNull($createdCurrency);
        $this->assertSame('Lifecycle Currency', $createdCurrency['title']);
        $this->assertSame('LCT', $createdCurrency['code']);
        $this->assertSame('L$', $createdCurrency['symbol_left']);
        $this->assertSame('2', (string) $createdCurrency['decimal_places']);
        $this->assertSame('1.234500', (string) $createdCurrency['value']);

        $editPage = $this->getAdmin('/admin/index.php?cmd=currencies&page=1&cID=' . $currencyId . '&action=edit')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Edit Currency')
            ->assertSee('Lifecycle Currency');

        $editResponse = $this->submitAdminForm($editPage, 'currencies', [
            'title' => 'Lifecycle Currency Updated',
            'code' => 'LCU',
            'symbol_left' => '',
            'symbol_right' => ' UC',
            'decimal_point' => '.',
            'thousands_point' => ',',
            'decimal_places' => '3',
            'value' => '2.5',
        ]);

        $editPage = $editResponse->isRedirect() ? $this->followAdminRedirect($editResponse) : $editResponse;

        $editPage->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Currencies')
            ->assertSee('Lifecycle Currency Updated')
            ->assertSee('LCU');

        $updatedCurrency = TestDb::selectOne(
            'SELECT title, code, symbol_left, symbol_right, decimal_places, value FROM currencies WHERE currencies_id = :currency_id LIMIT 1',
            [':currency_id' => $currencyId]
        );

        $this->assertNotNull($updatedCurrency);
        $this->assertSame('Lifecycle Currency Updated', $updatedCurrency['title']);
        $this->assertSame('LCU', $updatedCurrency['code']);
        $this->assertSame('', $updatedCurrency['symbol_left']);
        $this->assertSame('UC', $updatedCurrency['symbol_right']);
        $this->assertSame('3', (string) $updatedCurrency['decimal_places']);
        $this->assertSame('2.500000', (string) $updatedCurrency['value']);

        $deletePage = $this->getAdmin('/admin/index.php?cmd=currencies&page=1&cID=' . $currencyId . '&action=delete')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Delete Currency')
            ->assertSee('Lifecycle Currency Updated');

        $deleteResponse = $this->submitAdminForm($deletePage, 'delete', [
            'cID' => (string) $currencyId,
        ]);

        $deletePage = $deleteResponse->isRedirect() ? $this->followAdminRedirect($deleteResponse) : $deleteResponse;

        $deletePage->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Currencies');

        $deletedCurrency = TestDb::selectValue(
            'SELECT currencies_id FROM currencies WHERE currencies_id = :currency_id LIMIT 1',
            [':currency_id' => $currencyId]
        );

        $this->assertNull($deletedCurrency);
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
