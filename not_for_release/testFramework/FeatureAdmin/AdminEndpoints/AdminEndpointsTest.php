<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureAdmin\AdminEndpoints;

use Tests\Support\zcInProcessFeatureTestCaseAdmin;

/**
 * @group parallel-candidate
 */
class AdminEndpointsTest extends zcInProcessFeatureTestCaseAdmin
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    protected array $quickTestMap = [
        'configuration&gID=1' => ['strings' => ['Admin Session Time Out in Seconds']],
        'categories' => ['strings' => ['Admin Categories']],
        'products_price_manager' => ['strings' => ['Admin Products Price Manager']],
        'attributes_controller' => ['strings' => ['Please select a Category to display the Product Attributes of']],
        'option_name' => ['strings' => ['Option Name Sort Order']],
        'option_values' => ['strings' => ['Option Value Sorter']],
        'products_to_categories' => ['strings' => ['Admin Products To Categories']],
        'modules&set=payment' => ['strings' => ['Authorize.net']],
        'modules&set=shipping' => ['strings' => ['Free Shipping Options']],
        'modules&set=ordertotal' => ['strings' => ['Low Order Fee']],
        'plugin_manager' => ['strings' => ['Display Logs']],
        'stats_customers_referrals' => ['strings' => ['Customers Referral Report']],
        'stats_sales_report_graphs' => ['strings' => ['Sales Reports']],
        'define_pages_editor' => ['strings' => ['Define Pages Editor for:']],
        'developers_tool_kit' => ['strings' => ['Look-up CONSTANT or Language File defines']],
        'sqlpatch' => ['strings' => ['SQL Query Executor']],
        'layout_controller' => ['strings' => ['Editing Sideboxes for template:']],
        'mail' => ['strings' => ['Send Email To Customers']],
        'server_info' => ['strings' => ['Database Engine:']],
        'store_manager' => ['strings' => ['Update ALL Products Price Sorter']],
        'whos_online' => ['strings' => ['Updating Manually']],
        'gv_mail' => ['strings' => ['Send a Gift Certificate To Customers']],
        'profiles' => ['strings' => ['User Profiles']],
        'users' => ['strings' => ['Reset Password']],
        'admin_activity' => ['strings' => ['Review or Export Logs']],
    ];

    public function testSimpleEndpoints()
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

        $response = $this->submitAdminSetupWizard([
            'store_name' => 'Zencart Store',
            'store_owner' => 'Store Owner',
        ])->assertOk();

        $response->assertSee('Admin Home');
        $this->quickLinksTest();
    }


    public function quickLinksTest()
    {
        foreach ($this->quickTestMap as $page => $contentTest) {
            $response = $this->visitAdminEndpoint($page)->assertOk();
            foreach ($contentTest['strings'] as $contentString) {
                $response->assertSee($contentString);
            }
        }
    }

    protected function visitAdminEndpoint(string $page)
    {
        return $this->visitAdminCommand($page);
    }

}
