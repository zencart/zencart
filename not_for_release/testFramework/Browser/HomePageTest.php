<?php

namespace Tests\Browser;

use Tests\Browser\Pages\AdminHomePage;
use Tests\Browser\Pages\HomePage;
use Tests\Browser\Pages\zcInstallPage;
use Tests\Browser\DuskTestCase;
use Laravel\Dusk\Browser;

class HomePageTest extends DuskTestCase
{
    /** @test */
    public function zcinstall_page_displays()
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(1920, 1080);
            $browser->visit(new zcInstallPage)
                    ->assertSee('System Inspection');
        });
    }

    public function home_page_displays()
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(1920, 1080);
            $browser->visit(new HomePage)
                    ->assertSee('Congratulations!');
        });
    }

    /** @ test */
    public function admin_home_page_displays()
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(1920, 1080);
            $browser->visit(new AdminHomePage)
                    ->assertSee('Admin');
        });
    }
}
