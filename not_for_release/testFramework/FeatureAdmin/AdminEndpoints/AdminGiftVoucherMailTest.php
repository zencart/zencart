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
class AdminGiftVoucherMailTest extends zcInProcessFeatureTestCaseAdmin
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testAdminCanPreviewAndSendGiftVoucherEmail(): void
    {
        $this->completeInitialAdminSetup();

        $recipientEmail = 'giftvoucher-recipient@example.com';
        $subject = 'Test Gift Voucher Subject';

        $page = $this->getAdmin('/admin/index.php?cmd=gv_mail')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Send a Gift Certificate To Customers');

        $preview = $this->submitAdminForm($page, 'mail', [
            'from' => 'admin@example.com',
            'customers_email_address' => '',
            'email_to' => $recipientEmail,
            'email_to_name' => 'Gift Recipient',
            'subject' => $subject,
            'amount' => '35.50',
            'message' => 'Please enjoy this gift voucher.',
        ]);

        $preview->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee($subject)
            ->assertSee($recipientEmail)
            ->assertSee('$35.50');

        $response = $this->submitAdminForm($preview, 'mail', [
            'send' => 'Send',
        ]);

        $page = $response->isRedirect() ? $this->followAdminRedirect($response) : $response;

        $page->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Send a Gift Certificate To Customers');

        $coupon = TestDb::selectOne(
            'SELECT coupon_id, coupon_type, coupon_amount
               FROM coupons
              WHERE coupon_type = :coupon_type
              ORDER BY coupon_id DESC
              LIMIT 1',
            [':coupon_type' => 'G']
        );

        $this->assertNotNull($coupon);
        $this->assertSame('G', $coupon['coupon_type']);
        $this->assertSame('35.5000', (string) $coupon['coupon_amount']);

        $emailTrack = TestDb::selectOne(
            'SELECT coupon_id, customer_id_sent, emailed_to
               FROM coupon_email_track
              WHERE emailed_to = :email
              ORDER BY date_sent DESC
              LIMIT 1',
            [':email' => $recipientEmail]
        );

        $this->assertNotNull($emailTrack);
        $this->assertSame((string) $coupon['coupon_id'], (string) $emailTrack['coupon_id']);
        $this->assertSame('0', (string) $emailTrack['customer_id_sent']);
        $this->assertSame($recipientEmail, $emailTrack['emailed_to']);
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
