<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureAdmin\AdminEndpoints;

use Tests\Support\Database\TestDb;
use Tests\Support\Traits\CustomerAccountConcerns;
use Tests\Support\Traits\InProcessStorefrontCheckoutConcerns;
use Tests\Support\zcInProcessFeatureTestCaseAdmin;

/**
 * @group parallel-candidate
 */
class AdminCustomerManagementTest extends zcInProcessFeatureTestCaseAdmin
{
    use CustomerAccountConcerns;
    use InProcessStorefrontCheckoutConcerns;

    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function setUp(): void
    {
        parent::setUp();
        $this->resetStorefrontSession();
    }

    public function testAdminCanEditCustomerAndAssignGroups(): void
    {
        $profile = $this->createCustomerAccountOrLogin('florida-basic2');
        $customerId = $this->getCustomerIdFromEmail($profile['email_address']);

        $this->assertNotNull($customerId);

        TestDb::insert('group_pricing', [
            'group_name' => 'Admin Test Pricing',
            'group_percentage' => '12.5000',
        ]);
        $pricingGroupId = (int) TestDb::selectValue(
            'SELECT group_id FROM group_pricing WHERE group_name = :name ORDER BY group_id DESC LIMIT 1',
            [':name' => 'Admin Test Pricing']
        );

        TestDb::insert('customer_groups', [
            'group_name' => 'Admin Test Group',
            'group_comment' => 'Created by admin feature test',
        ]);
        $customerGroupId = (int) TestDb::selectValue(
            'SELECT group_id FROM customer_groups WHERE group_name = :name ORDER BY group_id DESC LIMIT 1',
            [':name' => 'Admin Test Group']
        );

        $this->assertGreaterThan(0, $pricingGroupId);
        $this->assertGreaterThan(0, $customerGroupId);

        $this->completeInitialAdminSetup();

        $this->visitAdminCommand('customers')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Customers')
            ->assertSee($profile['email_address']);

        $editPage = $this->getAdmin('/admin/index.php?cmd=customers&cID=' . $customerId . '&action=edit')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Personal')
            ->assertSee($profile['email_address']);

        $response = $this->submitAdminForm($editPage, 'customers', [
            'customers_firstname' => 'AdminEdited',
            'customers_lastname' => 'Customer',
            'customers_email_address' => $profile['email_address'],
            'customers_telephone' => '5552223333',
            'customers_newsletter' => '0',
            'customers_group_pricing' => $pricingGroupId,
            'customers_referral' => 'admin-feature-test',
            'customer_groups' => [0, $customerGroupId],
        ]);

        $response->assertOk()
            ->assertSee('Customers')
            ->assertSee('AdminEdited')
            ->assertSee($profile['email_address']);

        $customer = TestDb::selectOne(
            'SELECT customers_firstname, customers_lastname, customers_telephone, customers_newsletter, customers_group_pricing, customers_referral
               FROM customers
              WHERE customers_id = :customer_id
              LIMIT 1',
            [':customer_id' => $customerId]
        );

        $this->assertNotNull($customer);
        $this->assertSame('AdminEdited', $customer['customers_firstname']);
        $this->assertSame('Customer', $customer['customers_lastname']);
        $this->assertSame('5552223333', $customer['customers_telephone']);
        $this->assertSame('0', (string) $customer['customers_newsletter']);
        $this->assertSame((string) $pricingGroupId, (string) $customer['customers_group_pricing']);
        $this->assertSame('admin-feature-test', $customer['customers_referral']);

        $assignment = TestDb::selectValue(
            'SELECT group_id FROM customers_to_groups WHERE customer_id = :customer_id AND group_id = :group_id LIMIT 1',
            [':customer_id' => $customerId, ':group_id' => $customerGroupId]
        );

        $this->assertSame((string) $customerGroupId, (string) $assignment);
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
