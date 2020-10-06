<?php

namespace Tests\Browser;

use Tests\Browser\Pages\AdminHomePage;
use Tests\Browser\DuskTestCase;
use Laravel\Dusk\Browser;

class AdminHomePageTest extends DuskTestCase
{
    /** @test */
    public function admin_home_page_displays()
    {
        $this->markTestIncomplete('Incomplete test: requires scripted install before visiting landing page');
        $this->browse(function (Browser $browser) {
            $browser->resize(1920, 1080);
            $browser->visit(new AdminHomePage)
                    ->assertSee('Admin');
        });
    }
}
