<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureAdmin\AdminEndpoints;

use Tests\Support\zcFeatureTestCaseAdmin;

class AdminEndpointsTest extends zcFeatureTestCaseAdmin
{

    protected array $quickTestMap = [
        'configuration&gID=1' => ['strings' => ['Admin Session Time Out in Seconds']],
        'category_product_listing' => ['strings' => ['Admin Category Product Listing']],
        'product_types' => ['strings' => ['Admin Product Types']],
        'products_price_manager' => ['strings' => ['Admin Products Price Manager']],
        'options_name_manager' => ['strings' => ['Admin Options Name Manager']],
        'options_values_manager' => ['strings' => ['Admin Options Values Manager']],
        'attributes_controller' => ['strings' => ['Please select a Category to display the Product Attributes of']],
        'downloads_manager' => ['strings' => ['Admin Downloads Manager']],
        'option_name' => ['strings' => ['Option Name Sort Order']],
        'option_values' => ['strings' => ['Option Value Sorter']],
        'manufacturers' => ['strings' => ['Admin Manufacturers']],
        'reviews' => ['strings' => ['Admin Reviews']],
        'specials' => ['strings' => ['Admin Specials']],
        'featured' => ['strings' => ['Sample of Document Product Type']],
        'salemaker' => ['strings' => ['Admin Salemaker']],
        'products_expected' => ['strings' => ['Admin Products Expected']],
        'products_to_categories' => ['strings' => ['Admin Products To Categories']],
        'modules&set=payment' => ['strings' => ['Authorize.net']],
        'modules&set=shipping' => ['strings' => ['Free Shipping Options']],
        'modules&set=ordertotal' => ['strings' => ['Low Order Fee']],
        'plugin_manager' => ['strings' => ['Display Logs']],

        'customers' => ['strings' => ['Account Created']],
        'orders' => ['strings' => ['Date Purchased']],
        'group_pricing' => ['strings' => ['Group Name']],
        'customer_groups' => ['strings' => ['# of Customers']],

        'countries' => ['strings' => ['Address Format']],
        'zones' => ['strings' => ['Zones Name:']],
        'geo_zones' => ['strings' => ['Number of Zones:']],
        'tax_classes' => ['strings' => ['Tax Classes']],
        'tax_rates' => ['strings' => ['Priority']],

        'currencies' => ['strings' => ['Symbol Left:']],
        'languages' => ['strings' => ['Code:']],
        'orders_status' => ['strings' => ['Orders Status ID']],

        'stats_customers' => ['strings' => ['Best Customer Orders-Total']],
        'stats_customers_referrals' => ['strings' => ['Customers Referral Report']],
        'stats_products_lowstock' => ['strings' => ['Product Stock Report']],
        'stats_products_purchased' => ['strings' => ['Best Products Purchased']],
        'stats_products_viewed' => ['strings' => ['Most-Viewed Products']],
        'stats_sales_report_graphs' => ['strings' => ['Sales Reports']],

        'banner_manager' => ['strings' => ['Banner Manager']],
        'define_pages_editor' => ['strings' => ['Define Pages Editor for:']],
        'developers_tool_kit' => ['strings' => ['Look-up CONSTANT or Language File defines']],
        'ezpages' => ['strings' => ['EZ-Pages Select a page']],
        'sqlpatch' => ['strings' => ['SQL Query Executor']],
        'layout_controller' => ['strings' => ['Editing Sideboxes for template:']],
        'newsletters' => ['strings' => ['Newsletter and Product Notifications Manager']],
        'mail' => ['strings' => ['Send Email To Customers']],
        'server_info' => ['strings' => ['Database Engine:']],
        'store_manager' => ['strings' => ['Update ALL Products Price Sorter']],
        'template_select' => ['strings' => ['Template Directory']],
        'whos_online' => ['strings' => ['Updating Manually']],

        'coupon_admin' => ['strings' => ['Coupon Code']],
        'gv_queue' => ['strings' => ['Gift Certificate Release Queue']],
        'gv_mail' => ['strings' => ['Send a Gift Certificate To Customers']],
        'gv_sent' => ['strings' => ['Gift Certificate Value']],

        'profiles' => ['strings' => ['User Profiles']],
        'users' => ['strings' => ['Reset Password']],
        'admin_activity' => ['strings' => ['Review or Export Logs']],

        'record_artists' => ['strings' => ['Recording Artists']],
        'record_company' => ['strings' => ['Record Companies']],
        'music_genre' => ['strings' => ['Music Genres']],
        'media_manager' => ['strings' => ['Assign to Product']],
        'media_types' => ['strings' => ['Extension']],
    ];

    public function testSimpleEndpoints()
    {
        $this->browser->request('GET', HTTP_SERVER . '/admin');
        $response = $this->browser->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->browser->request('GET', HTTP_SERVER . '/admin');
        $response = $this->browser->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Admin Login', (string)$response->getContent() );
        $this->browser->submitForm('Submit', [
            'admin_name' => 'Admin',
            'admin_pass' => 'password',
        ]);
        $this->browser->submitForm('Update', [
            'store_name' => 'Zencart Store',
        ]);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Initial Setup Wizard', (string)$response->getContent() );
        $this->browser->submitForm('Update', [
            'store_name' => 'Zencart Store',
            'store_owner' => 'Store Owner',
        ]);
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Admin Home', (string)$response->getContent() );
        $this->quickLinksTest();
    }


    public function quickLinksTest()
    {
        foreach ($this->quickTestMap as $page => $contentTest) {
            $pageURI = $this->buildAdminLink($page);
            $this->browser->request('GET', $pageURI);
            $response = $this->browser->getResponse();
            $this->assertEquals(200, $response->getStatusCode());
            foreach ($contentTest['strings'] as $contentString) {
                $this->assertStringContainsString($contentString, (string)$response->getContent() );
            }
        }
    }

}
