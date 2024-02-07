<?php

namespace Tests\Feature\TestGVCoupons;

use Tests\Support\Traits\CustomerAccountConcerns;
use Tests\Support\zcFeatureTestCaseStore;

class GiftVoucherRedeemTest extends zcFeatureTestCaseStore
{
    use CustomerAccountConcerns;

    public function testGvRedeemGuestNoGVNum()
    {
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=gv_redeem');
        $response = $this->browser->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('To redeem a Gift Voucher you must create an account.', (string)$response->getContent() );
        $res = $this->logFilesExists();
        $this->assertCount(0, $res);
    }

    public function testGvRedeemFixedCustomer()
    {
        $profile = $this->createCustomerAccount('florida-basic2');
        $this->browser->request('GET', HTTP_SERVER  . '/index.php?main_page=gv_redeem&gv_no=VALID10');
        $response = $this->browser->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Congratulations, you have redeemed a Gift Certificate worth $10.00.', (string)$response->getContent() );
        $res = $this->logFilesExists();
        $this->assertCount(0, $res);
    }

}
