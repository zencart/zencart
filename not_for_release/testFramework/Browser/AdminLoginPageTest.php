<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;

class AdminLoginPageTest extends AdminDuskTestCase
{

    /** @test */
    public function admin_login_page_displays()
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(1920, 1080);
            $browser->visit(HTTP_SERVER . '/admin/')
                ->waitFor('#admin_pass')
                ->assertSee('Admin Login')
            ;
        });
    }

    /** @test */
    public function complete_admin_setup_wizard()
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(1920, 1080);
            $browser->visit(HTTP_SERVER . '/admin/');
            $this->doLogin($browser);
            $browser->waitFor('#store_name')
                ->type('#store_name', 'zencart')
                ->type('#store_owner', 'zencart')
                ->press('Update')
                ->assertSee('You are presently using')
            ;
        });
    }
}
