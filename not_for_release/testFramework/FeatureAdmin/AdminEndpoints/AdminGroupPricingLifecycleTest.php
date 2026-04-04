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
class AdminGroupPricingLifecycleTest extends zcInProcessFeatureTestCaseAdmin
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testAdminCanCreateEditAndDeleteGroupPricingDefinition(): void
    {
        $this->completeInitialAdminSetup();

        $newPage = $this->getAdmin('/admin/index.php?cmd=group_pricing&action=new')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Group Pricing')
            ->assertSee('New Pricing Group');

        $createResponse = $this->submitAdminForm($newPage, 'group_pricing', [
            'group_name' => 'Lifecycle Pricing Group',
            'group_percentage' => '12.5',
        ]);

        $createPage = $createResponse->isRedirect() ? $this->followAdminRedirect($createResponse) : $createResponse;

        $createPage->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Group Pricing')
            ->assertSee('Lifecycle Pricing Group')
            ->assertSee('12.5');

        $groupId = (int) TestDb::selectValue(
            'SELECT group_id FROM group_pricing WHERE group_name = :name ORDER BY group_id DESC LIMIT 1',
            [':name' => 'Lifecycle Pricing Group']
        );

        $this->assertGreaterThan(0, $groupId);

        $createdGroup = TestDb::selectOne(
            'SELECT group_name, group_percentage FROM group_pricing WHERE group_id = :group_id LIMIT 1',
            [':group_id' => $groupId]
        );

        $this->assertNotNull($createdGroup);
        $this->assertSame('Lifecycle Pricing Group', $createdGroup['group_name']);
        $this->assertSame('12.50', (string) $createdGroup['group_percentage']);

        $editPage = $this->getAdmin('/admin/index.php?cmd=group_pricing&page=1&gID=' . $groupId . '&action=edit')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Edit Pricing Group')
            ->assertSee('Lifecycle Pricing Group');

        $editResponse = $this->submitAdminForm($editPage, 'group_pricing', [
            'group_name' => 'Lifecycle Pricing Group Updated',
            'group_percentage' => '18.75',
        ]);

        $editResultPage = $editResponse->isRedirect() ? $this->followAdminRedirect($editResponse) : $editResponse;

        $editResultPage->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Group Pricing')
            ->assertSee('Lifecycle Pricing Group Updated')
            ->assertSee('18.75');

        $updatedGroup = TestDb::selectOne(
            'SELECT group_name, group_percentage FROM group_pricing WHERE group_id = :group_id LIMIT 1',
            [':group_id' => $groupId]
        );

        $this->assertNotNull($updatedGroup);
        $this->assertSame('Lifecycle Pricing Group Updated', $updatedGroup['group_name']);
        $this->assertSame('18.75', (string) $updatedGroup['group_percentage']);

        $customerId = $this->insertCustomerLinkedToPricingGroup(
            'Group',
            'Member',
            'group-member@example.com',
            $groupId
        );

        $deletePage = $this->getAdmin('/admin/index.php?cmd=group_pricing&page=1&gID=' . $groupId . '&action=delete')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Delete Pricing Group')
            ->assertSee('Lifecycle Pricing Group Updated');

        $deleteResponse = $this->submitAdminForm($deletePage, 'group_pricing', [
            'gID' => (string) $groupId,
            'delete_customers' => 'on',
        ]);

        $deleteResultPage = $deleteResponse->isRedirect() ? $this->followAdminRedirect($deleteResponse) : $deleteResponse;

        $deleteResultPage->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Group Pricing');

        $deletedGroup = TestDb::selectValue(
            'SELECT group_id FROM group_pricing WHERE group_id = :group_id LIMIT 1',
            [':group_id' => $groupId]
        );

        $customerPricingGroup = TestDb::selectValue(
            'SELECT customers_group_pricing FROM customers WHERE customers_id = :customer_id LIMIT 1',
            [':customer_id' => $customerId]
        );

        $this->assertNull($deletedGroup);
        $this->assertSame('0', (string) $customerPricingGroup);
    }

    protected function insertCustomerLinkedToPricingGroup(string $firstname, string $lastname, string $email, int $groupId): int
    {
        $customerId = (int) TestDb::insert('customers', [
            'customers_gender' => 'm',
            'customers_firstname' => $firstname,
            'customers_lastname' => $lastname,
            'customers_dob' => '0001-01-01 00:00:00',
            'customers_email_address' => $email,
            'customers_nick' => '',
            'customers_default_address_id' => 0,
            'customers_telephone' => '555-0100',
            'customers_password' => password_hash('password', PASSWORD_DEFAULT),
            'customers_secret' => '',
            'customers_newsletter' => 0,
            'customers_group_pricing' => $groupId,
            'customers_email_format' => 'TEXT',
            'customers_authorization' => 0,
            'activation_required' => 0,
            'welcome_email_sent' => 1,
            'customers_referral' => '',
            'registration_ip' => '127.0.0.1',
            'last_login_ip' => '127.0.0.1',
            'customers_paypal_payerid' => '',
            'customers_paypal_ec' => 0,
            'customers_whole' => 0,
        ]);

        TestDb::insert('customers_info', [
            'customers_info_id' => $customerId,
            'customers_info_date_account_created' => date('Y-m-d H:i:s'),
            'global_product_notifications' => 0,
        ]);

        return $customerId;
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
