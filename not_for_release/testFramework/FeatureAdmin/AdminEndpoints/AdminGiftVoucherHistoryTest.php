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
class AdminGiftVoucherHistoryTest extends zcInProcessFeatureTestCaseAdmin
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testAdminCanViewSentAndRedeemedGiftVoucherHistory(): void
    {
        $this->completeInitialAdminSetup();

        $senderId = $this->insertCustomer('Voucher', 'Sender', 'voucher-sender@example.com');
        $redeemerId = $this->insertCustomer('Voucher', 'Redeemer', 'voucher-redeemer@example.com');
        $couponId = $this->insertGiftVoucherCoupon('HISTORY-CODE-1', '45.0000');

        TestDb::insert('coupon_email_track', [
            'coupon_id' => $couponId,
            'customer_id_sent' => $senderId,
            'sent_firstname' => 'Voucher',
            'sent_lastname' => 'Sender',
            'emailed_to' => 'friend@example.com',
            'date_sent' => '2026-03-01 10:00:00',
        ]);

        TestDb::insert('coupon_redeem_track', [
            'coupon_id' => $couponId,
            'customer_id' => $redeemerId,
            'redeem_date' => '2026-03-02 15:30:00',
            'redeem_ip' => '127.0.0.1',
            'order_id' => 0,
        ]);

        $page = $this->getAdmin('/admin/index.php?cmd=gv_sent&gid=' . $couponId . '&page=1')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Gift Certificates Sent')
            ->assertSee('Voucher Sender')
            ->assertSee('HISTORY-CODE-1')
            ->assertSee('$45.00')
            ->assertSee('friend@example.com')
            ->assertSee('Date Redeemed:')
            ->assertSee('voucher-sender@example.com')
            ->assertSee('voucher-redeemer@example.com');

        $notRedeemedCouponId = $this->insertGiftVoucherCoupon('HISTORY-CODE-2', '20.0000');

        TestDb::insert('coupon_email_track', [
            'coupon_id' => $notRedeemedCouponId,
            'customer_id_sent' => 0,
            'sent_firstname' => 'Admin',
            'sent_lastname' => 'User',
            'emailed_to' => 'unredeemed@example.com',
            'date_sent' => '2026-03-03 12:00:00',
        ]);

        $this->getAdmin('/admin/index.php?cmd=gv_sent&gid=' . $notRedeemedCouponId . '&page=1')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Gift Certificates Sent')
            ->assertSee('HISTORY-CODE-2')
            ->assertSee('unredeemed@example.com')
            ->assertSee('Not Redeemed');
    }

    protected function insertGiftVoucherCoupon(string $couponCode, string $amount): int
    {
        return (int) TestDb::insert('coupons', [
            'coupon_type' => 'G',
            'coupon_code' => $couponCode,
            'coupon_amount' => $amount,
            'coupon_minimum_order' => '0.0000',
            'coupon_start_date' => '2026-01-01 00:00:00',
            'coupon_expire_date' => '2027-01-01 00:00:00',
            'uses_per_coupon' => 1,
            'uses_per_user' => 1,
            'coupon_active' => 'Y',
            'date_created' => '2026-03-01 09:00:00',
            'date_modified' => '2026-03-01 09:00:00',
        ]);
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
