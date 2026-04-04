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
class AdminDiscountManagementTest extends zcInProcessFeatureTestCaseAdmin
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testAdminCanAddCategoryRestrictionToCoupon(): void
    {
        $this->completeInitialAdminSetup();

        $couponId = $this->insertCoupon('Admin Test Coupon');
        $categoryId = (int) TestDb::selectValue(
            'SELECT categories_id FROM categories ORDER BY categories_id ASC LIMIT 1'
        );

        $this->assertGreaterThan(0, $categoryId);

        $page = $this->getAdmin('/admin/index.php?cmd=coupon_restrict&cid=' . $couponId)
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Discount Coupons Product/Category Restrictions')
            ->assertSee('Admin Test Coupon');

        $response = $this->postAdmin(
            '/admin/index.php?cmd=coupon_restrict&cid=' . $couponId . '&cPath=' . $categoryId . '&action=add_category',
            array_merge($page->formDefaults('new-cat'), [
                'cid' => (string) $couponId,
                'cPath' => (string) $categoryId,
                'restrict_status' => 'Allow',
            ])
        );

        $page = $response->isRedirect() ? $this->followAdminRedirect($response) : $response;

        $page->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Discount Coupons Product/Category Restrictions')
            ->assertSee('Category Restrictions');

        $restriction = TestDb::selectOne(
            'SELECT category_id, coupon_restrict
               FROM coupon_restrict
              WHERE coupon_id = :coupon_id
                AND category_id = :category_id
              LIMIT 1',
            [
                ':coupon_id' => $couponId,
                ':category_id' => $categoryId,
            ]
        );

        $this->assertNotNull($restriction);
        $this->assertSame((string) $categoryId, (string) $restriction['category_id']);
        $this->assertSame('N', $restriction['coupon_restrict']);
    }

    public function testAdminCanReleaseGiftVoucherQueueEntry(): void
    {
        $this->completeInitialAdminSetup();

        $customerId = $this->insertCustomer('Voucher', 'Recipient', 'voucher-recipient@example.com');
        $queueId = (int) TestDb::insert('coupon_gv_queue', [
            'customer_id' => $customerId,
            'order_id' => 501,
            'amount' => '25.0000',
            'date_created' => date('Y-m-d H:i:s'),
            'ipaddr' => '127.0.0.1',
            'release_flag' => 'N',
        ]);

        $page = $this->getAdmin('/admin/index.php?cmd=gv_queue&gid=' . $queueId . '&page=1')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Gift Certificate Release Queue')
            ->assertSee('Voucher Recipient')
            ->assertSee('501');

        $confirmPage = $this->getAdmin('/admin/index.php?cmd=gv_queue&action=release&gid=' . $queueId . '&page=1')
            ->assertOk()
            ->assertSee('Gift Certificate Release Queue')
            ->assertSee('Confirm');

        $response = $this->postAdmin(
            '/admin/index.php?cmd=gv_queue&action=confirmrelease&page=1',
            array_merge($confirmPage->formDefaults('gv_release'), [
                'gid' => (string) $queueId,
            ])
        );

        $page = $response->isRedirect() ? $this->followAdminRedirect($response) : $response;

        $page->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Gift Certificate Release Queue');

        $releaseFlag = TestDb::selectValue(
            'SELECT release_flag FROM coupon_gv_queue WHERE unique_id = :queue_id LIMIT 1',
            [':queue_id' => $queueId]
        );
        $balance = TestDb::selectValue(
            'SELECT amount FROM coupon_gv_customer WHERE customer_id = :customer_id LIMIT 1',
            [':customer_id' => $customerId]
        );

        $this->assertSame('Y', (string) $releaseFlag);
        $this->assertSame('25.0000', (string) $balance);
    }

    protected function insertCoupon(string $couponName): int
    {
        $couponId = (int) TestDb::insert('coupons', [
            'coupon_type' => 'F',
            'coupon_code' => 'ADMIN-' . uniqid(),
            'coupon_amount' => '10.0000',
            'coupon_minimum_order' => '0.0000',
            'coupon_start_date' => '2026-01-01 00:00:00',
            'coupon_expire_date' => '2027-01-01 00:00:00',
            'uses_per_coupon' => 10,
            'uses_per_user' => 1,
            'coupon_active' => 'Y',
            'date_created' => date('Y-m-d H:i:s'),
            'date_modified' => date('Y-m-d H:i:s'),
        ]);

        TestDb::insert('coupons_description', [
            'coupon_id' => $couponId,
            'language_id' => 1,
            'coupon_name' => $couponName,
            'coupon_description' => 'Admin feature test coupon',
        ]);

        return $couponId;
    }

    protected function insertCustomer(string $firstname, string $lastname, string $email): int
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
            'customers_group_pricing' => 0,
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
