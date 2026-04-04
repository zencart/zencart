<?php

namespace Tests\FeatureStore\GVCoupons;

use Tests\Support\Traits\LogFileConcerns;
use Tests\Support\zcInProcessFeatureTestCaseStore;

/**
 * @group parallel-candidate
 */
class GiftVoucherRedeemReadOnlyTest extends zcInProcessFeatureTestCaseStore
{
    use LogFileConcerns;

    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    /**
     * @test
     * scenario GV 1
     */
    public function testGvRedeemGuestNoGVNum(): void
    {
        $response = $this->visitGiftVoucherRedeem()
            ->assertRedirect('main_page=login');

        $this->followRedirect($response)
            ->assertOk()
            ->assertSee('To redeem a Gift Voucher you must create an account.');

        $res = $this->logFilesExists();
        $this->assertCount(0, $res);
    }
}
