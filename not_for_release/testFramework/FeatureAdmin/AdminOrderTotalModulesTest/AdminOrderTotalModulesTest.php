<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Features\TestAdminFeatures;

use Tests\Support\zcFeatureTestCaseAdmin;

class AdminOrderTotalModulesTest extends zcFeatureTestCaseAdmin
{
    /**
     * @test
     */

    public function I_can_see_order_total_modules_page()
    {
        $this->setAdminWizardSettings();
        $this->browser->request('GET', HTTP_SERVER . '/admin');
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Admin Login', (string)$response->getContent() );
        $this->browser->submitForm('Submit', [
            'admin_name' => 'Admin',
            'admin_pass' => 'password',
        ]);
        $this->browser->request('GET', HTTP_SERVER . '/admin/index.php?cmd=modules&set=ordertotal');
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Sub-Total', (string)$response->getContent() );
    }

    /**
     * @test
     */

     public function I_can_see_that_sub_total_module_is_enabled()
     {
         $this->setAdminWizardSettings();
         $this->browser->request('GET', HTTP_SERVER . '/admin');
         $response = $this->browser->getResponse();
         $this->assertStringContainsString('Admin Login', (string)$response->getContent() );
         $this->browser->submitForm('Submit', [
             'admin_name' => 'Admin',
             'admin_pass' => 'password',
         ]);
         $this->browser->request('GET', HTTP_SERVER . '/admin/index.php?cmd=modules&set=ordertotal&module=ot_subtotal');
         $response = $this->browser->getResponse();
         $this->assertStringNotContainsString('Install Module', (string)$response->getContent() );
     }

}
