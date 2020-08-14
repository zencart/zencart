<?php

namespace Tests\Browser;

use Tests\Browser\Pages\AdminHomePage;
use Laravel\Dusk\Browser;
use Tests\Browser\Traits\ConfigureFileConcerns;
use Tests\Browser\Traits\DatabaseConcerns;

class AdminLoginPageTest extends AdminDuskTestCase
{
    use DatabaseConcerns;
    use ConfigureFileConcerns;

    /** @test */
    public function admin_login_page_displays()
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(1920, 1080);
            $browser->visit(new AdminHomePage)
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
            $browser->visit(new AdminHomePage);
            $this->doLogin($browser);
            $browser->waitFor('#store_name')
                ->type('#store_name', 'zencart')
                ->type('#store_owner', 'zencart')
                ->press('Update')
                ->assertSee('Your version of Zen Cart')
            ;
        });
    }
}
