<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\Browser\Traits\ConfigureFileConcerns;
use Tests\Browser\Traits\DatabaseConcerns;

class MultipleAdminPagesTest extends AdminDuskTestCase
{
    protected $pageMap = [
        ['page' => 'home', 'see' => 'Statistics'],
        ['page' => 'admin_account', 'see' => 'Reset Password'],
        ['page' => 'admin_activity', 'see' => 'Admin Activity Log'],
        ['page' => 'admin_page_registration', 'see' => 'Admin Page Registration'],
        ['page' => 'plugin_manager', 'see' => 'Plugin Manager'],
    ];

    /** @test */
    public function visit_multiple_admin_pages()
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(1920, 1080);
            $browser->visit(HTTP_SERVER . '/admin/index.php')
                ->waitFor('#admin_pass')
                ->type('admin_name', 'Admin')
                ->type('#admin_pass', 'develop1')
                ->press('Submit');
        });

        $this->browse(function (Browser $browser) {
            $browser->resize(1920, 1080);
            $browser->visit(HTTP_SERVER . '/admin/');
            $browser->waitFor('#store_name')
                ->type('#store_name', 'zencart')
                ->type('#store_owner', 'zencart')
                ->press('Update')
                ->assertSee('You are presently using')
            ;
        });


        foreach ($this->pageMap as $page) {
            $this->browse(function (Browser $browser) use ($page) {
                $browser->resize(1920, 1080);
                $browser->visit(HTTP_SERVER . '/admin/index.php?cmd=' . $page['page'])
                    ->screenshot('admin_' . $page['page']);
                if (isset($page['see'])) {
                    $browser->assertSee($page['see']);
                }
            });
        }
    }

}
